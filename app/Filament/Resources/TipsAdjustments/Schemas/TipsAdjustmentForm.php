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
                        'january' => 'January Release',
                        'february' => 'February Release',
                        'march' => 'March Release',
                        'april' => 'April Release',
                        'may' => 'May Release',
                        'june' => 'June Release',
                        'july' => 'July Release',
                        'august' => 'August Release',
                        'september' => 'September Release',
                        'october' => 'October Release',
                        'november' => 'November Release',
                        'december' => 'December Release',
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
