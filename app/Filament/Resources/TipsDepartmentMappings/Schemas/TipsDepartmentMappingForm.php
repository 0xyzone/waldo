<?php

namespace App\Filament\Resources\TipsDepartmentMappings\Schemas;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TipsDepartmentMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('page_name')
                        ->label('Report Page / Tab Name')
                        ->placeholder('e.g., PIT, Cage, Customer Service, Security + Transport')
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),

                    Select::make('department_ids')
                        ->label('Assigned Departments')
                        ->helperText('Select all departments that should appear on this report page.')
                        ->options(fn () => Department::orderBy('name')->pluck('name', 'id')->toArray())
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columnSpanFull(),
            ]);
    }
}
