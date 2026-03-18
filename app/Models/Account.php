<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'type',
        'initial_balance',
        'currency',
        'last_reconciled_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_reconciled_at' => 'datetime',
            'is_active' => 'boolean',
            'initial_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'account_categories');
    }

    /**
     * Calculate current account balance.
     * Balance = Initial Balance + Total Income - Total Expense
     * For credit accounts: Balance = Initial Balance - Total Spending + Total Payment
     */
    public function calculateBalance(): float
    {
        $ledgerEntries = $this->ledgerEntries()
            ->selectRaw('
                COALESCE(SUM(debit_amount), 0) as total_debit,
                COALESCE(SUM(credit_amount), 0) as total_credit
            ')
            ->first();

        $ledgerBalance = (float) $ledgerEntries->total_credit - (float) $ledgerEntries->total_debit;

        return (float) $this->initial_balance + $ledgerBalance;
    }

    /**
     * Get balance history for chart.
     */
    public function getBalanceHistory(int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $entries = $this->ledgerEntries()
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($entry) {
                return $entry->created_at->format('Y-m-d');
            });

        $runningBalance = (float) $this->initial_balance;
        $history = [];
        $currentDate = now()->copy()->subDays($days)->startOfDay();
        
        while ($currentDate->lte(now())) {
            $dateKey = $currentDate->format('Y-m-d');
            
            if (isset($entries[$dateKey])) {
                foreach ($entries[$dateKey] as $entry) {
                    $runningBalance += (float) $entry->credit_amount - (float) $entry->debit_amount;
                }
            }
            
            $history[] = [
                'date' => $dateKey,
                'balance' => round($runningBalance, 2),
            ];
            
            $currentDate->addDay();
        }
        
        return $history;
    }

    /**
     * Get total income for this account.
     */
    public function getTotalIncome(): float
    {
        return (float) $this->transactions()
            ->where('type', 'income')
            ->where('status', 'posted')
            ->sum('amount');
    }

    /**
     * Get total expense for this account.
     */
    public function getTotalExpense(): float
    {
        return (float) $this->transactions()
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->sum('amount');
    }
}
