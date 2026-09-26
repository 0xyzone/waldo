<?php

namespace App\Filament\Resources\TipsNonEmployees\Schemas;

use App\Models\TipsNonEmployee;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TipsNonEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Non-Employee Information')
                    ->description('Manage external staff details for tips distribution (e.g. outsourced security, caretakers).')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->label('Code')
                                ->default(fn () => TipsNonEmployee::generateNextCode())
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->helperText('Auto-generated 3-digit code (e.g. 001, 002)'),

                            TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('e.g. Jit Man Tamang'),

                            TextInput::make('designation')
                                ->label('Designation / Organization')
                                ->placeholder('e.g. Garud Security, Care Taker')
                                ->maxLength(255),

                            TextInput::make('tips_percentage')
                                ->label('Tips %')
                                ->numeric()
                                ->default(100)
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->required(),

                            TextInput::make('distribution_amount')
                                ->label('Distribution Amount')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->placeholder('e.g. 500, 2500'),

                            Toggle::make('is_active')
                                ->label('Active for Tips Generation')
                                ->default(true)
                                ->helperText('When enabled, this person will be automatically included in newly generated tips distribution sheets.'),
                        ]),
                    ]),
            ]);
    }
}
