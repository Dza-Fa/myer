<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Account Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('accounts', AccountController::class);
    Route::get('accounts/{account}/balance-history', [AccountController::class, 'balanceHistory']);
    Route::post('accounts/{account}/reconcile', [AccountController::class, 'reconcile']);
    
    // Category Routes
    Route::apiResource('categories', CategoryController::class);
    Route::post('categories/reorder', [CategoryController::class, 'reorder']);
});
