<?php

namespace App\Filament\Resources\ActionLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ActionLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Action Log Overview')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextEntry::make('user_name')
                                    ->label('Performed By')
                                    ->badge()
                                    ->icon('heroicon-o-user')
                                    ->color('primary')
                                    ->placeholder('System / Auto'),

                                TextEntry::make('actionable_type')
                                    ->label('Resource Type')
                                    ->formatStateUsing(fn ($record) => $record->resource_name)
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('actionable_id')
                                    ->label('Record Identifier')
                                    ->formatStateUsing(fn ($record) => $record->record_title)
                                    ->badge()
                                    ->color('gray'),
                            ]),

                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextEntry::make('action')
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

                                TextEntry::make('from_status')
                                    ->label('From Status')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('to_status')
                                    ->label('To Status')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        TextEntry::make('action_description')
                            ->label('Action Description')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('Timestamp')
                            ->dateTime()
                            ->timezone(config('app.timezone', 'Asia/Kathmandu')),
                    ]),

                Section::make('Attribute Modifications')
                    ->icon('heroicon-o-document-text')
                    ->description('Detailed breakdown of before and after field values.')
                    ->schema([
                        ViewEntry::make('changes')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->view('filament.components.action-log-changes-table'),
                    ])
                    ->collapsible(),
            ]);
    }
}
