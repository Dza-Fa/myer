<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService
    ) {}

    /**
     * GET /api/budgets - List budgets
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->input('limit', 12);
        
        $budgets = $this->budgetService->getBudgetList($user, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $budgets,
        ]);
    }

    /**
     * GET /api/budgets/{year}/{month} - Get budget for specific month
     */
    public function show(int $year, int $month): JsonResponse
    {
        $user = Auth::user();
        
        $budget = $this->budgetService->getBudgetWithAlerts($user, $year, $month);
        
        return response()->json([
            'success' => true,
            'data' => $budget,
        ]);
    }

    /**
     * POST /api/budgets - Create/update budget with items
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'nullable|integer|exists:categories,id',
            'items.*.planned_amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        
        $budget = $this->budgetService->getOrCreateBudget(
            $user, 
            $validated['year'], 
            $validated['month']
        );
        
        $budget = $this->budgetService->updateBudgetItems(
            $budget, 
            $validated['items']
        );
        
        $result = $this->budgetService->getBudgetWithAlerts(
            $user, 
            $validated['year'], 
            $validated['month']
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Budget saved successfully',
            'data' => $result,
        ]);
    }

    /**
     * GET /api/budgets/current - Get current month budget
     */
    public function current(): JsonResponse
    {
        $user = Auth::user();
        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');
        
        $budget = $this->budgetService->getBudgetWithAlerts($user, $year, $month);
        
        return response()->json([
            'success' => true,
            'data' => $budget,
        ]);
    }

    /**
     * DELETE /api/budgets/{year}/{month} - Delete budget
     */
    public function destroy(int $year, int $month): JsonResponse
    {
        $user = Auth::user();
        
        $budget = $this->budgetService->getBudget($user, $year, $month);
        
        if (!$budget) {
            return response()->json([
                'success' => false,
                'message' => 'Budget not found',
            ], 404);
        }
        
        $this->budgetService->deleteBudget($budget);
        
        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully',
        ]);
    }
}
