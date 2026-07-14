<?php

namespace App\Filament\Resources\BiometricAllotment\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BiometricAllotmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Biometric Allotment Details')
                    ->headerActions([
                        Action::make('mark_done')
                            ->label('Mark as Done')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->action(function (BiometricAllotment $record) {
                                $record->update([
                                    'status' => 'Done',
                                    'enrolled_date' => now(),
                                ]);
                            }),
                    ])
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->required(),
                                Select::make('department_id')
                                    ->relationship(
                                        name: 'department',
                                        titleAttribute: 'name'
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->disabledOn('edit'),
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'Done' => 'Done',
                                        'Left Job' => 'Left Job',
                                        'Not Done Yet' => 'Not Done Yet',
                                        'Bio Not Required' => 'Bio Not Required',
                                    ])
                                    ->native(false)
                                    ->default('Not Done Yet'),
                                DatePicker::make('enrolled_date')
                                    ->label('Enrolled Date')
                                    ->native(false)
                                    ->disabledOn('edit'),
                                Select::make('set_by')
                                    ->label('Set By')
                                    ->options([
                                        'Shuraz' => 'Shuraz',
                                        'Saugat' => 'Saugat',
                                        'Suraj Raj Karmacharya' => 'Suraj Raj Karmacharya'
                                    ])
                                    ->native(false)
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Toggle::make('old_checkout_device')
                                    ->label('Olde Check out Device'),
                                Toggle::make('new_checkin')
                                    ->label('New Checkin'),
                                Toggle::make('new_checkout')
                                    ->label('New CheckOut'),
                            ]),
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Select::make('shift')
                                    ->label('Shift')
                                    ->options([
                                        'Morning' => 'Morning',
                                        'Evening' => 'Evening',
                                        'Night' => 'Night',
                                    ])
                                    ->native(false),
                            ]),
                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
