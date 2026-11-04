<?php

namespace App\Filament\Resources\NametagReturns\Tables;

use App\Models\NametagReturn;
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

class NametagReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
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
                TextColumn::make('return_date')
                    ->label('Return Date')
                    ->date('M d, Y')
                    ->sortable(),
                SelectColumn::make('condition')
                    ->label('Condition')
                    ->options([
                        'Good' => 'Good Condition',
                        'Damaged' => 'Damaged',
                        'Lost' => 'Lost / Missing',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(25)
                    ->tooltip(fn (NametagReturn $record): ?string => $record->remarks)
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
            ->recordClasses(fn (NametagReturn $record) => match ($record->condition) {
                'Good' => 'border-l-4 border-emerald-500',
                'Damaged' => 'border-l-4 border-amber-500',
                'Lost' => 'border-l-4 border-rose-500',
                default => null,
            })
            ->filters([
                SelectFilter::make('condition')
                    ->label('Condition')
                    ->options([
                        'Good' => 'Good Condition',
                        'Damaged' => 'Damaged',
                        'Lost' => 'Lost / Missing',
                    ]),

                SelectFilter::make('department')
                    ->label('Department')
                    ->relationship('employee.department', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('designation')
                    ->label('Designation')
                    ->relationship('employee.designation', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('return_date')
                    ->form([
                        DatePicker::make('from')->label('Return Date From')->native(false),
                        DatePicker::make('until')->label('Return Date To')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('return_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('return_date', '<=', $date),
                            );
                    }),
            ])
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
