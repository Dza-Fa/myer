<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class WebReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->format('Y'));
        $month = (int) $request->get('month', now()->format('n'));

        $monthlyReport = $this->reportService->getMonthlyReport($user, $year, $month);
        $cashflowReport = $this->reportService->getCashflowReport($user, $year, 1, 12);
        $categoryReport = $this->reportService->getCategoryReport($user, $year);

        return view('reports.index', compact('monthlyReport', 'cashflowReport', 'categoryReport', 'year', 'month'));
    }

    public function monthly(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->format('Y'));
        $month = (int) $request->get('month', now()->format('n'));

        $report = $this->reportService->getMonthlyReport($user, $year, $month);

        return view('reports.monthly', compact('report', 'year', 'month'));
    }

    public function cashflow(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->format('Y'));

        $report = $this->reportService->getCashflowReport($user, $year, 1, 12);

        return view('reports.cashflow', compact('report', 'year'));
    }

    public function category(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->format('Y'));

        $report = $this->reportService->getCategoryReport($user, $year);

        return view('reports.category', compact('report', 'year'));
    }
}
