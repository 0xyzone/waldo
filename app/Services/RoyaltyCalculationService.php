<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Employee;
use App\Models\RoyaltyExemptEmployee;
use App\Models\RoyaltyReport;
use App\Models\RoyaltyReportItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoyaltyCalculationService
{
    /**
     * Generate Royalty Report items from uploaded Excel files and employee data.
     */
    public function generate(RoyaltyReport $report): void
    {
        DB::transaction(function () use ($report) {
            // Clear any existing items if re-generating
            $report->items()->delete();

            // 1. Load Global Settings
            $settings = $this->getSettings();

            // 2. Preload Exempt Employees
            $exemptEmployeeCodes = RoyaltyExemptEmployee::query()
                ->where('is_active', true)
                ->pluck('employee_code')
                ->map(fn ($c) => strtoupper(trim((string) $c)))
                ->flip()
                ->toArray();

            // 3. Query all eligible employees (Active, Resigning This Month, Suspended) with royalty-eligible designations
            $employees = Employee::query()
                ->with(['department', 'designation'])
                ->whereIn('employee_status', ['Active', 'Resigning This Month', 'Suspended'])
                ->whereHas('designation', function ($q) {
                    $q->where('is_royalty_eligible', true);
                })
                ->orderBy('employee_code')
                ->get();

            // 4. Parse Attendance Excel file
            $attendanceData = [];
            if ($report->attendance_file_path) {
                $absPath = Storage::disk('public')->path($report->attendance_file_path);
                if (file_exists($absPath)) {
                    $attendanceData = $this->parseAttendanceFile($absPath);
                }
            }

            // 5. Parse Performance Excel file
            $performanceData = [];
            if ($report->performance_file_path) {
                $absPath = Storage::disk('public')->path($report->performance_file_path);
                if (file_exists($absPath)) {
                    $performanceData = $this->parsePerformanceFile($absPath);
                }
            }

            // 6. Calculate for each employee and create Report Item
            foreach ($employees as $employee) {
                $code = strtoupper(trim((string) $employee->employee_code));
                $designation = $employee->designation;

                $annualBudget = (float) ($designation?->royalty_annual_budget ?? 0);
                $monthlyMax = (float) ($designation?->royalty_monthly_max ?? 0);
                $monthlyMin = (float) ($designation?->royalty_monthly_min ?? 0);

                // Auto-fallback if max/min not saved on designation
                if ($annualBudget > 0 && $monthlyMax <= 0) {
                    $monthlyMax = ceil(($annualBudget / 12) / 100) * 100;
                    $monthlyMin = round($monthlyMax / 2, 2);
                }

                $isExempt = isset($exemptEmployeeCodes[$code]);

                $att = $attendanceData[$code] ?? [
                    'absent_days' => 0.0,
                    'late_early_count' => 0,
                    'missing_thumb_count' => 0,
                ];

                $perfInfo = $performanceData[$code] ?? null;
                $perfDeductionPercent = $perfInfo ? (float) ($perfInfo['percent'] ?? 0.0) : 0.0;
                $perfRemarks = $perfInfo ? trim((string) ($perfInfo['remarks'] ?? '')) : null;

                // Calculate deductions and payout
                $calc = $this->computePayout(
                    monthlyMax: $monthlyMax,
                    monthlyMin: $monthlyMin,
                    isExempt: $isExempt,
                    perfPercent: $perfDeductionPercent,
                    absentDays: (float) $att['absent_days'],
                    lateEarlyCount: (int) $att['late_early_count'],
                    missingThumbCount: (int) $att['missing_thumb_count'],
                    settings: $settings
                );

                $isHold = ($employee->employee_status === 'Suspended') || strtoupper(trim((string) ($employee->tips_status ?? ''))) === 'HOLD';
                $payoutStatus = $isHold ? 'Hold' : 'Release';
                $heldReason = $isHold ? ($employee->employee_status === 'Suspended' ? 'Staff suspended' : 'Staff on hold') : null;

                $itemRemarks = null;
                if ($isExempt) {
                    $itemRemarks = 'Exempt from deductions (Always Max Payout)';
                } elseif (! empty($perfRemarks)) {
                    $itemRemarks = $perfRemarks;
                }

                RoyaltyReportItem::create([
                    'royalty_report_id' => $report->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->name ?? $employee->employee_code,
                    'department' => $employee->department?->name ?? 'General',
                    'department_id' => $employee->department_id,
                    'designation' => $designation?->name ?? 'General',
                    'designation_id' => $employee->designation_id,
                    'employee_status' => $employee->employee_status ?? 'Active',
                    'annual_budget' => $annualBudget,
                    'monthly_max_payout' => $monthlyMax,
                    'monthly_min_payout' => $monthlyMin,
                    'is_exempt' => $isExempt,
                    'absent_days' => $att['absent_days'],
                    'late_early_count' => $att['late_early_count'],
                    'missing_thumb_count' => $att['missing_thumb_count'],
                    'performance_deduction_percent' => $perfDeductionPercent,
                    'performance_deduction_amount' => $calc['perf_deduction_amount'],
                    'attendance_deduction_amount' => $calc['attendance_deduction_amount'],
                    'total_deduction_amount' => $calc['total_deduction_amount'],
                    'final_payout' => $calc['final_payout'],
                    'payout_status' => $payoutStatus,
                    'held_reason' => $heldReason,
                    'remarks' => $itemRemarks,
                ]);
            }
        });
    }

    /**
     * Recalculate single report item (e.g. after toggling exemption).
     */
    public function recalculateItem(RoyaltyReportItem $item): void
    {
        $settings = $this->getSettings();

        $calc = $this->computePayout(
            monthlyMax: (float) $item->monthly_max_payout,
            monthlyMin: (float) $item->monthly_min_payout,
            isExempt: (bool) $item->is_exempt,
            perfPercent: (float) $item->performance_deduction_percent,
            absentDays: (float) $item->absent_days,
            lateEarlyCount: (int) $item->late_early_count,
            missingThumbCount: (int) $item->missing_thumb_count,
            settings: $settings
        );

        $item->update([
            'performance_deduction_amount' => $calc['perf_deduction_amount'],
            'attendance_deduction_amount' => $calc['attendance_deduction_amount'],
            'total_deduction_amount' => $calc['total_deduction_amount'],
            'final_payout' => $calc['final_payout'],
            'remarks' => $item->is_exempt ? 'Exempt from deductions (Always Max Payout)' : null,
        ]);
    }

    /**
     * Core payout computation algorithm.
     *
     * @param  array<string, mixed>  $settings
     * @return array{perf_deduction_amount: float, attendance_deduction_amount: float, total_deduction_amount: float, final_payout: float}
     */
    public function computePayout(
        float $monthlyMax,
        float $monthlyMin,
        bool $isExempt,
        float $perfPercent,
        float $absentDays,
        int $lateEarlyCount,
        int $missingThumbCount,
        array $settings
    ): array {
        if ($isExempt || $monthlyMax <= 0) {
            return [
                'perf_deduction_amount' => 0.0,
                'attendance_deduction_amount' => 0.0,
                'total_deduction_amount' => 0.0,
                'final_payout' => $monthlyMax,
            ];
        }

        // 1. Performance Deduction from baseline
        $perfDeductionAmount = 0.0;
        if ($perfPercent > 0) {
            $perfDeductionAmount = round($monthlyMax * ($perfPercent / 100), 2);
        }

        // 2. Attendance Deductions
        $attendanceDeductionAmount = 0.0;

        // Absent Days
        if ($absentDays > 0) {
            if ($settings['absent_mode'] === 'boolean') {
                $attendanceDeductionAmount += (float) $settings['absent_amount'];
            } else {
                $attendanceDeductionAmount += round($absentDays * (float) $settings['absent_amount'], 2);
            }
        }

        // Late In / Early Out
        if ($lateEarlyCount > 0) {
            $threshold = (int) $settings['late_early_threshold'];
            $penalty = (float) $settings['late_early_penalty'];
            if ($settings['late_early_mode'] === 'boolean') {
                if ($lateEarlyCount > $threshold) {
                    $attendanceDeductionAmount += $penalty;
                }
            } else {
                $deductibleUnits = max(0, $lateEarlyCount - $threshold);
                $attendanceDeductionAmount += round($deductibleUnits * $penalty, 2);
            }
        }

        // Missing Thumb
        if ($missingThumbCount > 0) {
            $threshold = (int) $settings['missing_thumb_threshold'];
            $penalty = (float) $settings['missing_thumb_penalty'];
            if ($settings['missing_thumb_mode'] === 'boolean') {
                if ($missingThumbCount > $threshold) {
                    $attendanceDeductionAmount += $penalty;
                }
            } else {
                $deductibleUnits = max(0, $missingThumbCount - $threshold);
                $attendanceDeductionAmount += round($deductibleUnits * $penalty, 2);
            }
        }

        $totalDeductions = round($perfDeductionAmount + $attendanceDeductionAmount, 2);
        $calculatedPayout = $monthlyMax - $totalDeductions;

        // Floor at Monthly Min Payout (unless minimum floor disabled)
        if ($settings['enforce_min_floor'] && $monthlyMin > 0) {
            $finalPayout = max($monthlyMin, min($monthlyMax, $calculatedPayout));
        } else {
            $finalPayout = max(0, min($monthlyMax, $calculatedPayout));
        }

        return [
            'perf_deduction_amount' => $perfDeductionAmount,
            'attendance_deduction_amount' => $attendanceDeductionAmount,
            'total_deduction_amount' => $totalDeductions,
            'final_payout' => round($finalPayout, 2),
        ];
    }

    /**
     * Retrieve Royalty Bonus Global Settings.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return [
            'absent_mode' => AppSetting::get('royalty_absent_mode', 'boolean'), // 'boolean' | 'countable'
            'absent_amount' => (float) AppSetting::get('royalty_absent_amount', '500.00'),
            'late_early_mode' => AppSetting::get('royalty_late_early_mode', 'countable'), // 'boolean' | 'countable'
            'late_early_threshold' => (int) AppSetting::get('royalty_late_early_threshold', '3'),
            'late_early_penalty' => (float) AppSetting::get('royalty_late_early_penalty', '100.00'),
            'missing_thumb_mode' => AppSetting::get('royalty_missing_thumb_mode', 'countable'), // 'boolean' | 'countable'
            'missing_thumb_threshold' => (int) AppSetting::get('royalty_missing_thumb_threshold', '2'),
            'missing_thumb_penalty' => (float) AppSetting::get('royalty_missing_thumb_penalty', '100.00'),
            'enforce_min_floor' => (bool) AppSetting::get('royalty_enforce_min_floor', '1'),
        ];
    }

    /**
     * Parse Attendance Excel file into metrics indexed by employee code.
     *
     * @return array<string, array{absent_days: float, late_early_count: int, missing_thumb_count: int}>
     */
    public function parseAttendanceFile(string $filePath): array
    {
        $rows = $this->readSheetRows($filePath);
        if (empty($rows)) {
            return [];
        }

        // 1. Locate header row containing 'username' or ('full name' and 'id') or 'employee code'
        $headerRowIdx = null;
        $categoryRowIdx = null;

        for ($i = 0; $i < min(15, count($rows)); $i++) {
            $normalized = array_map(fn ($c) => strtolower(trim((string) $c)), $rows[$i]);
            if (
                in_array('username', $normalized) ||
                (in_array('full name', $normalized) && in_array('id', $normalized)) ||
                in_array('employee code', $normalized) ||
                in_array('employee_code', $normalized) ||
                in_array('staff id', $normalized) ||
                in_array('staff_id', $normalized) ||
                in_array('biometric id', $normalized)
            ) {
                $headerRowIdx = $i;
                // Find nearest non-empty row above headerRowIdx as categoryRow
                for ($k = $i - 1; $k >= 0; $k--) {
                    $nonEmpty = array_filter($rows[$k], fn ($c) => trim((string) $c) !== '');
                    if (! empty($nonEmpty)) {
                        $categoryRowIdx = $k;
                        break;
                    }
                }
                break;
            }
        }

        if ($headerRowIdx === null) {
            $headerRowIdx = 0;
        }

        $headerRow = $rows[$headerRowIdx];
        $categoryRow = $categoryRowIdx !== null ? $rows[$categoryRowIdx] : [];

        $usernameCol = null;
        $lateInCol = null;
        $earlyOutCol = null;
        $missingPunchInCol = null;
        $absentDaysCol = null;
        $absentCols = [];

        foreach ($headerRow as $idx => $cell) {
            $norm = strtolower(trim((string) $cell));
            $catNorm = isset($categoryRow[$idx]) ? strtolower(trim((string) $categoryRow[$idx])) : '';

            if (in_array($norm, ['username', 'employee code', 'employee_code', 'staff id', 'staff_id', 'biometric id', 'code', 'id']) || in_array($catNorm, ['username', 'employee code'])) {
                if ($usernameCol === null || $norm === 'username' || $norm === 'employee code' || $norm === 'employee_code') {
                    $usernameCol = $idx;
                }
            }

            if (in_array($norm, ['absent days', 'absent_days', 'absent', 'lop days', 'lop']) || in_array($catNorm, ['absent days', 'absent_days', 'absent'])) {
                $absentDaysCol = $idx;
            }

            // Late in / Early out check on either headerRow or categoryRow
            if (in_array($norm, ['late in count', 'late in', 'late_in_count', 'late count', 'late']) || in_array($catNorm, ['late in count', 'late in', 'late_in_count'])) {
                $lateInCol = $idx;
            }

            if (in_array($norm, ['early out count', 'early out', 'early_out_count', 'early count', 'early']) || in_array($catNorm, ['early out count', 'early out', 'early_out_count'])) {
                $earlyOutCol = $idx;
            }

            // Missing Punch In / Missing Thumb Count check on either headerRow or categoryRow
            if (
                in_array($norm, ['missing punch in count', 'missing punch in', 'missing_punch_in_count', 'missing punch', 'missing_punch', 'missing thumb', 'missing_thumb', 'missing thumbs'])
                || in_array($catNorm, ['missing punch in count', 'missing punch in', 'missing_punch_in_count', 'missing punch', 'missing thumb', 'missing_thumb'])
            ) {
                $missingPunchInCol = $idx;
            }

            // Check for Used Balance columns in HRMS Leave report
            if ($norm === 'used balance' || $norm === 'used') {
                $cat = trim((string) ($categoryRow[$idx] ?? ''));
                if ($cat === '' && $idx > 0) {
                    $cat = trim((string) ($categoryRow[$idx - 1] ?? ''));
                }
                $catLower = strtolower($cat);
                if (str_contains($catLower, 'lop') || str_contains($catLower, 'loss of pay') || str_contains($catLower, 'absent')) {
                    $absentCols[$idx] = $cat;
                }
            }
        }

        if ($usernameCol === null) {
            return [];
        }

        $results = [];

        for ($r = $headerRowIdx + 1; $r < count($rows); $r++) {
            $cells = $rows[$r];
            $username = trim((string) ($cells[$usernameCol] ?? ''));
            if ($username === '') {
                continue;
            }

            // Absent days: strictly use explicit absent days column if present; otherwise fallback to summing LOP/Absent used balances
            $absentDays = 0.0;
            if ($absentDaysCol !== null) {
                $val = $cells[$absentDaysCol] ?? null;
                $absentDays = ($val !== '-' && is_numeric($val)) ? (float) $val : 0.0;
            } elseif (! empty($absentCols)) {
                foreach ($absentCols as $cIdx => $name) {
                    $v = $cells[$cIdx] ?? 0;
                    if ($v !== '-' && is_numeric($v) && (float) $v > 0) {
                        $absentDays += (float) $v;
                    }
                }
            }

            $lateIn = $lateInCol !== null && is_numeric($cells[$lateInCol] ?? null)
                ? (int) $cells[$lateInCol]
                : 0;

            $earlyOut = $earlyOutCol !== null && is_numeric($cells[$earlyOutCol] ?? null)
                ? (int) $cells[$earlyOutCol]
                : 0;

            $missingPunchIn = $missingPunchInCol !== null && is_numeric($cells[$missingPunchInCol] ?? null)
                ? (int) $cells[$missingPunchInCol]
                : 0;

            $code = strtoupper(trim($username));
            $results[$code] = [
                'absent_days' => max(0.0, $absentDays),
                'late_early_count' => max(0, $lateIn + $earlyOut),
                'missing_thumb_count' => max(0, $missingPunchIn),
            ];
        }

        return $results;
    }

    /**
     * Parse Performance Excel file into deduction percentage and remarks indexed by employee code.
     *
     * @return array<string, array{percent: float, remarks: ?string}>
     */
    public function parsePerformanceFile(string $filePath): array
    {
        $rows = $this->readSheetRows($filePath);
        if (empty($rows)) {
            return [];
        }

        $headerRowIdx = null;
        for ($i = 0; $i < min(15, count($rows)); $i++) {
            $normalized = array_map(fn ($c) => strtolower(trim((string) $c)), $rows[$i]);
            if (
                in_array('username', $normalized) ||
                in_array('employee code', $normalized) ||
                in_array('employee_code', $normalized) ||
                in_array('staff id', $normalized) ||
                in_array('code', $normalized) ||
                in_array('id', $normalized)
            ) {
                $headerRowIdx = $i;
                break;
            }
        }

        if ($headerRowIdx === null) {
            $headerRowIdx = 0;
        }

        $headerRow = $rows[$headerRowIdx];
        $codeCol = null;
        $deductionCol = null;
        $remarksCol = null;

        foreach ($headerRow as $idx => $cell) {
            $norm = strtolower(trim((string) $cell));

            if (in_array($norm, ['username', 'employee code', 'employee_code', 'staff id', 'code', 'id', 'biometric id', 'emp code', 'emp_code', 'emp id', 'staff code'])) {
                if ($codeCol === null || $norm === 'employee code' || $norm === 'employee_code' || $norm === 'code') {
                    $codeCol = $idx;
                }
            }

            if (in_array($norm, [
                'percentage', 'percent', '%', 'deduction', 'deduction %', 'performance deduction', 'performance_deduction',
                'deduction percent', 'deduction_percent', 'number of percentage', 'number of percent', 'deduction percentage',
                'performance %', 'perf %', 'rate', 'deduction rate', 'performance deduction %', 'performance deduction percent',
            ])) {
                $deductionCol = $idx;
            }

            if (in_array($norm, ['remarks', 'remark', 'reason', 'reasons', 'notes', 'note', 'comment', 'comments', 'description', 'detail', 'details'])) {
                $remarksCol = $idx;
            }
        }

        // Positional fallbacks for a 3-column file: [0 => Employee Code, 1 => Percentage, 2 => Remarks]
        if ($codeCol === null && isset($headerRow[0])) {
            $codeCol = 0;
        }
        if ($deductionCol === null && isset($headerRow[1])) {
            $deductionCol = 1;
        }
        if ($remarksCol === null && isset($headerRow[2])) {
            $remarksCol = 2;
        }

        if ($codeCol === null || $deductionCol === null) {
            return [];
        }

        $results = [];
        for ($i = $headerRowIdx + 1; $i < count($rows); $i++) {
            $cells = $rows[$i];
            $code = strtoupper(trim((string) ($cells[$codeCol] ?? '')));
            if ($code === '' || $code === 'EMPLOYEE CODE' || $code === 'CODE') {
                continue;
            }

            $rawVal = trim((string) ($cells[$deductionCol] ?? ''));
            $rawVal = str_replace(['%', ' '], '', $rawVal);

            $percent = 0.0;
            if (is_numeric($rawVal)) {
                $val = (float) $rawVal;
                // If expressed as fraction like 0.05, convert to 5%
                if ($val > 0 && $val <= 1.0) {
                    $percent = $val * 100;
                } else {
                    $percent = $val;
                }
            }

            $remarks = $remarksCol !== null ? trim((string) ($cells[$remarksCol] ?? '')) : null;
            if ($remarks === '') {
                $remarks = null;
            }

            $results[$code] = [
                'percent' => round(max(0.0, min(100.0, $percent)), 2),
                'remarks' => $remarks,
            ];
        }

        return $results;
    }

    /**
     * Read rows from an XLSX or CSV file.
     *
     * @return array<int, array<int, mixed>>
     */
    protected function readSheetRows(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            $rows = [];
            $handle = fopen($filePath, 'r');
            if (! $handle) {
                return [];
            }
            while (($cells = fgetcsv($handle)) !== false) {
                if (! empty($cells)) {
                    $rows[] = $cells;
                }
            }
            fclose($handle);

            return $rows;
        }

        try {
            $reader = new XlsxReader;
            $reader->open($filePath);

            $rows = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    if (! empty($cells)) {
                        $rows[] = $cells;
                    }
                }
                break; // First sheet
            }

            $reader->close();

            return $rows;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Export royalty report items to an Excel (.xlsx) file.
     */
    public function exportExcel(RoyaltyReport $report): StreamedResponse
    {
        $fileName = 'Royalty_Bonus_'.str_replace(' ', '_', $report->title).'_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($report) {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');

            // Header Row Styling
            $headerStyle = (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('0F172A'); // Slate 900

            $headers = [
                'S.N.',
                'Employee Code',
                'Employee Name',
                'Department',
                'Designation',
                'Status',
                'Annual Budget (Rs.)',
                'Monthly Max Payout (Rs.)',
                'Monthly Min Payout (Rs.)',
                'Exempt from Deductions?',
                'Performance Deduction %',
                'Performance Deduction (Rs.)',
                'Absent Days',
                'Late / Early Count',
                'Missing Thumb Count',
                'Attendance Deduction (Rs.)',
                'Total Deduction (Rs.)',
                'Final Net Payout (Rs.)',
                'Payout Status',
                'Hold Reason / Remarks',
            ];

            $writer->addRow(Row::fromValues($headers, $headerStyle));

            $items = $report->items()
                ->orderBy('department')
                ->orderBy('employee_code')
                ->get();

            $index = 1;
            foreach ($items as $item) {
                $rowValues = [
                    $index++,
                    $item->employee_code,
                    $item->employee_name,
                    $item->department ?? '-',
                    $item->designation ?? '-',
                    $item->employee_status,
                    (float) $item->annual_budget,
                    (float) $item->monthly_max_payout,
                    (float) $item->monthly_min_payout,
                    $item->is_exempt ? 'YES' : 'NO',
                    (float) $item->performance_deduction_percent.'%',
                    (float) $item->performance_deduction_amount,
                    (float) $item->absent_days,
                    (int) $item->late_early_count,
                    (int) $item->missing_thumb_count,
                    (float) $item->attendance_deduction_amount,
                    (float) $item->total_deduction_amount,
                    (float) $item->final_payout,
                    $item->payout_status ?? 'Release',
                    $item->held_reason ?: ($item->remarks ?: '-'),
                ];

                $writer->addRow(Row::fromValues($rowValues));
            }

            // Summary row
            $summaryStyle = (new Style)
                ->setFontBold()
                ->setBackgroundColor('F1F5F9');

            $totalRow = [
                'TOTAL',
                '',
                count($items).' Employees',
                '',
                '',
                '',
                '',
                (float) $items->sum('monthly_max_payout'),
                '',
                '',
                '',
                (float) $items->sum('performance_deduction_amount'),
                '',
                '',
                '',
                (float) $items->sum('attendance_deduction_amount'),
                (float) $items->sum('total_deduction_amount'),
                (float) $items->sum('final_payout'),
            ];
            $writer->addRow(Row::fromValues($totalRow, $summaryStyle));

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Download sample Excel template for Performance Deduction upload (strictly 3 columns).
     */
    public function downloadPerformanceSample(): StreamedResponse
    {
        $fileName = 'Royalty_Performance_Deduction_Sample.xlsx';

        return response()->streamDownload(function () {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');

            $headerStyle = (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $headers = [
                'Employee Code',
                'Percentage',
                'Remarks',
            ];
            $writer->addRow(Row::fromValues($headers, $headerStyle));

            $sampleRows = [
                ['EMP001', '10%', 'Late submission of monthly deliverables'],
                ['EMP002', '15%', 'Monthly KPI shortfall (-15%)'],
                ['EMP003', '25%', 'Service escalation penalty (-25%)'],
                ['EMP004', '5%', 'Uniform & grooming policy non-compliance'],
            ];

            foreach ($sampleRows as $row) {
                $writer->addRow(Row::fromValues($row));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
