<?php

namespace App\Http\Controllers;

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
                ->orderBy('employee_code')
                ->get();

            return response()->view('tips.none-employee-sheet', [
                'report' => $report,
                'department' => 'None Employee',
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

        return response()->view('tips.payout-sheet', [
            'report' => $report,
            'department' => $deptTitle,
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

        $itemsByDept = $report->items()
            ->selectRaw('department, is_left_out, sum(final_distribution_amount) as total')
            ->groupBy('department', 'is_left_out')
            ->get();

        $leftOutsTotal = (float) $itemsByDept->where('is_left_out', true)->sum('total');
        if ($leftOutsTotal == 0) {
            $leftOutsTotal = (float) $itemsByDept->where('department', 'Left Outs')->sum('total');
        }

        $deptTotals = [];
        foreach ($itemsByDept->where('is_left_out', false) as $row) {
            if ($row->department === 'Left Outs') {
                continue;
            }
            $deptTotals[$row->department] = (float) $row->total;
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

        $adjustmentsTotal = (float) $report->items()->sum('amount_to_adjust') - (float) $report->items()->sum('amount_to_deduct');
        $adjustmentsLeftOuts = $leftOutsTotal + max(0, $adjustmentsTotal);
        if ($adjustmentsLeftOuts == 0 && $leftOutsTotal > 0) {
            $adjustmentsLeftOuts = $leftOutsTotal;
        }

        $companyShouldAdd = abs($actualTotalCollection + $adjustmentsLeftOuts - $totalToDistribute);

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
}
