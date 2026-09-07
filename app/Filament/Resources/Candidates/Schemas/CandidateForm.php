<?php

namespace App\Filament\Resources\Candidates\Schemas;

use App\Helpers\NepaliDate\NepaliDate;
use App\Models\Designation;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('phone_number')
                            ->unique(ignoreRecord: true)
                            ->numeric()
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'contacted' => 'Contacted',
                                'unreachable' => 'Unreachable',
                                'not_coming' => 'Not Coming',
                                'approved' => 'Approved',
                                'no_show' => 'No Show',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->disablePlaceholderSelection()
                            ->default('pending'),
                    ])
                    ->columnSpanFull(),
                DatePicker::make('dob_ad')
                    ->label('Date of Birth (AD)')
                    ->native(false)
                    ->live(onBlur: true)
                    ->firstDayOfWeek(0)
                    ->hint(function ($state) {
                        if (!empty($state)) {
                            try {
                                $age = Carbon::parse($state)->age;

                                return $age . ' years old';
                            } catch (\Exception $e) {
                                // ignore
                            }
                        }

                        return null;
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (empty($state)) {
                            $set('dob_bs', null);

                            return;
                        }
                        try {
                            $date = Carbon::parse($state);
                            $converter = new NepaliDate;
                            $converted = $converter->convertAdToBs($date->year, $date->month, $date->day);
                            if (!empty($converted)) {
                                $set('dob_bs', sprintf('%04d.%02d.%02d', $converted['year'], $converted['month'], $converted['day']));
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }),
                TextInput::make('dob_bs')
                    ->label('Date of Birth (BS)')
                    ->disabled()
                    ->dehydrated(),
                Grid::make(3)
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->required()
                            ->preload()
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('designation_id', null))
                            ->disablePlaceholderSelection(),
                        Select::make('designation_id')
                            ->label('Designation')
                            ->native(false)
                            ->searchable()
                            ->nullable()
                            ->hidden(fn(callable $get) => blank($get('department_id')))
                            ->options(fn(callable $get) => Designation::where('department_id', $get('department_id'))
                                ->where('is_active', true)
                                ->orderBy('rank')
                                ->pluck('name', 'id')
                                ->toArray()),
                        TextInput::make('reference')
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip('If there are no reference then you can leave it blank')
                            ->placeholder('Prabal Pradhan'),
                    ])
                    ->columnSpanFull(),
                FileUpload::make('cv_image')
                    ->label('CV / Portfolio Images')
                    ->image()
                    ->panelLayout('grid')
                    ->appendFiles()
                    ->multiple()
                    ->reorderable()
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('cvs')
                    ->maxSize(20480),
                Textarea::make('notes')
                    ->autosize(),
            ]);
    }
}
