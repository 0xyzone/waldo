<?php

namespace App\Filament\Resources\TerminatedEmployees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TerminatedEmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('employee_id')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->searchable(),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->searchable(),
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
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
