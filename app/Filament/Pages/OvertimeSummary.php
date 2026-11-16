<?php

namespace App\Filament\Pages;

use App\Models\OvertimeEntry;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class OvertimeSummary extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Overtime';

    protected static ?string $navigationLabel = 'OT Monthly Summary';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.overtime-summary';

    public int $month;

    public int $year;

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    public function getBlockSummary(): Collection
    {
        return OvertimeEntry::with('block')
            ->whereMonth('ot_date', $this->month)
            ->whereYear('ot_date', $this->year)
            ->get()
            ->groupBy('ot_block_id')
            ->map(function (Collection $entries): array {
                $blockName = $entries->first()?->block?->name ?? 'Unknown';
                $normal = (float) $entries->where('is_special_ot', false)->sum('hours');
                $special = (float) $entries->where('is_special_ot', true)->sum('hours');

                return [
                    'block' => $blockName,
                    'normal_hours' => $normal,
                    'special_hours' => $special,
                    'total_hours' => $normal + $special,
                    'entries_count' => $entries->count(),
                ];
            })
            ->sortBy('block')
            ->values();
    }

    public function getEmployeeSummary(): Collection
    {
        return OvertimeEntry::with(['employee.department', 'employee.designation', 'block'])
            ->whereMonth('ot_date', $this->month)
            ->whereYear('ot_date', $this->year)
            ->get()
            ->groupBy('employee_id')
            ->map(function (Collection $entries, string $employeeId): array {
                $emp = $entries->first()?->employee;
                $normal = (float) $entries->where('is_special_ot', false)->sum('hours');
                $special = (float) $entries->where('is_special_ot', true)->sum('hours');

                $blocksBreakdown = $entries
                    ->groupBy(fn ($e) => $e->block?->name ?? 'Unknown')
                    ->map(fn (Collection $bEntries) => (float) $bEntries->sum('hours'))
                    ->toArray();

                return [
                    'employee_code' => $employeeId,
                    'name' => $emp?->name ?? 'Unknown',
                    'department' => $emp?->department?->name ?? '—',
                    'designation' => $emp?->designation?->name ?? '—',
                    'normal_hours' => $normal,
                    'special_hours' => $special,
                    'total_hours' => $normal + $special,
                    'blocks' => $blocksBreakdown,
                ];
            })
            ->sortBy('employee_code')
            ->values();
    }
}
