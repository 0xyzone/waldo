<?php

namespace App\Filament\Resources\UnofficialNametagRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnofficialNametagRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'ready' => 'Ready',
                        'distributed' => 'Distributed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title('Status Updated')
                            ->body("NameTag request for {$record->employee_name} updated to {$state}.")
                            ->success()
                            ->send();
                    }),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'employee' => 'info',
                        'custom' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'employee' => 'Existing Employee',
                        'custom' => 'Custom Entry',
                        default => (string) $state,
                    })
                    ->sortable(),

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('employee_name')
                    ->label('Person Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name_on_tag')
                    ->label('Name on Tag')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('remarks')
                    ->label('Remarks / Notes')
                    ->limit(35)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—'),

                TextColumn::make('creator.name')
                    ->label('Requested By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Requested Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Record Status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'ready' => 'Ready',
                        'distributed' => 'Distributed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'employee' => 'Existing Employee',
                        'custom' => 'Custom Entry',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Requested From')
                            ->native(false),
                        DatePicker::make('created_to')
                            ->label('Requested To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
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
