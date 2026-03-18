<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {}

    /**
     * Display a listing of the user's accounts.
     */
    public function index(Request $request)
    {
        $accounts = $this->accountService->getWithBalances($request->user());

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

        $account = $this->accountService->create($request->user(), $validated);

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

        $account = $this->accountService->update($account, $validated);

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

        $this->accountService->delete($account);

        return response()->json(['success' => true, 'message' => 'Account deleted']);
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

        $account = $this->accountService->reconcile($account);

        return response()->json(['success' => true, 'message' => 'Account reconciled', 'data' => $account]);
    }
}
