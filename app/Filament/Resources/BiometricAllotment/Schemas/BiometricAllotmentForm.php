<?php

namespace App\Filament\Resources\BiometricAllotment\Schemas;

use App\Filament\Resources\BiometricAllotmentResource;
use App\Models\BiometricAllotment;
use App\Models\MapUser;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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
                            ->form([
                                Toggle::make('old_checkout_device')
                                    ->label('Old Checkout Device')
                                    ->required(),
                                Toggle::make('new_checkin')
                                    ->label('New Checkin')
                                    ->required(),
                                Toggle::make('new_checkout')
                                    ->label('New Checkout')
                                    ->required(),
                                Textarea::make('remarks')
                                    ->label('Remarks'),
                            ])
                            ->action(function (BiometricAllotment $record, array $data, Component $livewire) {
                                $mapuser = MapUser::where('user_id', Auth::id())->first();
                                $record->update([
                                    'status' => 'Done',
                                    'enrolled_date' => now(),
                                    'set_by' => $mapuser->setby_name,
                                    'old_checkout_device' => $data['old_checkout_device'],
                                    'new_checkin' => $data['new_checkin'],
                                    'new_checkout' => $data['new_checkout'],
                                    'remarks' => $data['remarks'],
                                ]);
                                Notification::make()
                                    ->title('Marked as Done')
                                    ->body('Successfully Marked as Done')
                                    ->success()
                                    ->send();

                                $livewire->redirect(BiometricAllotmentResource::getUrl('index'));
                            })
                            ->visible(function ($record) {
                                return MapUser::where('user_id', Auth::id())->exists() && $record->status != 'Done';
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
                                    ->default('Not Done Yet')
                                    ->disabledOn('edit'),
                                DatePicker::make('enrolled_date')
                                    ->label('Enrolled Date')
                                    ->native(false)
                                    ->disabledOn('edit'),
                                Select::make('set_by')
                                    ->label('Set By')
                                    ->options([
                                        'Shuraz' => 'Shuraz',
                                        'Saugat' => 'Saugat',
                                        'Suraj Raj Karmacharya' => 'Suraj Raj Karmacharya',
                                    ])
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->disabledOn('edit'),
                            ]),
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Toggle::make('old_checkout_device')
                                    ->label('Olde Check out Device')
                                    ->disabledOn('edit'),
                                Toggle::make('new_checkin')
                                    ->label('New Checkin')
                                    ->disabledOn('edit'),
                                Toggle::make('new_checkout')
                                    ->label('New CheckOut')
                                    ->disabledOn('edit'),
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
