<?php

namespace App\Filament\Resources\Adjustments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.employee_code')
                    ->searchable(),
                TextColumn::make('employee.name')
                    ->searchable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'add' => 'Addition (+)',
                        'subtract' => 'Deduction (-)',
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'add' => 'success',
                        'subtract' => 'danger',
                    })
                    ->searchable(),
                TextColumn::make('for_month')
                    ->searchable(),
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
