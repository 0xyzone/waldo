<?php

namespace App\Filament\Resources\TipsAdjustments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TipsAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper($record->employee_code).' | '.$record->name)
                    ->preload()
                    ->searchable(['name', 'employee_code'])
                    ->native(false)
                    ->required(),
                Select::make('type')
                    ->options([
                        'add' => 'Add',
                        'deduct' => 'Deduct',
                    ])
                    ->native(false)
                    ->required(),
                Select::make('for_month')
                    ->label('For Month')
                    ->options([
                        'january' => 'January',
                        'february' => 'February',
                        'march' => 'March',
                        'april' => 'April',
                        'may' => 'May',
                        'june' => 'June',
                        'july' => 'July',
                        'august' => 'August',
                        'september' => 'September',
                        'october' => 'October',
                        'november' => 'November',
                        'december' => 'December',
                    ])
                    ->default(strtolower(now()->format('F')))
                    ->native(false)
                    ->required(),
                TextInput::make('year')
                    ->label('Year')
                    ->default(now()->format('Y'))
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
