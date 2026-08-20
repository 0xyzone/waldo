<?php

namespace App\Filament\Resources\TerminatedEmployees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
            ]);
    }
}
