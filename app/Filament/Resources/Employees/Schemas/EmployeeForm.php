<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Filament\Components\ActionLogSection;
use App\Helpers\NepaliDate\NepaliDate;
use App\Models\Designation;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Js;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->skippable()
                    ->nextAction(fn (Action $action) => $action->tooltip('Next section (Ctrl/Cmd + →)'))
                    ->previousAction(fn (Action $action) => $action->tooltip('Previous section (Ctrl/Cmd + ←)'))
                    ->steps([
                        Step::make('👤 Who Are They?')
                            ->description('Basic personal information')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->columnSpanFull(),
                                Grid::make(['default' => 1, 'sm' => 3])
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label('First Name')
                                            ->copyable(),
                                        TextInput::make('middle_name')
                                            ->label('Middle Name')
                                            ->copyable(),
                                        TextInput::make('last_name')
                                            ->label('Last Name')
                                            ->copyable(),
                                    ])
                                    ->disabled(),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        Select::make('gender')
                                            ->options([
                                                'Male' => 'Male',
                                                'Female' => 'Female',
                                                'Other' => 'Other',
                                            ])
                                            ->searchable()
                                            ->preload()
                                            ->native(false),
                                        Select::make('marital_status')
                                            ->label('Marital Status')
                                            ->options([
                                                'Married' => 'Married',
                                                'Single' => 'Single',
                                                'Others' => 'Others',
                                            ])
                                            ->searchable()
                                            ->preload()
                                            ->default('Single')
                                            ->native(false),
                                    ]),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        DatePicker::make('dob_ad')
                                            ->label('Date of Birth (AD)')
                                            ->live()
                                            ->native(false)
                                            ->hint(function ($state) {
                                                if (! empty($state)) {
                                                    try {
                                                        $age = Carbon::parse($state)->age;

                                                        return $age.' years old';
                                                    } catch (\Exception $e) {
                                                        // ignore
                                                    }
                                                }

                                                return null;
                                            })
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (empty($state)) {
                                                    $set('dob_bs', null);

                                                    return;
                                                }
                                                try {
                                                    $date = Carbon::parse($state);
                                                    $converter = new NepaliDate;
                                                    $converted = $converter->convertAdToBs($date->year, $date->month, $date->day);
                                                    if (! empty($converted)) {
                                                        $set('dob_bs', sprintf('%04d.%02d.%02d', $converted['year'], $converted['month'], $converted['day']));
                                                    }
                                                } catch (\Exception $e) {
                                                    // ignore
                                                }
                                            }),
                                        TextInput::make('dob_bs')
                                            ->label('Date of Birth (BS)')
                                            ->placeholder('YYYY.MM.DD')
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        TextInput::make('email')
                                            ->email()
                                            ->copyable(),
                                        TextInput::make('contact_number')
                                            ->label('Contact Number')
                                            ->copyable(),
                                    ]),
                            ]),
                        Step::make('💼 Work Details')
                            ->description('Employment & role information')
                            ->schema([
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        TextInput::make('employee_code')
                                            ->label('Employee Code')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                            ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                                            ->suffixAction(
                                                Action::make('copyCodeNumber')
                                                    ->icon('heroicon-m-clipboard-document-list')
                                                    ->color('gray')
                                                    ->tooltip('Copy employee code number')
                                                    ->alpineClickHandler(function (mixed $state): string {
                                                        $digits = preg_replace('/[^0-9]/', '', (string) $state);
                                                        $jsDigits = Js::from($digits);

                                                        return <<<JS
                                                            const currentVal = (typeof state !== 'undefined' && state) ? state : {$jsDigits};
                                                            const digits = String(currentVal).replace(/[^0-9]/g, '');
                                                            window.navigator.clipboard.writeText(digits);
                                                            \$tooltip('Copied ' + digits, {
                                                                theme: \$store.theme,
                                                                timeout: 2000,
                                                            });
                                                            JS;
                                                    })
                                            ),
                                        Select::make('employee_status')
                                            ->label('Employee Status')
                                            ->options([
                                                'Active' => 'Active',
                                                'Inactive' => 'Inactive',
                                                'Suspended' => 'Suspended',
                                                'Resigned' => 'Resigned',
                                                'Resigning This Month' => 'Resigning This Month',
                                                'Terminated' => 'Terminated',
                                            ])
                                            ->native(false)
                                            ->default('Active'),
                                    ]),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        Select::make('department_id')
                                            ->relationship(
                                                name: 'department',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->where('name', 'not like', '%20%')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (callable $set) {
                                                $set('designation_id', null);
                                                $set('tips_amount', null);
                                            }),
                                        Select::make('designation_id')
                                            ->relationship(
                                                name: 'designation',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query, callable $get) => $query
                                                    ->where('is_active', true)
                                                    ->orderBy('rank')
                                                    ->when($get('department_id'), fn ($q, $deptId) => $q->where('department_id', $deptId))
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $defaultTips = 0.0;
                                                if ($state) {
                                                    $designation = Designation::find($state);
                                                    $defaultTips = $designation?->default_tips_amount !== null ? (float) $designation->default_tips_amount : 0.0;
                                                }
                                                $adj = (float) ($get('tips_adj') ?? 0);
                                                $total = $defaultTips + $adj;
                                                $set('tips_amount', $state ? $total : ($adj > 0 ? $adj : null));
                                            }),
                                    ]),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        DatePicker::make('join_date_formatted')
                                            ->label('Join Date')
                                            ->native(false)
                                            ->displayFormat('d F, Y')
                                            ->format('d F, Y')
                                            ->placeholder('e.g. 01 January, 2024')
                                            ->formatStateUsing(function ($state) {
                                                if (empty($state)) {
                                                    return null;
                                                }
                                                try {
                                                    return Carbon::parse(str_replace(',', '', $state))->format('d F, Y');
                                                } catch (\Exception) {
                                                    return $state;
                                                }
                                            })
                                            ->dehydrateStateUsing(function ($state) {
                                                if (empty($state)) {
                                                    return null;
                                                }
                                                try {
                                                    return Carbon::parse(str_replace(',', '', $state))->format('d F, Y');
                                                } catch (\Exception) {
                                                    return $state;
                                                }
                                            })
                                            ->suffixAction(
                                                Action::make('copyDateOfJoining')
                                                    ->icon('heroicon-m-clipboard-document-list')
                                                    ->color('gray')
                                                    ->tooltip('Copy date of joining')
                                                    ->alpineClickHandler(function (mixed $state): string {
                                                        $date = strtotime($state);
                                                        $convertedDate = date('Y.m.d', $date);
                                                        $jsDate = Js::from($convertedDate);

                                                        return <<<JS
                                                            const currentVal = (typeof state !== 'undefined' && state) ? state : {$jsDate};
                                                            window.navigator.clipboard.writeText(currentVal);
                                                            \$tooltip('Copied ' + currentVal, {
                                                                theme: \$store.theme,
                                                                timeout: 2000,
                                                            });
                                                            JS;
                                                    })
                                            ),
                                        Select::make('shift')
                                            ->label('Shift')
                                            ->options([
                                                'Morning' => 'Morning',
                                                'Evening' => 'Evening',
                                                'Night' => 'Night',
                                            ])
                                            ->native(false)
                                            ->default('Morning'),
                                    ]),
                                Section::make('HRMS Credentials')
                                ->description('Click on values to copy')
                                    ->visibleOn('view')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                TextEntry::make('hrms_username')
                                                    ->label('HRMS username')
                                                    ->default(function ($record) {
                                                        return strtolower($record->employee_code);
                                                    })
                                                    ->copyable(),
                                                TextEntry::make('email')
                                                    ->label('HRMS Email')
                                                    ->default(function ($record) {
                                                        return strtolower($record->email);
                                                    })
                                                    ->copyable(),
                                                TextEntry::make('hrms_password')
                                                    ->label('HRMS Password')
                                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                                                    ->dehydrated(fn ($state) => filled($state))
                                                    ->copyable(),
                                            ]),
                                    ]),
                            ]),
                        Step::make('🪪 ID & Payroll')
                            ->description('Citizenship, payroll & tips settings')
                            ->schema([
                                Section::make('Legal Identification')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                TextInput::make('citizenship_number')
                                                    ->label('Citizenship Number')
                                                    ->autocomplete(false),
                                                TextInput::make('citizenship_issue_date')
                                                    ->label('Citizenship Issue Date')
                                                    ->autocomplete(false),
                                                TextInput::make('citizenship_issue_place')
                                                    ->label('Citizenship Issue Place')
                                                    ->autocomplete(false),
                                            ]),
                                        TextInput::make('ssid')
                                            ->label('SSID')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Tips & Points Settings')
                                    ->schema([
                                        Grid::make(['default' => 2, 'sm' => 4])
                                            ->schema([
                                                TextInput::make('tips_amount')
                                                    ->label('Tips Amount')
                                                    ->numeric()
                                                    ->prefix('₹')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->helperText('Expected tips (Desig. Default + Adj.). Google Sheet calculates & syncs final value.')
                                                    ->formatStateUsing(function ($state, callable $get) {
                                                        if ($state !== null && $state !== '') {
                                                            return $state;
                                                        }
                                                        $desigId = $get('designation_id');
                                                        if ($desigId) {
                                                            $desig = Designation::find($desigId);
                                                            if ($desig && $desig->default_tips_amount !== null) {
                                                                $adj = (float) ($get('tips_adj') ?? 0);

                                                                return (float) $desig->default_tips_amount + $adj;
                                                            }
                                                        }

                                                        return $state;
                                                    }),
                                                TextInput::make('tips_adj')
                                                    ->label('Tips Adj.')
                                                    ->numeric()
                                                    ->prefix('₹')
                                                    ->placeholder('0.00')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $desigId = $get('designation_id');
                                                        $defaultTips = 0.0;
                                                        if ($desigId) {
                                                            $designation = Designation::find($desigId);
                                                            $defaultTips = $designation?->default_tips_amount !== null ? (float) $designation->default_tips_amount : 0.0;
                                                        }
                                                        $adj = (float) ($state ?? 0);
                                                        $total = $defaultTips + $adj;
                                                        $set('tips_amount', $desigId ? $total : ($adj > 0 ? $adj : null));
                                                    }),
                                                Select::make('tips_status')
                                                    ->label('Tips Status')
                                                    ->options([
                                                        'Release' => 'Release',
                                                        'Hold' => 'Hold',
                                                    ])
                                                    ->default('Release')
                                                    ->native(false),
                                                TextInput::make('point_value')
                                                    ->label('Point Value')
                                                    ->numeric()
                                                    ->default(1),
                                            ]),
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                Toggle::make('tips_blank')
                                                    ->label('Tips Blank'),
                                                Toggle::make('publish_tips')
                                                    ->label('Publish Tips')
                                                    ->default(false),
                                                Toggle::make('tips_fixed')
                                                    ->label('Tips Fixed')
                                                    ->default(true),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
                ActionLogSection::make(),
            ]);
    }
}
