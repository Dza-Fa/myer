<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

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
            'client_request_id' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'posted'])],
        ]);

        try {
            $transaction = $this->transactionService->create($request->user(), $validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $transaction->load(['account', 'category', 'transferAccount', 'tags', 'ledgerEntries']);

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

        try {
            $transaction = $this->transactionService->update($transaction, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $this->transactionService->delete($transaction);
            return response()->json(['success' => true, 'message' => 'Transaction deleted']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get transaction summary.
     */
    public function summary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $summary = $this->transactionService->getSummary($request->user(), $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get transactions by category.
     */
    public function byCategory(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $byCategory = $this->transactionService->getByCategory($request->user(), $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $byCategory,
        ]);
    }
}
