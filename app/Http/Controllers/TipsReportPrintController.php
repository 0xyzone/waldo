<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TipsAdjustment;
use App\Models\TipsDepartmentMapping;
use App\Models\TipsReport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TipsReportPrintController extends Controller
{
    /**
     * Print department payout sheet.
     */
    public function printDepartment(Request $request, TipsReport $report, string $department): Response
    {
        $query = $report->items();

        if (in_array(strtolower(trim($department)), ['none employee', 'non employee', 'none-employee'])) {
            $items = $query->where('department', 'None Employee')
                ->orderByNumericCode()
                ->get();

            $selectedDepartments = TipsDepartmentMapping::getDepartmentNamesForPage('None Employee');
            if (empty($selectedDepartments) && $items->isNotEmpty()) {
                $selectedDepartments = $items->loadMissing('employee.department')
                    ->map(fn ($item) => $item->employee?->department?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }

            return response()->view('tips.none-employee-sheet', [
                'report' => $report,
                'department' => 'None Employee',
                'selectedDepartments' => $selectedDepartments,
                'items' => $items,
            ]);
        }

        if (strtolower($department) === 'left outs') {
            $items = $query->where(function ($q) {
                $q->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })
                ->orderByHierarchy()
                ->get();
            $deptTitle = 'Left Outs';
        } else {
            $items = $query->where('department', $department)
                ->where('is_left_out', false)
                ->orderByHierarchy()
                ->get();
            $deptTitle = $department;
        }

        $selectedDepartments = TipsDepartmentMapping::getDepartmentNamesForPage($deptTitle);
        if (empty($selectedDepartments) && $items->isNotEmpty()) {
            $selectedDepartments = $items->loadMissing('employee.department')
                ->map(fn ($item) => $item->employee?->department?->name)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return response()->view('tips.payout-sheet', [
            'report' => $report,
            'department' => $deptTitle,
            'selectedDepartments' => $selectedDepartments,
            'items' => $items,
        ]);
    }

    /**
     * Print Tips Distribution Summary (Screenshot 1).
     */
    public function printSummary(Request $request, TipsReport $report): Response
    {
        $collections = collect($report->collection_summary ?? []);

        // Helper to sum by department keywords
        $getSum = function (array $names) use ($collections) {
            $matched = $collections->filter(function ($item) use ($names) {
                $dept = strtolower(trim((string) ($item['department'] ?? '')));
                foreach ($names as $n) {
                    if (str_contains($dept, strtolower($n))) {
                        return true;
                    }
                }

                return false;
            });

            return [
                'chips' => (float) $matched->sum('chips_amount'),
                'cash' => (float) $matched->sum('cash_amount'),
                'total' => (float) $matched->sum('total_amount'),
            ];
        };

        $pit = $getSum(['pit', 'slot', 'gaming', 'surveillance']);
        $cage = $getSum(['cage']);
        $fb = $getSum(['f&b', 'food']);
        $cs = $getSum(['reception', 'customer service', 'gr']);
        $hk = $getSum(['housekeeping']);
        $sec = $getSum(['security', 'bouncer', 'transport']);

        $pitBO = -round($pit['total'] * 0.05);
        $cageBO = -round($cage['total'] * 0.10);
        $boTotal = abs($pitBO) + abs($cageBO);

        $rows = [
            [
                'name' => 'PIT(Surveillance+Slot+Gaming)',
                'chips' => $pit['chips'],
                'cash' => $pit['cash'],
                'total' => $pit['total'],
                'bo' => $pitBO,
                'transfer' => -60000,
                'remaining' => $pit['total'] + $pitBO - 60000,
                'round_up' => $pit['total'] + $pitBO - 60000,
            ],
            [
                'name' => 'Cage',
                'chips' => $cage['chips'],
                'cash' => $cage['cash'],
                'total' => $cage['total'],
                'bo' => $cageBO,
                'transfer' => 0,
                'remaining' => $cage['total'] + $cageBO,
                'round_up' => $cage['total'] + $cageBO,
            ],
            [
                'name' => 'F&B Service',
                'chips' => $fb['chips'],
                'cash' => $fb['cash'],
                'total' => $fb['total'],
                'bo' => 0,
                'transfer' => 0,
                'remaining' => $fb['total'],
                'round_up' => $fb['total'],
            ],
            [
                'name' => 'Customer Service',
                'chips' => $cs['chips'],
                'cash' => $cs['cash'],
                'total' => $cs['total'],
                'bo' => 0,
                'transfer' => 0,
                'remaining' => $cs['total'],
                'round_up' => $cs['total'],
            ],
            [
                'name' => 'Housekeeping',
                'chips' => $hk['chips'],
                'cash' => $hk['cash'],
                'total' => $hk['total'],
                'bo' => 0,
                'transfer' => 30000,
                'remaining' => $hk['total'] + 30000,
                'round_up' => $hk['total'] + 30000,
            ],
            [
                'name' => 'Security + Bouncer',
                'chips' => $sec['chips'],
                'cash' => $sec['cash'],
                'total' => $sec['total'],
                'bo' => 0,
                'transfer' => 30000,
                'remaining' => $sec['total'] + 30000,
                'round_up' => $sec['total'] + 30000,
            ],
            [
                'name' => 'Back Office',
                'chips' => 0,
                'cash' => 0,
                'total' => 0,
                'bo' => $boTotal,
                'transfer' => 0,
                'remaining' => $boTotal,
                'round_up' => $boTotal,
            ],
        ];

        return response()->view('tips.print-summary', [
            'report' => $report,
            'rows' => $rows,
            'totalChips' => collect($rows)->sum('chips'),
            'totalCash' => collect($rows)->sum('cash'),
            'totalAmount' => collect($rows)->sum('total'),
            'totalBO' => collect($rows)->sum('bo'),
            'totalTransfer' => collect($rows)->sum('transfer'),
            'totalRemaining' => collect($rows)->sum('remaining'),
            'totalRoundUp' => collect($rows)->sum('round_up'),
        ]);
    }

    /**
     * Print Tips Distribution Totals (Screenshot 2).
     */
    public function printTotals(Request $request, TipsReport $report): Response
    {
        $collections = collect($report->collection_summary ?? []);
        $actualTotalCollection = (float) $collections->sum('total_amount');

        // Preferred order from Screenshot 2
        $preferredOrder = [
            'PIT',
            'Customer Service',
            'F&B',
            'Housekeeping',
            'Security + Transport',
            'Kitchen',
            'None Employee',
            'Cage',
            'Back Office',
        ];

        $allItems = $report->items()->get();

        $leftOutsTotal = (float) $allItems->filter(function ($item) {
            return (bool) $item->is_left_out || $item->department === 'Left Outs';
        })->sum('final_distribution_amount');

        $deptTotals = [];
        foreach ($allItems->where('is_left_out', false) as $row) {
            if ($row->department === 'Left Outs') {
                continue;
            }
            $deptName = (string) $row->department;
            $deptTotals[$deptName] = ($deptTotals[$deptName] ?? 0.0) + (float) $row->final_distribution_amount;
        }

        $takeMatching = function (array $keywords) use (&$deptTotals): float {
            $sum = 0.0;
            foreach ($deptTotals as $name => $amount) {
                $lower = strtolower(trim((string) $name));
                foreach ($keywords as $kw) {
                    if (str_contains($lower, strtolower($kw))) {
                        $sum += $amount;
                        unset($deptTotals[$name]);
                        break;
                    }
                }
            }

            return $sum;
        };

        $deptList = [
            ['name' => 'PIT', 'amount' => $takeMatching(['pit', 'surveillance', 'slot', 'gaming'])],
            ['name' => 'Customer Service', 'amount' => $takeMatching(['customer service', 'reception', 'gr', 'front office'])],
            ['name' => 'F&B', 'amount' => $takeMatching(['f&b', 'food', 'beverage', 'restaurant'])],
            ['name' => 'Housekeeping', 'amount' => $takeMatching(['housekeeping', 'hk'])],
            ['name' => 'Security + Transport', 'amount' => $takeMatching(['security', 'bouncer', 'transport', 'driver'])],
            ['name' => 'Kitchen', 'amount' => $takeMatching(['kitchen', 'culinary', 'cook'])],
            ['name' => 'None Employee', 'amount' => ($noneEmp = $takeMatching(['none employee', 'non employee'])) > 0 ? $noneEmp : $leftOutsTotal],
            ['name' => 'Cage', 'amount' => $takeMatching(['cage'])],
        ];

        // Back Office gets back office keywords + any remaining unassigned departments
        $backOfficeAmt = $takeMatching(['back office', 'admin', 'hr', 'it', 'accounts', 'finance']);
        foreach ($deptTotals as $remDept => $amt) {
            $backOfficeAmt += $amt;
        }
        $deptList[] = ['name' => 'Back Office', 'amount' => $backOfficeAmt];

        $totalToDistribute = collect($deptList)->sum('amount');

        $adjustmentsLeftOuts = $leftOutsTotal;

        $companyShouldAdd = abs($actualTotalCollection + $leftOutsTotal - $totalToDistribute);

        $n = count($deptList);
        if ($n === 9) {
            $s1 = 2; // Rows 1-2: PIT, Customer Service
            $s2 = 2; // Rows 3-4: F&B, Housekeeping
            $s3 = 3; // Rows 5-7: Security + Transport, Kitchen, None Employee
            $s4 = 2; // Rows 8-9: Cage, Back Office
        } else {
            $s1 = (int) max(1, floor($n / 4));
            $s2 = (int) max(1, floor(($n - $s1) / 3));
            $s3 = (int) max(1, floor(($n - $s1 - $s2) / 2));
            $s4 = max(1, $n - $s1 - $s2 - $s3);
        }

        return response()->view('tips.print-totals', [
            'report' => $report,
            'departments' => $deptList,
            'actualTotalCollection' => $actualTotalCollection,
            'adjustmentsLeftOuts' => $adjustmentsLeftOuts,
            'totalToDistribute' => $totalToDistribute,
            'companyShouldAdd' => $companyShouldAdd,
            'span1Start' => 0,
            'span1Count' => $s1,
            'span2Start' => $s1,
            'span2Count' => $s2,
            'span3Start' => $s1 + $s2,
            'span3Count' => $s3,
            'span4Start' => $s1 + $s2 + $s3,
            'span4Count' => $s4,
        ]);
    }

    /**
     * Print Tips Adjustments and Company Error Penalties Audit Sheet.
     */
    public function printAdjustmentsErrors(Request $request, TipsReport $report): Response
    {
        $month = strtolower((string) ($report->month ?? ''));
        $year = (string) ($report->year ?? '');

        $adjustmentsQuery = TipsAdjustment::with(['employee.department', 'employee.designation']);
        if ($month !== '') {
            $adjustmentsQuery->whereRaw('LOWER(for_month) = ?', [$month]);
        }
        if ($year !== '') {
            $adjustmentsQuery->where('year', $year);
        }
        $adjustmentsQuery->where(function ($q) {
            $q->whereNull('status')->orWhereRaw('LOWER(status) != ?', ['cancelled']);
        });

        $reportItems = $report->items->keyBy(fn ($i) => strtoupper(trim((string) $i->employee_code)));

        $adjustments = $adjustmentsQuery->get()
            ->filter(function ($adj) use ($reportItems) {
                // Skip cancelled adjustments
                if (strtolower(trim((string) ($adj->status ?? ''))) === 'cancelled') {
                    return false;
                }

                // Skip adjustments for employees not in the sheet
                $code = strtoupper(trim((string) $adj->employee_id));

                return $code !== '' && $reportItems->has($code);
            })
            ->map(function ($adj) use ($reportItems) {
                $code = strtoupper(trim((string) $adj->employee_id));
                $item = $reportItems->get($code);

                return [
                    'employee_code' => $code,
                    'employee_name' => $item?->employee_name ?? $adj->employee?->name ?? 'Unknown',
                    'department' => $item?->department ?? $adj->employee?->department?->name ?? '-',
                    'designation' => $item?->designation ?? $adj->employee?->designation?->name ?? '-',
                    'type' => strtolower((string) ($adj->type ?? 'add')),
                    'amount' => (float) ($adj->amount ?? 0),
                    'status' => strtolower((string) ($adj->status ?? 'pending')),
                    'remarks' => trim((string) ($adj->remarks ?? '')),
                    'is_applied' => true,
                ];
            })
            ->values();

        $rawErrors = $report->company_errors ?? [];
        $errors = collect(is_array($rawErrors) ? $rawErrors : [])
            ->filter(function ($err) use ($reportItems) {
                // Skip errors for employees not in the sheet
                $code = strtoupper(trim((string) ($err['employee_code'] ?? '')));

                return $code !== '' && $reportItems->has($code);
            })
            ->map(function ($err) use ($reportItems) {
                $code = strtoupper(trim((string) ($err['employee_code'] ?? '')));
                $item = $reportItems->get($code);
                $pct = (float) ($err['percentage_to_deduct'] ?? 0);
                $reason = trim((string) ($err['reason'] ?? ''));

                $estimatedDeduction = 0.0;
                if ($item) {
                    $multiplier = $item->is_fixed ? 1.0 : ($item->completion_factor + $item->point_value);
                    $adjustedBase = ($item->base_tips_amount ?? 0) * $multiplier;
                    $estimatedDeduction = round(($adjustedBase * $pct) / 100, 2);
                }

                return [
                    'employee_code' => $code,
                    'employee_name' => $item?->employee_name ?? $code,
                    'department' => $item?->department ?? '-',
                    'designation' => $item?->designation ?? '-',
                    'percentage_to_deduct' => $pct,
                    'reason' => $reason,
                    'base_tips' => $item?->base_tips_amount ?? 0,
                    'estimated_deduction' => $estimatedDeduction,
                    'is_applied' => true,
                ];
            })
            ->values();

        $rawHeld = $report->held_tips ?? [];
        $heldByCode = collect(is_array($rawHeld) ? $rawHeld : [])->keyBy(fn ($h) => strtoupper(trim((string) ($h['employee_code'] ?? ''))));

        $holdItems = $report->items->filter(fn ($i) => strtoupper(trim((string) $i->tips_status)) === 'HOLD');
        $allHeldCodes = $heldByCode->keys()
            ->merge($holdItems->map(fn ($i) => strtoupper(trim((string) $i->employee_code))))
            ->filter()
            ->unique()
            ->values();

        if ($employees === null) {
            $employees = Employee::with(['department', 'designation'])
                ->get()
                ->keyBy(fn ($e) => strtoupper(trim((string) $e->employee_code)));
        }

        $heldTips = $allHeldCodes->map(function ($code) use ($heldByCode, $reportItems, $employees, $report) {
            $item = $reportItems->get($code);
            $heldData = $heldByCode->get($code);
            $emp = $employees->get($code);

            $name = $item?->employee_name ?? $emp?->name;
            $dept = $item?->department ?? ($emp?->department?->name ?? '-');
            $desig = $item?->designation ?? ($emp?->designation?->name ?? '-');
            $baseTips = (float) ($item?->base_tips_amount ?? ($emp?->tips_amount ?? 0));

            $reason = trim((string) ($heldData['reason'] ?? ($item ? 'Employee tips status set to Hold' : 'Tips placed on Hold')));

            $heldAmount = (float) ($baseTips ?? 0);
            $currentTipsStatus = $report->isValidated() ? 'Release' : ($emp?->tips_status ?? ($item?->tips_status ?? 'Hold'));

            return [
                'employee_code' => $code,
                'employee_name' => $name ?? $code,
                'department' => $dept,
                'designation' => $desig,
                'base_tips' => $baseTips,
                'held_amount' => $heldAmount,
                'reason' => $reason,
                'is_applied' => $item !== null,
                'tips_status' => $currentTipsStatus,
            ];
        });

        return response()->view('tips.print-adjustments-errors', [
            'report' => $report,
            'adjustments' => $adjustments,
            'errors' => $errors,
            'heldTips' => $heldTips,
            'totalAdd' => (float) $adjustments->where('type', 'add')->sum('amount'),
            'totalDeduct' => (float) $adjustments->where('type', 'deduct')->sum('amount'),
            'totalErrorDeduction' => (float) $errors->sum('estimated_deduction'),
            'totalHeldAmount' => (float) $heldTips->sum('held_amount'),
        ]);
    }
}
