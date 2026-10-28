<?php

namespace App\Filament\Resources\ActionLogs\Tables;

use App\Models\ActionLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ActionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y h:i A')
                    ->timezone(config('app.timezone', 'Asia/Kathmandu'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user_name')
                    ->label('Performed By')
                    ->badge()
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->placeholder('System / Auto')
                    ->searchable(),

                TextColumn::make('actionable_type')
                    ->label('Resource')
                    ->formatStateUsing(fn (ActionLog $record): string => $record->resource_name)
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('actionable_id')
                    ->label('Record')
                    ->formatStateUsing(fn (ActionLog $record): string => $record->record_title)
                    ->tooltip(fn (ActionLog $record): string => "ID: {$record->actionable_id}")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('actionable_id', 'like', "%{$search}%");
                    }),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'status_changed' => 'info',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Str::headline($state)),

                TextColumn::make('status_transition')
                    ->label('Status')
                    ->state(function (ActionLog $record): string {
                        if ($record->from_status && $record->to_status) {
                            return "{$record->from_status} → {$record->to_status}";
                        }

                        return $record->to_status ?? '—';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === '—' ? 'gray' : 'primary')
                    ->toggleable(),

                TextColumn::make('action_description')
                    ->label('Details')
                    ->limit(55)
                    ->tooltip(fn (ActionLog $record): ?string => $record->action_description)
                    ->searchable()
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('actionable_type')
                    ->label('Filter by Resource')
                    ->options(function (): array {
                        return ActionLog::query()
                            ->distinct()
                            ->pluck('actionable_type')
                            ->mapWithKeys(function (string $type): array {
                                return [$type => Str::headline(class_basename($type))];
                            })
                            ->sort()
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('action')
                    ->label('Action Type')
                    ->options([
                        'created' => 'Created',
                        'status_changed' => 'Status Changed',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
