<?php

namespace App\Filament\Resources\EmployeePromotions\Schemas;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeePromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Hidden snapshot fields — captured when employee is selected
                Hidden::make('from_department_id'),
                Hidden::make('from_designation_id'),

                Section::make('Employee')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(fn () => Employee::with('department', 'designation')
                                ->get()
                                ->mapWithKeys(fn (Employee $e) => [
                                    $e->employee_code => strtoupper($e->employee_code).' | '.$e->name
                                        .(($e->designation?->name || $e->department?->name)
                                            ? ' ('.implode(', ', array_filter([$e->designation?->name, $e->department?->name])).')'
                                            : ''),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    // Clear all dependent fields when employee is deselected
                                    $set('from_department_id', null);
                                    $set('from_designation_id', null);
                                    $set('to_department_id', null);
                                    $set('to_designation_id', null);

                                    return;
                                }

                                $employee = Employee::where('employee_code', $state)->first();

                                if ($employee) {
                                    // Snapshot current state for "from" fields
                                    $set('from_department_id', $employee->department_id);
                                    $set('from_designation_id', $employee->designation_id);

                                    // Pre-populate "to" fields with employee's current values
                                    $set('to_department_id', $employee->department_id);
                                    $set('to_designation_id', $employee->designation_id);
                                }
                            }),
                    ]),

                Section::make('Promotion Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])
                            ->schema([
                                DatePicker::make('promotion_date')
                                    ->label('Promotion Date')
                                    ->native(false)
                                    ->required()
                                    ->disabled(fn (callable $get) => ! $get('employee_id')),
                                Select::make('to_department_id')
                                    ->label('New Department')
                                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabled(fn (callable $get) => ! $get('employee_id'))
                                    ->afterStateUpdated(fn (callable $set) => $set('to_designation_id', null)),
                                Select::make('to_designation_id')
                                    ->label('New Designation')
                                    ->options(function (callable $get) {
                                        $deptId = $get('to_department_id');
                                        if (! $deptId) {
                                            return Designation::pluck('name', 'id')->toArray();
                                        }

                                        return Designation::where('department_id', $deptId)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (callable $get) => ! $get('employee_id')),
                            ]),
                        Textarea::make('remarks')
                            ->label('Remarks / Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn (callable $get) => ! $get('employee_id')),
                    ]),
            ]);
    }
}
