<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use App\Models\Employee;
use App\Models\TipsAdjustment;
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
use Illuminate\Support\Collection;

class ViewTipsReport extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TipsReportResource::class;

    protected string $view = 'filament.resources.tips-reports.pages.view-tips-report';

    public string $activeDepartment = 'PIT';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $departments = $this->departments;
        $requestedDept = request()->query('department') ?? request()->query('tab');

        if ($requestedDept === 'adjustments-errors' || $requestedDept === 'Adjustments & Errors') {
            $this->activeDepartment = 'Adjustments & Errors';
        } elseif ($requestedDept && in_array($requestedDept, $departments, true)) {
            $this->activeDepartment = $requestedDept;
        } elseif (! empty($departments)) {
            $this->activeDepartment = $departments[0];
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
            })->get()->sum('final_distribution_amount');
        }

        return (float) $query
            ->where('department', $dept)
            ->where('is_left_out', false)
            ->get()
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
        return (float) $this->record->items->sum('final_distribution_amount');
    }

    public function getLeftOutsTotalProperty(): float
    {
        return (float) TipsReportItem::where('tips_report_id', $this->record->id)
            ->where(function ($q) {
                $q->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })->get()->sum('final_distribution_amount');
    }

    public function getCompanyShouldAddProperty(): float
    {
        return (float) abs($this->actualCollection + $this->leftOutsTotal - $this->totalToDistribute);
    }

    public function getAdjustmentsAndLeftOutsProperty(): float
    {
        return $this->leftOutsTotal;
    }

    public function getLeftOutsCountProperty(): int
    {
        return TipsReportItem::where('tips_report_id', $this->record->id)
            ->where(function ($q) {
                $q->where('is_left_out', true)
                    ->orWhere('department', 'Left Outs');
            })->count();
    }

    public function getAdjustmentsListProperty(): Collection
    {
        $month = strtolower((string) ($this->record->month ?? ''));
        $year = (string) ($this->record->year ?? '');

        $query = TipsAdjustment::with(['employee.department', 'employee.designation']);
        if ($month !== '') {
            $query->whereRaw('LOWER(for_month) = ?', [$month]);
        }
        if ($year !== '') {
            $query->where('year', $year);
        }

        $reportItems = $this->record->items->keyBy(fn ($i) => strtoupper(trim((string) $i->employee_code)));

        return $query->get()->map(function ($adj) use ($reportItems) {
            $code = strtoupper(trim((string) $adj->employee_id));
            $item = $reportItems->get($code);

            return [
                'id' => $adj->id,
                'employee_code' => $code,
                'employee_name' => $item?->employee_name ?? $adj->employee?->name ?? 'Unknown',
                'department' => $item?->department ?? $adj->employee?->department?->name ?? '-',
                'designation' => $item?->designation ?? $adj->employee?->designation?->name ?? '-',
                'type' => strtolower((string) ($adj->type ?? 'add')),
                'amount' => (float) ($adj->amount ?? 0),
                'status' => strtolower((string) ($adj->status ?? 'pending')),
                'remarks' => trim((string) ($adj->remarks ?? '')),
                'is_applied' => $item !== null,
                'report_item' => $item,
            ];
        });
    }

    public function getErrorsListProperty(): Collection
    {
        $rawErrors = $this->record->company_errors ?? [];
        if (! is_array($rawErrors)) {
            return collect();
        }

        $reportItems = $this->record->items->keyBy(fn ($i) => strtoupper(trim((string) $i->employee_code)));
        $employees = null;

        return collect($rawErrors)->map(function ($err) use ($reportItems, &$employees) {
            $code = strtoupper(trim((string) ($err['employee_code'] ?? '')));
            $item = $reportItems->get($code);

            $name = $item?->employee_name;
            $dept = $item?->department;
            $desig = $item?->designation;
            $baseTips = $item?->base_tips_amount;

            if (! $item && $code !== '') {
                if ($employees === null) {
                    $employees = Employee::with(['department', 'designation'])
                        ->get()
                        ->keyBy(fn ($e) => strtoupper(trim((string) $e->employee_code)));
                }
                $emp = $employees->get($code);
                $name = $emp?->name ?? $code;
                $dept = $emp?->department?->name ?? '-';
                $desig = $emp?->designation?->name ?? '-';
                $baseTips = (float) ($emp?->tips_amount ?? 0);
            }

            $pct = (float) ($err['percentage_to_deduct'] ?? 0);
            $reason = trim((string) ($err['reason'] ?? ''));

            $estimatedDeduction = 0.0;
            if ($item) {
                $multiplier = $item->is_fixed ? 1.0 : ($item->completion_factor + $item->point_value);
                $adjustedBase = ($item->base_tips_amount ?? 0) * $multiplier;
                $estimatedDeduction = round(($adjustedBase * $pct) / 100, 2);
            } elseif ($baseTips) {
                $estimatedDeduction = round(($baseTips * $pct) / 100, 2);
            }

            return [
                'employee_code' => $code,
                'employee_name' => $name ?? $code,
                'department' => $dept ?? '-',
                'designation' => $desig ?? '-',
                'percentage_to_deduct' => $pct,
                'reason' => $reason,
                'base_tips' => $baseTips ?? 0,
                'estimated_deduction' => $estimatedDeduction,
                'is_applied' => $item !== null,
                'report_item' => $item,
            ];
        });
    }

    public function getTotalAdjustmentsAndErrorsCountProperty(): int
    {
        return $this->adjustmentsList->count() + $this->errorsList->count();
    }

    public function getTotalAddAdjustmentsAmountProperty(): float
    {
        return (float) $this->adjustmentsList->where('type', 'add')->sum('amount');
    }

    public function getTotalDeductAdjustmentsAmountProperty(): float
    {
        return (float) $this->adjustmentsList->where('type', 'deduct')->sum('amount');
    }

    public function getTotalNetAdjustmentAmountProperty(): float
    {
        return $this->totalAddAdjustmentsAmount - $this->totalDeductAdjustmentsAmount;
    }

    public function getTotalEstimatedErrorDeductionProperty(): float
    {
        return (float) $this->errorsList->sum('estimated_deduction');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = TipsReportItem::query()->where('tips_report_id', $this->record->id);

                if ($this->activeDepartment === 'Adjustments & Errors') {
                    return $query->whereRaw('1 = 0');
                }

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
                    ->searchable()
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

                TextColumn::make('missing_punch_in_count')
                    ->label('Missing Punch')
                    ->formatStateUsing(fn ($state) => $state + 0)
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
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

                TextColumn::make('final_distribution_amount')
                    ->label('Actual Payout')
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
                    ->visible(fn () => $this->activeDepartment !== 'Adjustments & Errors')
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
                ->modalDescription('Once validated, this tips report is permanently locked for auditing. Any applied tips adjustments will be updated to "Updated". Editing and recalculating will be disabled. Proceed?')
                ->modalSubmitActionLabel('Yes, Validate & Lock')
                ->visible(fn () => ! $this->record->isValidated())
                ->action(function () {
                    $updatedCount = $this->record->validateAndLock(auth()->id());
                    $body = 'This tips report has been validated and locked.';
                    if ($updatedCount > 0) {
                        $body .= " {$updatedCount} applied tips adjustment(s) updated to 'Updated'.";
                    }
                    Notification::make()
                        ->title('Report Validated')
                        ->body($body)
                        ->success()
                        ->send();
                }),

            Action::make('unlockReport')
                ->label('Unlock')
                ->icon('heroicon-m-lock-open')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Unlock Tips Report')
                ->modalDescription('Unlocking will permit modifications and recalculations again. Applied tips adjustments will be reverted to "Pending". Are you sure?')
                ->modalSubmitActionLabel('Yes, Unlock')
                ->visible(fn () => $this->record->isValidated() && (auth()->user()?->hasRole('super_admin') ?? true))
                ->action(function () {
                    $revertedCount = $this->record->unlock();
                    $body = 'This tips report is now unlocked.';
                    if ($revertedCount > 0) {
                        $body .= " {$revertedCount} tips adjustment(s) reverted to 'Pending'.";
                    }
                    Notification::make()
                        ->title('Report Unlocked')
                        ->body($body)
                        ->warning()
                        ->send();
                }),

            Action::make('printAdjustmentsErrors')
                ->label('Print Adjustments & Errors')
                ->icon('heroicon-m-clipboard-document-list')
                ->color('amber')
                ->url(fn () => route('tips.reports.print-adjustments-errors', ['report' => $this->record->id]))
                ->openUrlInNewTab(),

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
