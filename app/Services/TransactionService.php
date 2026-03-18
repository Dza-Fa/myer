<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\LedgerEntry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Create a new transaction with ledger entries.
     */
    public function create(User $user, array $data): Transaction
    {
        // Validate account ownership
        $account = Account::where('id', $data['account_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check idempotency
        if (!empty($data['idempotency_key'])) {
            $existing = Transaction::where('idempotency_key', $data['idempotency_key'])
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw new \InvalidArgumentException('Duplicate transaction');
            }
        }

        // Validate transfer account
        if ($data['type'] === 'transfer' && !empty($data['transfer_account_id'])) {
            $transferAccount = Account::where('id', $data['transfer_account_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        // Use DB transaction for atomicity
        return DB::transaction(function () use ($user, $data, $account) {
            $transactionGroupId = Str::uuid()->toString();

            // Create transaction
            $tx = $user->transactions()->create([
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'client_request_id' => $data['client_request_id'] ?? null,
                'account_id' => $data['account_id'],
                'transfer_account_id' => $data['transfer_account_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'posted',
                'posting_date' => $data['status'] === 'draft' ? null : now(),
                'transaction_group_id' => $transactionGroupId,
                'version' => 1,
            ]);

            // Only create ledger entries if posted
            if ($tx->status === 'posted') {
                $this->createLedgerEntries($tx, $data['type'], $data['amount'], $transactionGroupId);
            }

            // Attach tags
            if (!empty($data['tags'])) {
                $tx->tags()->attach($data['tags']);
            }

            return $tx->fresh(['account', 'category', 'tags']);
        }, 5); // 5 retries for deadlocks
    }

    /**
     * Update a transaction.
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        if (in_array($transaction->status, ['locked', 'voided'])) {
            throw new \InvalidArgumentException('Cannot update locked/voided transaction');
        }

        // Lock the transaction row for update
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update($data);

            // Update tags if provided
            if (isset($data['tags'])) {
                $transaction->tags()->sync($data['tags']);
            }

            // If amount or type changed, update ledger entries
            if (isset($data['amount']) || isset($data['type']) || isset($data['account_id'])) {
                // Delete old entries
                $transaction->ledgerEntries()->delete();

                // Create new entries if posted
                if ($transaction->status === 'posted') {
                    $this->createLedgerEntries(
                        $transaction,
                        $transaction->type,
                        $transaction->amount,
                        $transaction->transaction_group_id
                    );
                }
            }

            return $transaction->fresh(['account', 'category', 'tags', 'ledgerEntries']);
        }, 5);
    }

    /**
     * Delete a transaction.
     */
    public function delete(Transaction $transaction): bool
    {
        if ($transaction->status === 'locked') {
            throw new \InvalidArgumentException('Cannot delete locked transaction');
        }

        return DB::transaction(function () use ($transaction) {
            // Delete ledger entries first
            $transaction->ledgerEntries()->delete();
            
            // Delete tags
            $transaction->tags()->detach();

            // Soft or hard delete
            if ($transaction->ledgerEntries()->withTrashed()->count() > 0) {
                $transaction->delete();
            } else {
                $transaction->forceDelete();
            }

            return true;
        }, 5);
    }

    /**
     * Reverse a transaction.
     */
    public function reverse(Transaction $transaction): Transaction
    {
        if ($transaction->is_reversal) {
            throw new \InvalidArgumentException('Transaction is already a reversal');
        }

        if ($transaction->status !== 'posted') {
            throw new \InvalidArgumentException('Can only reverse posted transactions');
        }

        return DB::transaction(function () use ($transaction) {
            // Create reversal transaction
            $reversal = $transaction->user->transactions()->create([
                'account_id' => $transaction->account_id,
                'transfer_account_id' => $transaction->transfer_account_id,
                'category_id' => $transaction->category_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'transaction_date' => now()->toDateString(),
                'notes' => 'Reversal of transaction #' . $transaction->id,
                'status' => 'posted',
                'posting_date' => now(),
                'reversal_of_id' => $transaction->id,
                'is_reversal' => true,
                'transaction_group_id' => Str::uuid()->toString(),
                'version' => 1,
            ]);

            // Create reversal ledger entries
            $this->createLedgerEntries(
                $reversal,
                $reversal->type,
                $reversal->amount,
                $reversal->transaction_group_id
            );

            return $reversal->fresh(['account', 'category']);
        }, 5);
    }

    /**
     * Get transaction summary.
     */
    public function getSummary(User $user, string $startDate, string $endDate): array
    {
        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'posted');

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_income' => (float) $transactions->clone()->where('type', 'income')->sum('amount'),
            'total_expense' => (float) $transactions->clone()->where('type', 'expense')->sum('amount'),
            'total_transfer' => (float) $transactions->clone()->where('type', 'transfer')->sum('amount'),
            'net_savings' => (float) $transactions->clone()->where('type', 'income')->sum('amount') 
                           - (float) $transactions->clone()->where('type', 'expense')->sum('amount'),
        ];
    }

    /**
     * Get transactions by category.
     */
    public function getByCategory(User $user, string $startDate, string $endDate): array
    {
        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'posted')
            ->whereIn('type', ['income', 'expense'])
            ->with('category:id,name,color')
            ->get()
            ->groupBy('category_id')
            ->map(function ($items) {
                return [
                    'category_id' => $items->first()->category_id,
                    'category_name' => $items->first()->category?->name ?? 'Uncategorized',
                    'category_color' => $items->first()->category?->color ?? '#666666',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();

        return $transactions;
    }

    /**
     * Create ledger entries for a transaction.
     */
    private function createLedgerEntries(Transaction $transaction, string $type, float $amount, string $groupId): void
    {
        if ($type === 'income') {
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->account_id,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'transaction_group_id' => $groupId,
                'sequence_no' => 1,
            ]);
        } elseif ($type === 'expense') {
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->account_id,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'transaction_group_id' => $groupId,
                'sequence_no' => 1,
            ]);
        } elseif ($type === 'transfer' && $transaction->transfer_account_id) {
            // Debit from source
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->account_id,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'transaction_group_id' => $groupId,
                'sequence_no' => 1,
            ]);

            // Credit to destination
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->transfer_account_id,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'transaction_group_id' => $groupId,
                'sequence_no' => 2,
            ]);
        }
    }
}
