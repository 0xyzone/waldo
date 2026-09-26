<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use App\Models\TipsDepartmentMapping;
use App\Models\TipsReportItem;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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

    public string $activeDepartment = 'PIT';

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

        // Ensure Left Outs is present and at the end (Summary tab removed)
        $final = array_values(array_filter($merged, fn ($d) => ! in_array($d, ['Left Outs', 'Summary'])));
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
                        ->orderBy('department_rank')
                        ->orderBy('designation_rank')
                        ->orderBy('employee_name');
                }

                return $query
                    ->where('department', $this->activeDepartment)
                    ->where('is_left_out', false)
                    ->orderBy('department_rank')
                    ->orderBy('designation_rank')
                    ->orderBy('employee_name');
            })
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('employee_code')
                    ->label('Code')
                    ->copyable()
                    ->sortable()
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
                    ->placeholder('-'),
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
            Action::make('printSummary')
                ->label('🖨️ Print Summary')
                ->color('info')
                ->url(fn () => route('tips.reports.print-summary', ['report' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('printTotals')
                ->label('🖨️ Print Totals')
                ->color('warning')
                ->url(fn () => route('tips.reports.print-totals', ['report' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('printCurrent')
                ->label(fn () => '🖨️ Print '.$this->activeDepartment.' Sheet')
                ->color('success')
                ->url(fn () => route('tips.reports.print', [
                    'report' => $this->record->id,
                    'department' => $this->activeDepartment,
                ]))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
