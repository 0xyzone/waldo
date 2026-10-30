<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\RoyaltyReport;
use App\Models\RoyaltyReportItem;
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
     * Print Individual Employee Held Balance Sheet.
     */
    public function printEmployeeHeldBalance(string $employeeCode): Response
    {
        $employee = Employee::query()
            ->with(['department', 'designation', 'royaltyReleases' => fn ($q) => $q->latest()])
            ->where('employee_code', $employeeCode)
            ->firstOrFail();

        $heldItems = RoyaltyReportItem::query()
            ->with('report')
            ->where('employee_code', $employeeCode)
            ->where(function ($q) {
                $q->where('payout_status', 'Hold')
                    ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
            })
            ->join('royalty_reports', 'royalty_report_items.royalty_report_id', '=', 'royalty_reports.id')
            ->orderByDesc('royalty_reports.year')
            ->orderByDesc('royalty_reports.month')
            ->select('royalty_report_items.*')
            ->get();

        $totalHeld = (float) $heldItems->sum(function ($item) {
            if ($item->payout_status === 'Hold') {
                return (float) $item->monthly_max_payout;
            }

            return max(0, (float) $item->monthly_max_payout - (float) $item->final_payout);
        });

        $totalReleased = (float) $employee->royaltyReleases->sum('amount');
        $remainingBalance = max(0, $totalHeld - $totalReleased);

        return response()->view('royalty.print-held-balance', [
            'employee' => $employee,
            'heldItems' => $heldItems,
            'totalHeld' => $totalHeld,
            'totalReleased' => $totalReleased,
            'remainingBalance' => $remainingBalance,
        ]);
    }

    /**
     * Print Summary Sheet of All Held Balances.
     */
    public function printHeldBalancesList(Request $request): Response
    {
        $query = Employee::query()
            ->with(['department', 'designation', 'royaltyReleases'])
            ->where(function ($q) {
                $q->whereHas('royaltyReportItems', function ($itemQ) {
                    $itemQ->where('payout_status', 'Hold')
                        ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
                })
                    ->orWhereHas('royaltyReleases');
            });

        $selectedDept = $request->query('department');
        if ($selectedDept && strtolower($selectedDept) !== 'all') {
            $query->whereHas('department', function ($dq) use ($selectedDept) {
                $dq->where('name', $selectedDept);
            });
        }

        $employees = $query->orderBy('name')->get();

        $records = $employees->map(function ($employee) {
            $heldItems = RoyaltyReportItem::query()
                ->where('employee_code', $employee->employee_code)
                ->where(function ($q) {
                    $q->where('payout_status', 'Hold')
                        ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
                })
                ->get();

            $totalHeld = (float) $heldItems->sum(function ($item) {
                if ($item->payout_status === 'Hold') {
                    return (float) $item->monthly_max_payout;
                }

                return max(0, (float) $item->monthly_max_payout - (float) $item->final_payout);
            });

            $totalReleased = (float) $employee->royaltyReleases->sum('amount');
            $remainingBalance = max(0, $totalHeld - $totalReleased);

            return [
                'employee' => $employee,
                'total_held' => $totalHeld,
                'total_released' => $totalReleased,
                'remaining_balance' => $remainingBalance,
                'items_count' => $heldItems->count(),
                'has_full_hold' => $heldItems->contains(fn ($it) => $it->payout_status === 'Hold'),
            ];
        })->filter(fn ($r) => $r['total_held'] > 0 || $r['total_released'] > 0);

        return response()->view('royalty.print-held-balances-list', [
            'records' => $records,
            'department' => $selectedDept ?: 'All Departments',
        ]);
    }

    /**
     * Print Royalty Budget Balance Overview Sheet.
     */
    public function printBalanceOverview(Request $request): Response
    {
        $reportId = ($request->query('report') && is_numeric($request->query('report')))
            ? (int) $request->query('report')
            : null;

        $department = ($request->query('department') && strtolower($request->query('department')) !== 'all')
            ? $request->query('department')
            : null;

        $report = $reportId ? RoyaltyReport::find($reportId) : null;
        $records = app(RoyaltyCalculationService::class)->getBalanceOverviewRecords($reportId, $department);

        return response()->view('royalty.print-balance-overview', [
            'report' => $report,
            'department' => $department ?: 'All Departments',
            'records' => $records,
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

    /**
     * Download Excel Report for Royalty Budget Balance Overview.
     */
    public function exportBalanceOverview(Request $request): StreamedResponse
    {
        $reportId = $request->query('report') ? (int) $request->query('report') : null;
        $department = $request->query('department');

        return app(RoyaltyCalculationService::class)->exportBalanceOverviewExcel($reportId, $department);
    }
}
