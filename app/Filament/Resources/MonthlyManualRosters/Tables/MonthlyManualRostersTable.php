<?php

namespace App\Filament\Resources\MonthlyManualRosters\Tables;

use App\Filament\Resources\MonthlyManualRosters\MonthlyManualRosterResource;
use App\Models\MonthlyManualRoster;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MonthlyManualRostersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period')
                    ->label('Month & Year')
                    ->state(fn (MonthlyManualRoster $record): string => $record->period)
                    ->weight('bold')
                    ->icon('heroicon-o-calendar')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('year', $direction)->orderBy('month', $direction)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Completed',
                        'in_progress' => 'In Progress',
                        'empty' => 'No Employees',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'empty' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('progress')
                    ->label('Roster Progress')
                    ->state(function (MonthlyManualRoster $record): string {
                        if ($record->total_employees === 0) {
                            return '0 / 0 (0%)';
                        }

                        return "{$record->completed_count} / {$record->total_employees} ({$record->completion_percentage}%)";
                    })
                    ->badge()
                    ->color(fn (MonthlyManualRoster $record): string => $record->completion_percentage === 100 ? 'success' : ($record->completion_percentage > 0 ? 'info' : 'gray')),

                TextColumn::make('total_employees')
                    ->label('Total Staff')
                    ->alignCenter(),

                TextColumn::make('completed_count')
                    ->label('Updated in HRMS')
                    ->alignCenter(),

                TextColumn::make('pending_count')
                    ->label('Pending')
                    ->state(fn (MonthlyManualRoster $record): int => $record->pending_count)
                    ->color(fn (MonthlyManualRoster $record): string => $record->pending_count > 0 ? 'danger' : 'gray')
                    ->alignCenter(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'empty' => 'No Employees',
                    ]),

                SelectFilter::make('year')
                    ->options(function (): array {
                        return MonthlyManualRoster::distinct()->pluck('year', 'year')->toArray();
                    }),
            ])
            ->actions([
                Action::make('view_checklist')
                    ->label('Open Checklist')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('primary')
                    ->url(fn (MonthlyManualRoster $record): string => MonthlyManualRosterResource::getUrl('view', ['record' => $record])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
