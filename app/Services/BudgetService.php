<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class BudgetService
{
    public function getBudget(User $user, int $year, int $month): ?Budget
    {
        return $user->budgets()
            ->with(['items.category'])
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    public function getOrCreateBudget(User $user, int $year, int $month): Budget
    {
        $budget = $this->getBudget($user, $year, $month);

        if (!$budget) {
            $budget = $user->budgets()->create([
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $budget->load('items.category');
    }

    public function updateBudgetItems(Budget $budget, array $items): Budget
    {
        $existingIds = $budget->items()->pluck('id')->toArray();
        $newIds = [];

        foreach ($items as $item) {
            if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                $budgetItem = BudgetItem::find($item['id']);
                $budgetItem->update([
                    'category_id' => $item['category_id'] ?? null,
                    'planned_amount' => $item['planned_amount'] ?? 0,
                ]);
                $newIds[] = $item['id'];
            } else {
                $budgetItem = $budget->items()->create([
                    'category_id' => $item['category_id'] ?? null,
                    'planned_amount' => $item['planned_amount'] ?? 0,
                ]);
                $newIds[] = $budgetItem->id;
            }
        }

        $toDelete = array_diff($existingIds, $newIds);
        if (!empty($toDelete)) {
            BudgetItem::destroy($toDelete);
        }

        return $budget->load('items.category');
    }

    public function syncActualSpending(Budget $budget): Budget
    {
        $startDate = sprintf('%04d-%02d-01', $budget->year, $budget->month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $transactions = Transaction::where('user_id', $budget->user_id)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get()
            ->groupBy('category_id');

        foreach ($budget->items as $item) {
            $spent = $transactions->get($item->category_id)?->sum('amount') ?? 0;
            $item->update([
                'actual_amount' => $spent,
                'last_synced_at' => now(),
            ]);
        }

        return $budget->load('items.category');
    }

    public function getBudgetWithAlerts(User $user, int $year, int $month): array
    {
        $budget = $this->getOrCreateBudget($user, $year, $month);
        $this->syncActualSpending($budget);

        $items = $budget->items->map(function ($item) {
            $remaining = $item->planned_amount - $item->actual_amount;
            $percentage = $item->planned_amount > 0 
                ? round(($item->actual_amount / $item->planned_amount) * 100, 1) 
                : 0;

            $status = 'ok';
            if ($percentage >= 100) {
                $status = 'exceeded';
            } elseif ($percentage >= 80) {
                $status = 'warning';
            }

            return [
                'id' => $item->id,
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name ?? 'Uncategorized',
                'category_color' => $item->category?->color ?? '#666666',
                'planned_amount' => (float) $item->planned_amount,
                'actual_amount' => (float) $item->actual_amount,
                'remaining' => (float) $remaining,
                'percentage' => $percentage,
                'status' => $status,
            ];
        });

        $totalPlanned = $items->sum('planned_amount');
        $totalActual = $items->sum('actual_amount');
        $totalRemaining = $totalPlanned - $totalActual;
        $totalPercentage = $totalPlanned > 0 
            ? round(($totalActual / $totalPlanned) * 100, 1) 
            : 0;

        $alerts = $items->filter(fn($item) => $item['status'] !== 'ok')
            ->values()
            ->toArray();

        return [
            'budget' => [
                'id' => $budget->id,
                'year' => $budget->year,
                'month' => $budget->month,
                'month_name' => $this->getMonthName($budget->month) . ' ' . $budget->year,
            ],
            'items' => $items,
            'summary' => [
                'total_planned' => $totalPlanned,
                'total_actual' => $totalActual,
                'total_remaining' => $totalRemaining,
                'total_percentage' => $totalPercentage,
            ],
            'alerts' => $alerts,
        ];
    }

    public function getBudgetList(User $user, int $limit = 12): Collection
    {
        return $user->budgets()
            ->with(['items.category'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit($limit)
            ->get()
            ->map(function ($budget) {
                $totalPlanned = $budget->items->sum('planned_amount');
                $totalActual = $budget->items->sum('actual_amount');

                return [
                    'id' => $budget->id,
                    'year' => $budget->year,
                    'month' => $budget->month,
                    'month_name' => $this->getMonthName($budget->month) . ' ' . $budget->year,
                    'total_planned' => $totalPlanned,
                    'total_actual' => $totalActual,
                    'total_remaining' => $totalPlanned - $totalActual,
                    'percentage' => $totalPlanned > 0 
                        ? round(($totalActual / $totalPlanned) * 100, 1) 
                        : 0,
                ];
            });
    }

    public function deleteBudget(Budget $budget): bool
    {
        return $budget->delete();
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
