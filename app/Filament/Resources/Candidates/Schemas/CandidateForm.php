<?php

namespace App\Filament\Resources\Candidates\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone_number')
                    ->numeric()
                    ->required(),
                FileUpload::make('cv_image')
                    ->image()
                    ->directory('cvs')
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'contacted' => 'Contacted',
                        'unreachable' => 'Unreachable',
                        'not_coming' => 'Not Coming',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->disablePlaceholderSelection()
                    ->default('pending'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
