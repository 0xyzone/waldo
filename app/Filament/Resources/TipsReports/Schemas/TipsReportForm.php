<?php

namespace App\Filament\Resources\TipsReports\Schemas;

use App\Models\Employee;
use App\Models\TipsDepartmentMapping;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class TipsReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Setup & Attendance File')
                        ->icon('heroicon-m-document-text')
                        ->description('Report title, cutoff date & HRMS summary')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('title')
                                    ->label('Report Title')
                                    ->placeholder('e.g., September 2026 TIPS')
                                    ->default(now()->format('F Y').' TIPS')
                                    ->required(),

                                Select::make('month')
                                    ->label('Billing Month')
                                    ->options([
                                        'january' => 'January',
                                        'february' => 'February',
                                        'march' => 'March',
                                        'april' => 'April',
                                        'may' => 'May',
                                        'june' => 'June',
                                        'july' => 'July',
                                        'august' => 'August',
                                        'september' => 'September',
                                        'october' => 'October',
                                        'november' => 'November',
                                        'december' => 'December',
                                    ])
                                    ->default(strtolower(now()->format('F')))
                                    ->required(),

                                TextInput::make('year')
                                    ->label('Billing Year')
                                    ->default(now()->format('Y'))
                                    ->numeric()
                                    ->required(),
                            ]),

                            Grid::make(2)->schema([
                                DatePicker::make('cutoff_date')
                                    ->label('Cutoff Date (for Tenure Calculation)')
                                    ->default(now()->toDateString())
                                    ->native(false)
                                    ->required(),

                                FileUpload::make('excel_file_path')
                                    ->label('HRMS Attendance Summary File (.xlsx / .csv)')
                                    ->disk('public')
                                    ->directory('hrms-tips')
                                    ->acceptedFileTypes([
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-excel',
                                        'text/csv',
                                    ])
                                    ->maxSize(15360)
                                    ->required(),
                            ]),
                        ]),

                    Step::make('Collections (Chips & Cash)')
                        ->icon('heroicon-m-banknotes')
                        ->description('Department initial collections')
                        ->schema([
                            Repeater::make('collection_summary')
                                ->label('Department Collections')
                                ->default([
                                    ['department' => 'Cage', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'PIT', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'SLOT', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'F&B', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'Reception / GR', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'Bouncer', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'Housekeeping', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'Security', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                    ['department' => 'Back Office', 'chips_amount' => 0, 'cash_amount' => 0, 'total_amount' => 0],
                                ])
                                ->schema([
                                    TextInput::make('department')
                                        ->label('Department')
                                        ->required()
                                        ->columnSpan(1),

                                    TextInput::make('chips_amount')
                                        ->label('Chips Amount')
                                        ->numeric()
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $chips = (float) ($get('chips_amount') ?? 0);
                                            $cash = (float) ($get('cash_amount') ?? 0);
                                            $set('total_amount', $chips + $cash);
                                        })
                                        ->columnSpan(1),

                                    TextInput::make('cash_amount')
                                        ->label('Cash Amount')
                                        ->numeric()
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $chips = (float) ($get('chips_amount') ?? 0);
                                            $cash = (float) ($get('cash_amount') ?? 0);
                                            $set('total_amount', $chips + $cash);
                                        })
                                        ->columnSpan(1),

                                    TextInput::make('total_amount')
                                        ->label('Total Amount')
                                        ->numeric()
                                        ->readOnly()
                                        ->default(0)
                                        ->columnSpan(1),
                                ])
                                ->columns(4)
                                ->collapsible()
                                ->reorderableWithButtons(),
                        ]),

                    Step::make('Company Error Report')
                        ->icon('heroicon-m-exclamation-triangle')
                        ->description('Penalty percentage deductions per employee')
                        ->schema([
                            Repeater::make('company_errors')
                                ->label('Deduction Adjustments')
                                ->schema([
                                    Select::make('employee_code')
                                        ->label('Employee')
                                        ->options(fn () => Employee::query()
                                            ->whereIn('employee_status', ['Active', 'Resigning This Month'])
                                            ->orderBy('employee_code')
                                            ->get()
                                            ->mapWithKeys(fn ($e) => [
                                                $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->department?->name ?? 'No Dept').')',
                                            ])
                                            ->toArray())
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('percentage_to_deduct')
                                        ->label('Deduct %')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%')
                                        ->default(10)
                                        ->required()
                                        ->columnSpan(1),

                                    TextInput::make('reason')
                                        ->label('Reason / Notes')
                                        ->placeholder('e.g., Short cash, Procedure breach')
                                        ->columnSpan(2),
                                ])
                                ->columns(5)
                                ->collapsible()
                                ->addActionLabel('Add Employee Error Penalty'),
                        ]),

                    Step::make('Left Outs')
                        ->icon('heroicon-m-user-plus')
                        ->description('Manual overrides for previously missed staff')
                        ->schema([
                            Repeater::make('left_outs')
                                ->label('Left Out Staff Overrides')
                                ->schema([
                                    Select::make('employee_id')
                                        ->label('Employee')
                                        ->options(fn () => Employee::query()
                                            ->with(['department', 'designation'])
                                            ->orderBy('employee_code')
                                            ->get()
                                            ->mapWithKeys(fn ($e) => [
                                                $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->designation?->name ?? 'No Designation').')',
                                            ])
                                            ->toArray())
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (! $state) {
                                                return;
                                            }
                                            $emp = Employee::with(['department', 'designation'])->where('employee_code', $state)->first();
                                            if ($emp) {
                                                $set('designation', $emp->designation?->name ?? '');
                                                $base = (float) ($emp->tips_amount ?? 0);
                                                $set('base_tips_amount', $base);

                                                $isHold = strtoupper(trim((string) ($emp->tips_status ?? ''))) === 'HOLD';
                                                if ($isHold) {
                                                    $set('final_distribution_amount', 0);
                                                } else {
                                                    $set('final_distribution_amount', $base > 0 ? (ceil($base / 100) * 100) : 0);
                                                }

                                                $pageName = TipsDepartmentMapping::resolvePageName($emp->department_id, $emp->department?->name);
                                                if ($pageName) {
                                                    $set('department', $pageName);
                                                }
                                            }
                                        })
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('designation')
                                        ->label('Designation')
                                        ->placeholder('Auto-fetched from DB')
                                        ->columnSpan(2),

                                    Select::make('department')
                                        ->label('Department Page')
                                        ->options(function () {
                                            $masterPages = TipsDepartmentMapping::where('is_active', true)
                                                ->orderBy('sort_order')
                                                ->pluck('page_name', 'page_name')
                                                ->toArray();

                                            return array_merge($masterPages, [
                                                'Left Outs' => 'Left Outs',
                                            ]);
                                        })
                                        ->default('Left Outs')
                                        ->required()
                                        ->columnSpan(1),

                                    TextInput::make('base_tips_amount')
                                        ->label('Base Tips')
                                        ->numeric()
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $empCode = $get('employee_id');
                                            $emp = $empCode ? Employee::where('employee_code', $empCode)->first() : null;
                                            $isHold = $emp && strtoupper(trim((string) ($emp->tips_status ?? ''))) === 'HOLD';
                                            if ($isHold) {
                                                $set('final_distribution_amount', 0);

                                                return;
                                            }

                                            $base = (float) ($get('base_tips_amount') ?? 0);
                                            $pct = (float) ($get('tips_percentage') ?? 100);
                                            $raw = ($base * $pct) / 100;
                                            $set('final_distribution_amount', $raw > 0 ? (ceil($raw / 100) * 100) : 0);
                                        })
                                        ->columnSpan(1),

                                    TextInput::make('tips_percentage')
                                        ->label('Tips %')
                                        ->numeric()
                                        ->default(100)
                                        ->suffix('%')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $empCode = $get('employee_id');
                                            $emp = $empCode ? Employee::where('employee_code', $empCode)->first() : null;
                                            $isHold = $emp && strtoupper(trim((string) ($emp->tips_status ?? ''))) === 'HOLD';
                                            if ($isHold) {
                                                $set('final_distribution_amount', 0);

                                                return;
                                            }

                                            $base = (float) ($get('base_tips_amount') ?? 0);
                                            $pct = (float) ($get('tips_percentage') ?? 100);
                                            $raw = ($base * $pct) / 100;
                                            $set('final_distribution_amount', $raw > 0 ? (ceil($raw / 100) * 100) : 0);
                                        })
                                        ->columnSpan(1),

                                    TextInput::make('final_distribution_amount')
                                        ->label('Final Amount')
                                        ->numeric()
                                        ->columnSpan(1),

                                    TextInput::make('notes')
                                        ->label('Notes')
                                        ->columnSpan(2),
                                ])
                                ->columns(10)
                                ->collapsible()
                                ->addActionLabel('Add Left Out Employee'),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
