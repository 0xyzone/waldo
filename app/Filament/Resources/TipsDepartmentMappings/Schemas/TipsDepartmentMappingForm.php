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
                        ->columnSpanFull()
                        ->afterStateHydrated(function ($component, $state) {
                            if (is_array($state)) {
                                $component->state(array_map('strval', $state));
                            }
                        })
                        ->extraAlpineAttributes([
                            'x-init' => <<<'JS'
                                const setupSearchClear = () => {
                                    if (typeof select === 'undefined' || !select) {
                                        setTimeout(setupSearchClear, 30);
                                        return;
                                    }
                                    if (select._searchClearHooked) {
                                        return;
                                    }
                                    select._searchClearHooked = true;

                                    const resetSearchInput = () => {
                                        if (select && select.searchInput) {
                                            select.searchInput.value = '';
                                            select.searchQuery = '';
                                            if (!select.hasDynamicOptions && select.originalOptions) {
                                                select.options = JSON.parse(JSON.stringify(select.originalOptions));
                                            }
                                            select.renderOptions();
                                            if (typeof select.deferPositionDropdown === 'function') {
                                                select.deferPositionDropdown();
                                            }
                                            select.searchInput.focus();
                                        }
                                    };

                                    const origSelectOption = select.selectOption.bind(select);
                                    select.selectOption = function (value) {
                                        const wasSelected = Array.isArray(select.state) && select.state.includes(value);
                                        origSelectOption(value);
                                        if (!wasSelected) {
                                            resetSearchInput();
                                        }
                                    };
                                };
                                $nextTick(setupSearchClear);
                                $watch('state', (newVal, oldVal) => {
                                    if (Array.isArray(newVal) && (!Array.isArray(oldVal) || newVal.length > oldVal.length)) {
                                        if (typeof select !== 'undefined' && select && select.searchInput && select.searchInput.value) {
                                            select.searchInput.value = '';
                                            select.searchQuery = '';
                                            if (!select.hasDynamicOptions && select.originalOptions) {
                                                select.options = JSON.parse(JSON.stringify(select.originalOptions));
                                            }
                                            select.renderOptions();
                                            if (typeof select.deferPositionDropdown === 'function') {
                                                select.deferPositionDropdown();
                                            }
                                            select.searchInput.focus();
                                        }
                                    }
                                });
                            JS,
                        ]),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
