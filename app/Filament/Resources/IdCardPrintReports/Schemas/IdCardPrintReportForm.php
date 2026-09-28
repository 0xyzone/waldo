<?php

namespace App\Filament\Resources\IdCardPrintReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IdCardPrintReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Details')
                    ->description('Specify batch title, print date, and optional remarks.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Batch Title')
                            ->placeholder('e.g. ID Card Print Batch - 27 Sep 2026')
                            ->default('ID Card Print Batch - ' . now()->format('jS F Y'))
                            ->required(),
                        Grid::make(3)->schema([
                            DatePicker::make('batch_date')
                                ->label('Print / Batch Date')
                                ->default(now()->toDateString())
                                ->autoFocus()
                                ->native(false)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (callable $set, $state) {
                                    if (!$state) {
                                        return;
                                    }

                                    $set('title', 'ID Card Print Batch - ' . date('jS F Y', strtotime($state)));
                                })
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'active' => 'Active',
                                    'completed' => 'Completed',
                                    'archived' => 'Archived',
                                ])
                                ->default('active')
                                ->required(),
                        ]),
                        Textarea::make('notes')
                            ->label('Batch Notes / Instructions')
                            ->placeholder('Optional notes regarding this printing batch...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Upload Employee CSV / Excel File (Optional)')
                    ->description('Upload file with columns: code, name, depart, or skip to start an empty batch.')
                    ->schema([
                        FileUpload::make('csv_file_path')
                            ->label('Employee CSV File (.csv / .xlsx)')
                            ->disk('public')
                            ->directory('id-card-batches')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'application/csv',
                            ])
                            ->maxSize(15360)
                            ->helperText('Optional. If uploaded, code column will be matched with master employee records. You can skip this and add employees directly in the batch.')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
