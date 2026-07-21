<?php

namespace App\Filament\Resources\Designations\Tables;

use App\Models\Department;
use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DesignationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('rank')
                    ->label('#')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->width('50px'),

                TextColumn::make('active_employees_count')
                    ->label('Active Staff')
                    ->getStateUsing(fn ($record) => Employee::where('designation_id', $record->id)
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
            ->defaultSort('department_id', 'asc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::where('is_active', true)->orderBy('rank')->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('All Departments'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Designations')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
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
