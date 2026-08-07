<?php

namespace App\Filament\Resources\Adjustments\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Services\AdjustmentExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('employee.employee_code')
                    ->label('Employee Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'add' => 'Addition (+)',
                        'subtract' => 'Deduction (-)',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'add' => 'success',
                        'subtract' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('for_month')
                    ->label('For Month')
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state))
                    ->searchable()
                    ->sortable(),
                SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ])
                    ->searchable(),
                TextColumn::make('notes_by_hr')
                    ->label('Notes by HR')
                    ->limit(15)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('notes_by_finance')
                    ->label('Notes by Finance')
                    ->limit(15)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                SelectFilter::make('for_month')
                    ->label('Target Month')
                    ->options([
                        'january' => 'January',
                        'february' => 'February',
                        'march' => 'March',
                        'april' => 'April',
                        'may' => 'May',
                        'june' => 'June',
                        'july' => 'July',
                        'august' => 'August',
                        'september' => 'September',
                        'october' => 'October',
                        'november' => 'November',
                        'december' => 'December',
                    ])
                    ->native(false),
                Filter::make('target_year')
                    ->label('Year')
                    ->form([
                        Select::make('year')
                            ->label('Year')
                            ->options(array_combine(range(now()->year + 1, 2020), range(now()->year + 1, 2020)))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['year'] ?? null,
                            fn (Builder $query, $year): Builder => $query->whereYear('created_at', $year)
                        );
                    }),
                SelectFilter::make('type')
                    ->label('Adjustment Type')
                    ->options([
                        'add' => 'Addition (+)',
                        'subtract' => 'Deduction (-)',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created From')
                            ->native(false),
                        DatePicker::make('created_to')
                            ->label('Created To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
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
                    ->modalHeading('Export Filtered Adjustments')
                    ->modalDescription('Choose file format and select header columns to include in your report.')
                    ->modalSubmitActionLabel('Download Report')
                    ->form([
                        Radio::make('format')
                            ->label('Export Format')
                            ->options([
                                'xlsx' => 'Excel Spreadsheet (.xlsx) — formatted with type colors',
                                'csv' => 'CSV File (.csv) — plain text data',
                            ])
                            ->default('xlsx')
                            ->required(),
                        Select::make('filter_month')
                            ->label('Filter Target Month')
                            ->options([
                                '' => 'All Months',
                                'january' => 'January',
                                'february' => 'February',
                                'march' => 'March',
                                'april' => 'April',
                                'may' => 'May',
                                'june' => 'June',
                                'july' => 'July',
                                'august' => 'August',
                                'september' => 'September',
                                'october' => 'October',
                                'november' => 'November',
                                'december' => 'December',
                            ])
                            ->native(false),
                        Select::make('filter_year')
                            ->label('Filter Year (Created Date)')
                            ->options(['' => 'All Years'] + array_combine(range(now()->year + 1, 2020), range(now()->year + 1, 2020)))
                            ->native(false),
                        Toggle::make('apply_styling')
                            ->label('Apply Adjustment Type Colors & Formatting (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Headers to Include')
                            ->options(AdjustmentExportService::getAvailableColumns())
                            ->default(array_keys(AdjustmentExportService::getAvailableColumns()))
                            ->columns(3)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, AdjustmentExportService $service) {
                        $query = $livewire->getFilteredTableQuery()
                            ->with(['employee.department', 'employee.designation']);

                        if (! empty($data['filter_month'])) {
                            $query->where('for_month', $data['filter_month']);
                        }

                        if (! empty($data['filter_year'])) {
                            $query->whereYear('created_at', $data['filter_year']);
                        }

                        $adjustments = $query->get();

                        return $service->export(
                            $adjustments,
                            $data['columns'] ?? array_keys(AdjustmentExportService::getAvailableColumns()),
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
