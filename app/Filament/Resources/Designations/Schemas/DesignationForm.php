<?php

namespace App\Filament\Resources\Designations\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DesignationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Designation Details')
                    ->description('Assign a designation to a department with a sort rank.')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Designation Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Shift Manager, HR Admin')
                            ->columnSpanFull(),

                        RichEditor::make('job_description')
                            ->label('Job Description')
                            ->placeholder('Enter the job description, key responsibilities, and duties for this designation...')
                            ->helperText('This job description will be available as pre-built variable {{ employee_job_description }} when generating letters.')
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('rank')
                            ->label('Sort Rank')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->placeholder('Lower number = higher priority')
                            ->helperText('Used for ordering in reports.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive designations are hidden from dropdowns.')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
