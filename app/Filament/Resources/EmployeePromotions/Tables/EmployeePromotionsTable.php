<?php

namespace App\Filament\Resources\EmployeePromotions\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeePromotion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmployeePromotionsTable
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
                TextColumn::make('fromDepartment.name')
                    ->label('From Dept.')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('fromDesignation.name')
                    ->label('From Designation')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('toDepartment.name')
                    ->label('To Dept.')
                    ->placeholder('—')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('toDesignation.name')
                    ->label('To Designation')
                    ->placeholder('—')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('promotion_date')
                    ->label('Promotion Date')
                    ->date()
                    ->sortable(),
                IconColumn::make('acknowledged')
                    ->label('Acknowledged')
                    ->boolean()
                    ->color(fn (EmployeePromotion $record) => $record->acknowledged ? 'success' : 'gray'),
                IconColumn::make('hrms_synced')
                    ->label('HRMS Synced')
                    ->boolean()
                    ->color(fn (EmployeePromotion $record) => $record->hrms_synced ? 'info' : 'gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (EmployeePromotion $record) => match (true) {
                $record->acknowledged && $record->hrms_synced => 'bg-emerald-950 border-emerald-200 dark:border-emerald-900',
                $record->acknowledged => 'bg-amber-950 border-amber-200 dark:border-amber-900',
                default => null,
            })
            ->filters([
                SelectFilter::make('to_department_id')
                    ->label('New Department')
                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('to_designation_id')
                    ->label('New Designation')
                    ->options(fn () => Designation::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('acknowledged')
                    ->label('Acknowledged')
                    ->placeholder('All Records')
                    ->trueLabel('Acknowledged')
                    ->falseLabel('Not Acknowledged'),
                TernaryFilter::make('hrms_synced')
                    ->label('HRMS Synced')
                    ->placeholder('All Records')
                    ->trueLabel('HRMS Synced')
                    ->falseLabel('HRMS Not Synced'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('3xl'),
                Action::make('acknowledge')
                    ->label('Acknowledge')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Acknowledge Promotion')
                    ->modalDescription(fn (EmployeePromotion $record) => "Confirm acknowledgement of the promotion for {$record->employee?->name} ({$record->employee_id}).")
                    ->modalSubmitActionLabel('Acknowledge')
                    ->visible(fn (EmployeePromotion $record): bool => ! $record->acknowledged)
                    ->action(function (EmployeePromotion $record): void {
                        $record->update([
                            'acknowledged' => true,
                            'acknowledged_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Promotion Acknowledged')
                            ->body("Promotion for {$record->employee_id} has been acknowledged.")
                            ->success()
                            ->send();
                    }),
                Action::make('markHrmsSynced')
                    ->label(fn (EmployeePromotion $record) => $record->hrms_synced ? 'Mark HRMS Unsynced' : 'Mark HRMS Synced')
                    ->icon(fn (EmployeePromotion $record) => $record->hrms_synced ? 'heroicon-o-arrow-uturn-left' : 'heroicon-o-computer-desktop')
                    ->color(fn (EmployeePromotion $record) => $record->hrms_synced ? 'gray' : 'info')
                    ->button()
                    ->visible(fn () => Auth::user()->hasRole(['super_admin', 'HR']))
                    ->requiresConfirmation()
                    ->modalHeading(fn (EmployeePromotion $record) => $record->hrms_synced ? 'Unmark HRMS Sync' : 'Mark as HRMS Synced')
                    ->modalDescription(fn (EmployeePromotion $record) => $record->hrms_synced
                        ? "This will mark the HRMS sync as incomplete for {$record->employee_id}."
                        : "Confirm that you have manually updated HRMS for {$record->employee_id}.")
                    ->action(function (EmployeePromotion $record): void {
                        $isSyncing = ! $record->hrms_synced;
                        $record->update([
                            'hrms_synced' => $isSyncing,
                            'hrms_synced_at' => $isSyncing ? now() : null,
                        ]);

                        Notification::make()
                            ->title($isSyncing ? 'Marked as HRMS Synced' : 'HRMS Sync Unmarked')
                            ->body($isSyncing
                                ? "Promotion for {$record->employee_id} has been marked as synced to HRMS."
                                : "Promotion for {$record->employee_id} HRMS sync status has been reset.")
                            ->color($isSyncing ? 'info' : 'warning')
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
