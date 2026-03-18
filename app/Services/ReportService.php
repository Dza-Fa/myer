<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get monthly report for a specific month/year.
     */
    public function getMonthlyReport(User $user, int $year, int $month): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'posted')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');
        $savings = $income - $expense;
        $savingsRate = $income > 0 ? round(($savings / $income) * 100, 1) : 0;

        // Daily breakdown
        $dailyData = $transactions->groupBy(function ($tx) {
            return $tx->transaction_date->format('Y-m-d');
        })->map(function ($dayTransactions, $date) {
            return [
                'date' => $date,
                'income' => $dayTransactions->where('type', 'income')->sum('amount'),
                'expense' => $dayTransactions->where('type', 'expense')->sum('amount'),
            ];
        })->sortBy('date')->values();

        // Account breakdown
        $accountData = $transactions->groupBy('account_id')
            ->map(function ($txs, $accountId) {
                $account = $txs->first()->account;
                return [
                    'account_id' => $accountId,
                    'account_name' => $account?->name ?? 'Unknown',
                    'income' => $txs->where('type', 'income')->sum('amount'),
                    'expense' => $txs->where('type', 'expense')->sum('amount'),
                ];
            })->values();

        // Category breakdown (expense only)
        $categoryData = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($txs, $categoryId) {
                $category = $txs->first()->category;
                return [
                    'category_id' => $categoryId,
                    'category_name' => $category?->name ?? 'Uncategorized',
                    'category_color' => $category?->color ?? '#666666',
                    'total' => $txs->sum('amount'),
                    'count' => $txs->count(),
                ];
            })->sortByDesc('total')->values();

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $this->getMonthName($month) . ' ' . $year,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_income' => (float) $income,
                'total_expense' => (float) $expense,
                'net_savings' => (float) $savings,
                'savings_rate' => $savingsRate,
                'transaction_count' => $transactions->count(),
            ],
            'daily' => $dailyData,
            'by_account' => $accountData,
            'by_category' => $categoryData,
        ];
    }

    /**
     * Get cashflow report (income vs expense over time).
     */
    public function getCashflowReport(User $user, int $year, int $startMonth = 1, int $endMonth = 12): array
    {
        $data = [];

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $transactions = $user->transactions()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('status', 'posted');

            $income = (float) $transactions->clone()->where('type', 'income')->sum('amount');
            $expense = (float) $transactions->clone()->where('type', 'expense')->sum('amount');

            $data[] = [
                'year' => $year,
                'month' => $month,
                'month_name' => $this->getMonthName($month),
                'income' => $income,
                'expense' => $expense,
                'savings' => $income - $expense,
                'savings_rate' => $income > 0 ? round((($income - $expense) / $income) * 100, 1) : 0,
            ];
        }

        $totalIncome = array_sum(array_column($data, 'income'));
        $totalExpense = array_sum(array_column($data, 'expense'));
        $totalSavings = $totalIncome - $totalExpense;

        return [
            'year' => $year,
            'monthly' => $data,
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'total_savings' => $totalSavings,
                'average_income' => count($data) > 0 ? $totalIncome / count($data) : 0,
                'average_expense' => count($data) > 0 ? $totalExpense / count($data) : 0,
            ],
        ];
    }

    /**
     * Get category report (expense breakdown by category).
     */
    public function getCategoryReport(User $user, int $year, int $month = null): array
    {
        if ($month) {
            // Single month report
            return $this->getMonthlyReport($user, $year, $month)['by_category'];
        }

        // Full year report
        $startDate = sprintf('%04d-01-01', $year);
        $endDate = sprintf('%04d-12-31', $year);

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->get();

        $byCategory = $transactions->groupBy('category_id')
            ->map(function ($txs, $categoryId) use ($year) {
                $category = $txs->first()->category;
                $monthlyData = $txs->groupBy(function ($tx) {
                    return $tx->transaction_date->format('n');
                })->map(function ($monthTx, $month) {
                    return [
                        'month' => (int) $month,
                        'total' => (float) $monthTx->sum('amount'),
                    ];
                })->sortBy('month')->values();

                return [
                    'category_id' => $categoryId,
                    'category_name' => $category?->name ?? 'Uncategorized',
                    'category_color' => $category?->color ?? '#666666',
                    'total' => (float) $txs->sum('amount'),
                    'count' => $txs->count(),
                    'monthly' => $monthlyData,
                ];
            })->sortByDesc('total')->values();

        $totalExpense = $byCategory->sum('total');

        return [
            'year' => $year,
            'categories' => $byCategory,
            'summary' => [
                'total_expense' => $totalExpense,
                'category_count' => $byCategory->count(),
                'average_monthly' => $totalExpense / 12,
            ],
        ];
    }

    /**
     * Get year-to-date report.
     */
    public function getYTDReport(User $user, int $year): array
    {
        $currentMonth = (int) now()->format('n');
        return $this->getCashflowReport($user, $year, 1, $currentMonth);
    }

    /**
     * Compare months report.
     */
    public function getComparisonReport(User $user, int $year, int $month1, int $month2): array
    {
        $report1 = $this->getMonthlyReport($user, $year, $month1);
        $report2 = $this->getMonthlyReport($user, $year, $month2);

        return [
            'period_1' => $report1['period'],
            'period_2' => $report2['period'],
            'comparison' => [
                'income_change' => $report2['summary']['total_income'] - $report1['summary']['total_income'],
                'expense_change' => $report2['summary']['total_expense'] - $report1['summary']['total_expense'],
                'savings_change' => $report2['summary']['net_savings'] - $report1['summary']['net_savings'],
            ],
            'period_1_summary' => $report1['summary'],
            'period_2_summary' => $report2['summary'],
        ];
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $months[$month] ?? '';
    }
}
