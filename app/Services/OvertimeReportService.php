<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OtBlock;
use App\Models\OvertimeAdjustment;
use App\Models\OvertimeEntry;
use App\Models\OvertimeReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OvertimeReportService
{
    /**
     * Build block-wise and employee-wise OT summary (including adjustments) for a month/year.
     *
     * @return array{
     *     report: OvertimeReport|null,
     *     periodLabel: string,
     *     blockSummary: Collection<int, array<string, mixed>>,
     *     employeeBlockSummary: Collection<int, array<string, mixed>>,
     *     allBlocks: array<int, string>
     * }
     */
    public static function buildPeriodSummaryData(int $month, int $year): array
    {
        $entries = OvertimeEntry::with('block:id,name')
            ->whereMonth('ot_date', $month)
            ->whereYear('ot_date', $year)
            ->get();

        $report = OvertimeReport::where('month', $month)->where('year', $year)->first();
        $adjustments = $report
            ? OvertimeAdjustment::with('block:id,name')
                ->where('overtime_report_id', $report->id)
                ->get()
            : collect();

        $blockMap = [];
        foreach ($entries as $entry) {
            $bId = $entry->ot_block_id;
            $bName = $entry->block?->name ?? 'Unknown';
            if (! isset($blockMap[$bId])) {
                $blockMap[$bId] = [
                    'block' => $bName,
                    'normal_hours' => 0.0,
                    'special_hours' => 0.0,
                    'entries_count' => 0,
                    'employees' => [],
                ];
            }
            $hrs = (float) $entry->hours;
            if ($entry->is_special_ot) {
                $blockMap[$bId]['special_hours'] += $hrs;
            } else {
                $blockMap[$bId]['normal_hours'] += $hrs;
            }
            $blockMap[$bId]['entries_count']++;
            $blockMap[$bId]['employees'][$entry->employee_id] = true;
        }

        foreach ($adjustments as $adj) {
            if (! $adj->ot_block_id) {
                continue;
            }
            $bId = $adj->ot_block_id;
            $bName = $adj->block?->name ?? 'Unknown';
            if (! isset($blockMap[$bId])) {
                $blockMap[$bId] = [
                    'block' => $bName,
                    'normal_hours' => 0.0,
                    'special_hours' => 0.0,
                    'entries_count' => 0,
                    'employees' => [],
                ];
            }
            $hrs = (float) $adj->hours;
            if ($adj->ot_type === 'special') {
                $blockMap[$bId]['special_hours'] += $hrs;
            } else {
                $blockMap[$bId]['normal_hours'] += $hrs;
            }
            $blockMap[$bId]['entries_count']++;
            $blockMap[$bId]['employees'][$adj->employee_id] = true;
        }

        $blockSummary = collect($blockMap)
            ->map(fn (array $b): array => [
                'block' => $b['block'],
                'normal_hours' => $b['normal_hours'],
                'special_hours' => $b['special_hours'],
                'total_hours' => $b['normal_hours'] + $b['special_hours'],
                'entries_count' => $b['entries_count'],
                'employees_count' => count($b['employees']),
            ])
            ->sortBy('block')
            ->values();

        $allEmpCodes = $entries->pluck('employee_id')
            ->merge($adjustments->pluck('employee_id'))
            ->unique()
            ->values();

        $employeesByCode = Employee::with(['department:id,name', 'designation:id,name'])
            ->whereIn('employee_code', $allEmpCodes)
            ->get()
            ->keyBy('employee_code');

        $entriesByEmployee = $entries->groupBy('employee_id');
        $adjustmentsByEmployee = $adjustments->groupBy('employee_id');

        $employeeBlockSummary = $allEmpCodes
            ->map(function (string $empCode) use ($entriesByEmployee, $adjustmentsByEmployee, $employeesByCode): array {
                $group = $entriesByEmployee->get($empCode, collect());
                $empAdjustments = $adjustmentsByEmployee->get($empCode, collect());
                $emp = $employeesByCode->get($empCode);

                $rawNormal = (float) $group->where('is_special_ot', false)->sum('hours');
                $rawSpecial = (float) $group->where('is_special_ot', true)->sum('hours');
                $normalAdj = (float) $empAdjustments->where('ot_type', 'normal')->sum('hours');
                $specialAdj = (float) $empAdjustments->where('ot_type', 'special')->sum('hours');

                $netNormal = $rawNormal + $normalAdj;
                $netSpecial = $rawSpecial + $specialAdj;

                $blocks = [];
                foreach ($group as $entry) {
                    $blkName = $entry->block?->name ?? 'Unknown';
                    $blocks[$blkName] = ($blocks[$blkName] ?? 0.0) + (float) $entry->hours;
                }
                foreach ($empAdjustments as $adj) {
                    if ($adj->ot_block_id) {
                        $blkName = $adj->block?->name ?? 'Unknown';
                        $blocks[$blkName] = ($blocks[$blkName] ?? 0.0) + (float) $adj->hours;
                    }
                }

                $remarks = $empAdjustments
                    ->map(function (OvertimeAdjustment $a): ?string {
                        $typeLabel = $a->ot_type === 'special' ? 'Special' : 'Normal';
                        $hrsLabel = ($a->hours > 0 ? '+' : '').number_format((float) $a->hours, 2).'h';
                        $dateLabel = $a->adjustment_date?->format('d M, Y');
                        $prefix = $dateLabel ? "{$typeLabel} {$hrsLabel} ({$dateLabel})" : "{$typeLabel} {$hrsLabel}";

                        return filled($a->remarks) ? "{$prefix}: {$a->remarks}" : $prefix;
                    })
                    ->filter()
                    ->implode(' | ');

                return [
                    'employee_code' => $empCode,
                    'sort_num' => (int) preg_replace('/\D+/', '', $empCode),
                    'name' => $emp?->name ?? 'Unknown',
                    'department' => $emp?->department?->name ?? '—',
                    'designation' => $emp?->designation?->name ?? '—',
                    'normal_adj' => $normalAdj,
                    'raw_normal_hours' => $rawNormal,
                    'normal_hours' => $netNormal,
                    'special_adj' => $specialAdj,
                    'raw_special_hours' => $rawSpecial,
                    'special_hours' => $netSpecial,
                    'total_hours' => $netNormal + $netSpecial,
                    'remarks' => $remarks,
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
}
