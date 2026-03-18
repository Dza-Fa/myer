<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard data.
     */
    public function index(Request $request)
    {
        $data = $this->dashboardService->getDashboardData($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get balance trend for chart.
     */
    public function trend(Request $request)
    {
        $months = $request->get('months', 6);
        $trend = $this->dashboardService->getBalanceTrend($request->user(), $months);

        return response()->json([
            'success' => true,
            'data' => $trend,
        ]);
    }

    /**
     * Get accounts by type summary.
     */
    public function accountsByType(Request $request)
    {
        $summary = $this->dashboardService->getAccountsByType($request->user());

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
