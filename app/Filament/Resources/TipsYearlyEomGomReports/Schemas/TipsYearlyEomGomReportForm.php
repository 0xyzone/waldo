<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports\Schemas;

use App\Models\TipsYearlyEomGomReport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TipsYearlyEomGomReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('year')
                        ->label('Year')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(2100)
                        ->default((int) now()->format('Y'))
                        ->required()
                        ->unique(TipsYearlyEomGomReport::class, 'year', ignoreRecord: true),

                    TextInput::make('title')
                        ->label('Report Title')
                        ->placeholder('e.g. Yearly EOM & GOM Report 2026')
                        ->default(fn () => 'Yearly EOM & GOM Report '.now()->format('Y')),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'archived' => 'Archived',
                        ])
                        ->default('active')
                        ->native(false)
                        ->required(),

                    Textarea::make('notes')
                        ->label('Notes / Description')
                        ->placeholder('Optional notes regarding this yearly report cycle')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
