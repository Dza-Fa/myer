<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class WebDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $data = $this->dashboardService->getDashboardData($user);
        
        return view('dashboard', [
            'overview' => $data['overview'],
            'accounts' => $data['accounts'],
            'recentTransactions' => $data['recent_transactions'],
            'topExpenses' => $data['top_expenses'],
            'period' => $data['period'],
        ]);
    }
}
