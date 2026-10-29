<?php

namespace App\Filament\Resources\RoyaltyReports\Pages;

use App\Filament\Resources\RoyaltyReports\RoyaltyReportResource;
use App\Models\RoyaltyReportItem;
use App\Services\RoyaltyCalculationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewRoyaltyReport extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = RoyaltyReportResource::class;

    protected string $view = 'filament.resources.royalty-reports.pages.view-royalty-report';

    public string $activeDepartment = 'All';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $requestedDept = request()->query('department');
        if ($requestedDept && in_array($requestedDept, $this->departments, true)) {
            $this->activeDepartment = $requestedDept;
        }
    }

    public function setActiveDepartment(string $dept): void
    {
        $this->activeDepartment = $dept;
        $this->resetTable();
    }

    public function getDepartmentsProperty(): array
    {
        return RoyaltyReportItem::where('royalty_report_id', $this->record->id)
            ->whereNotNull('department_name')
            ->distinct()
            ->orderBy('department_name')
            ->pluck('department_name')
            ->toArray();
    }

    public function getDepartmentCount(string $dept): int
    {
        $query = RoyaltyReportItem::where('royalty_report_id', $this->record->id);
        if ($dept !== 'All') {
            $query->where('department_name', $dept);
        }

        return $query->count();
    }

    public function getTotalStaffProperty(): int
    {
        return $this->record->items()->count();
    }

    public function getTotalMaxPayoutProperty(): float
    {
        return (float) $this->record->items()->sum('monthly_max_payout');
    }

    public function getTotalDeductionsProperty(): float
    {
        return (float) $this->record->items()->sum('total_deduction_amount');
    }

    public function getTotalFinalPayoutProperty(): float
    {
        return (float) $this->record->items()->sum('final_payout');
    }

    public function getExemptStaffCountProperty(): int
    {
        return $this->record->items()->where('is_exempt', true)->count();
    }

    public function toggleExempt(int $itemId): void
    {
        if ($this->record->isValidated()) {
            Notification::make()
                ->title('Report is Locked')
                ->body('Cannot modify items while report is validated and locked.')
                ->danger()
                ->send();

            return;
        }

        $item = RoyaltyReportItem::where('royalty_report_id', $this->record->id)
            ->where('id', $itemId)
            ->first();

        if (! $item) {
            return;
        }

        $newExempt = ! $item->is_exempt;
        $settings = app(RoyaltyCalculationService::class)->getSettings();

        $calc = app(RoyaltyCalculationService::class)->computePayout(
            (float) $item->monthly_max_payout,
            (float) $item->monthly_min_payout,
            $newExempt,
            (float) $item->perf_deduction_percent,
            (float) $item->absent_days,
            (int) $item->late_early_count,
            (int) $item->missing_thumb_count,
            $settings
        );

        $item->update([
            'is_exempt' => $newExempt,
            'perf_deduction_amount' => $calc['perf_deduction_amount'],
            'attendance_deduction_amount' => $calc['attendance_deduction_amount'],
            'total_deduction_amount' => $calc['total_deduction_amount'],
            'final_payout' => $calc['final_payout'],
        ]);

        $this->record->update([
            'total_payout' => $this->record->items()->sum('final_payout'),
            'total_deductions' => $this->record->items()->sum('total_deduction_amount'),
        ]);

        $statusText = $newExempt ? 'marked as EXEMPT (received max payout)' : 'unmarked from exempt (deductions reapplied)';
        Notification::make()
            ->title('Exemption Updated')
            ->body("{$item->employee_code} {$item->employee_name} has been {$statusText}.")
            ->success()
            ->send();

        $this->record->refresh();
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = RoyaltyReportItem::query()->where('royalty_report_id', $this->record->id);
                if ($this->activeDepartment !== 'All') {
                    $query->where('department_name', $this->activeDepartment);
                }

                return $query->orderBy('department_name')->orderBy('employee_code');
            })
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('employee_code')
                    ->label('Code')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('employee_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('department_name')
                    ->label('Department')
                    ->badge()
                    ->color('info')
                    ->visible(fn () => $this->activeDepartment === 'All'),

                TextColumn::make('designation_name')
                    ->label('Designation')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('employee_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ?? 'Active')
                    ->color(fn ($state): string => match ($state) {
                        'Active' => 'success',
                        'Resigning This Month' => 'warning',
                        'Suspended' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('monthly_max_payout')
                    ->label('Max Payout')
                    ->money('NPR')
                    ->sortable(),

                TextColumn::make('perf_deduction_percent')
                    ->label('Perf %')
                    ->formatStateUsing(fn ($state) => $state > 0 ? ($state + 0).'%' : '-')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('perf_deduction_amount')
                    ->label('Perf Ded.')
                    ->money('NPR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('absent_days')
                    ->label('Absent')
                    ->formatStateUsing(fn ($state) => $state + 0)
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                TextColumn::make('late_early_count')
                    ->label('Late/Early')
                    ->formatStateUsing(fn ($state) => $state + 0)
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                TextColumn::make('missing_thumb_count')
                    ->label('Thumb')
                    ->formatStateUsing(fn ($state) => $state + 0)
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                TextColumn::make('attendance_deduction_amount')
                    ->label('Attn Ded.')
                    ->money('NPR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('total_deduction_amount')
                    ->label('Total Ded.')
                    ->money('NPR')
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                TextColumn::make('final_payout')
                    ->label('Net Payout')
                    ->money('NPR')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                IconColumn::make('is_exempt')
                    ->label('Exempt')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->action(fn (RoyaltyReportItem $record) => $this->toggleExempt($record->id))
                    ->tooltip(fn ($record) => $this->record->isValidated() ? 'Report is locked' : ($record->is_exempt ? 'Click to re-enable deductions' : 'Click to exempt & grant max payout')),
            ])
            ->filters([
                SelectFilter::make('department_name')
                    ->label('Department')
                    ->options(fn () => RoyaltyReportItem::where('royalty_report_id', $this->record->id)->distinct()->pluck('department_name', 'department_name')->toArray())
                    ->visible(fn () => $this->activeDepartment === 'All'),

                TernaryFilter::make('is_exempt')
                    ->label('Exemption')
                    ->boolean()
                    ->trueLabel('Exempt Staff Only')
                    ->falseLabel('Non-Exempt Staff Only')
                    ->native(false),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('validateReport')
                ->label('Validate & Lock')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Validate & Lock Royalty Report')
                ->modalDescription('Once validated, this royalty bonus report is permanently locked for auditing and payroll disbursement. Item editing, exemption changes, and recalculation will be disabled. Proceed?')
                ->modalSubmitActionLabel('Yes, Validate & Lock')
                ->visible(fn () => ! $this->record->isValidated() && (RoyaltyReportResource::canShield('ValidateReport') || (auth()->user()?->hasRole(['super_admin', 'HR', 'Finance']) ?? true)))
                ->action(function () {
                    $this->record->validateAndLock(auth()->id());
                    $this->record->refresh();
                    $this->resetTable();

                    Notification::make()
                        ->title('Report Validated & Locked')
                        ->body('This royalty bonus report is now officially validated and locked.')
                        ->success()
                        ->send();
                }),

            Action::make('unlockReport')
                ->label('Unlock')
                ->icon('heroicon-m-lock-open')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Unlock Royalty Report')
                ->modalDescription('Unlocking will re-enable recalculation, editing, and exemption toggling. Proceed?')
                ->modalSubmitActionLabel('Yes, Unlock')
                ->visible(fn () => $this->record->isValidated() && (RoyaltyReportResource::canShield('UnlockReport') || (auth()->user()?->hasRole(['super_admin', 'Finance']) ?? true)))
                ->action(function () {
                    $this->record->unlock();
                    $this->record->refresh();
                    $this->resetTable();

                    Notification::make()
                        ->title('Report Unlocked')
                        ->body('This royalty bonus report is now unlocked for adjustments.')
                        ->warning()
                        ->send();
                }),

            Action::make('recalculate')
                ->label('Recalculate Payouts')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Recalculate Royalty Bonuses')
                ->modalDescription('This will re-parse the uploaded attendance and performance files and recalculate deductions for all eligible staff. Any manual exemption toggles on this report will reset to global master defaults. Proceed?')
                ->modalSubmitActionLabel('Yes, Recalculate')
                ->visible(fn () => ! $this->record->isValidated())
                ->action(function () {
                    app(RoyaltyCalculationService::class)->generate($this->record);
                    $this->record->refresh();
                    $this->resetTable();

                    Notification::make()
                        ->title('Payouts Recalculated')
                        ->body('Staff distribution amounts and deductions have been refreshed.')
                        ->success()
                        ->send();
                }),

            Action::make('exportExcel')
                ->label('Download Excel')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->url(fn () => route('royalty.reports.export', ['report' => $this->record->id]))
                ->openUrlInNewTab(),

            Action::make('printReport')
                ->label('Print Payout Sheet')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('royalty.reports.print', [
                    'report' => $this->record->id,
                    'department' => $this->activeDepartment === 'All' ? null : $this->activeDepartment,
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
