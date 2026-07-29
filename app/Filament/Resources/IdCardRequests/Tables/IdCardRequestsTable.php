<?php

namespace App\Filament\Resources\IdCardRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdCardRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Record Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'designed' => 'info',
                        'sent for print' => 'primary',
                        'done' => 'success',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'designed' => 'Designed',
                        'sent for print' => 'Sent for Print',
                        'done' => 'Done',
                        default => (string) $state,
                    })
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'employee' => 'Existing Employee',
                        'custom' => 'Custom Entry',
                        default => (string) $state,
                    }),

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('employee_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('employee_designation')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('employee_department')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('notes')
                    ->label('IT Notes')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created Date')
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
                        'designed' => 'Designed',
                        'sent for print' => 'Sent for Print',
                        'done' => 'Done',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'employee' => 'Existing Employee',
                        'custom' => 'Custom Entry',
                    ]),
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
