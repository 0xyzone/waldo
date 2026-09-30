<?php

namespace App\Filament\Resources\SalaryIncrementRequests\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SalaryIncrementRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personnel & Recommendation Information')
                    ->description('Select the Head of Department and the employee under them.')
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('date_requested')
                                ->label('Date Requested')
                                ->default(now()->toDateString())
                                ->required()
                                ->native(false),

                            DatePicker::make('date_applicable')
                                ->label('Date Applicable / Effective')
                                ->helperText('Date the salary increment will take effect')
                                ->native(false),

                            DatePicker::make('date_approved')
                                ->label('Date Approved')
                                ->helperText('Official management approval date')
                                ->native(false),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('hod_id')
                                ->label('Recommending HOD (Head of Department)')
                                ->placeholder('Select HOD')
                                ->options(function () {
                                    return Employee::with('department')
                                        ->where('employee_status', 'Active')
                                        ->orderByDesc('is_manager')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(function (Employee $e) {
                                            $tag = $e->is_manager ? '★ [HOD] ' : '';
                                            $dept = $e->department?->name ? ' ('.$e->department->name.')' : '';

                                            return [$e->employee_code => "{$tag}{$e->employee_code} — {$e->name}{$dept}"];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    if ($state) {
                                        $hod = Employee::with('department')->find($state);
                                        if ($hod && $hod->department_id) {
                                            $set('department_id', $hod->department_id);
                                        }
                                    }
                                }),

                            Select::make('employee_id')
                                ->label('Employee (Under HOD)')
                                ->placeholder('Select Employee')
                                ->options(function (Get $get) {
                                    $query = Employee::with(['department', 'designation'])
                                        ->where('employee_status', 'Active');

                                    $hodId = $get('hod_id');
                                    if ($hodId) {
                                        $hod = Employee::find($hodId);
                                        if ($hod && $hod->department_id) {
                                            $query->orderByRaw("CASE WHEN department_id = {$hod->department_id} THEN 0 ELSE 1 END");
                                        }
                                    }

                                    return $query->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(function (Employee $e) {
                                            $dept = $e->department?->name ?? 'No Dept';
                                            $desig = $e->designation?->name ? " • {$e->designation->name}" : '';

                                            return [$e->employee_code => "{$e->employee_code} — {$e->name} ({$dept}{$desig})"];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                    if (! $state) {
                                        return;
                                    }

                                    $employee = Employee::with(['department', 'designation'])->find($state);
                                    if ($employee) {
                                        $set('department_id', $employee->department_id);
                                        $set('current_designation_id', $employee->designation_id);

                                        if (! $get('hod_id') && $employee->department) {
                                            $manager = $employee->department->getEffectiveManager();
                                            if ($manager) {
                                                $set('hod_id', $manager->employee_code);
                                            }
                                        }
                                    }
                                }),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('department_id')
                                ->label('Department')
                                ->relationship('department', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('current_designation_id')
                                ->label('Current Designation')
                                ->relationship('currentDesignation', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                    ]),

                Section::make('Salary & Increment Details')
                    ->description('Record current compensation, proposed new salary, and increment metrics.')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('current_salary')
                                ->label('Current Salary (NPR)')
                                ->numeric()
                                ->prefix('NPR')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $current = (float) $state;
                                    $proposed = (float) $get('proposed_salary');
                                    if ($current > 0 && $proposed > 0) {
                                        $diff = $proposed - $current;
                                        $set('increment_amount', number_format($diff, 2, '.', ''));
                                        $set('increment_percentage', round(($diff / $current) * 100, 2));
                                    }
                                }),

                            TextInput::make('proposed_salary')
                                ->label('Proposed Salary (NPR)')
                                ->numeric()
                                ->prefix('NPR')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $current = (float) $get('current_salary');
                                    $proposed = (float) $state;
                                    if ($current > 0 && $proposed > 0) {
                                        $diff = $proposed - $current;
                                        $set('increment_amount', number_format($diff, 2, '.', ''));
                                        $set('increment_percentage', round(($diff / $current) * 100, 2));
                                    }
                                }),

                            TextInput::make('increment_amount')
                                ->label('Increment Amount (NPR)')
                                ->numeric()
                                ->prefix('NPR')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $current = (float) $get('current_salary');
                                    $incr = (float) $state;
                                    if ($current > 0 && $incr > 0) {
                                        $set('proposed_salary', number_format($current + $incr, 2, '.', ''));
                                        $set('increment_percentage', round(($incr / $current) * 100, 2));
                                    }
                                }),

                            TextInput::make('increment_percentage')
                                ->label('Increment (%)')
                                ->numeric()
                                ->suffix('%'),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('reason')
                                ->label('Increment Reason')
                                ->options([
                                    'Annual Performance Appraisal' => 'Annual Performance Appraisal',
                                    'Exceptional Merit / High Performance' => 'Exceptional Merit / High Performance',
                                    'Promotion / Role Expansion' => 'Promotion / Role Expansion',
                                    'Market Rate Adjustment' => 'Market Rate Adjustment',
                                    'Probation Completion' => 'Probation Completion',
                                    'Retention / Counter-Offer' => 'Retention / Counter-Offer',
                                    'Other' => 'Other',
                                ])
                                ->default('Annual Performance Appraisal')
                                ->required(),

                            Select::make('proposed_designation_id')
                                ->label('Proposed Designation (Optional)')
                                ->relationship('proposedDesignation', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('If promotion accompanies the increment'),

                            Select::make('status')
                                ->label('Request Status')
                                ->options([
                                    'pending' => 'Pending Review',
                                    'hr_acknowledged' => 'HR Acknowledged',
                                    'finance_acknowledged' => 'Finance Acknowledged',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->default('pending')
                                ->required(),
                        ]),

                        Textarea::make('notes')
                            ->label('HOD Justification & Recommendation Notes')
                            ->placeholder('Provide background, performance metrics, achievements, or justification for this increment request...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Verifications & Acknowledgements')
                    ->description('HR and Finance department reviews.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('hr_acknowledged')
                                ->label('HR Acknowledged')
                                ->inline(false),

                            Toggle::make('finance_acknowledged')
                                ->label('Finance Acknowledged')
                                ->inline(false),

                            Textarea::make('hr_notes')
                                ->label('HR Review Remarks')
                                ->placeholder('Optional HR remarks...')
                                ->rows(2),

                            Textarea::make('finance_notes')
                                ->label('Finance Review Remarks')
                                ->placeholder('Optional Finance remarks...')
                                ->rows(2),
                        ]),
                    ]),
            ]);
    }
}
