<?php

namespace App\Filament\Resources\Departments\Tables;

use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->numeric()
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('designations_count')
                    ->label('Designations')
                    ->counts('designations')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('active_employees_count')
                    ->label('Active Staff')
                    ->getStateUsing(fn ($record) => Employee::where('department_id', $record->id)
                        ->where('employee_status', 'Active')
                        ->count())
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('rank', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Departments')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                \Filament\Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
