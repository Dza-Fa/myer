<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Str;

class AccountService
{
    /**
     * Create a new account.
     */
    public function create(User $user, array $data): Account
    {
        return $user->accounts()->create([
            'uuid' => Str::uuid()->toString(),
            'name' => $data['name'],
            'type' => $data['type'],
            'initial_balance' => $data['initial_balance'] ?? 0,
            'currency' => $data['currency'] ?? 'IDR',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an account.
     */
    public function update(Account $account, array $data): Account
    {
        $account->update($data);
        return $account->fresh();
    }

    /**
     * Delete an account.
     */
    public function delete(Account $account): bool
    {
        if ($account->transactions()->count() > 0) {
            $account->delete();
            return true;
        }

        $account->forceDelete();
        return true;
    }

    /**
     * Reconcile account.
     */
    public function reconcile(Account $account): Account
    {
        $account->update(['last_reconciled_at' => now()]);
        return $account->fresh();
    }

    /**
     * Get accounts with balances.
     */
    public function getWithBalances(User $user)
    {
        return $user->accounts()
            ->withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function ($account) {
                $account->balance = $account->calculateBalance();
                return $account;
            });
    }

    /**
     * Get total balance across all accounts.
     */
    public function getTotalBalance(User $user): float
    {
        return $user->accounts()
            ->get()
            ->sum(fn($account) => $account->calculateBalance());
    }
}
