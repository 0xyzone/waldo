<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Models\Employee;
use App\Models\OvertimeAdjustment;
use App\Models\OvertimeEntry;
use App\Models\OvertimeReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class OvertimeSummary extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Overtime';

    protected static ?string $navigationLabel = 'OT Monthly Summary';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.overtime-summary';

    public int $month;

    public int $year;

    public static function getShieldPagePermissions(): array
    {
        return [
            'view' => 'View OT Monthly Summary',
            'print' => 'Print Block-wise & Finance Summary',
            'export' => 'Download Excel Exports',
        ];
    }

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printBlockwise')
                ->label('Print Block-wise OT')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->visible(fn (): bool => static::canShield('print'))
                ->url(fn (): string => route('overtime.summary.print-blockwise', ['month' => $this->month, 'year' => $this->year]), shouldOpenInNewTab: true),
            Action::make('printFinance')
                ->label('Print Finance Summary')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->visible(fn (): bool => static::canShield('print'))
                ->url(fn (): string => route('overtime.summary.print-finance', ['month' => $this->month, 'year' => $this->year]), shouldOpenInNewTab: true),
            Action::make('exportSummaryExcel')
                ->label('Download Summary Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => static::canShield('export'))
                ->url(fn (): string => route('overtime.summary.export-summary-excel', ['month' => $this->month, 'year' => $this->year])),
            Action::make('exportDailyExcel')
                ->label('Download Daily Sheet Excel')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->visible(fn (): bool => static::canShield('export'))
                ->url(fn (): string => route('overtime.summary.export-daily-excel', ['month' => $this->month, 'year' => $this->year])),
        ];
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
        $entries = OvertimeEntry::with('block')
            ->whereMonth('ot_date', $this->month)
            ->whereYear('ot_date', $this->year)
            ->get();

        $report = OvertimeReport::where('month', $this->month)->where('year', $this->year)->first();
        $adjustments = $report
            ? OvertimeAdjustment::where('overtime_report_id', $report->id)->get()->keyBy('employee_id')
            : collect();

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

        $entriesByEmp = $entries->groupBy('employee_id');

        return $allEmpCodes
            ->map(function (string $employeeId) use ($entriesByEmp, $employeesByCode, $adjustments): array {
                $empEntries = $entriesByEmp->get($employeeId, collect());
                $emp = $employeesByCode->get($employeeId);
                $adj = $adjustments->get($employeeId);

                $normal = (float) $empEntries->where('is_special_ot', false)->sum('hours');
                $special = (float) $empEntries->where('is_special_ot', true)->sum('hours');
                $normalAdj = (float) ($adj?->normal_adjustment ?? 0.0);
                $specialAdj = (float) ($adj?->special_adjustment ?? 0.0);

                $blocksBreakdown = $empEntries
                    ->groupBy(fn ($e) => $e->block?->name ?? 'Unknown')
                    ->map(fn (Collection $bEntries) => (float) $bEntries->sum('hours'))
                    ->toArray();

                return [
                    'employee_code' => $employeeId,
                    'sort_num' => (int) preg_replace('/\D+/', '', $employeeId),
                    'name' => $emp?->name ?? 'Unknown',
                    'department' => $emp?->department?->name ?? '—',
                    'designation' => $emp?->designation?->name ?? '—',
                    'normal_adj' => $normalAdj,
                    'normal_hours' => $normal,
                    'special_adj' => $specialAdj,
                    'special_hours' => $special,
                    'total_hours' => $normal + $normalAdj + $special + $specialAdj,
                    'blocks' => $blocksBreakdown,
                ];
            })
            ->sortBy([
                ['sort_num', 'asc'],
                ['employee_code', 'asc'],
            ])
            ->values();
    }
}
