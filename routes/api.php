<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
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
});
