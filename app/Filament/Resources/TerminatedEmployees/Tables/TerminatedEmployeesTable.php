<?php

namespace App\Filament\Resources\TerminatedEmployees\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\TerminatedEmployee;
use App\Services\TerminatedEmployeeExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
                TextColumn::make('is_letter_printed')
                    ->label('Letter Printed')
                    ->badge()
                    ->formatStateUsing(fn (bool $state, TerminatedEmployee $record): string => $state ? ($record->letter_printed_at ? 'Printed ('.$record->letter_printed_at->format('M d').')' : 'Printed') : 'Not Printed')
                    ->color(fn (bool $state): string => $state ? 'info' : 'gray')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-printer' : 'heroicon-m-x-circle')
                    ->sortable(),
                TextColumn::make('is_letter_released')
                    ->label('Letter Released')
                    ->badge()
                    ->formatStateUsing(fn (bool $state, TerminatedEmployee $record): string => $state ? ($record->letter_released_at ? 'Released ('.$record->letter_released_at->format('M d').')' : 'Released') : 'Not Released')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(15)
                    ->tooltip(fn ($record) => $record->reason),
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
                TernaryFilter::make('is_letter_printed')
                    ->label('Letter Printed')
                    ->trueLabel('Printed')
                    ->falseLabel('Not Printed'),
                TernaryFilter::make('is_letter_released')
                    ->label('Letter Released')
                    ->trueLabel('Released')
                    ->falseLabel('Not Released'),
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
                Action::make('markLetterPrinted')
                    ->label(fn (TerminatedEmployee $record): string => $record->is_letter_printed ? 'Printed' : 'Mark as Printed')
                    ->tooltip(fn (TerminatedEmployee $record): string => $record->is_letter_printed ? 'Letter printed on '.($record->letter_printed_at?->format('M d, Y') ?? 'record').'. Click to revert.' : 'Mark termination letter as Printed')
                    ->icon(fn (TerminatedEmployee $record): string => $record->is_letter_printed ? 'heroicon-s-printer' : 'heroicon-o-printer')
                    ->color(fn (TerminatedEmployee $record): string => $record->is_letter_printed ? 'gray' : 'info')
                    ->requiresConfirmation()
                    ->modalHeading(fn (TerminatedEmployee $record): string => $record->is_letter_printed ? 'Revert Printed Status' : 'Mark Letter as Printed')
                    ->modalDescription(fn (TerminatedEmployee $record): string => $record->is_letter_printed
                        ? "Are you sure you want to revert the printed status for {$record->employee?->name} ({$record->employee_id}) back to Not Printed?"
                        : "Mark termination letter for {$record->employee?->name} ({$record->employee_id}) as Printed?")
                    ->modalSubmitActionLabel(fn (TerminatedEmployee $record): string => $record->is_letter_printed ? 'Yes, Revert' : 'Yes, Mark as Printed')
                    ->action(function (TerminatedEmployee $record) {
                        $newStatus = ! $record->is_letter_printed;
                        $record->update([
                            'is_letter_printed' => $newStatus,
                            'letter_printed_at' => $newStatus ? now() : null,
                        ]);

                        Notification::make()
                            ->title($newStatus ? 'Letter Marked as Printed' : 'Printed Status Reverted')
                            ->body("{$record->employee?->name} ({$record->employee_id}) letter status updated.")
                            ->success()
                            ->send();
                    }),
                Action::make('markLetterReleased')
                    ->label(fn (TerminatedEmployee $record): string => $record->is_letter_released ? 'Released' : 'Mark as Released')
                    ->tooltip(fn (TerminatedEmployee $record): string => $record->is_letter_released ? 'Letter released on '.($record->letter_released_at?->format('M d, Y') ?? 'record').'. Click to revert.' : 'Mark termination letter as Released to the employee')
                    ->icon(fn (TerminatedEmployee $record): string => $record->is_letter_released ? 'heroicon-s-check-badge' : 'heroicon-o-paper-airplane')
                    ->color(fn (TerminatedEmployee $record): string => $record->is_letter_released ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (TerminatedEmployee $record): string => $record->is_letter_released ? 'Revert Released Status' : 'Mark Letter as Released')
                    ->modalDescription(fn (TerminatedEmployee $record): string => $record->is_letter_released
                        ? "Are you sure you want to revert the released status for {$record->employee?->name} ({$record->employee_id}) back to Not Released?"
                        : "Mark termination letter for {$record->employee?->name} ({$record->employee_id}) as Released to the employee?")
                    ->modalSubmitActionLabel(fn (TerminatedEmployee $record): string => $record->is_letter_released ? 'Yes, Revert' : 'Yes, Mark as Released')
                    ->action(function (TerminatedEmployee $record) {
                        $newStatus = ! $record->is_letter_released;
                        $record->update([
                            'is_letter_released' => $newStatus,
                            'letter_released_at' => $newStatus ? now() : null,
                        ]);

                        Notification::make()
                            ->title($newStatus ? 'Letter Marked as Released' : 'Released Status Reverted')
                            ->body("{$record->employee?->name} ({$record->employee_id}) letter release status updated.")
                            ->success()
                            ->send();
                    }),
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
                    BulkAction::make('markLetterPrintedBulk')
                        ->label('Mark as Letter Printed')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Selected as Letter Printed')
                        ->modalDescription('Mark all selected terminated employees as having their termination letter printed?')
                        ->action(function (Collection $records) {
                            $records->each(fn (TerminatedEmployee $record) => $record->update([
                                'is_letter_printed' => true,
                                'letter_printed_at' => now(),
                            ]));

                            Notification::make()
                                ->title('Marked as Letter Printed')
                                ->body("{$records->count()} employee(s) marked as Letter Printed.")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('markLetterReleasedBulk')
                        ->label('Mark as Letter Released')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Selected as Letter Released')
                        ->modalDescription('Mark all selected terminated employees as having their termination letter released to them?')
                        ->action(function (Collection $records) {
                            $records->each(fn (TerminatedEmployee $record) => $record->update([
                                'is_letter_released' => true,
                                'letter_released_at' => now(),
                            ]));

                            Notification::make()
                                ->title('Marked as Letter Released')
                                ->body("{$records->count()} employee(s) marked as Letter Released.")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
