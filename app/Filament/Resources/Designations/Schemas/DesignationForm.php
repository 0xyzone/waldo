<?php

namespace App\Filament\Resources\Designations\Schemas;

use App\Filament\Components\ActionLogSection;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DesignationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Designation Details')
                    ->description('Assign a designation to a department with a sort rank.')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Designation Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Shift Manager, HR Admin')
                            ->columnSpanFull(),

                        RichEditor::make('job_description')
                            ->label('Job Description')
                            ->placeholder('Enter the job description, key responsibilities, and duties for this designation...')
                            ->helperText('This job description will be available as pre-built variable {{ employee_job_description }} when generating letters.')
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('rank')
                            ->label('Sort Rank')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->placeholder('Lower number = higher priority')
                            ->helperText('Used for ordering in reports.'),

                        TextInput::make('default_tips_amount')
                            ->label('Default Tips Amount')
                            ->numeric()
                            ->prefix('₹')
                            ->placeholder('0.00')
                            ->helperText('Baseline tips amount for employees under this designation.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive designations are hidden from dropdowns.')
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Royalty Bonus Configuration')
                    ->description('Set annual budget for royalty bonus. Monthly maximum and minimum payouts are automatically calculated.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('royalty_annual_budget')
                            ->label('Total Annual Budget')
                            ->numeric()
                            ->prefix('Rs.')
                            ->placeholder('0.00')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $val = (float) ($state ?? 0);
                                if ($val > 0) {
                                    $monthlyRaw = $val / 12;
                                    $max = ceil($monthlyRaw / 100) * 100;
                                    $min = round($max / 2, 2);
                                    $set('royalty_monthly_max', number_format($max, 2, '.', ''));
                                    $set('royalty_monthly_min', number_format($min, 2, '.', ''));
                                } else {
                                    $set('royalty_monthly_max', '0.00');
                                    $set('royalty_monthly_min', '0.00');
                                }
                            })
                            ->helperText('Total yearly budget allocated for this designation.')
                            ->columnSpan(1),

                        TextInput::make('royalty_monthly_max')
                            ->label('Monthly Max Payout')
                            ->numeric()
                            ->prefix('Rs.')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Budget/12, rounded up to nearest 100.')
                            ->columnSpan(1),

                        TextInput::make('royalty_monthly_min')
                            ->label('Monthly Min Payout')
                            ->numeric()
                            ->prefix('Rs.')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Max payout / 2.')
                            ->columnSpan(1),

                        Toggle::make('is_royalty_eligible')
                            ->label('Include in Royalty Bonus Calculation')
                            ->helperText('When enabled, active employees with this designation will be calculated in monthly royalty bonus reports.')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),

                ActionLogSection::make(),
            ]);
    }
}
