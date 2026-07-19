<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Helpers\NepaliDate\NepaliDate;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->skippable()
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
                                            ->label('First Name'),
                                        TextInput::make('middle_name')
                                            ->label('Middle Name'),
                                        TextInput::make('last_name')
                                            ->label('Last Name'),
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
                                            ->native(false),
                                    ]),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        DatePicker::make('dob_ad')
                                            ->label('Date of Birth (AD)')
                                            ->live()
                                            ->native(false)
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (empty($state)) {
                                                    $set('dob_bs', null);

                                                    return;
                                                }
                                                try {
                                                    $date = Carbon::parse($state);
                                                    $converter = new NepaliDate;
                                                    $converted = $converter->convertAdToBs($date->year, $date->month, $date->day);
                                                    if (!empty($converted)) {
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
                                            ->email(),
                                        TextInput::make('contact_number')
                                            ->label('Contact Number'),
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
                                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),
                                        Select::make('employee_status')
                                            ->label('Employee Status')
                                            ->options([
                                                'Active' => 'Active',
                                                'Inactive' => 'Inactive',
                                                'Resigned' => 'Resigned',
                                                'Resigning this month' => 'Resigning this month',
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
                                                modifyQueryUsing: fn($query) => $query->where('name', 'not like', '%20%')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(fn(callable $set) => $set('designation_id', null)),
                                        Select::make('designation_id')
                                            ->relationship(
                                                name: 'designation',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn($query, callable $get) => $query
                                                    ->when($get('department_id'), fn($q, $deptId) => $q->where('department_id', $deptId))
                                            )
                                            ->searchable()
                                            ->preload(),
                                    ]),
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        DatePicker::make('join_date_formatted')
                                            ->label('Join Date')
                                            ->native(false)
                                            ->format('d F, Y')
                                            ->placeholder('e.g. 01 January, 2024')
                                            ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d F, Y') : null)
                                            ->dehydrateStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d F, Y') : null),
                                    ]),
                                Section::make('HRMS Credentials')
                                    ->visibleOn('view')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                TextEntry::make('hrms_username')
                                                    ->label('HRMS username')
                                                    ->default(function ($record) {
                                                        return strtolower($record->employee_code);
                                                    }),
                                                TextEntry::make('email')
                                                    ->label('HRMS Email')
                                                    ->default(function ($record) {
                                                        return strtolower($record->email);
                                                    }),
                                                TextEntry::make('hrms_password')
                                                    ->label('HRMS Password')
                                                    ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                                                    ->dehydrated(fn($state) => filled($state)),
                                            ])
                                    ])
                            ]),
                        Step::make('🪪 ID & Payroll')
                            ->description('Citizenship, payroll & tips settings')
                            ->schema([
                                Section::make('Legal Identification')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                TextInput::make('citizenship_number')
                                                    ->label('Citizenship Number'),
                                                TextInput::make('citizenship_issue_date')
                                                    ->label('Citizenship Issue Date'),
                                                TextInput::make('citizenship_issue_place')
                                                    ->label('Citizenship Issue Place'),
                                            ]),
                                        TextInput::make('ssid')
                                            ->label('SSID')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Tips & Points Settings')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                TextInput::make('tips_amount')
                                                    ->label('Tips Amount')
                                                    ->numeric()
                                                    ->prefix('₹'),
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
                                                    ->default(true),
                                                Toggle::make('tips_fixed')
                                                    ->label('Tips Fixed')
                                                    ->default(true),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
