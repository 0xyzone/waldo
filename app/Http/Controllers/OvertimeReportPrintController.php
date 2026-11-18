<?php

namespace App\Http\Controllers;

use App\Filament\Pages\OvertimeSummary;
use App\Models\Employee;
use App\Models\OtSpecialDate;
use App\Models\OvertimeAdjustment;
use App\Models\OvertimeEntry;
use App\Models\OvertimeReport;
use App\Services\OvertimeReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $data = OvertimeReportService::buildPeriodSummaryData($month, $year);

        return response()->view('overtime.print-blockwise', $data);
    }

    private function renderFinanceForPeriod(int $month, int $year): Response
    {
        $data = OvertimeReportService::buildPeriodSummaryData($month, $year);

        return response()->view('overtime.print-finance', $data);
    }

    private function streamSummaryExcelForPeriod(int $month, int $year): StreamedResponse
    {
        $data = OvertimeReportService::buildPeriodSummaryData($month, $year);
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
                'Special Adj.',
                'Special OT (x2)',
                'Total OT',
            ];
            foreach ($allBlocks as $blk) {
                $headers[] = "Block {$blk}";
            }
            $headers[] = 'Adjustment Details';
            fputcsv($handle, $headers);

            foreach ($data['employeeBlockSummary'] as $emp) {
                $row = [
                    $emp['employee_code'],
                    $emp['name'],
                    $emp['department'],
                    $emp['designation'],
                    number_format($emp['normal_adj'], 2, '.', ''),
                    number_format($emp['normal_hours'], 2, '.', ''),
                    number_format($emp['special_adj'], 2, '.', ''),
                    number_format($emp['special_hours'], 2, '.', ''),
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
            ? OvertimeAdjustment::with('block')->where('overtime_report_id', $report->id)->get()->groupBy('employee_id')
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

                $empAdjs = $adjustments->get($emp->employee_code, collect());
                $normalAdj = (float) $empAdjs->where('ot_type', 'normal')->sum('hours');
                $specialAdj = (float) $empAdjs->where('ot_type', 'special')->sum('hours');

                foreach ($empAdjs as $adj) {
                    if ($adj->ot_block_id) {
                        $blk = $adj->block?->name ?? '?';
                        $blockTotals[$blk] = ($blockTotals[$blk] ?? 0.0) + (float) $adj->hours;
                    }
                }

                $netNormal = $normal + $normalAdj;
                $netSpecial = $special + $specialAdj;

                $row[] = number_format($normalAdj, 2, '.', '');
                $row[] = number_format($netNormal, 2, '.', '');
                $row[] = number_format($specialAdj, 2, '.', '');
                $row[] = number_format($netSpecial, 2, '.', '');
                $row[] = number_format($netNormal + $netSpecial, 2, '.', '');
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
