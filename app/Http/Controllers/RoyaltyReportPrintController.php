<?php

namespace App\Http\Controllers;

use App\Models\RoyaltyReport;
use App\Services\RoyaltyCalculationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoyaltyReportPrintController extends Controller
{
    /**
     * Print Royalty Bonus Payout Sheet.
     */
    public function printReport(Request $request, RoyaltyReport $report, ?string $department = null): Response
    {
        $query = $report->items();

        $selectedDept = $department ?? $request->query('department');
        if ($selectedDept && strtolower($selectedDept) !== 'all') {
            $query->where('department', $selectedDept);
        }

        $items = $query->orderBy('department')
            ->orderBy('employee_code')
            ->get();

        $allDepartments = $report->items()
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->toArray();

        return response()->view('royalty.print', [
            'report' => $report,
            'department' => $selectedDept ?: 'All Departments',
            'departments' => $allDepartments,
            'items' => $items,
        ]);
    }

    /**
     * Download Excel Report for Royalty Bonus.
     */
    public function exportExcel(RoyaltyReport $report): StreamedResponse
    {
        return app(RoyaltyCalculationService::class)->exportExcel($report);
    }

    /**
     * Download sample Excel template for Performance Deduction upload.
     */
    public function downloadPerformanceSample(): StreamedResponse
    {
        return app(RoyaltyCalculationService::class)->downloadPerformanceSample();
    }
}
