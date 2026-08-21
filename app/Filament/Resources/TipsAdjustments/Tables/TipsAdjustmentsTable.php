<?php

namespace App\Filament\Resources\TipsAdjustments\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\TipsAdjustment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TipsAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'updated' => 'Updated',
                        'cancelled' => 'Cancelled',
                    ]),
                TextColumn::make('employee.employee_code')
                    ->label('Employee Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        if ($record->type === 'add') {
                            return '+ ' . $record->amount;
                        }
                        return '- ' . $record->amount;
                    })
                    ->color(fn($record) => $record->type === 'add' ? 'success' : 'danger')
                    ->badge()
                    ->sortable(),
                TextColumn::make('remarks')
                    ->limit(20)
                    ->tooltip(fn(TipsAdjustment $record) => $record->remarks),
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
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(fn() => Department::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $deptId) => $query->whereHas('employee', fn(Builder $q) => $q->where('department_id', $deptId))
                        );
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(fn() => Designation::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $desigId) => $query->whereHas('employee', fn(Builder $q) => $q->where('designation_id', $desigId))
                        );
                    })
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created From')
                            ->native(false),
                        DatePicker::make('created_to')
                            ->label('Created To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
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
