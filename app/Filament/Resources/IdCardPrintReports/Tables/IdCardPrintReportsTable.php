<?php

namespace App\Filament\Resources\IdCardPrintReports\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdCardPrintReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('title')
                    ->label('Batch Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('batch_date')
                    ->label('Batch Date')
                    ->date('d M, Y')
                    ->sortable(),

                TextColumn::make('total_records')
                    ->label('Total Cards')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('sent_for_print_count')
                    ->label('Sent for Print')
                    ->badge()
                    ->color('gray')
                    ->state(fn ($record) => $record->sent_for_print_count),

                TextColumn::make('printed_count')
                    ->label('Printed')
                    ->badge()
                    ->color('info')
                    ->state(fn ($record) => $record->printed_count),

                TextColumn::make('in_office_count')
                    ->label('In Office')
                    ->badge()
                    ->color('warning')
                    ->state(fn ($record) => $record->in_office_count),

                TextColumn::make('released_count')
                    ->label('Released')
                    ->badge()
                    ->color('success')
                    ->state(fn ($record) => $record->released_count),

                TextColumn::make('on_hold_count')
                    ->label('On Hold')
                    ->badge()
                    ->color('danger')
                    ->state(fn ($record) => $record->on_hold_count),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'primary',
                        'archived' => 'gray',
                        default => 'info',
                    }),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'archived' => 'Archived',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('print')
                    ->label('Print Sheet')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->url(fn ($record) => route('id-card-print-reports.print', ['report' => $record->id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
