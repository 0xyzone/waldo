<?php

namespace App\Filament\Resources\TipsEomGomExcludedDepartments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipsEomGomExcludedDepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('department.name')
                    ->label('Excluded Department')
                    ->badge()
                    ->color('danger')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Reason / Notes')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->notes)
                    ->placeholder('None specified'),

                TextColumn::make('created_at')
                    ->label('Excluded On')
                    ->dateTime('d M, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
