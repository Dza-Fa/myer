<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * GET /api/reports/monthly - Monthly report
     */
    public function monthly(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->input('year', now()->format('Y'));
        $month = (int) $request->input('month', now()->format('n'));

        $report = $this->reportService->getMonthlyReport($user, $year, $month);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * GET /api/reports/cashflow - Cashflow report
     */
    public function cashflow(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->input('year', now()->format('Y'));
        $startMonth = (int) $request->input('start_month', 1);
        $endMonth = (int) $request->input('end_month', 12);

        $report = $this->reportService->getCashflowReport($user, $year, $startMonth, $endMonth);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * GET /api/reports/category - Category report
     */
    public function category(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->input('year', now()->format('Y'));
        $month = $request->input('month') ? (int) $request->input('month') : null;

        $report = $this->reportService->getCategoryReport($user, $year, $month);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * GET /api/reports/ytd - Year to date report
     */
    public function ytd(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->input('year', now()->format('Y'));

        $report = $this->reportService->getYTDReport($user, $year);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * GET /api/reports/comparison - Compare two months
     */
    public function comparison(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->input('year', now()->format('Y'));
        $month1 = (int) $request->input('month1', 1);
        $month2 = (int) $request->input('month2', 2);

        $report = $this->reportService->getComparisonReport($user, $year, $month1, $month2);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
