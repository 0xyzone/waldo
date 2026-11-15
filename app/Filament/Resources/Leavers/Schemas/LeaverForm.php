<?php

namespace App\Filament\Resources\Leavers\Schemas;

use App\Filament\Components\ActionLogSection;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LeaverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leaving Details')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->relationship(
                                        name: 'employee',
                                        titleAttribute: 'name',
                                        // modifyQueryUsing: fn ($query) => $query->where('employee_status', 'active'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper($record->employee_code).' | '.$record->name)
                                    ->searchable(['name', 'employee_code'])
                                    ->preload()
                                    ->live()
                                    ->required(),
                                DatePicker::make('leaving_date')
                                    ->label('Leaving Date')
                                    ->native(false)
                                    ->required(),
                            ]),
                        Textarea::make('remarks')
                            ->label('Remarks / Reason for Leaving')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Payroll & Financial Holds')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Toggle::make('hold_salary')
                                    ->label('Hold Salary')
                                    ->required(),
                                Toggle::make('hold_tips')
                                    ->label('Hold Tips')
                                    ->required(),
                                Toggle::make('publish_cl')
                                    ->label('Publish CL Balance')
                                    ->required()
                                    ->default(true),
                            ]),
                    ]),
                ActionLogSection::make(),
                Section::make('Selected Employee Info')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => filled($get('employee_id')))
                    ->schema([
                        Placeholder::make('employee_info')
                            ->hiddenLabel()
                            ->content(function (Get $get): HtmlString {
                                $employee = Employee::with(['department', 'designation'])
                                    ->where('employee_code', $get('employee_id'))
                                    ->first();

                                if (! $employee) {
                                    return new HtmlString('<span class="text-sm text-gray-500">Employee details not found.</span>');
                                }

                                $department = e($employee->department?->name ?? '—');
                                $designation = e($employee->designation?->name ?? '—');
                                $joinDate = $employee->join_date ? e($employee->join_date->format('d M, Y')) : '—';
                                $status = e(ucfirst((string) ($employee->employee_status ?? '—')));

                                return new HtmlString(
                                    '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">'
                                    ."<div><span class=\"block text-xs text-gray-500 dark:text-gray-400\">Department</span><span class=\"font-medium\">{$department}</span></div>"
                                    ."<div><span class=\"block text-xs text-gray-500 dark:text-gray-400\">Designation</span><span class=\"font-medium\">{$designation}</span></div>"
                                    ."<div><span class=\"block text-xs text-gray-500 dark:text-gray-400\">Join Date</span><span class=\"font-medium\">{$joinDate}</span></div>"
                                    ."<div><span class=\"block text-xs text-gray-500 dark:text-gray-400\">Status</span><span class=\"font-medium\">{$status}</span></div>"
                                    .'</div>'
                                );
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
