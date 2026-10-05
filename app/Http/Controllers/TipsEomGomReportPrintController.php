<?php

namespace App\Http\Controllers;

use App\Models\TipsYearlyEomGomReport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TipsEomGomReportPrintController extends Controller
{
    /**
     * Print monthly report sheet with official signatures.
     */
    public function printMonthly(Request $request, TipsYearlyEomGomReport $report, int $month): Response
    {
        $period = $report->getPeriodForMonth($month);

        $entries = $report->entries()
            ->where('month_number', $month)
            ->with([
                'department',
                'eomEmployee1.designation',
                'eomEmployee1.department',
                'eomEmployee2.designation',
                'eomEmployee2.department',
                'gomEmployee1.designation',
                'gomEmployee1.department',
            ])
            ->orderBy('entry_number')
            ->get();

        return response()->view('tips.eom-gom.print-monthly', [
            'report' => $report,
            'month' => $month,
            'monthName' => $period['evaluated_month'],
            'evaluatedYear' => $period['evaluated_year'],
            'periodLabel' => $period['evaluated_label'],
            'releaseMonth' => $period['release_month'],
            'releaseLabel' => $period['release_label'],
            'entries' => $entries,
        ]);
    }

    /**
     * HRMS Celebration Wish Card (High-fashion canvas for image export & print).
     */
    public function hrmsCard(Request $request, TipsYearlyEomGomReport $report, int $month): Response
    {
        $period = $report->getPeriodForMonth($month);
        $monthName = $period['evaluated_month'];

        $entries = $report->entries()
            ->where('month_number', $month)
            ->with([
                'department',
                'eomEmployee1.designation',
                'eomEmployee1.department',
                'eomEmployee2.designation',
                'eomEmployee2.department',
                'gomEmployee1.designation',
                'gomEmployee1.department',
            ])
            ->orderBy('entry_number')
            ->get();

        $e1 = $entries->firstWhere('entry_number', 1);
        $e2 = $entries->firstWhere('entry_number', 2);
        $e3 = $entries->firstWhere('entry_number', 3);

        // Group EOM winners (up to 3)
        $eomWinners = collect([
            [
                'title' => 'Employee of the Month',
                'badge' => 'EOM Winner',
                'entry_label' => 'Entry 1 (Gaming / Slot)',
                'department' => $e1?->eomEmployee1?->department?->name ?? 'Gaming / Slot',
                'employee' => $e1?->eomEmployee1,
                'remarks' => $e1?->eom_remarks_1,
            ],
            [
                'title' => 'Employee of the Month',
                'badge' => 'EOM Winner',
                'entry_label' => 'Entry 1 (Gaming / Slot)',
                'department' => $e1?->eomEmployee2?->department?->name ?? 'Gaming / Slot',
                'employee' => $e1?->eomEmployee2,
                'remarks' => $e1?->eom_remarks_2,
            ],
            [
                'title' => 'Employee of the Month',
                'badge' => 'EOM Winner',
                'entry_label' => 'Entry 2 (Allowed Dept)',
                'department' => $e2?->department_name ?? ($e2?->department?->name ?? 'Department'),
                'employee' => $e2?->eomEmployee1,
                'remarks' => $e2?->eom_remarks_1,
            ],
        ]);

        // Group GOM winners (up to 2)
        $gomWinners = collect([
            [
                'title' => 'Grooming of the Month',
                'badge' => 'GOM Winner',
                'entry_label' => 'Entry 1 (Gaming / Slot)',
                'department' => $e1?->gomEmployee1?->department?->name ?? 'Gaming / Slot',
                'employee' => $e1?->gomEmployee1,
                'remarks' => $e1?->gom_remarks_1,
            ],
            [
                'title' => 'Grooming of the Month',
                'badge' => 'GOM Winner',
                'entry_label' => 'Entry 3 (Allowed Dept)',
                'department' => $e3?->department_name ?? ($e3?->department?->name ?? 'Department'),
                'employee' => $e3?->gomEmployee1,
                'remarks' => $e3?->gom_remarks_1,
            ],
        ]);

        return response()->view('tips.eom-gom.hrms-card', [
            'report' => $report,
            'month' => $month,
            'monthName' => $monthName,
            'evaluatedYear' => $period['evaluated_year'],
            'periodLabel' => $period['evaluated_label'],
            'releaseMonth' => $period['release_month'],
            'releaseLabel' => $period['release_label'],
            'entries' => $entries,
            'eomWinners' => $eomWinners,
            'gomWinners' => $gomWinners,
        ]);
    }
}
