<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard Routes
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/trend', [DashboardController::class, 'trend']);
    Route::get('dashboard/accounts-by-type', [DashboardController::class, 'accountsByType']);
    
    // Account Routes
    Route::apiResource('accounts', AccountController::class);
    Route::get('accounts/{account}/balance-history', [AccountController::class, 'balanceHistory']);
    Route::post('accounts/{account}/reconcile', [AccountController::class, 'reconcile']);
    
    // Category Routes
    Route::apiResource('categories', CategoryController::class);
    Route::post('categories/reorder', [CategoryController::class, 'reorder']);
    
    // Transaction Routes
    Route::apiResource('transactions', TransactionController::class);
    Route::get('transactions-summary', [TransactionController::class, 'summary']);
    Route::get('transactions-by-category', [TransactionController::class, 'byCategory']);
    
    // Budget Routes
    Route::get('budgets', [BudgetController::class, 'index']);
    Route::get('budgets/current', [BudgetController::class, 'current']);
    Route::get('budgets/{year}/{month}', [BudgetController::class, 'show']);
    Route::post('budgets', [BudgetController::class, 'store']);
    Route::delete('budgets/{year}/{month}', [BudgetController::class, 'destroy']);
    
    // Report Routes
    Route::get('reports/monthly', [ReportController::class, 'monthly']);
    Route::get('reports/cashflow', [ReportController::class, 'cashflow']);
    Route::get('reports/category', [ReportController::class, 'category']);
    Route::get('reports/ytd', [ReportController::class, 'ytd']);
    Route::get('reports/comparison', [ReportController::class, 'comparison']);
});
