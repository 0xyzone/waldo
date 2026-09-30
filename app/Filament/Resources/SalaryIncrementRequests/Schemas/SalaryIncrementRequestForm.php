<?php

namespace App\Filament\Resources\SalaryIncrementRequests\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                Section::make('Salary Increment Request Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->columnSpanFull()->schema([
                            TextInput::make('request_number')
                                ->label('Ref #')
                                ->placeholder('Auto-generated (SIR-YYYY-####)')
                                ->disabled()
                                ->dehydrated(false),
                            DatePicker::make('date_requested')
                                ->label('Date')
                                ->default(now()->toDateString())
                                ->required()
                                ->native(false),
                            DatePicker::make('date_applicable')
                                ->label('Effective Date')
                                ->required()
                                ->native(false),
                        ]),
                        Grid::make(3)->schema([
                            Select::make('employee_id')
                                ->label('Employee Code')
                                ->placeholder('Select Employee')
                                ->options(function () {
                                    return Employee::query()
                                        ->where('employee_status', 'Active')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(function (Employee $e) {
                                            $dept = $e->department?->name ? " ({$e->department->name})" : '';

                                            return [$e->employee_code => "{$e->employee_code} — {$e->name}{$dept}"];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    if (! $state) {
                                        $set('employee_name_display', null);
                                        $set('join_date_display', null);
                                        $set('department_id', null);
                                        $set('current_designation_id', null);

                                        return;
                                    }

                                    $employee = Employee::with(['department', 'designation'])->find($state);
                                    if ($employee) {
                                        $set('employee_name_display', $employee->name);
                                        $set('join_date_display', $employee->join_date_formatted ?? '—');
                                        $set('department_id', $employee->department_id);
                                        $set('current_designation_id', $employee->designation_id);
                                    }
                                }),
                            TextInput::make('employee_name_display')
                                ->label('Employee Name')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (Set $set, Get $get) {
                                    $empId = $get('employee_id');
                                    if ($empId) {
                                        $emp = Employee::find($empId);
                                        $set('employee_name_display', $emp?->name);
                                    }
                                }),
                            TextInput::make('join_date_display')
                                ->label('Date of Joining')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (Set $set, Get $get) {
                                    $empId = $get('employee_id');
                                    if ($empId) {
                                        $emp = Employee::find($empId);
                                        $set('join_date_display', $emp?->join_date_formatted ?? '—');
                                    }
                                }),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('current_salary')
                                ->label('Current Salary (NPR)')
                                ->numeric()
                                ->prefix('NPR')
                                ->required()
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
                                ->required()
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
                                ->disabled()
                                ->dehydrated()
                                ->helperText(fn (Get $get) => $get('increment_percentage') ? "({$get('increment_percentage')}%)" : null),
                        ]),
                    ]),
            ]);
    }
}
