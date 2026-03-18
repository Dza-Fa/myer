<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Display a listing of the user's accounts.
     */
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->accounts()
            ->withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function ($account) {
                $account->balance = $account->calculateBalance();
                return $account;
            });

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['cash', 'bank', 'ewallet', 'investment', 'credit'])],
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3', 'uppercase'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $account = $request->user()->accounts()->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'currency' => $validated['currency'] ?? 'IDR',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $account,
        ], 201);
    }

    /**
     * Display the specified account.
     */
    public function show(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $account->load(['transactions' => fn($q) => $q->latest()->limit(10)]);
        $account->balance = $account->calculateBalance();

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }

    /**
     * Update the specified account.
     */
    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'type' => ['sometimes', Rule::in(['cash', 'bank', 'ewallet', 'investment', 'credit'])],
            'currency' => ['sometimes', 'string', 'size:3', 'uppercase'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'data' => $account,
        ]);
    }

    /**
     * Remove the specified account.
     */
    public function destroy(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($account->transactions()->count() > 0) {
            $account->delete();
            return response()->json(['success' => true, 'message' => 'Account deleted (soft delete)']);
        }

        $account->forceDelete();

        return response()->json(['success' => true, 'message' => 'Account deleted permanently']);
    }

    /**
     * Get account balance history.
     */
    public function balanceHistory(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $days = $request->get('days', 30);
        $history = $account->getBalanceHistory($days);

        return response()->json(['success' => true, 'data' => $history]);
    }

    /**
     * Reconcile account.
     */
    public function reconcile(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $account->update(['last_reconciled_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Account reconciled', 'data' => $account]);
    }
}
