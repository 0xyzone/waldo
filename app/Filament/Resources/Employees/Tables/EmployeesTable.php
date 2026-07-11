<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use App\Services\GoogleSheetsService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->searchable()
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderByRaw('CAST(SUBSTRING(employee_code, 4) AS UNSIGNED) ' . $direction);
                    }),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('designation.name')
                    ->label('Designation')
                    ->sortable(),
                TextColumn::make('employee_status')
                    ->label('Status')
                    ->sortable(),
                TextColumn::make('join_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('employee_code', 'asc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('syncToGoogleSheet')
                    ->label('Sync to Google Sheet')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function (Employee $record, GoogleSheetsService $service) {
                        $service->syncEmployee($record);

                        Notification::make()
                            ->title('Synced successfully')
                            ->body("Employee {$record->employee_code} was successfully synced to Google Sheets.")
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
