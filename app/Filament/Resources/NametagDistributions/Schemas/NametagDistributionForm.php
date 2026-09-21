<?php

namespace App\Filament\Resources\NametagDistributions\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NametagDistributionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Live Employee Information Preview Card
                Section::make('Selected Employee Information')
                    ->description('Details retrieved directly from the employee profile.')
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

                // Main Form Inputs Section
                Section::make('NameTag Distribution Details')
                    ->description('Assign employee, record date, and manage tag lifecycle status.')
                    ->icon('heroicon-o-tag')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])
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

                                DatePicker::make('date')
                                    ->label('Distribution Date')
                                    ->native(false)
                                    ->required()
                                    ->default(now()->toDateString()),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'Pending' => 'Pending',
                                        'Printed' => 'Printed',
                                        'Released' => 'Released',
                                    ])
                                    ->default('Pending')
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('remarks')
                            ->label('Remarks / Notes')
                            ->placeholder('Add any optional notes about this nametag (e.g. reprint reason, distributed to manager, etc.)...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
