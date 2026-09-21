<?php

namespace App\Filament\Resources\NametagFines\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NametagFineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Selected Employee Live Preview Card
                Section::make('Selected Employee Profile')
                    ->description('Employee snapshot retrieved from database.')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('info_code')
                                    ->label('Employee Code')
                                    ->badge()
                                    ->color('primary')
                                    ->fontFamily('mono')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->employee_code),

                                TextEntry::make('info_name')
                                    ->label('Full Name')
                                    ->weight('bold')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->name),

                                TextEntry::make('info_department')
                                    ->label('Department')
                                    ->badge()
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->department?->name),

                                TextEntry::make('info_designation')
                                    ->label('Designation')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->designation?->name),
                            ]),
                    ])
                    ->visible(fn (Get $get) => filled($get('employee_id'))),

                // Fine Record Details Section
                Section::make('Fine Assessment Details')
                    ->description('Specify the employee, reason for re-issue, fine amount, and target pay cycle month.')
                    ->icon('heroicon-o-banknotes')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->relationship('employee', 'name')
                                    ->getOptionLabelFromRecordUsing(function (Employee $record): string {
                                        $dept = $record->department?->name ? " [{$record->department->name}]" : '';

                                        return strtoupper($record->employee_code).' | '.$record->name.$dept;
                                    })
                                    ->searchable(['name', 'employee_code'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->autofocus(),

                                Select::make('reason')
                                    ->label('Fine Reason')
                                    ->options([
                                        'Lost' => 'Lost NameTag',
                                        'Damaged' => 'Damaged NameTag',
                                        'Other' => 'Other',
                                    ])
                                    ->default('Lost')
                                    ->required()
                                    ->native(false),
                            ]),

                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Select::make('for_month')
                                    ->label('For Month')
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
                                    ->required()
                                    ->native(false),

                                Select::make('for_year')
                                    ->label('For Year')
                                    ->options(array_combine(range(now()->year + 1, 2024), range(now()->year + 1, 2024)))
                                    ->default(now()->year)
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('remarks')
                            ->label('Remarks / Circumstances')
                            ->placeholder('Provide additional context on how or when the name tag was lost/damaged...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
