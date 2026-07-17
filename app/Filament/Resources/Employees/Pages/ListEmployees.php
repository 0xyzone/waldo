<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Helpers\NepaliDate\NepaliDate;
use App\Models\Employee;
use App\Services\EmployeeSyncService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Employees'),
            'incomplete' => Tab::make('Incomplete')
                ->modifyQueryUsing(fn (Builder $query) => $query->isIncomplete())
                ->badge(Employee::query()->isIncomplete()->count()),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Active'))
                ->badge(Employee::where('employee_status', 'Active')->count()),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Inactive'))
                ->badge(Employee::where('employee_status', 'Inactive')->count()),
            'resigning_this_month' => Tab::make('Resigning This Month')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Resigning this month'))
                ->badge(Employee::where('employee_status', 'Resigning this month')->count()),
            'resigned' => Tab::make('Resigned')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Resigned'))
                ->badge(Employee::where('employee_status', 'Resigned')->count()),
            'terminated' => Tab::make('Terminated')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Terminated'))
                ->badge(Employee::where('employee_status', 'Terminated')->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Employees')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Sync Employees from Google Sheet')
                ->modalDescription('Are you sure you want to pull data from the Google Sheet? This will update existing employees and create new ones.')
                ->action(function (EmployeeSyncService $syncService): void {
                    try {
                        $count = $syncService->sync();

                        Notification::make()
                            ->title('Sync Completed')
                            ->body("Successfully synced {$count} employees from Google Sheet!")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make()
                ->steps([
                    Step::make('👤 Who Are They?')
                        ->description('Basic personal information')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->columnSpanFull()
                                ->placeholder('Enter the employee\'s full name'),

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
                                        ->placeholder('name@example.com'),
                                    TextInput::make('contact_number')
                                        ->label('Contact Number')
                                        ->placeholder('+977 98XXXXXXXX'),
                                ]),
                        ]),

                    Step::make('💼 Work Details')
                        ->description('Employment & role information')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Grid::make(['default' => 1, 'sm' => 2])
                                ->schema([
                                    TextInput::make('employee_code')
                                        ->label('Employee Code')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                        ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                                        ->placeholder('e.g. CWD001'),
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
                                        ->format('d F, Y')
                                        ->placeholder('e.g. 01 January, 2024')
                                        ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d F, Y') : null)
                                        ->dehydrateStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d F, Y') : null),
                                ]),
                        ]),

                    Step::make('🪪 ID & Payroll')
                        ->description('Citizenship, payroll & tips settings')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Section::make('Legal Identification')
                                ->icon('heroicon-m-identification')
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
                                ->icon('heroicon-m-currency-rupee')
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
                ->skippableSteps()
                ->modalWidth('7xl')
                ->successNotificationTitle('🎉 Employee added successfully!'),
            Action::make('Visit Sheet')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url('https://docs.google.com/spreadsheets/d/1i8M_P8KejphnCEFpiUWIhaTvkDZtGqt1_AuWt-vNJGE/edit?gid=0#gid=0', true)
                ->openUrlInNewTab(),
        ];
    }
}
