<?php

namespace App\Http\Controllers;

use App\Filament\Pages\OvertimeSummary;
use App\Models\Employee;
use App\Models\OtBlock;
use App\Models\OtSpecialDate;
use App\Models\OvertimeAdjustment;
use App\Models\OvertimeEntry;
use App\Models\OvertimeReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OvertimeReportPrintController extends Controller
{
    public function printBlockwise(OvertimeReport $report): Response
    {
        return $this->renderBlockwiseForPeriod($report->month, $report->year);
    }

    public function exportExcel(OvertimeReport $report): StreamedResponse
    {
        return $this->streamDailyExcelForPeriod($report->month, $report->year);
    }

    public function printSummaryBlockwise(Request $request): Response
    {
        abort_unless(OvertimeSummary::canShield('print'), 403);

        [$month, $year] = $this->resolvePeriod($request);

        return $this->renderBlockwiseForPeriod($month, $year);
    }

    public function printSummaryFinance(Request $request): Response
    {
        abort_unless(OvertimeSummary::canShield('print'), 403);

        [$month, $year] = $this->resolvePeriod($request);

        return $this->renderFinanceForPeriod($month, $year);
    }

    public function exportSummaryExcel(Request $request): StreamedResponse
    {
        abort_unless(OvertimeSummary::canShield('export'), 403);

        [$month, $year] = $this->resolvePeriod($request);

        return $this->streamSummaryExcelForPeriod($month, $year);
    }

    public function exportSummaryDailyExcel(Request $request): StreamedResponse
    {
        abort_unless(OvertimeSummary::canShield('export'), 403);

        [$month, $year] = $this->resolvePeriod($request);

        return $this->streamDailyExcelForPeriod($month, $year);
    }

    private function resolvePeriod(Request $request): array
    {
        $month = max(1, min(12, $request->integer('month', (int) now()->month)));
        $year = max(2000, min(2100, $request->integer('year', (int) now()->year)));

        return [$month, $year];
    }

    private function renderBlockwiseForPeriod(int $month, int $year): Response
    {
        $data = $this->buildPeriodSummaryData($month, $year);

        return response()->view('overtime.print-blockwise', $data);
    }

    private function renderFinanceForPeriod(int $month, int $year): Response
    {
        $data = $this->buildPeriodSummaryData($month, $year);

        return response()->view('overtime.print-finance', $data);
    }

    private function buildPeriodSummaryData(int $month, int $year): array
    {
        $entries = OvertimeEntry::with(['employee.department', 'employee.designation', 'block'])
            ->whereMonth('ot_date', $month)
            ->whereYear('ot_date', $year)
            ->get();

        $report = OvertimeReport::where('month', $month)->where('year', $year)->first();
        $adjustments = $report
            ? OvertimeAdjustment::where('overtime_report_id', $report->id)->get()->keyBy('employee_id')
            : collect();

        $blockSummary = $entries
            ->groupBy('ot_block_id')
            ->map(function (Collection $group): array {
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

        $allEmpCodes = $entries->pluck('employee_id')
            ->merge(
                $adjustments
                    ->filter(fn (OvertimeAdjustment $a): bool => (float) $a->normal_adjustment != 0.0 || (float) $a->special_adjustment != 0.0)
                    ->keys()
            )
            ->unique()
            ->values();

        $employeesByCode = Employee::with(['department:id,name', 'designation:id,name'])
            ->whereIn('employee_code', $allEmpCodes)
            ->get()
            ->keyBy('employee_code');

        $entriesByEmployee = $entries->groupBy('employee_id');

        $employeeBlockSummary = $allEmpCodes
            ->map(function (string $empCode) use ($entriesByEmployee, $employeesByCode, $adjustments): array {
                $group = $entriesByEmployee->get($empCode, collect());
                $emp = $employeesByCode->get($empCode);
                $adj = $adjustments->get($empCode);

                $normal = (float) $group->where('is_special_ot', false)->sum('hours');
                $special = (float) $group->where('is_special_ot', true)->sum('hours');
                $normalAdj = (float) ($adj?->normal_adjustment ?? 0.0);
                $specialAdj = (float) ($adj?->special_adjustment ?? 0.0);
                $netNormal = $normal + $normalAdj;
                $netSpecial = $special + $specialAdj;

                $blocks = $group->groupBy(fn ($e) => $e->block?->name ?? 'Unknown')
                    ->map(fn (Collection $b) => (float) $b->sum('hours'))
                    ->toArray();

                return [
                    'employee_code' => $empCode,
                    'sort_num' => (int) preg_replace('/\D+/', '', $empCode),
                    'name' => $emp?->name ?? 'Unknown',
                    'department' => $emp?->department?->name ?? '—',
                    'designation' => $emp?->designation?->name ?? '—',
                    'normal_hours' => $normal,
                    'normal_adj' => $normalAdj,
                    'net_normal' => $netNormal,
                    'special_hours' => $special,
                    'special_adj' => $specialAdj,
                    'net_special' => $netSpecial,
                    'total_hours' => $netNormal + $netSpecial,
                    'remarks' => (string) ($adj?->remarks ?? ''),
                    'blocks' => $blocks,
                ];
            })
            ->sortBy([
                ['sort_num', 'asc'],
                ['employee_code', 'asc'],
            ])
            ->values();

        $allBlocks = OtBlock::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();

        return [
            'report' => $report,
            'periodLabel' => Carbon::create($year, $month, 1)->format('F Y'),
            'blockSummary' => $blockSummary,
            'employeeBlockSummary' => $employeeBlockSummary,
            'allBlocks' => $allBlocks,
        ];
    }

    private function streamSummaryExcelForPeriod(int $month, int $year): StreamedResponse
    {
        $data = $this->buildPeriodSummaryData($month, $year);
        $periodSlug = Carbon::create($year, $month, 1)->format('Y_m_F');
        $filename = "OT_Summary_{$periodSlug}.csv";

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $allBlocks = $data['allBlocks'];
            $headers = [
                'Employee Code',
                'Name',
                'Department',
                'Designation',
                'Normal Adj.',
                'Normal OT (x1.5)',
                'Net Normal OT (x1.5)',
                'Special Adj.',
                'Special OT (x2)',
                'Net Special OT (x2)',
                'Total OT',
            ];
            foreach ($allBlocks as $blk) {
                $headers[] = "Block {$blk}";
            }
            $headers[] = 'Adjustment Remarks';
            fputcsv($handle, $headers);

            foreach ($data['employeeBlockSummary'] as $emp) {
                $row = [
                    $emp['employee_code'],
                    $emp['name'],
                    $emp['department'],
                    $emp['designation'],
                    number_format($emp['normal_adj'], 2, '.', ''),
                    number_format($emp['normal_hours'], 2, '.', ''),
                    number_format($emp['net_normal'], 2, '.', ''),
                    number_format($emp['special_adj'], 2, '.', ''),
                    number_format($emp['special_hours'], 2, '.', ''),
                    number_format($emp['net_special'], 2, '.', ''),
                    number_format($emp['total_hours'], 2, '.', ''),
                ];
                foreach ($allBlocks as $blk) {
                    $row[] = number_format((float) ($emp['blocks'][$blk] ?? 0.0), 2, '.', '');
                }
                $row[] = $emp['remarks'];
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function streamDailyExcelForPeriod(int $month, int $year): StreamedResponse
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $periodSlug = Carbon::create($year, $month, 1)->format('Y_m_F');
        $filename = "OT_Report_{$periodSlug}.csv";

        $entries = OvertimeEntry::with('block')
            ->whereMonth('ot_date', $month)
            ->whereYear('ot_date', $year)
            ->get();

        $report = OvertimeReport::where('month', $month)->where('year', $year)->first();
        $adjustments = $report
            ? OvertimeAdjustment::where('overtime_report_id', $report->id)->get()->keyBy('employee_id')
            : collect();

        $specialDays = OtSpecialDate::whereMonth('date', $month)
            ->whereYear('date', $year)
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
            ->orderByRaw("CAST(REPLACE(UPPER(employee_code), 'CWD', '') AS UNSIGNED) ASC")
            ->orderBy('employee_code')
            ->get();

        return response()->streamDownload(function () use ($daysInMonth, $specialDays, $entriesMap, $employees, $adjustments): void {
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
            $headers[] = 'Normal Adj.';
            $headers[] = 'Normal OT (x1.5)';
            $headers[] = 'Special Adj.';
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

                $adj = $adjustments->get($emp->employee_code);
                $normalAdj = (float) ($adj?->normal_adjustment ?? 0.0);
                $specialAdj = (float) ($adj?->special_adjustment ?? 0.0);

                $row[] = number_format($normalAdj, 2, '.', '');
                $row[] = number_format($normal, 2, '.', '');
                $row[] = number_format($specialAdj, 2, '.', '');
                $row[] = number_format($special, 2, '.', '');
                $row[] = number_format($normal + $normalAdj + $special + $specialAdj, 2, '.', '');
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
