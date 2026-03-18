<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\LedgerEntry;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = $request->user()->transactions()
            ->with(['account', 'category', 'tags'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->get('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->get('account_id')) {
            $query->where('account_id', $request->get('account_id'));
        }

        if ($request->get('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('start_date')) {
            $query->where('transaction_date', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->where('transaction_date', '<=', $request->get('end_date'));
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'account_id' => ['required', 'exists:accounts,id'],
            'transfer_account_id' => ['required_if:type,transfer', 'nullable', 'exists:accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'idempotency_key' => ['nullable', 'string'],
        ]);

        // Validate account ownership
        $account = Account::where('id', $validated['account_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found'], 422);
        }

        // Check idempotency
        if (!empty($validated['idempotency_key'])) {
            $existing = Transaction::where('idempotency_key', $validated['idempotency_key'])
                ->where('user_id', $request->user()->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate transaction',
                    'existing' => $existing,
                ], 409);
            }
        }

        // For transfer, validate second account
        if ($validated['type'] === 'transfer' && !empty($validated['transfer_account_id'])) {
            $transferAccount = Account::where('id', $validated['transfer_account_id'])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$transferAccount) {
                return response()->json(['success' => false, 'message' => 'Transfer account not found'], 422);
            }
        }

        // Create transaction with ledger entries in transaction
        $transaction = DB::transaction(function () use ($request, $validated, $account) {
            $transactionGroupId = \Illuminate\Support\Str::uuid()->toString();

            // Create transaction
            $tx = $request->user()->transactions()->create([
                'idempotency_key' => $validated['idempotency_key'] ?? null,
                'client_request_id' => $request->header('X-Request-ID'),
                'account_id' => $validated['account_id'],
                'transfer_account_id' => $validated['transfer_account_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'posted',
                'posting_date' => now(),
                'transaction_group_id' => $transactionGroupId,
                'version' => 1,
            ]);

            // Create ledger entries (double-entry)
            if ($validated['type'] === 'income') {
                // Income: debit to account (asset increase)
                LedgerEntry::create([
                    'transaction_id' => $tx->id,
                    'account_id' => $validated['account_id'],
                    'debit_amount' => $validated['amount'],
                    'credit_amount' => 0,
                    'transaction_group_id' => $transactionGroupId,
                    'sequence_no' => 1,
                ]);
            } elseif ($validated['type'] === 'expense') {
                // Expense: credit from account (asset decrease)
                LedgerEntry::create([
                    'transaction_id' => $tx->id,
                    'account_id' => $validated['account_id'],
                    'debit_amount' => 0,
                    'credit_amount' => $validated['amount'],
                    'transaction_group_id' => $transactionGroupId,
                    'sequence_no' => 1,
                ]);
            } elseif ($validated['type'] === 'transfer') {
                // Transfer: debit from source, credit to destination
                LedgerEntry::create([
                    'transaction_id' => $tx->id,
                    'account_id' => $validated['account_id'],
                    'debit_amount' => $validated['amount'],
                    'credit_amount' => 0,
                    'transaction_group_id' => $transactionGroupId,
                    'sequence_no' => 1,
                ]);

                LedgerEntry::create([
                    'transaction_id' => $tx->id,
                    'account_id' => $validated['transfer_account_id'],
                    'debit_amount' => 0,
                    'credit_amount' => $validated['amount'],
                    'transaction_group_id' => $transactionGroupId,
                    'sequence_no' => 2,
                ]);
            }

            // Attach tags
            if (!empty($validated['tags'])) {
                $tx->tags()->attach($validated['tags']);
            }

            return $tx;
        });

        $transaction->load(['account', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $transaction->load(['account', 'category', 'transferAccount', 'tags', 'ledgerEntries', 'attachments']);

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Only allow updating draft or posted transactions
        if (in_array($transaction->status, ['locked', 'voided'])) {
            return response()->json(['success' => false, 'message' => 'Cannot update locked/voided transaction'], 422);
        }

        $validated = $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:1'],
            'account_id' => ['sometimes', 'exists:accounts,id'],
            'transfer_account_id' => ['nullable', 'exists:accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'transaction_date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'posted'])],
            'tags' => ['nullable', 'array'],
        ]);

        // Verify account ownership
        if (isset($validated['account_id'])) {
            $account = Account::where('id', $validated['account_id'])
                ->where('user_id', $request->user()->id)
                ->first();
            
            if (!$account) {
                return response()->json(['success' => false, 'message' => 'Account not found'], 422);
            }
        }

        $transaction->update($validated);

        // Update tags if provided
        if (isset($validated['tags'])) {
            $transaction->tags()->sync($validated['tags']);
        }

        $transaction->load(['account', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => $transaction,
        ]);
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($transaction->status === 'locked') {
            return response()->json(['success' => false, 'message' => 'Cannot delete locked transaction'], 422);
        }

        // Soft delete if has ledger entries
        if ($transaction->ledgerEntries()->count() > 0) {
            $transaction->delete();
            return response()->json(['success' => true, 'message' => 'Transaction deleted (has entries)']);
        }

        $transaction->forceDelete();

        return response()->json(['success' => true, 'message' => 'Transaction deleted permanently']);
    }

    /**
     * Get transaction summary.
     */
    public function summary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $transactions = $request->user()->transactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'posted');

        $income = (float) $transactions->clone()->where('type', 'income')->sum('amount');
        $expense = (float) $transactions->clone()->where('type', 'expense')->sum('amount');
        $transfer = (float) $transactions->clone()->where('type', 'transfer')->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'total_income' => $income,
                'total_expense' => $expense,
                'total_transfer' => $transfer,
                'net_savings' => $income - $expense,
            ],
        ]);
    }

    /**
     * Get transactions by category.
     */
    public function byCategory(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $byCategory = $request->user()->transactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'posted')
            ->whereIn('type', ['income', 'expense'])
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
            ->values();

        return response()->json([
            'success' => true,
            'data' => $byCategory,
        ]);
    }
}
