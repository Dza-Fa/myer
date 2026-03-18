<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use Illuminate\Http\Request;

class WebBudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        
        $year = (int) $request->get('year', now()->format('Y'));
        $month = (int) $request->get('month', now()->format('n'));
        
        $budgetData = $this->budgetService->getBudgetWithAlerts($user, $year, $month);
        $budgetList = $this->budgetService->getBudgetList($user, 12);
        
        $categories = $user->categories()->where('type', 'expense')->get();
        
        return view('budgets.index', compact('budgetData', 'budgetList', 'categories', 'year', 'month'));
    }
}
