<?php

namespace App\Filament\Resources\NametagDistributions\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\NametagDistribution;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NametagDistributionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label('Code')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Printed' => 'Printed',
                        'Released' => 'Released',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(25)
                    ->tooltip(fn (NametagDistribution $record): ?string => $record->remarks)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (NametagDistribution $record) => match ($record->status) {
                'Pending' => 'border-l-4 border-amber-500',
                'Printed' => 'border-l-4 border-blue-500',
                'Released' => 'border-l-4 border-emerald-500',
                default => null,
            })
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Printed' => 'Printed',
                        'Released' => 'Released',
                    ])
                    ->native(false),

                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $deptId) => $query->whereHas('employee', fn (Builder $q) => $q->where('department_id', $deptId))
                        );
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(fn () => Designation::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $desigId) => $query->whereHas('employee', fn (Builder $q) => $q->where('designation_id', $desigId))
                        );
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From Date')
                            ->native(false),
                        DatePicker::make('date_until')
                            ->label('Until Date')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
