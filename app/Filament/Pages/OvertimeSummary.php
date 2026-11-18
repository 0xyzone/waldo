<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Services\OvertimeReportService;
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
        return OvertimeReportService::buildPeriodSummaryData($this->month, $this->year)['blockSummary'];
    }

    public function getEmployeeSummary(): Collection
    {
        return OvertimeReportService::buildPeriodSummaryData($this->month, $this->year)['employeeBlockSummary'];
    }
}
