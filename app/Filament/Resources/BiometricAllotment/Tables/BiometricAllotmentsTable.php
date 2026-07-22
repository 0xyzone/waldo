<?php

namespace App\Filament\Resources\BiometricAllotment\Tables;

use App\Models\BiometricAllotment;
use App\Models\DiscordSetting;
use App\Models\Employee;
use App\Services\DiscordService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BiometricAllotmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Done' => 'success',
                        'Left Job' => 'danger',
                        'Not Done Yet' => 'warning',
                        'Bio Not Required' => 'info',
                    }),
                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('enrolled_date')
                    ->date(),
                TextColumn::make('set_by')
                    ->label('Set By')
                    ->limit(10)
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Shuraz' => 'info',
                        'Saugat' => 'danger',
                        'Suraj Raj Karmacharya' => 'warning'
                    }),
                IconColumn::make('old_checkout_device')
                    ->label('Old CO Device')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('new_checkin')
                    ->label('New Checkin')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('new_checkout')
                    ->label('New Checkout')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shift')
                    ->label('Shift'),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(20)
                    ->tooltip(fn($state) => $state),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->icon('heroicon-o-phone')
                    ->iconColor('info')
                    ->copyable()
                    ->copyMessage('Phone number copied!')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->orderByRaw('CAST(REGEXP_REPLACE(code, "[^0-9]", "") AS UNSIGNED) DESC'))
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('call')
                    ->label('Call')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->iconButton()
                    ->url(fn($record) => $record->phone ? 'tel:' . $record->phone : null)
                    ->openUrlInNewTab(false)
                    ->requiresConfirmation()
                    ->visible(fn($record) => filled($record->phone)),
                Action::make('add to employee')
                    ->label('Convert')
                    ->button()
                    ->visible(function ($record) {
                        $auth = auth()->user();
                        if ($auth->hasRole('HR')) {
                            $employee = Employee::where('employee_code', $record->code)->first();

                            return !$employee;
                        }

                        return false;
                    })
                    ->form([
                        TextInput::make('code')
                            ->label('Employee Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn($record) => $record->code),
                        TextInput::make('name')
                            ->label('Employee Name')
                            ->required()
                            ->default(fn($record) => $record->name),
                        TextInput::make('phone_number')
                            ->default(fn($record) => $record?->phone),
                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->label('Department')
                            ->required()
                            ->default(fn($record) => $record->department_id),
                        Select::make('shift')
                            ->label('Shift')
                            ->options([
                                'Morning' => 'Morning',
                                'Evening' => 'Evening',
                                'Night' => 'Night'
                            ])
                            ->default(fn($record) => $record->shift),
                    ])
                    ->action(function (array $data) {
                        $employee = Employee::create([
                            'employee_code' => $data['code'],
                            'name' => $data['name'],
                            'department_id' => $data['department_id'],
                            'contact_number' => $data['phone_number'],
                            'employee_status' => 'Active',
                            'shift' => $data['shift'],
                            'tips_status' => 'Release',
                            'point_value' => 1,
                            'publish_tips' => true,
                            'tips_fixed' => true,
                        ]);
                        Notification::make()
                            ->title('Employee Created')
                            ->body('Employee created successfully')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('sendDiscordNotification')
                    ->label('Send to Discord')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->form([
                        Select::make('setting_id')
                            ->label('Bot Configuration')
                            ->options(fn() => DiscordSetting::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Select a bot')
                            ->required()
                            ->live(),
                        Select::make('channel_id')
                            ->label('Target Discord Channel')
                            ->options(fn(Get $get) => $get('setting_id')
                                ? DiscordService::getChannelsGroupedByCategoryForSetting($get('setting_id'))
                                : [])
                            ->searchable()
                            ->placeholder('Select a channel')
                            ->required()
                            ->disabled(fn(Get $get) => !$get('setting_id')),
                    ])
                    ->modalHeading('Send Biometric Requests to Discord')
                    ->modalDescription('Select the bot and target Discord channel, then notify the IT role about pending biometric allotment requests.')
                    ->action(function (array $data) {
                        $pendingAllotments = BiometricAllotment::where('status', 'Not Done Yet')->get();

                        if ($pendingAllotments->isEmpty()) {
                            Notification::make()
                                ->title('No Pending Allotments')
                                ->body('There are no biometric allotments with "Not Done Yet" status.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $setting = DiscordSetting::find($data['setting_id']);
                        if (!$setting || !$setting->bot_token || !$setting->guild_id) {
                            Notification::make()
                                ->title('Discord Setup Incomplete')
                                ->body('Please configure the Discord bot settings first in the Discord Setup page.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $itRoleMention = DiscordService::getItRoleMentionForSetting($setting);

                        $description = "The following employees have been added and need their biometric enrollment completed:\n\n";
                        foreach ($pendingAllotments as $allotment) {
                            $description .= "• **{$allotment->name}** (Code: `{$allotment->code}`)\n";
                        }

                        $embed = [
                            'title' => '🚨 Pending Biometric Allotments',
                            'description' => $description,
                            'color' => 0xFFCC00,
                            'timestamp' => now()->toISOString(),
                            'footer' => [
                                'text' => 'Waldo Biometric Allotments',
                            ],
                        ];

                        $content = "{$itRoleMention} - New biometric allotment(s) require action:";

                        $success = DiscordService::sendEmbedMessageForSetting(
                            $setting,
                            $data['channel_id'],
                            $embed,
                            $content
                        );

                        if ($success) {
                            Notification::make()
                                ->title('Discord Notification Sent')
                                ->body('Successfully notified the IT role on Discord.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to Send Notification')
                                ->body('An error occurred while trying to send the message to Discord. Please check your token and channel setup.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
