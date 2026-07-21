<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department Details')
                    ->description('Manage department name, sort rank, and active status.')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Department Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Gaming, Human Resource')
                            ->columnSpanFull(),

                        TextInput::make('rank')
                            ->label('Sort Rank')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->placeholder('Lower number = higher priority')
                            ->helperText('Used to order departments in reports and lists.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive departments are hidden from dropdowns.')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
