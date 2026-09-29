<?php

namespace App\Filament\Resources\MonthlyManualRosters\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MonthlyManualRosterForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear - 1; $y <= $currentYear + 3; $y++) {
            $years[$y] = (string) $y;
        }

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        return $schema
            ->components([
                Section::make('Monthly Manual Roster Period')
                    ->description('Select the target month and year for manual roster entry in your HRMS.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('year')
                                ->label('Year')
                                ->options($years)
                                ->default($currentYear)
                                ->native(false)
                                ->required()
                                ->disabled(fn (string $operation): bool => $operation === 'edit'),

                            Select::make('month')
                                ->label('Month')
                                ->options($months)
                                ->default((int) date('m'))
                                ->native(false)
                                ->required()
                                ->disabled(fn (string $operation): bool => $operation === 'edit')
                                ->unique(
                                    table: 'monthly_manual_rosters',
                                    column: 'month',
                                    ignoreRecord: true,
                                    modifyRuleUsing: function (Unique $rule, Get $get) {
                                        return $rule->where('year', $get('year'));
                                    }
                                ),
                        ]),

                        Placeholder::make('info_notice')
                            ->label('Automatic Employee Import')
                            ->content('When you create this record, all currently active Manual Attendance Employees will be automatically populated into this monthly roster checklist.')
                            ->hidden(fn (string $operation): bool => $operation === 'edit'),

                        Textarea::make('notes')
                            ->label('Notes / Instructions (Optional)')
                            ->placeholder('e.g. Shifts confirmed with heads of department; pending HRMS entry')
                            ->rows(3),
                    ]),
            ]);
    }
}
