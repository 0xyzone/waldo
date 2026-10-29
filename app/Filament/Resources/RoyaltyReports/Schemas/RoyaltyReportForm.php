<?php

namespace App\Filament\Resources\RoyaltyReports\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class RoyaltyReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Period & Details')
                    ->description('Specify billing period and cutoff date for employee eligibility evaluation.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('title')
                                ->label('Report Title')
                                ->placeholder('e.g., October 2026 Royalty Bonus')
                                ->default(now()->format('F Y').' Royalty Bonus')
                                ->required()
                                ->columnSpan(1),

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
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                    $month = $state ?: strtolower(now()->format('F'));
                                    $year = $get('year') ?: now()->format('Y');
                                    try {
                                        $cutoff = Carbon::parse("1 {$month} {$year}")->subDay()->toDateString();
                                        $set('cutoff_date', $cutoff);
                                        $set('title', ucfirst($month).' '.$year.' Royalty Bonus');
                                    } catch (\Throwable) {
                                    }
                                })
                                ->required(),

                            TextInput::make('year')
                                ->label('Billing Year')
                                ->default(now()->format('Y'))
                                ->numeric()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                    $month = $get('month') ?: strtolower(now()->format('F'));
                                    $year = $state ?: now()->format('Y');
                                    if ($month && $year) {
                                        try {
                                            $cutoff = Carbon::parse("1 {$month} {$year}")->subDay()->toDateString();
                                            $set('cutoff_date', $cutoff);
                                            $set('title', ucfirst($month).' '.$year.' Royalty Bonus');
                                        } catch (\Throwable) {
                                        }
                                    }
                                })
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('cutoff_date')
                                ->label('Cutoff Date')
                                ->default(function () {
                                    try {
                                        return Carbon::parse('first day of this month')->subDay()->toDateString();
                                    } catch (\Throwable) {
                                        return now()->toDateString();
                                    }
                                })
                                ->required()
                                ->helperText('Employee tenure and status cutoff date for this report.'),

                            Textarea::make('notes')
                                ->label('Notes / Remarks')
                                ->placeholder('Optional comments or directives for this payout cycle...')
                                ->rows(2),
                        ]),
                    ]),

                Section::make('Report Data Source Files (Excel / CSV)')
                    ->description('Upload attendance statistics and performance appraisal deduction files.')
                    ->icon(Heroicon::OutlinedDocumentArrowUp)
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('attendance_file_path')
                                ->label('Attendance Report (Excel / CSV)')
                                ->disk('public')
                                ->directory('royalty/attendance')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                    'text/csv',
                                    'text/plain',
                                ])
                                ->helperText('Upload standard HRMS attendance report (identical format to Tips generation).')
                                ->maxSize(20480),

                            FileUpload::make('performance_file_path')
                                ->label('Performance Deduction Report (Excel / CSV)')
                                ->disk('public')
                                ->directory('royalty/performance')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                    'text/csv',
                                    'text/plain',
                                ])
                                ->helperText(new HtmlString('Upload Excel with 3 columns: <strong>Employee Code</strong>, <strong>Percentage</strong>, and <strong>Remarks</strong> (deductions only apply to employees listed in this file). <a href="/royalty/samples/performance-deduction" class="text-primary-600 dark:text-primary-400 font-bold underline" target="_blank">📥 Download Sample Excel Template</a>'))
                                ->maxSize(20480),
                        ]),
                    ]),
            ]);
    }
}
