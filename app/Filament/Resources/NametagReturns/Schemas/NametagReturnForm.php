<?php

namespace App\Filament\Resources\NametagReturns\Schemas;

use App\Filament\Components\ActionLogSection;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NametagReturnForm
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
                Section::make('NameTag Return Details')
                    ->description('Record the returned nametag date, physical condition, and handling notes.')
                    ->icon('heroicon-o-arrow-path-rounded-square')
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

                                DatePicker::make('return_date')
                                    ->label('Return Date')
                                    ->native(false)
                                    ->required()
                                    ->default(now()->toDateString()),

                                Select::make('condition')
                                    ->label('Condition')
                                    ->options([
                                        'Good' => 'Good Condition',
                                        'Damaged' => 'Damaged',
                                        'Lost' => 'Lost / Missing',
                                    ])
                                    ->default('Good')
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('remarks')
                            ->label('Remarks / Handover Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Add optional handover notes, return reason (e.g., resignation, transfer, replacement), or damage description...'),
                    ]),

                ActionLogSection::make(),
            ]);
    }
}
