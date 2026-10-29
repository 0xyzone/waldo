<?php

namespace App\Filament\Resources\RoyaltyExemptEmployees\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoyaltyExemptEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exempt Employee Details')
                    ->description('Select employee to exempt from royalty bonus performance and attendance deductions.')
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('employee_code')
                                ->label('Employee')
                                ->options(function () {
                                    return Employee::query()
                                        ->with(['department', 'designation'])
                                        ->whereIn('employee_status', ['Active', 'Resigning This Month', 'Suspended'])
                                        ->orderBy('employee_code')
                                        ->get()
                                        ->mapWithKeys(fn (Employee $e) => [
                                            $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->designation?->name ?? 'No Designation').') ['.($e->department?->name ?? 'No Dept').']',
                                        ])
                                        ->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->helperText('Only active, resigning this month, or suspended employees are eligible.'),

                            Textarea::make('reason')
                                ->label('Exemption Reason')
                                ->placeholder('e.g., Executive agreement, Fixed bonus contract, Approved exemption by Management')
                                ->rows(3)
                                ->maxLength(500),

                            Toggle::make('is_active')
                                ->label('Active Exemption')
                                ->default(true)
                                ->helperText('When enabled, this employee will always receive the maximum designation payout on royalty reports.'),
                        ]),
                    ]),
            ]);
    }
}
