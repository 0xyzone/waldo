<?php

namespace App\Filament\Resources\Leavers\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\LeaverExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('employee_id')
                    ->label('Employee Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->sortable(),
                TextColumn::make('leaving_date')
                    ->label('Leaving Date')
                    ->date()
                    ->sortable(),
                IconColumn::make('hold_salary')
                    ->label('Hold Salary')
                    ->boolean()
                    ->color(fn ($record) => $record->hold_salary ? 'danger' : 'success'),
                IconColumn::make('hold_tips')
                    ->label('Hold Tips')
                    ->boolean()
                    ->color(fn ($record) => $record->hold_tips ? 'danger' : 'success'),
                IconColumn::make('publish_cl')
                    ->label('Publish CL')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $deptId) => $query->whereHas('employee', fn (Builder $q) => $q->where('department_id', $deptId))
                        );
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(fn () => Designation::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $desigId) => $query->whereHas('employee', fn (Builder $q) => $q->where('designation_id', $desigId))
                        );
                    })
                    ->searchable()
                    ->preload(),
                Filter::make('leaving_date')
                    ->form([
                        DatePicker::make('leaving_date_from')
                            ->label('Leaving From')
                            ->native(false),
                        DatePicker::make('leaving_date_to')
                            ->label('Leaving To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['leaving_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('leaving_date', '>=', $date)
                            )
                            ->when(
                                $data['leaving_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('leaving_date', '<=', $date)
                            );
                    }),
                Filter::make('leaving_month_year')
                    ->label('Leaving Month & Year')
                    ->form([
                        Select::make('month')
                            ->label('Month')
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->native(false),
                        Select::make('year')
                            ->label('Year')
                            ->options(array_combine(range(now()->year + 1, 2020), range(now()->year + 1, 2020)))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['month'] ?? null, fn (Builder $q, $m) => $q->whereMonth('leaving_date', $m))
                            ->when($data['year'] ?? null, fn (Builder $q, $y) => $q->whereYear('leaving_date', $y));
                    }),
                TernaryFilter::make('hold_salary')
                    ->label('Hold Salary')
                    ->placeholder('All Records')
                    ->trueLabel('Salary On Hold')
                    ->falseLabel('Salary Released'),
                TernaryFilter::make('hold_tips')
                    ->label('Hold Tips')
                    ->placeholder('All Records')
                    ->trueLabel('Tips On Hold')
                    ->falseLabel('Tips Released'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('Offboard')
                    ->button()
                    ->color(fn ($record) => $record->offboarded == true ? 'gray' : 'danger')
                    ->label(fn ($record) => $record->offboarded == true ? 'Offboarded!' : 'Offboard')
                    ->requiresConfirmation()
                    ->disabled(fn ($record) => $record->offboarded)
                    ->action(function ($record) {
                        $record->offboarded = true;
                        $record->save();

                        $employee = Employee::where('employee_code', $record->employee_id)->first();
                        if ($employee) {
                            $employee->employee_status = 'Resigned';
                            $employee->save();
                        }

                        Notification::make()
                            ->title('Leaver Offboarded')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Filtered Leavers')
                    ->modalDescription('Choose file format and select header columns to include in your report.')
                    ->modalSubmitActionLabel('Download Report')
                    ->form([
                        Radio::make('format')
                            ->label('Export Format')
                            ->options([
                                'xlsx' => 'Excel Spreadsheet (.xlsx) — formatted with row highlight',
                                'csv' => 'CSV File (.csv) — plain text data',
                            ])
                            ->default('xlsx')
                            ->required(),
                        Select::make('filter_month')
                            ->label('Filter Month (Leaving Date)')
                            ->options([
                                '' => 'All Months',
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->native(false),
                        Select::make('filter_year')
                            ->label('Filter Year (Leaving Date)')
                            ->options(['' => 'All Years'] + array_combine(range(now()->year + 1, 2020), range(now()->year + 1, 2020)))
                            ->native(false),
                        Toggle::make('apply_styling')
                            ->label('Apply Status Highlight & Formatting (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Headers to Include')
                            ->options(LeaverExportService::getAvailableColumns())
                            ->default(array_keys(LeaverExportService::getAvailableColumns()))
                            ->columns(3)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, LeaverExportService $service) {
                        $query = $livewire->getFilteredTableQuery()
                            ->with(['employee.department', 'employee.designation']);

                        if (! empty($data['filter_month'])) {
                            $query->whereMonth('leaving_date', $data['filter_month']);
                        }

                        if (! empty($data['filter_year'])) {
                            $query->whereYear('leaving_date', $data['filter_year']);
                        }

                        $leavers = $query->get();

                        return $service->export(
                            $leavers,
                            $data['columns'] ?? array_keys(LeaverExportService::getAvailableColumns()),
                            $data['format'] ?? 'xlsx',
                            (bool) ($data['apply_styling'] ?? true)
                        );
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
