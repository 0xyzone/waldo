<?php

namespace App\Filament\Resources\TipsDepartmentMappings\Tables;

use App\Models\Department;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipsDepartmentMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('page_name')
                    ->label('Report Page / Tab Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('department_ids')
                    ->label('Assigned Departments')
                    ->formatStateUsing(function ($state) {
                        if (empty($state) || ! is_array($state)) {
                            return '-';
                        }
                        $names = Department::whereIn('id', $state)->pluck('name')->toArray();

                        return implode(', ', $names);
                    })
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
