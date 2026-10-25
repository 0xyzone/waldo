<?php

namespace App\Filament\Resources\ManualAttendanceStaffs\Tables;

use App\Models\Department;
use App\Models\ManualAttendanceStaff;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ManualAttendanceStaffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Employee code copied')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->placeholder('—')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->afterStateUpdated(function (ManualAttendanceStaff $record, bool $state): void {
                        Notification::make()
                            ->title('Status Updated')
                            ->body("Employee {$record->employee_code} set to ".($state ? 'Active' : 'Inactive').' for manual attendance.')
                            ->success()
                            ->send();
                    }),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->limit(40)
                    ->tooltip(fn (ManualAttendanceStaff $record): ?string => $record->notes),

                TextColumn::make('created_at')
                    ->label('Designated On')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),

                SelectFilter::make('department')
                    ->label('Department')
                    ->options(fn (): array => Department::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            return $query->whereHas('employee', function (Builder $q) use ($data) {
                                $q->where('department_id', $data['value']);
                            });
                        }

                        return $query;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Mark Active')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title('Staff Marked Active')
                                ->body(count($records).' employee(s) marked as active for manual attendance.')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label('Mark Inactive')
                        ->icon('heroicon-m-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title('Staff Marked Inactive')
                                ->body(count($records).' employee(s) marked as inactive for manual attendance.')
                                ->warning()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
