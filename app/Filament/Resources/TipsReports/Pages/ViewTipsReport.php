<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use App\Models\TipsDepartmentMapping;
use App\Models\TipsReportItem;
use App\Services\TipsCalculationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewTipsReport extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TipsReportResource::class;

    protected string $view = 'filament.resources.tips-reports.pages.view-tips-report';

    public string $activeDepartment = 'F&B';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Default to first department with items, or PIT
        $firstDept = TipsReportItem::where('tips_report_id', $this->record->id)
            ->where('is_left_out', false)
            ->value('department');

        if ($firstDept) {
            $this->activeDepartment = $firstDept;
        }
    }

    public function setActiveDepartment(string $dept): void
    {
        $this->activeDepartment = $dept;
        $this->resetTable();
    }

    public function getDepartmentsProperty(): array
    {
        $masterPages = TipsDepartmentMapping::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('page_name')
            ->toArray();

        $existingDepts = TipsReportItem::where('tips_report_id', $this->record->id)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->toArray();

        $merged = array_unique(array_merge($masterPages, $existingDepts));

        // Ensure None Employee and Left Outs are positioned at the end (Summary tab removed)
        $final = array_values(array_filter($merged, fn ($d) => ! in_array($d, ['None Employee', 'Left Outs', 'Summary'])));
        if (in_array('None Employee', $merged) || in_array('None Employee', $existingDepts) || TipsReportItem::where('tips_report_id', $this->record->id)->where('department', 'None Employee')->exists()) {
            $final[] = 'None Employee';
        }
        $final[] = 'Left Outs';

        return $final;
    }

    public function getDepartmentCount(string $dept): int
    {
        $query = TipsReportItem::where('tips_report_id', $this->record->id);

        if ($dept === 'Left Outs') {
            return $query->where(function ($q) {
                $q
                    ->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })->count();
        }

        return $query
            ->where('department', $dept)
            ->where('is_left_out', false)
            ->count();
    }

    public function getDepartmentTotal(string $dept): float
    {
        $query = TipsReportItem::where('tips_report_id', $this->record->id);

        if ($dept === 'Left Outs') {
            return (float) $query->where(function ($q) {
                $q
                    ->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })->sum('final_distribution_amount');
        }

        return (float) $query
            ->where('department', $dept)
            ->where('is_left_out', false)
            ->sum('final_distribution_amount');
    }

    public function getActualCollectionProperty(): float
    {
        return (float) collect($this->record->collection_summary ?? [])->sum('total_amount');
    }

    public function getTotalChipsProperty(): float
    {
        return (float) collect($this->record->collection_summary ?? [])->sum('chips_amount');
    }

    public function getTotalCashProperty(): float
    {
        return (float) collect($this->record->collection_summary ?? [])->sum('cash_amount');
    }

    public function getTotalToDistributeProperty(): float
    {
        return (float) $this->record->items()->sum('final_distribution_amount');
    }

    public function getCompanyShouldAddProperty(): float
    {
        return (float) abs($this->actualCollection + $this->adjustmentsAndLeftOuts - $this->totalToDistribute);
    }

    public function getAdjustmentsAndLeftOutsProperty(): float
    {
        $leftOuts = (float) TipsReportItem::where('tips_report_id', $this->record->id)
            ->where(function ($q) {
                $q->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })->sum('final_distribution_amount');

        $adjustmentsTotal = (float) TipsReportItem::where('tips_report_id', $this->record->id)->sum('amount_to_adjust')
            - (float) TipsReportItem::where('tips_report_id', $this->record->id)->sum('amount_to_deduct');

        $result = $leftOuts + max(0.0, $adjustmentsTotal);

        return $result > 0 ? $result : $leftOuts;
    }

    public function getLeftOutsCountProperty(): int
    {
        return TipsReportItem::where('tips_report_id', $this->record->id)
            ->where(function ($q) {
                $q->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = TipsReportItem::query()->where('tips_report_id', $this->record->id);

                if ($this->activeDepartment === 'Left Outs') {
                    return $query
                        ->where(function ($q) {
                            $q
                                ->where('is_left_out', true)
                                ->orWhere('department', 'Left Outs');
                        })
                        ->orderByHierarchy();
                }

                return $query
                    ->where('department', $this->activeDepartment)
                    ->where('is_left_out', false)
                    ->orderByHierarchy();
            })
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('employee_code')
                    ->label('Code')
                    ->copyable()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByNumericCode($direction))
                    ->weight('bold'),
                TextColumn::make('employee_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('designation')
                    ->label('Designation')
                    ->placeholder('-'),
                TextColumn::make('department')
                    ->label('Department')
                    ->visible(fn () => $this->activeDepartment === 'Left Outs'),
                TextColumn::make('working_duration')
                    ->label('Tenure')
                    ->placeholder('-')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByTenure($direction)),
                TextColumn::make('completion_factor')
                    ->label('Compl.')
                    ->formatStateUsing(fn ($state) => $state + 0),
                TextColumn::make('point_value')
                    ->label('PV')
                    ->formatStateUsing(fn ($state) => $state + 0),
                TextColumn::make('base_tips_amount')
                    ->label('Base Tips')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('total_leaves')
                    ->label('Leaves')
                    ->formatStateUsing(fn ($state) => $state + 0)
                    ->sortable(),

                TextColumn::make('absent_days')
                    ->label('Absent')
                    ->formatStateUsing(fn ($state) => $state + 0)
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                TextColumn::make('tips_percentage')
                    ->label('Tips %')
                    ->suffix('%')
                    ->color(fn ($state) => $state < 100 ? 'warning' : 'success')
                    ->sortable(),

                TextColumn::make('percentage_to_deduct')
                    ->label('Err %')
                    ->suffix('%')
                    ->color('danger')
                    ->visible(fn () => TipsReportItem::where('tips_report_id', $this->record->id)->where('percentage_to_deduct', '>', 0)->exists()),

                TextColumn::make('amount_to_adjust')
                    ->label('Adj (+)')
                    ->numeric(2)
                    ->color('success'),

                TextColumn::make('amount_to_deduct')
                    ->label('Deduct (-)')
                    ->numeric(2)
                    ->color('danger'),

                TextColumn::make('unrounded_amount')
                    ->label('Actual Payout')
                    ->formatStateUsing(fn ($record, $state) => ($record->is_blank || $state === null) ? '-' : ($state == (int) $state ? number_format((float) $state, 0) : number_format((float) $state, 2)))
                    ->sortable(),

                TextColumn::make('final_distribution_amount')
                    ->label('Final Payout (Rounded)')
                    ->formatStateUsing(fn ($record, $state) => ($record->is_blank || $state === null) ? '-' : number_format((float) $state, 0))
                    ->weight('bold')
                    ->badge()
                    ->color(fn ($record, $state) => ($record->is_blank || $state === null) ? 'gray' : (strtoupper((string) $record->tips_status) === 'HOLD' ? 'danger' : 'primary'))
                    ->sortable(),

                TextColumn::make('tips_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => strtoupper((string) $state) === 'RELEASE' ? 'success' : 'danger'),
            ])
            ->headerActions([
                Action::make('printSheet')
                    ->label(fn () => 'Print '.$this->activeDepartment.' Payout Sheet')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->url(fn () => route('tips.reports.print', [
                        'report' => $this->record->id,
                        'department' => $this->activeDepartment,
                    ]))
                    ->openUrlInNewTab(),
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
                ->modalHeading('Validate & Lock Tips Report')
                ->modalDescription('Once validated, this tips report is permanently locked for auditing. Editing and recalculating will be disabled. Proceed?')
                ->modalSubmitActionLabel('Yes, Validate & Lock')
                ->visible(fn () => ! $this->record->isValidated())
                ->action(function () {
                    $this->record->update([
                        'status' => 'validated',
                        'validated_at' => now(),
                        'validated_by' => auth()->id(),
                    ]);
                    Notification::make()
                        ->title('Report Validated')
                        ->body('This tips report has been validated and locked.')
                        ->success()
                        ->send();
                }),

            Action::make('unlockReport')
                ->label('Unlock')
                ->icon('heroicon-m-lock-open')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Unlock Tips Report')
                ->modalDescription('Unlocking will permit modifications and recalculations again. Are you sure?')
                ->modalSubmitActionLabel('Yes, Unlock')
                ->visible(fn () => $this->record->isValidated() && (auth()->user()?->hasRole('super_admin') ?? true))
                ->action(function () {
                    $this->record->update([
                        'status' => 'generated',
                    ]);
                    Notification::make()
                        ->title('Report Unlocked')
                        ->body('This tips report is now unlocked.')
                        ->warning()
                        ->send();
                }),

            Action::make('printSummary')
                ->label('Print Summary')
                ->icon('heroicon-m-table-cells')
                ->color('info')
                ->url(fn () => route('tips.reports.print-summary', ['report' => $this->record->id]))
                ->openUrlInNewTab(),

            Action::make('printTotals')
                ->label('Print Totals')
                ->icon('heroicon-m-calculator')
                ->color('warning')
                ->url(fn () => route('tips.reports.print-totals', ['report' => $this->record->id]))
                ->openUrlInNewTab(),

            Action::make('regenerate')
                ->label('Regenerate')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Regenerate Tips Calculation')
                ->modalDescription('This will re-calculate staff distributions using the current attendance file and employee records. Any existing calculated items will be replaced. Proceed?')
                ->modalSubmitActionLabel('Yes, Regenerate')
                ->visible(fn () => ! $this->record->isValidated())
                ->action(function () {
                    app(TipsCalculationService::class)->generate($this->record, [
                        'company_errors' => $this->record->company_errors ?? [],
                        'left_outs' => $this->record->left_outs ?? [],
                    ]);
                    Notification::make()
                        ->title('Tips Distribution Regenerated')
                        ->body('Report data has been re-calculated and saved.')
                        ->success()
                        ->send();
                    $this->resetTable();
                }),

            EditAction::make()
                ->hidden(fn () => $this->record->isValidated()),
        ];
    }
}
