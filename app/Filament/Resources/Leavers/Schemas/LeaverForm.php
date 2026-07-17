<?php

namespace App\Filament\Resources\Leavers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leaving Details')
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
                                DatePicker::make('leaving_date')
                                    ->label('Leaving Date')
                                    ->native(false)
                                    ->required(),
                            ]),
                        Textarea::make('remarks')
                            ->label('Remarks / Reason for Leaving')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Payroll & Financial Holds')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Toggle::make('hold_salary')
                                    ->label('Hold Salary')
                                    ->required(),
                                Toggle::make('hold_tips')
                                    ->label('Hold Tips')
                                    ->required(),
                                Toggle::make('publish_cl')
                                    ->label('Publish CL Balance')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
