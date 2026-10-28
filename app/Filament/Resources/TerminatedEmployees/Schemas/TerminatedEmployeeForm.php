<?php

namespace App\Filament\Resources\TerminatedEmployees\Schemas;

use App\Filament\Components\ActionLogSection;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TerminatedEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Termination Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->relationship(
                                        name: 'employee',
                                        titleAttribute: 'name'
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper($record->employee_code).' | '.$record->name)
                                    ->searchable(['name', 'employee_code'])
                                    ->preload()
                                    ->required(),
                                DatePicker::make('termination_date')
                                    ->label('Date of Termination')
                                    ->native(false)
                                    ->default(now())
                                    ->required(),
                                DatePicker::make('last_working_date')
                                    ->label('Last Date of Working')
                                    ->native(false)
                                    ->default(now())
                                    ->required(),
                            ]),
                        Textarea::make('reason')
                            ->label('Reason for Termination')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Letter Status')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Toggle::make('is_letter_printed')
                                    ->label('Letter Printed')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, bool $state) {
                                        if ($state) {
                                            $set('letter_printed_at', now()->toDateTimeString());
                                        } else {
                                            $set('letter_printed_at', null);
                                        }
                                    }),
                                DatePicker::make('letter_printed_at')
                                    ->label('Printed Date')
                                    ->visible(fn (Get $get) => (bool) $get('is_letter_printed'))
                                    ->native(false),
                                Toggle::make('is_letter_released')
                                    ->label('Letter Released')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, bool $state) {
                                        if ($state) {
                                            $set('letter_released_at', now()->toDateTimeString());
                                        } else {
                                            $set('letter_released_at', null);
                                        }
                                    }),
                                DatePicker::make('letter_released_at')
                                    ->label('Released Date')
                                    ->visible(fn (Get $get) => (bool) $get('is_letter_released'))
                                    ->native(false),
                            ]),
                    ]),
                ActionLogSection::make(),
            ]);
    }
}
