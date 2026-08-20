<?php

namespace App\Filament\Resources\EmployeeSuspensions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeSuspensionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Suspension Details')
                    ->description('Specify the employee and suspension duration.')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->relationship(
                                        name: 'employee',
                                        titleAttribute: 'name'
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper($record->employee_code).' | '.$record->name)
                                    ->searchable(['name', 'employee_code'])
                                    ->preload()
                                    ->required()
                                    ->columnSpan(['sm' => 3]),

                                DatePicker::make('start_date')
                                    ->label('Suspended From')
                                    ->native(false)
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('end_date')
                                    ->label('Suspended To')
                                    ->native(false)
                                    ->default(now()->addDays(7))
                                    ->afterOrEqual('start_date')
                                    ->required(),

                                Select::make('status')
                                    ->label('Suspension Status')
                                    ->options([
                                        'active' => 'Active',
                                        'completed' => 'Completed / Uplifted',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('reason')
                            ->label('Reason for Suspension')
                            ->placeholder('Describe the incident, breach of policy, or reason for suspension...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Section::make('Related Attachments & Documents')
                    ->description('Upload suspension letters, evidence, notices, or related files.')
                    ->schema([
                        FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->reorderable()
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->directory('suspension-attachments')
                            ->disk('public')
                            ->maxSize(10240) // 10MB per file
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
