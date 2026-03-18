<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;

class WebTransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'all');
        $categoryId = $request->get('category_id');
        $accountId = $request->get('account_id');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->when($type !== 'all', fn($q) => $q->where('type', $type))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);
        
        $accounts = $user->accounts()->where('is_active', true)->get();
        $categories = $user->categories()->where('type', $type === 'all' ? 'expense' : $type)->get();
        
        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }
}
