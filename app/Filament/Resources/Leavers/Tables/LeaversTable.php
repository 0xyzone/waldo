<?php

namespace App\Filament\Resources\Leavers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->label('#')
                    ->state(fn ($record, $livewire) => $livewire->getPage() * $livewire->getPerPage() + $record->getIndex() + 1)
                    ->sortable(),
                TextColumn::make('employee_id')
                    ->searchable(),
                TextColumn::make('employee.name')
                    ->searchable(),
                TextColumn::make('employee.department.name'),
                TextColumn::make('employee.designation.name'),
                TextColumn::make('leaving_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('hold_salary')
                    ->boolean()
                    ->color(fn ($record) => $record->hold_salary ? 'danger' : 'success'),
                IconColumn::make('hold_tips')
                    ->boolean()
                    ->color(fn ($record) => $record->hold_tips ? 'danger' : 'success'),
                IconColumn::make('publish_cl')
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
