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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                $this->addLeftOutAction()
                    ->visible(fn () => $this->activeDepartment === 'Left Outs' && ! $this->record->isValidated()),

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

    public function addAdjustmentAction(): Action
    {
        return Action::make('addAdjustment')
            ->label('Add Adjustment')
            ->icon('heroicon-m-plus-circle')
            ->color('success')
            ->modalHeading('Add Tips Adjustment')
            ->modalDescription('Record an individual adjustment addition or deduction. The staff member payout in this report will be updated immediately.')
            ->modalSubmitActionLabel('Add Adjustment')
            ->visible(fn () => ! $this->record->isValidated())
            ->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn () => Employee::query()
                        ->whereIn('employee_status', ['Active', 'Resigning This Month'])
                        ->orderBy('employee_code')
                        ->get()
                        ->mapWithKeys(fn ($e) => [
                            $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->department?->name ?? 'No Dept').')',
                        ])
                        ->toArray())
                    ->searchable()
                    ->required(),

                Select::make('type')
                    ->label('Adjustment Type')
                    ->options([
                        'add' => 'Add (+) - Payout Bonus',
                        'deduct' => 'Deduct (-) - Payout Deduction',
                    ])
                    ->default('add')
                    ->required(),

                TextInput::make('amount')
                    ->label('Amount (Rs.)')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Textarea::make('remarks')
                    ->label('Remarks / Reason')
                    ->placeholder('e.g., Overtime payout adjustment, Approved floor bonus, etc.')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $month = strtolower((string) ($this->record->month ?? ''));
                $year = (string) ($this->record->year ?? '');
                $code = strtoupper(trim((string) $data['employee_id']));

                TipsAdjustment::create([
                    'employee_id' => $code,
                    'type' => $data['type'],
                    'amount' => $data['amount'],
                    'remarks' => $data['remarks'],
                    'for_month' => $month,
                    'year' => $year,
                    'status' => $this->record->isValidated() ? 'updated' : 'pending',
                ]);

                // Recalculate matching TipsReportItem if exists
                $item = TipsReportItem::where('tips_report_id', $this->record->id)
                    ->where('employee_code', $code)
                    ->first();

                if ($item) {
                    $adjustments = TipsAdjustment::whereRaw('LOWER(for_month) = ?', [$month])
                        ->where('year', $year)
                        ->where('employee_id', $code)
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                        })
                        ->get();

                    $amountToAdjust = (float) $adjustments->where('type', 'add')->sum('amount');
                    $amountToDeduct = (float) $adjustments->where('type', 'deduct')->sum('amount');

                    if (! $item->is_blank && strtoupper(trim((string) $item->tips_status)) !== 'HOLD') {
                        $multiplier = $item->is_fixed ? 1.0 : ($item->completion_factor + $item->point_value);
                        $adjustedBase = $item->base_tips_amount * $multiplier;
                        $effectivePercentage = max(0.0, ($item->tips_percentage - $item->percentage_to_deduct));
                        $rawCalculatedTips = ($adjustedBase * $effectivePercentage) / 100;
                        $calculatedTips = TipsCalculationService::roundUpToNearest100($rawCalculatedTips);
                        $rawFinalDistribution = $rawCalculatedTips + $amountToAdjust - $amountToDeduct;

                        if ($rawFinalDistribution <= 0) {
                            $finalDistribution = 0.0;
                            $unroundedAmount = 0.0;
                        } else {
                            $finalDistribution = TipsCalculationService::roundUpToNearest100($rawFinalDistribution);
                            $unroundedAmount = round($rawFinalDistribution, 2);
                        }

                        $item->update([
                            'amount_to_adjust' => $amountToAdjust,
                            'amount_to_deduct' => $amountToDeduct,
                            'calculated_tips' => $calculatedTips,
                            'unrounded_amount' => $unroundedAmount,
                            'final_distribution_amount' => $finalDistribution,
                        ]);
                    } else {
                        $item->update([
                            'amount_to_adjust' => $amountToAdjust,
                            'amount_to_deduct' => $amountToDeduct,
                        ]);
                    }
                }

                Notification::make()
                    ->title('Adjustment Added')
                    ->body("Successfully added {$data['type']} adjustment of Rs. {$data['amount']} for {$code}.")
                    ->success()
                    ->send();

                $this->resetTable();
            });
    }

    public function addErrorAction(): Action
    {
        return Action::make('addError')
            ->label('Add Company Error')
            ->icon('heroicon-m-exclamation-triangle')
            ->color('danger')
            ->modalHeading('Add Company Error Penalty')
            ->modalDescription('Record an employee error penalty percentage deduction. Payout will be updated immediately.')
            ->modalSubmitActionLabel('Add Penalty')
            ->visible(fn () => ! $this->record->isValidated())
            ->schema([
                Select::make('employee_code')
                    ->label('Employee')
                    ->options(fn () => Employee::query()
                        ->whereIn('employee_status', ['Active', 'Resigning This Month'])
                        ->orderBy('employee_code')
                        ->get()
                        ->mapWithKeys(fn ($e) => [
                            $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->department?->name ?? 'No Dept').')',
                        ])
                        ->toArray())
                    ->searchable()
                    ->required(),

                TextInput::make('percentage_to_deduct')
                    ->label('Percentage to Deduct (%)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(10)
                    ->required(),

                Textarea::make('reason')
                    ->label('Reason / Remarks')
                    ->placeholder('e.g., Short cash in drawer, Procedure breach...')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $code = strtoupper(trim((string) $data['employee_code']));
                $pct = (float) $data['percentage_to_deduct'];
                $reason = trim((string) $data['reason']);

                $errors = $this->record->company_errors ?? [];
                if (! is_array($errors)) {
                    $errors = [];
                }
                $errors[] = [
                    'employee_code' => $code,
                    'percentage_to_deduct' => $pct,
                    'reason' => $reason,
                ];
                $this->record->update(['company_errors' => $errors]);

                // Recalculate employee's item in this report
                $item = TipsReportItem::where('tips_report_id', $this->record->id)
                    ->where('employee_code', $code)
                    ->first();

                if ($item && ! $item->is_blank && strtoupper(trim((string) $item->tips_status)) !== 'HOLD') {
                    $totalDeductPct = collect($errors)
                        ->filter(fn ($e) => strtoupper(trim((string) ($e['employee_code'] ?? ''))) === $code)
                        ->sum('percentage_to_deduct');

                    $effectivePercentage = max(0.0, ($item->tips_percentage - $totalDeductPct));
                    $multiplier = $item->is_fixed ? 1.0 : ($item->completion_factor + $item->point_value);
                    $adjustedBase = $item->base_tips_amount * $multiplier;

                    $rawCalculatedTips = ($adjustedBase * $effectivePercentage) / 100;
                    $calculatedTips = TipsCalculationService::roundUpToNearest100($rawCalculatedTips);
                    $rawFinalDistribution = $rawCalculatedTips + $item->amount_to_adjust - $item->amount_to_deduct;

                    if ($rawFinalDistribution <= 0) {
                        $finalDistribution = 0.0;
                        $unroundedAmount = 0.0;
                    } else {
                        $finalDistribution = TipsCalculationService::roundUpToNearest100($rawFinalDistribution);
                        $unroundedAmount = round($rawFinalDistribution, 2);
                    }

                    $item->update([
                        'percentage_to_deduct' => $totalDeductPct,
                        'calculated_tips' => $calculatedTips,
                        'unrounded_amount' => $unroundedAmount,
                        'final_distribution_amount' => $finalDistribution,
                    ]);
                }

                Notification::make()
                    ->title('Company Error Penalty Added')
                    ->body("Successfully recorded {$pct}% penalty for {$code} and recalculated distribution.")
                    ->success()
                    ->send();

                $this->resetTable();
            });
    }

    public function addLeftOutAction(): Action
    {
        return Action::make('addLeftOut')
            ->label('Add Left Out Staff')
            ->icon('heroicon-m-user-plus')
            ->color('primary')
            ->modalHeading('Add Left Out Staff Override')
            ->modalDescription('Manually include an employee who was missed or excluded from attendance upload.')
            ->modalSubmitActionLabel('Add Staff Override')
            ->visible(fn () => ! $this->record->isValidated())
            ->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn () => Employee::query()
                        ->with(['department', 'designation'])
                        ->orderBy('employee_code')
                        ->get()
                        ->mapWithKeys(fn ($e) => [
                            $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->designation?->name ?? 'No Designation').')',
                        ])
                        ->toArray())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (! $state) {
                            return;
                        }
                        $emp = Employee::with(['department', 'designation'])->where('employee_code', $state)->first();
                        if ($emp) {
                            $set('designation', $emp->designation?->name ?? '');
                            $base = (float) ($emp->tips_amount ?? 0);
                            $set('base_tips_amount', $base);

                            $isHold = strtoupper(trim((string) ($emp->tips_status ?? ''))) === 'HOLD';
                            if ($isHold) {
                                $set('final_distribution_amount', 0);
                            } else {
                                $set('final_distribution_amount', TipsCalculationService::roundUpToNearest100($base));
                            }

                            $pageName = TipsDepartmentMapping::resolvePageName($emp->department_id, $emp->department?->name);
                            if ($pageName) {
                                $set('department', $pageName);
                            }
                        }
                    })
                    ->required(),

                TextInput::make('designation')
                    ->label('Designation')
                    ->placeholder('Auto-fetched from DB'),

                Select::make('department')
                    ->label('Department Page')
                    ->options(function () {
                        $masterPages = TipsDepartmentMapping::where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('page_name', 'page_name')
                            ->toArray();

                        return array_merge($masterPages, [
                            'Left Outs' => 'Left Outs',
                        ]);
                    })
                    ->default('Left Outs')
                    ->required(),

                TextInput::make('base_tips_amount')
                    ->label('Base Tips')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $base = (float) ($get('base_tips_amount') ?? 0);
                        $pct = (float) ($get('tips_percentage') ?? 100);
                        $raw = ($base * $pct) / 100;
                        $set('final_distribution_amount', TipsCalculationService::roundUpToNearest100($raw));
                    }),

                TextInput::make('tips_percentage')
                    ->label('Tips %')
                    ->numeric()
                    ->default(100)
                    ->suffix('%')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $base = (float) ($get('base_tips_amount') ?? 0);
                        $pct = (float) ($get('tips_percentage') ?? 100);
                        $raw = ($base * $pct) / 100;
                        $set('final_distribution_amount', TipsCalculationService::roundUpToNearest100($raw));
                    }),

                TextInput::make('final_distribution_amount')
                    ->label('Final Amount')
                    ->numeric(),

                Textarea::make('notes')
                    ->label('Notes / Reason')
                    ->placeholder('e.g., Missed in biometric sync, Special inclusion approved...')
                    ->rows(2),
            ])
            ->action(function (array $data) {
                $code = strtoupper(trim((string) $data['employee_id']));
                $employee = Employee::with(['department', 'designation'])->where('employee_code', $code)->first();

                $baseTips = (float) ($data['base_tips_amount'] ?? ($employee?->tips_amount ?? 0));
                $tipsPct = (float) ($data['tips_percentage'] ?? 100);
                $tipsStatus = (string) ($employee?->tips_status ?? 'Release');

                if (strtoupper(trim($tipsStatus)) === 'HOLD') {
                    $finalDist = 0.0;
                    $unrounded = 0.0;
                    $calculatedTips = 0.0;
                } else {
                    if (isset($data['final_distribution_amount']) && $data['final_distribution_amount'] !== null && $data['final_distribution_amount'] !== '') {
                        $rawDist = (float) $data['final_distribution_amount'];
                    } else {
                        $rawDist = ($baseTips * $tipsPct) / 100;
                    }
                    $finalDist = $rawDist > 0 ? TipsCalculationService::roundUpToNearest100($rawDist) : 0.0;
                    $unrounded = round($rawDist, 2);
                    $calculatedTips = $finalDist;
                }

                $leftOuts = $this->record->left_outs ?? [];
                if (! is_array($leftOuts)) {
                    $leftOuts = [];
                }
                $leftOuts[] = [
                    'employee_id' => $code,
                    'department' => $data['department'] ?? 'Left Outs',
                    'designation' => $data['designation'] ?? ($employee?->designation?->name ?? '-'),
                    'base_tips_amount' => $baseTips,
                    'tips_percentage' => $tipsPct,
                    'final_distribution_amount' => $finalDist,
                    'notes' => $data['notes'] ?? null,
                ];
                $this->record->update(['left_outs' => $leftOuts]);

                TipsReportItem::create([
                    'tips_report_id' => $this->record->id,
                    'employee_id' => $code,
                    'employee_code' => $code,
                    'department' => $data['department'] ?? 'Left Outs',
                    'department_rank' => $employee?->department?->rank ?? ($employee?->dp_rank ?? 999),
                    'designation' => $data['designation'] ?? ($employee?->designation?->name ?? '-'),
                    'designation_rank' => $employee?->designation?->rank ?? ($employee?->rank ?? 999),
                    'employee_name' => $employee?->name ?? $code,
                    'working_days' => 0,
                    'present_days' => 0,
                    'absent_days' => 0,
                    'total_leaves' => 0,
                    'late_in_count' => 0,
                    'early_out_count' => 0,
                    'missing_punch_in_count' => 0,
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
                    'unrounded_amount' => $unrounded,
                    'final_distribution_amount' => $finalDist,
                    'is_left_out' => true,
                ]);

                Notification::make()
                    ->title('Left Out Staff Added')
                    ->body("Successfully added {$code} to {$data['department']}.")
                    ->success()
                    ->send();

                $this->resetTable();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            // $this->addAdjustmentAction()
            //     ->visible(fn () => $this->activeDepartment === 'Adjustments & Errors' && ! $this->record->isValidated()),

            // $this->addErrorAction()
            //     ->visible(fn () => $this->activeDepartment === 'Adjustments & Errors' && ! $this->record->isValidated()),

            // $this->addLeftOutAction()
            //     ->visible(fn () => $this->activeDepartment === 'Left Outs' && ! $this->record->isValidated()),

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

            // Action::make('printAdjustmentsErrors')
            //     ->label('Print Adjustments & Errors')
            //     ->icon('heroicon-m-clipboard-document-list')
            //     ->color('amber')
            //     ->url(fn () => route('tips.reports.print-adjustments-errors', ['report' => $this->record->id]))
            //     ->openUrlInNewTab(),

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
