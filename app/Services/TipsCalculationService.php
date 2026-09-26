<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TipsAdjustment;
use App\Models\TipsDepartmentMapping;
use App\Models\TipsNonEmployee;
use App\Models\TipsReport;
use App\Models\TipsReportItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class TipsCalculationService
{
    /**
     * Generate tips report items from uploaded HRMS file and report input data.
     *
     * @param  array<string, mixed>  $data
     */
    public function generate(TipsReport $report, array $data): void
    {
        DB::transaction(function () use ($report, $data) {
            // Delete any existing items for this report (if re-generating)
            $report->items()->delete();

            // 1. Process Error Report (percentage deductions) indexed by normalized code
            $errorDeductions = [];
            if (! empty($data['company_errors']) && is_array($data['company_errors'])) {
                foreach ($data['company_errors'] as $err) {
                    $code = strtoupper(trim((string) ($err['employee_code'] ?? '')));
                    if ($code !== '') {
                        $errorDeductions[$code] = (float) ($err['percentage_to_deduct'] ?? 0);
                    }
                }
            }

            // 2. Query Tips Adjustments for active month/year
            $month = strtolower((string) ($report->month ?? ''));
            $year = (string) ($report->year ?? '');
            $adjustmentsQuery = TipsAdjustment::query();
            if ($month !== '') {
                $adjustmentsQuery->whereRaw('LOWER(for_month) = ?', [$month]);
            }
            if ($year !== '') {
                $adjustmentsQuery->where('year', $year);
            }
            $adjustmentsGrouped = $adjustmentsQuery->get()->groupBy(fn ($item) => strtoupper(trim((string) $item->employee_id)));

            // 3. Preload only Active and Resigning This Month employees indexed by uppercase employee_code
            $employees = Employee::with(['department', 'designation'])
                ->whereIn('employee_status', ['Active', 'Resigning This Month'])
                ->get()
                ->keyBy(fn ($emp) => strtoupper(trim((string) $emp->employee_code)));

            // 4. Parse HRMS Attendance file if uploaded
            $filePath = $report->excel_file_path;
            $cutoffCarbon = Carbon::parse($report->cutoff_date)->startOfDay();

            // Cutoff threshold: joining date must be strictly earlier than previous month's 15th day relative to cutoff_date
            $joinDateThreshold = $cutoffCarbon->copy()->subMonthNoOverflow()->day(15)->startOfDay();

            $processedCodes = [];

            if ($filePath) {
                $absolutePath = Storage::disk('public')->path($filePath);
                if (file_exists($absolutePath)) {
                    $attendanceRows = $this->parseHrmsFile($absolutePath);

                    foreach ($attendanceRows as $row) {
                        $code = strtoupper(trim((string) ($row['username'] ?? '')));
                        if ($code === '') {
                            continue;
                        }

                        $employee = $employees->get($code);
                        // 1. Only include active and resigning this month employees
                        if (! $employee) {
                            continue;
                        }

                        // 2. Check publish_tips: if false, exclude from report
                        if (! (bool) ($employee->publish_tips ?? true)) {
                            continue;
                        }

                        // 3. Check join date threshold: joining date must be < previous month's 15th
                        $joinDateRaw = $employee->join_date_formatted;
                        if (! empty($joinDateRaw)) {
                            try {
                                $cleanedDate = str_replace(',', '', $joinDateRaw);
                                $joinCarbon = Carbon::parse($cleanedDate)->startOfDay();
                                if ($joinCarbon->greaterThanOrEqualTo($joinDateThreshold)) {
                                    // Joined on or after previous month's 15th - skip
                                    continue;
                                }
                            } catch (\Throwable) {
                                // If unparseable date, keep employee to avoid accidental exclusion
                            }
                        }

                        $processedCodes[] = $code;

                        // Resolve department report page via Master Settings (TipsDepartmentMapping)
                        $pageName = TipsDepartmentMapping::resolvePageName(
                            $employee->department_id,
                            $employee->department?->name
                        );

                        $deptName = $pageName;
                        $desigName = $employee->designation?->name ?? '-';
                        $empName = $employee->name ?? ($row['full_name'] ?? $code);

                        // Attendance numbers
                        $workingDays = (float) ($row['working_days'] ?? 0);
                        $presentDays = (float) ($row['present_days'] ?? 0);
                        $absentDays = (float) ($row['absent_days'] ?? 0);
                        $totalLeaves = (float) ($row['total_leave'] ?? 0);
                        $lateIn = (int) ($row['late_in_count'] ?? 0);
                        $earlyOut = (int) ($row['early_out_count'] ?? 0);

                        // Employee baseline settings
                        $pointValue = (float) ($employee->point_value ?? 0);
                        $baseTipsAmount = (float) ($employee->tips_amount ?? 0);
                        $isBlank = (bool) ($employee->tips_blank ?? false);
                        $isFixed = (bool) ($employee->tips_fixed ?? false);
                        $publishTips = true;
                        $tipsStatus = (string) ($employee->tips_status ?? 'Release');

                        // Tenure & Date of Completion (DOC) factor (0.1 per full year of tenure)
                        [$workingDuration, $completionFactor] = $this->calculateTenure($joinDateRaw, $cutoffCarbon);

                        // Calculate attendance tips percentage:
                        // - If absent > 0 => 0%
                        // - If leave <= 3 => 100%
                        // - If leave > 3 => deduct 5% for every 0.5 step increase
                        $tipsPercentage = $this->calculateTipsPercentage($workingDays, $presentDays, $absentDays, $totalLeaves);

                        // Percentage to deduct from error report
                        $percentageToDeduct = $errorDeductions[$code] ?? 0.0;

                        // Tips adjustments (Add / Deduct)
                        $empAdjustments = $adjustmentsGrouped->get($code, collect());
                        $amountToAdjust = (float) $empAdjustments->where('type', 'add')->sum('amount');
                        $amountToDeduct = (float) $empAdjustments->where('type', 'deduct')->sum('amount');

                        // Calculated tips and final distribution
                        if ($isBlank) {
                            $calculatedTips = null;
                            $unroundedAmount = null;
                            $finalDistribution = null;
                        } elseif (strtoupper(trim($tipsStatus)) === 'HOLD') {
                            $calculatedTips = 0.0;
                            $unroundedAmount = 0.0;
                            $finalDistribution = 0.0;
                        } else {
                            $effectivePercentage = max(0.0, ($tipsPercentage - $percentageToDeduct));

                            // Base amount multiplier:
                            // If tips_fixed is true => ignore PV and DOC multiplier (or multiplier = 1.0)
                            // If tips_fixed is false => multiplier is (DOC + PV)
                            $multiplier = $isFixed ? 1.0 : ($completionFactor + $pointValue);
                            $adjustedBase = $baseTipsAmount * $multiplier;

                            $rawCalculatedTips = ($adjustedBase * $effectivePercentage) / 100;
                            $calculatedTips = $rawCalculatedTips > 0 ? (float) (ceil($rawCalculatedTips / 100) * 100) : 0.0;

                            $rawFinalDistribution = $rawCalculatedTips + $amountToAdjust - $amountToDeduct;
                            $unroundedAmount = max(0.0, round($rawFinalDistribution, 2));

                            if ($rawFinalDistribution <= 0) {
                                $finalDistribution = 0.0;
                            } else {
                                $finalDistribution = (float) (ceil($rawFinalDistribution / 100) * 100);
                            }
                        }

                        $deptRank = $employee->department?->rank ?? ($employee->dp_rank ?? 999);
                        $desigRank = $employee->designation?->rank ?? ($employee->rank ?? 999);

                        TipsReportItem::create([
                            'tips_report_id' => $report->id,
                            'employee_id' => $code,
                            'employee_code' => $code,
                            'department' => $deptName,
                            'department_rank' => $deptRank,
                            'designation' => $desigName,
                            'designation_rank' => $desigRank,
                            'employee_name' => $empName,
                            'working_days' => $workingDays,
                            'present_days' => $presentDays,
                            'absent_days' => $absentDays,
                            'total_leaves' => $totalLeaves,
                            'late_in_count' => $lateIn,
                            'early_out_count' => $earlyOut,
                            'join_date' => $joinDateRaw,
                            'working_duration' => $workingDuration,
                            'completion_factor' => $completionFactor,
                            'point_value' => $pointValue,
                            'base_tips_amount' => $baseTipsAmount,
                            'tips_percentage' => $tipsPercentage,
                            'is_blank' => $isBlank,
                            'is_fixed' => $isFixed,
                            'publish_tips' => $publishTips,
                            'tips_status' => $tipsStatus,
                            'amount_to_adjust' => $amountToAdjust,
                            'amount_to_deduct' => $amountToDeduct,
                            'percentage_to_deduct' => $percentageToDeduct,
                            'calculated_tips' => $calculatedTips,
                            'unrounded_amount' => $unroundedAmount,
                            'final_distribution_amount' => $finalDistribution,
                            'is_left_out' => false,
                        ]);
                    }
                }
            }

            // 5. Process Left Outs (Step 4)
            if (! empty($data['left_outs']) && is_array($data['left_outs'])) {
                foreach ($data['left_outs'] as $leftOut) {
                    $code = strtoupper(trim((string) ($leftOut['employee_id'] ?? '')));
                    if ($code === '') {
                        continue;
                    }

                    // Look up employee in $employees or directly from Employee DB (in case of inactive/left staff)
                    $employee = $employees->get($code) ?? Employee::with(['department', 'designation'])->where('employee_code', $code)->first();

                    $empName = $employee?->name ?? $code;
                    $desigName = $leftOut['designation'] ?? ($employee?->designation?->name ?? '-');

                    // If a custom department page was explicitly chosen in left outs form, use it, else default to 'Left Outs'
                    $deptName = ! empty($leftOut['department']) && $leftOut['department'] !== 'Left Outs'
                        ? $leftOut['department']
                        : 'Left Outs';

                    $deptRank = $employee?->department?->rank ?? ($employee?->dp_rank ?? 999);
                    $desigRank = $employee?->designation?->rank ?? ($employee?->rank ?? 999);

                    $baseTips = (float) ($leftOut['base_tips_amount'] ?? ($employee?->tips_amount ?? 0));
                    $tipsPct = (float) ($leftOut['tips_percentage'] ?? 100);
                    $tipsStatus = (string) ($employee?->tips_status ?? ($leftOut['tips_status'] ?? 'Release'));

                    if (strtoupper(trim($tipsStatus)) === 'HOLD') {
                        $calculatedTips = 0.0;
                        $unroundedAmount = 0.0;
                        $finalDist = 0.0;
                    } else {
                        if (isset($leftOut['final_distribution_amount']) && $leftOut['final_distribution_amount'] !== null && $leftOut['final_distribution_amount'] !== '') {
                            $rawDist = (float) $leftOut['final_distribution_amount'];
                        } else {
                            $rawDist = ($baseTips * $tipsPct) / 100;
                        }
                        $unroundedAmount = max(0.0, round($rawDist, 2));
                        $finalDist = $rawDist > 0 ? (float) (ceil($rawDist / 100) * 100) : 0.0;
                        $calculatedTips = $finalDist;
                    }

                    TipsReportItem::create([
                        'tips_report_id' => $report->id,
                        'employee_id' => $code,
                        'employee_code' => $code,
                        'department' => $deptName,
                        'department_rank' => $deptRank,
                        'designation' => $desigName,
                        'designation_rank' => $desigRank,
                        'employee_name' => $empName,
                        'working_days' => 0,
                        'present_days' => 0,
                        'absent_days' => 0,
                        'total_leaves' => 0,
                        'late_in_count' => 0,
                        'early_out_count' => 0,
                        'join_date' => $employee?->join_date_formatted,
                        'working_duration' => 'Left Out Override',
                        'completion_factor' => 1.0,
                        'point_value' => (float) ($employee?->point_value ?? 0),
                        'base_tips_amount' => $baseTips,
                        'tips_percentage' => $tipsPct,
                        'is_blank' => false,
                        'is_fixed' => true,
                        'publish_tips' => true,
                        'tips_status' => $tipsStatus,
                        'amount_to_adjust' => 0,
                        'amount_to_deduct' => 0,
                        'percentage_to_deduct' => 0,
                        'calculated_tips' => $calculatedTips,
                        'unrounded_amount' => $unroundedAmount,
                        'final_distribution_amount' => $finalDist,
                        'is_left_out' => true,
                    ]);
                }
            }

            // 6. Process Active Non-Employees (None Employee sheet)
            $activeNonEmployees = TipsNonEmployee::where('is_active', true)->orderBy('code')->get();
            foreach ($activeNonEmployees as $nonEmp) {
                $code = (string) $nonEmp->code;
                $empName = (string) $nonEmp->name;
                $desig = (string) ($nonEmp->designation ?? '-');
                $tipsPct = (float) ($nonEmp->tips_percentage ?? 100);
                $baseAmount = (float) ($nonEmp->distribution_amount ?? 0);

                $finalAmt = ($baseAmount * $tipsPct) / 100;
                $roundedAmt = $finalAmt > 0 ? (float) (ceil($finalAmt / 100) * 100) : 0.0;

                TipsReportItem::create([
                    'tips_report_id' => $report->id,
                    'employee_id' => $code,
                    'employee_code' => $code,
                    'department' => 'None Employee',
                    'department_rank' => 998,
                    'designation' => $desig,
                    'designation_rank' => 998,
                    'employee_name' => $empName,
                    'working_days' => 0,
                    'present_days' => 0,
                    'absent_days' => 0,
                    'total_leaves' => 0,
                    'late_in_count' => 0,
                    'early_out_count' => 0,
                    'join_date' => null,
                    'working_duration' => 'Non-Employee',
                    'completion_factor' => 1.0,
                    'point_value' => 0,
                    'base_tips_amount' => $baseAmount,
                    'tips_percentage' => $tipsPct,
                    'is_blank' => false,
                    'is_fixed' => true,
                    'publish_tips' => true,
                    'tips_status' => 'Release',
                    'amount_to_adjust' => 0,
                    'amount_to_deduct' => 0,
                    'percentage_to_deduct' => 0,
                    'calculated_tips' => $roundedAmt,
                    'unrounded_amount' => $finalAmt,
                    'final_distribution_amount' => $roundedAmt,
                    'is_left_out' => false,
                ]);
            }

            // Update status to generated
            $report->update(['status' => 'generated']);
        });
    }

    /**
     * Parse HRMS Attendance file (XLSX or CSV).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseHrmsFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->parseCsv($filePath);
        }

        return $this->parseXlsx($filePath);
    }

    /**
     * Parse XLSX using OpenSpout.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseXlsx(string $filePath): array
    {
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
            break; // Read first sheet
        }

        $reader->close();

        return $this->parseRowsToAttendanceMetrics($rows);
    }

    /**
     * Fallback CSV parser.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseCsv(string $filePath): array
    {
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

        return $this->parseRowsToAttendanceMetrics($rows);
    }

    /**
     * Process parsed sheet rows into structured attendance metrics.
     * Handles both HRMS leave-balance exports (two-tier headers with Used Balance columns)
     * and single-tier summary exports.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function parseRowsToAttendanceMetrics(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        // 1. Locate header row containing 'username' or ('full name' and 'id')
        $headerRowIdx = null;
        $categoryRowIdx = null;

        for ($i = 0; $i < min(15, count($rows)); $i++) {
            $normalized = array_map(fn ($c) => strtolower(trim((string) $c)), $rows[$i]);
            if (in_array('username', $normalized) || (in_array('full name', $normalized) && in_array('id', $normalized))) {
                $headerRowIdx = $i;
                if ($i > 0) {
                    $categoryRowIdx = $i - 1;
                }
                break;
            }
        }

        if ($headerRowIdx === null) {
            return [];
        }

        $headerRow = $rows[$headerRowIdx];
        $categoryRow = $categoryRowIdx !== null ? $rows[$categoryRowIdx] : [];

        $usernameCol = null;
        $fullNameCol = null;
        $idCol = null;
        $lateInCol = null;
        $earlyOutCol = null;
        $workingDaysCol = null;
        $presentDaysCol = null;
        $absentDaysCol = null;
        $totalLeaveCol = null;
        $leaveCols = [];
        $absentCols = [];

        foreach ($headerRow as $idx => $cell) {
            $norm = strtolower(trim((string) $cell));
            $catNorm = isset($categoryRow[$idx]) ? strtolower(trim((string) $categoryRow[$idx])) : '';

            if ($norm === 'username') {
                $usernameCol = $idx;
            }
            if ($norm === 'full name') {
                $fullNameCol = $idx;
            }
            if ($norm === 'id') {
                $idCol = $idx;
            }
            if (in_array($norm, ['working days', 'working_days']) || in_array($catNorm, ['working days', 'working_days'])) {
                $workingDaysCol = $idx;
            }
            if (in_array($norm, ['present days', 'present_days']) || in_array($catNorm, ['present days', 'present_days'])) {
                $presentDaysCol = $idx;
            }
            if (in_array($norm, ['absent days', 'absent_days', 'absent']) || in_array($catNorm, ['absent days', 'absent_days', 'absent'])) {
                $absentDaysCol = $idx;
            }
            if (in_array($norm, ['total leave', 'total leaves', 'total_leave', 'total_leaves']) || in_array($catNorm, ['total leave', 'total leaves', 'total_leave', 'total_leaves'])) {
                $totalLeaveCol = $idx;
            }

            // Late in / Early out check on either headerRow or categoryRow
            if (in_array($norm, ['late in count', 'late in', 'late_in_count']) || in_array($catNorm, ['late in count', 'late in', 'late_in_count'])) {
                $lateInCol = $idx;
            }

            if (in_array($norm, ['early out count', 'early out', 'early_out_count']) || in_array($catNorm, ['early out count', 'early out', 'early_out_count'])) {
                $earlyOutCol = $idx;
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
                } else {
                    $leaveCols[$idx] = $cat;
                }
            }
        }

        $results = [];

        for ($r = $headerRowIdx + 1; $r < count($rows); $r++) {
            $cells = $rows[$r];
            $username = $usernameCol !== null ? trim((string) ($cells[$usernameCol] ?? '')) : '';
            if ($username === '') {
                continue;
            }

            // Absent days: use explicit absent days column if present; otherwise sum LOP/Absent used balances
            $absentDays = 0.0;
            if ($absentDaysCol !== null && is_numeric($cells[$absentDaysCol] ?? null)) {
                $absentDays = (float) $cells[$absentDaysCol];
            } else {
                foreach ($absentCols as $cIdx => $name) {
                    $v = $cells[$cIdx] ?? 0;
                    if ($v !== '-' && is_numeric($v) && (float) $v > 0) {
                        $absentDays += (float) $v;
                    }
                }
            }

            // Total leaves: use explicit total leave column if present; otherwise sum individual leave used balances
            $totalLeaves = 0.0;
            if ($totalLeaveCol !== null && is_numeric($cells[$totalLeaveCol] ?? null)) {
                $totalLeaves = (float) $cells[$totalLeaveCol];
            } elseif (! empty($leaveCols)) {
                foreach ($leaveCols as $cIdx => $name) {
                    $v = $cells[$cIdx] ?? 0;
                    if ($v !== '-' && is_numeric($v) && (float) $v > 0) {
                        $totalLeaves += (float) $v;
                    }
                }
            }

            $workingDays = $workingDaysCol !== null && is_numeric($cells[$workingDaysCol] ?? null)
                ? (float) $cells[$workingDaysCol]
                : 0.0;

            $presentDays = $presentDaysCol !== null && is_numeric($cells[$presentDaysCol] ?? null)
                ? (float) $cells[$presentDaysCol]
                : 0.0;

            $lateIn = $lateInCol !== null && is_numeric($cells[$lateInCol] ?? null)
                ? (int) $cells[$lateInCol]
                : 0;

            $earlyOut = $earlyOutCol !== null && is_numeric($cells[$earlyOutCol] ?? null)
                ? (int) $cells[$earlyOutCol]
                : 0;

            $fullName = $fullNameCol !== null ? trim((string) ($cells[$fullNameCol] ?? '')) : null;
            $id = $idCol !== null ? ($cells[$idCol] ?? null) : null;

            $results[] = [
                'id' => $id,
                'full_name' => $fullName,
                'username' => $username,
                'working_days' => $workingDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'total_leave' => $totalLeaves,
                'late_in_count' => $lateIn,
                'early_out_count' => $earlyOut,
            ];
        }

        return $results;
    }

    /**
     * Calculate tenure string and completion factor (Date of Completion: 0.1 per full year of tenure) against cutoff date.
     *
     * @return array{0: string, 1: float}
     */
    public function calculateTenure(?string $joinDateRaw, Carbon $cutoffDate): array
    {
        if (empty($joinDateRaw)) {
            return ['No join date recorded', 0.0];
        }

        try {
            $cleanedDate = str_replace(',', '', $joinDateRaw);
            $joinCarbon = Carbon::parse($cleanedDate)->startOfDay();

            if ($joinCarbon->greaterThan($cutoffDate)) {
                return ['Joined after cutoff date', 0.0];
            }

            $diff = $joinCarbon->diff($cutoffDate);
            $parts = [];
            if ($diff->y > 0) {
                $parts[] = $diff->y.' yr'.($diff->y > 1 ? 's' : '');
            }
            if ($diff->m > 0) {
                $parts[] = $diff->m.' mo'.($diff->m > 1 ? 's' : '');
            }
            if ($diff->d > 0 || empty($parts)) {
                $parts[] = $diff->d.' day'.($diff->d > 1 ? 's' : '');
            }
            $durationStr = implode(', ', $parts);

            // Date of Completion (DOC): 0.1 for 1 year, 0.2 for 2 years, etc.
            $factor = round($diff->y * 0.1, 2);

            return [$durationStr, $factor];
        } catch (\Throwable $e) {
            return ['Unparseable join date', 0.0];
        }
    }

    /**
     * Calculate tips percentage based on attendance metrics:
     * - If absent count > 0: tips percentage is directly 0%
     * - If total leaves <= 3: 100% (not affected)
     * - If total leaves > 3: deduct 5% for every 0.5 step increase over 3.0 (e.g. 3.5 => 95%, 4.0 => 90%)
     */
    public function calculateTipsPercentage(float $workingDays, float $presentDays, float $absentDays, float $totalLeaves): float
    {
        // 1. If absent count > 0 => 0%
        if ($absentDays > 0) {
            return 0.0;
        }

        // 2. If leave <= 3 => 100%
        if ($totalLeaves <= 3.0) {
            return 100.0;
        }

        // 3. For every 0.5 step increase over 3.0, deduct 5%
        $excessLeaves = round($totalLeaves - 3.0, 2);
        $halfDaySteps = (int) floor(($excessLeaves + 0.0001) / 0.5);
        $penalty = $halfDaySteps * 5.0;

        $tipsPercentage = max(0.0, 100.0 - $penalty);

        return round($tipsPercentage, 2);
    }
}
