<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebDashboardController;
use App\Http\Controllers\WebAccountController;
use App\Http\Controllers\WebCategoryController;
use App\Http\Controllers\WebTransactionController;
use App\Http\Controllers\WebBudgetController;
use App\Http\Controllers\WebReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [WebDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/accounts', [WebAccountController::class, 'index'])->name('accounts.index');
    Route::get('/categories', [WebCategoryController::class, 'index'])->name('categories.index');
    Route::get('/transactions', [WebTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/budgets', [WebBudgetController::class, 'index'])->name('budgets.index');
    
    // Reports
    Route::get('/reports', [WebReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly', [WebReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/cashflow', [WebReportController::class, 'cashflow'])->name('reports.cashflow');
    Route::get('/reports/category', [WebReportController::class, 'category'])->name('reports.category');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
