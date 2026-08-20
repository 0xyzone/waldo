<?php

namespace App\Filament\Resources\EmployeeSuspensions\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeSuspension;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeSuspensionsTable
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
                TextColumn::make('start_date')
                    ->label('Suspended From')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Suspended To')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'completed' => 'Uplifted',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    }),
                TextColumn::make('attachments')
                    ->label('Attachments')
                    ->badge()
                    ->color('info')
                    ->state(function (EmployeeSuspension $record): string {
                        $count = is_array($record->attachments) ? count($record->attachments) : 0;

                        return $count > 0 ? "{$count} file(s)" : 'None';
                    }),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(35)
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
                SelectFilter::make('status')
                    ->label('Suspension Status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed / Uplifted',
                        'cancelled' => 'Cancelled',
                    ])
                    ->native(false),
                Filter::make('suspension_dates')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->native(false),
                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date)
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date)
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('5xl'),
                Action::make('uplift')
                    ->label('Uplift Suspension')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Uplift Employee Suspension')
                    ->modalDescription(fn (EmployeeSuspension $record) => "Are you sure you want to uplift the suspension for {$record->employee?->name} ({$record->employee_id})? This will mark the suspension as completed and restore the employee status to Active.")
                    ->modalSubmitActionLabel('Uplift Suspension')
                    ->visible(fn (EmployeeSuspension $record): bool => $record->status === 'active')
                    ->action(function (EmployeeSuspension $record): void {
                        $record->update(['status' => 'completed']);

                        // Check if employee has any other active suspensions
                        $hasOtherActive = EmployeeSuspension::where('employee_id', $record->employee_id)
                            ->where('id', '!=', $record->id)
                            ->where('status', 'active')
                            ->exists();

                        if (! $hasOtherActive) {
                            $employee = Employee::where('employee_code', $record->employee_id)->first();
                            if ($employee && $employee->employee_status === 'Suspended') {
                                $employee->update(['employee_status' => 'Active']);
                            }
                        }

                        Notification::make()
                            ->title('Suspension Uplifted')
                            ->body("Suspension for {$record->employee_id} has been uplifted and employee status reset to Active.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
