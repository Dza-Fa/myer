<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get dashboard data for user.
     */
    public function getDashboardData(User $user): array
    {
        $accounts = $user->accounts()->where('is_active', true)->get();
        
        $totalBalance = $accounts->sum(fn($account) => $account->calculateBalance());
        
        $thisMonth = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();
        
        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$thisMonth, $thisMonthEnd])
            ->where('status', 'posted');
        
        $monthlyIncome = (float) $transactions->clone()->where('type', 'income')->sum('amount');
        $monthlyExpense = (float) $transactions->clone()->where('type', 'expense')->sum('amount');
        
        $recentTransactions = $user->transactions()
            ->with(['account', 'category'])
            ->where('status', 'posted')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $accountsWithBalance = $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'currency' => $account->currency,
                'balance' => $account->calculateBalance(),
            ];
        });
        
        $topExpenses = $user->transactions()
            ->whereBetween('transaction_date', [$thisMonth, $thisMonthEnd])
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->with('category:id,name,color')
            ->get()
            ->groupBy('category_id')
            ->map(function ($items, $categoryId) {
                return [
                    'category_id' => $categoryId,
                    'category_name' => $items->first()->category?->name ?? 'Uncategorized',
                    'category_color' => $items->first()->category?->color ?? '#666666',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();
        
        return [
            'overview' => [
                'total_balance' => $totalBalance,
                'monthly_income' => $monthlyIncome,
                'monthly_expense' => $monthlyExpense,
                'monthly_savings' => $monthlyIncome - $monthlyExpense,
                'savings_rate' => $monthlyIncome > 0 ? round(($monthlyIncome - $monthlyExpense) / $monthlyIncome * 100, 1) : 0,
            ],
            'accounts' => $accountsWithBalance,
            'recent_transactions' => $recentTransactions,
            'top_expenses' => $topExpenses,
            'period' => [
                'month' => now()->format('F Y'),
                'start' => $thisMonth->format('Y-m-d'),
                'end' => $thisMonthEnd->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Get balance trend for chart.
     */
    public function getBalanceTrend(User $user, int $months = 6): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->startOfMonth()->format('Y-m-d');
            $end = $month->endOfMonth()->format('Y-m-d');
            
            $transactions = $user->transactions()
                ->whereBetween('transaction_date', [$start, $end])
                ->where('status', 'posted');
            
            $income = (float) $transactions->clone()->where('type', 'income')->sum('amount');
            $expense = (float) $transactions->clone()->where('type', 'expense')->sum('amount');
            
            $data[] = [
                'month' => $month->format('M Y'),
                'month_num' => $month->format('n'),
                'year' => $month->format('Y'),
                'income' => $income,
                'expense' => $expense,
                'savings' => $income - $expense,
            ];
        }
        
        return $data;
    }

    /**
     * Get accounts summary by type.
     */
    public function getAccountsByType(User $user): array
    {
        $accounts = $user->accounts()->where('is_active', true)->get();
        
        return $accounts->groupBy('type')
            ->map(function (Collection $group, string $type) {
                return [
                    'type' => $type,
                    'count' => $group->count(),
                    'total_balance' => $group->sum(fn($account) => $account->calculateBalance()),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get category chart data for pie chart.
     */
    public function getCategoryChartData(User $user, int $year = null, int $month = null): array
    {
        $year = $year ?? (int) now()->format('Y');
        $month = $month ?? (int) now()->format('n');
        
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $expenses = $user->transactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->with('category:id,name,color')
            ->get()
            ->groupBy('category_id')
            ->map(function ($items, $categoryId) {
                return [
                    'category_id' => $categoryId,
                    'category_name' => $items->first()->category?->name ?? 'Uncategorized',
                    'category_color' => $items->first()->category?->color ?? '#666666',
                    'total' => (float) $items->sum('amount'),
                    'percentage' => 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalExpense = $expenses->sum('total');
        
        return $expenses->map(function ($item) use ($totalExpense) {
            $item['percentage'] = $totalExpense > 0 
                ? round(($item['total'] / $totalExpense) * 100, 1) 
                : 0;
            return $item;
        })->toArray();
    }

    /**
     * Get cashflow chart data (income vs expense by month).
     */
    public function getCashflowChartData(User $user, int $months = 6): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->startOfMonth()->format('Y-m-d');
            $end = $month->endOfMonth()->format('Y-m-d');
            
            $transactions = $user->transactions()
                ->whereBetween('transaction_date', [$start, $end])
                ->where('status', 'posted');
            
            $income = (float) $transactions->clone()->where('type', 'income')->sum('amount');
            $expense = (float) $transactions->clone()->where('type', 'expense')->sum('amount');
            
            $data[] = [
                'month' => $month->format('M'),
                'month_full' => $month->format('F Y'),
                'income' => $income,
                'expense' => $expense,
                'savings' => $income - $expense,
            ];
        }
        
        return $data;
    }
}
