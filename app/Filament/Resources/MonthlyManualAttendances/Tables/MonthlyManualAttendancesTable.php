<?php

namespace App\Filament\Resources\MonthlyManualAttendances\Tables;

use App\Filament\Resources\MonthlyManualAttendances\MonthlyManualAttendanceResource;
use App\Models\MonthlyManualAttendance;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MonthlyManualAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period')
                    ->label('Month & Year')
                    ->state(fn (MonthlyManualAttendance $record): string => $record->period)
                    ->weight('bold')
                    ->icon('heroicon-o-calendar')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('year', $direction)->orderBy('month', $direction)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Completed',
                        'in_progress' => 'In Progress',
                        'empty' => 'No Staff',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'empty' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('progress')
                    ->label('Attendance Progress')
                    ->state(function (MonthlyManualAttendance $record): string {
                        if ($record->total_employees === 0) {
                            return '0 / 0 (0%)';
                        }

                        return "{$record->completed_count} / {$record->total_employees} ({$record->completion_percentage}%)";
                    })
                    ->badge()
                    ->color(fn (MonthlyManualAttendance $record): string => $record->completion_percentage === 100 ? 'success' : ($record->completion_percentage > 0 ? 'info' : 'gray')),

                TextColumn::make('total_employees')
                    ->label('Total Staff')
                    ->alignCenter(),

                TextColumn::make('completed_count')
                    ->label('Updated in HRMS')
                    ->alignCenter(),

                TextColumn::make('pending_count')
                    ->label('Pending')
                    ->state(fn (MonthlyManualAttendance $record): int => $record->pending_count)
                    ->color(fn (MonthlyManualAttendance $record): string => $record->pending_count > 0 ? 'danger' : 'gray')
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
                        'empty' => 'No Staff',
                    ]),

                SelectFilter::make('year')
                    ->options(function (): array {
                        return MonthlyManualAttendance::distinct()->pluck('year', 'year')->toArray();
                    }),
            ])
            ->actions([
                Action::make('view_checklist')
                    ->label('Open Checklist')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('primary')
                    ->url(fn (MonthlyManualAttendance $record): string => MonthlyManualAttendanceResource::getUrl('view', ['record' => $record])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (MonthlyManualAttendance $record): string => MonthlyManualAttendanceResource::getUrl('view', ['record' => $record]))
            ->recordAction('view_checklist');
    }
}
