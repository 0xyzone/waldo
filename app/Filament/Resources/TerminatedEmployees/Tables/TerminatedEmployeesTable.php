<?php

namespace App\Filament\Resources\TerminatedEmployees\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Services\TerminatedEmployeeExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TerminatedEmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('employee_id')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->sortable(),
                TextColumn::make('termination_date')
                    ->label('Date of Termination')
                    ->date()
                    ->sortable(),
                TextColumn::make('last_working_date')
                    ->label('Last Date of Working')
                    ->date()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
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
                Filter::make('termination_date')
                    ->form([
                        DatePicker::make('termination_date_from')
                            ->label('Terminated From')
                            ->native(false),
                        DatePicker::make('termination_date_to')
                            ->label('Terminated To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['termination_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('termination_date', '>=', $date)
                            )
                            ->when(
                                $data['termination_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('termination_date', '<=', $date)
                            );
                    }),
                Filter::make('last_working_date')
                    ->form([
                        DatePicker::make('last_working_from')
                            ->label('Last Working From')
                            ->native(false),
                        DatePicker::make('last_working_to')
                            ->label('Last Working To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['last_working_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('last_working_date', '>=', $date)
                            )
                            ->when(
                                $data['last_working_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('last_working_date', '<=', $date)
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Filtered Terminated Employees')
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
                            ->label('Filter Month (Termination Date)')
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
                            ->label('Filter Year (Termination Date)')
                            ->options(['' => 'All Years'] + array_combine(range(now()->year + 1, 2020), range(now()->year + 1, 2020)))
                            ->native(false),
                        Toggle::make('apply_styling')
                            ->label('Apply Status Highlight & Formatting (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Headers to Include')
                            ->options(TerminatedEmployeeExportService::getAvailableColumns())
                            ->default(array_keys(TerminatedEmployeeExportService::getAvailableColumns()))
                            ->columns(3)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, TerminatedEmployeeExportService $service) {
                        $query = $livewire->getFilteredTableQuery()
                            ->with(['employee.department', 'employee.designation']);

                        if (! empty($data['filter_month'])) {
                            $query->whereMonth('termination_date', $data['filter_month']);
                        }

                        if (! empty($data['filter_year'])) {
                            $query->whereYear('termination_date', $data['filter_year']);
                        }

                        $records = $query->get();

                        return $service->export(
                            $records,
                            $data['columns'] ?? array_keys(TerminatedEmployeeExportService::getAvailableColumns()),
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
