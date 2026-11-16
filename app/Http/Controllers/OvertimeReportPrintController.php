<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OtBlock;
use App\Models\OtSpecialDate;
use App\Models\OvertimeEntry;
use App\Models\OvertimeReport;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OvertimeReportPrintController extends Controller
{
    public function printBlockwise(OvertimeReport $report): Response
    {
        $entries = OvertimeEntry::with(['employee.department', 'block'])
            ->whereMonth('ot_date', $report->month)
            ->whereYear('ot_date', $report->year)
            ->get();

        $blockSummary = $entries
            ->groupBy('ot_block_id')
            ->map(function ($group): array {
                $normal = (float) $group->where('is_special_ot', false)->sum('hours');
                $special = (float) $group->where('is_special_ot', true)->sum('hours');

                return [
                    'block' => $group->first()?->block?->name ?? 'Unknown',
                    'normal_hours' => $normal,
                    'special_hours' => $special,
                    'total_hours' => $normal + $special,
                    'employees_count' => $group->pluck('employee_id')->unique()->count(),
                ];
            })
            ->sortBy('block')
            ->values();

        $employeeBlockSummary = $entries
            ->groupBy('employee_id')
            ->map(function ($group, string $empCode): array {
                $emp = $group->first()?->employee;
                $normal = (float) $group->where('is_special_ot', false)->sum('hours');
                $special = (float) $group->where('is_special_ot', true)->sum('hours');
                $blocks = $group->groupBy(fn ($e) => $e->block?->name ?? 'Unknown')
                    ->map(fn ($b) => (float) $b->sum('hours'))
                    ->toArray();

                return [
                    'employee_code' => $empCode,
                    'name' => $emp?->name ?? 'Unknown',
                    'department' => $emp?->department?->name ?? '—',
                    'normal_hours' => $normal,
                    'special_hours' => $special,
                    'total_hours' => $normal + $special,
                    'blocks' => $blocks,
                ];
            })
            ->sortBy('employee_code')
            ->values();

        $allBlocks = OtBlock::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();

        return response()->view('overtime.print-blockwise', [
            'report' => $report,
            'periodLabel' => Carbon::create($report->year, $report->month, 1)->format('F Y'),
            'blockSummary' => $blockSummary,
            'employeeBlockSummary' => $employeeBlockSummary,
            'allBlocks' => $allBlocks,
        ]);
    }

    public function exportExcel(OvertimeReport $report): StreamedResponse
    {
        $daysInMonth = Carbon::create($report->year, $report->month, 1)->daysInMonth;
        $periodSlug = Carbon::create($report->year, $report->month, 1)->format('Y_m_F');
        $filename = "OT_Report_{$periodSlug}.csv";

        $entries = OvertimeEntry::with('block')
            ->whereMonth('ot_date', $report->month)
            ->whereYear('ot_date', $report->year)
            ->get();

        $specialDays = OtSpecialDate::whereMonth('date', $report->month)
            ->whereYear('date', $report->year)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->day)
            ->toArray();

        $entriesMap = [];
        foreach ($entries as $entry) {
            $day = $entry->ot_date->day;
            $entriesMap[$entry->employee_id][$day][] = $entry;
        }

        $employees = Employee::with(['department', 'designation'])
            ->where('employee_status', 'active')
            ->orderBy('employee_code')
            ->get();

        return response()->streamDownload(function () use ($daysInMonth, $specialDays, $entriesMap, $employees): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $headers = ['Employee Code', 'Name', 'Department', 'Designation'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $label = str_pad((string) $d, 2, '0', STR_PAD_LEFT);
                if (in_array($d, $specialDays, true)) {
                    $label .= ' (x2)';
                }
                $headers[] = $label;
            }
            $headers[] = 'Normal OT (x1.5)';
            $headers[] = 'Special OT (x2)';
            $headers[] = 'Total OT';
            $headers[] = 'Block Breakdown';
            fputcsv($handle, $headers);

            foreach ($employees as $emp) {
                $row = [
                    $emp->employee_code,
                    $emp->name,
                    $emp->department?->name ?? '',
                    $emp->designation?->name ?? '',
                ];

                $normal = 0.0;
                $special = 0.0;
                $blockTotals = [];

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dayItems = $entriesMap[$emp->employee_code][$d] ?? [];
                    if (empty($dayItems)) {
                        $row[] = '';

                        continue;
                    }

                    $parts = [];
                    foreach ($dayItems as $item) {
                        $hrs = (float) $item->hours;
                        $blk = $item->block?->name ?? '?';
                        $parts[] = rtrim(rtrim(number_format($hrs, 2), '0'), '.')."({$blk})";
                        $blockTotals[$blk] = ($blockTotals[$blk] ?? 0.0) + $hrs;

                        if ($item->is_special_ot) {
                            $special += $hrs;
                        } else {
                            $normal += $hrs;
                        }
                    }
                    $row[] = implode(', ', $parts);
                }

                $row[] = number_format($normal, 2, '.', '');
                $row[] = number_format($special, 2, '.', '');
                $row[] = number_format($normal + $special, 2, '.', '');
                $row[] = collect($blockTotals)
                    ->map(fn ($h, $b) => "{$b}: ".rtrim(rtrim(number_format($h, 2), '0'), '.').'h')
                    ->implode(' | ');

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
