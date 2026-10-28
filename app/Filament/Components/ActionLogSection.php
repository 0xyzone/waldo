<?php

namespace App\Filament\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ActionLogSection
{
    public static function make(string $relationship = 'actionLogs'): Section
    {
        return Section::make('Action Log History')
            ->icon('heroicon-o-clock')
            ->description('Automated history of status changes, field modifications, and user actions.')
            ->schema([
                Repeater::make($relationship)
                    ->relationship($relationship)
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 4])
                            ->schema([
                                TextInput::make('user_name')
                                    ->label('Done By')
                                    ->disabled(),

                                TextInput::make('to_status')
                                    ->label('Status Set')
                                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record?->action ?? 'updated'))
                                    ->disabled(),

                                TextInput::make('action_description')
                                    ->label('Action Log')
                                    ->disabled(),

                                DateTimePicker::make('created_at')
                                    ->label('Timestamp')
                                    ->disabled(),
                            ]),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->collapsible()
                    ->columnSpanFull(),
            ])
            ->hidden(fn (string $operation): bool => $operation === 'create')
            ->columnSpanFull()
            ->collapsible();
    }
}
