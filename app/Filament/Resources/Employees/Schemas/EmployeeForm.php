<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Helpers\NepaliDate\NepaliDate;
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
                                            ->afterStateUpdated(fn (callable $set) => $set('designation_id', null)),
                                        Select::make('designation_id')
                                            ->relationship(
                                                name: 'designation',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query, callable $get) => $query
                                                    ->when($get('department_id'), fn ($q, $deptId) => $q->where('department_id', $deptId))
                                            )
                                            ->searchable()
                                            ->preload(),
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
                                            }),
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
                                                    ->default(false),
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
