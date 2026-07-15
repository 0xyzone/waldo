<?php

namespace App\Filament\Resources\Adjustments\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Employee Information')
                    ->description('Additional details retrieved from the selected employee profile.')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('employee_code')
                                    ->label('Employee Code')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->employee_code),

                                TextEntry::make('employee_name')
                                    ->label('Full Name')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->name),

                                TextEntry::make('employee_department')
                                    ->label('Department')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->department?->name),

                                TextEntry::make('employee_designation')
                                    ->label('Designation')
                                    ->placeholder('—')
                                    ->default(fn (Get $get) => Employee::find($get('employee_id'))?->designation?->name),
                            ]),
                    ])
                    ->visible(fn (Get $get) => filled($get('employee_id'))),
                Section::make('Basic Info')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 2])
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->relationship('employee', 'name')
                                    ->searchable(['name', 'employee_code'])
                                    ->required()
                                    ->live()
                                    ->preload()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (! $state) {
                                            $set('employee_name', null);
                                            $set('employee_department', null);
                                            $set('employee_designation', null);
                                            $set('employee_code', null);

                                            return;
                                        }
                                        $employee = Employee::find($state);
                                        $set('employee_name', $employee?->name);
                                        $set('employee_department', $employee?->department?->name);
                                        $set('employee_designation', $employee?->designation?->name);
                                        $set('employee_code', $employee?->employee_code);
                                    }),

                                Select::make('type')
                                    ->label('Adjustment Type')
                                    ->options([
                                        'add' => 'Addition (+)',
                                        'subtract' => 'Deduction (-)',
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->default('add'),

                                Select::make('for_month')
                                    ->label('Target Month')
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
                                    ->native(false)
                                    ->required(),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->default('pending'),
                            ]),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Grid::make(['default' => 1, 'lg' => 2])
                            ->schema([
                                Textarea::make('notes_by_hr')
                                    ->label('Notes by HR')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->autosize()
                                    ->disabled(fn () => !auth()->user()->hasRole('HR')),
                                Textarea::make('notes_by_finance')
                                    ->label('Notes by Finance')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->autosize()
                                    ->disabled(fn () => !auth()->user()->hasRole('Finance')),
                            ]),
                    ]),
            ]);
    }
}
