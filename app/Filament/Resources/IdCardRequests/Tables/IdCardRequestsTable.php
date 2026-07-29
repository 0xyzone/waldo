<?php

namespace App\Filament\Resources\IdCardRequests\Tables;

use App\Models\DiscordSetting;
use App\Models\IdCardRequest;
use App\Services\DiscordService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdCardRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Record Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'designed' => 'info',
                        'sent for print' => 'primary',
                        'done' => 'success',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'designed' => 'Designed',
                        'sent for print' => 'Sent for Print',
                        'done' => 'Done',
                        default => (string) $state,
                    })
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'employee' => 'Existing Employee',
                        'custom' => 'Custom Entry',
                        default => (string) $state,
                    }),

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('employee_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('employee_designation')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('employee_department')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('notes')
                    ->label('IT Notes')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Record Status')
                    ->options([
                        'pending' => 'Pending',
                        'designed' => 'Designed',
                        'sent for print' => 'Sent for Print',
                        'done' => 'Done',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'employee' => 'Existing Employee',
                        'custom' => 'Custom Entry',
                    ]),
            ])
            ->recordActions([
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
                            ->options(fn () => DiscordSetting::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Select a bot')
                            ->required()
                            ->live(),

                        Select::make('channel_id')
                            ->label('Target Discord Channel')
                            ->options(fn (Get $get) => $get('setting_id')
                                ? DiscordService::getChannelsGroupedByCategoryForSetting($get('setting_id'))
                                : [])
                            ->searchable()
                            ->placeholder('Select a channel')
                            ->required()
                            ->disabled(fn (Get $get) => ! $get('setting_id')),

                        Select::make('role_ids')
                            ->label('Mention Roles (Optional)')
                            ->multiple()
                            ->options(fn (Get $get) => $get('setting_id')
                                ? DiscordService::getRolesForSetting($get('setting_id'))
                                : [])
                            ->default(fn (Get $get) => $get('setting_id')
                                ? DiscordService::getDefaultRoleIdsForSetting($get('setting_id'))
                                : [])
                            ->searchable()
                            ->preload()
                            ->placeholder('Select role(s) to ping')
                            ->disabled(fn (Get $get) => ! $get('setting_id')),
                    ])
                    ->modalHeading('Send ID Card Requests to Discord')
                    ->modalDescription('Select the bot, target Discord channel, and roles to notify about pending ID card reprint requests.')
                    ->action(function (array $data) {
                        $pendingRequests = IdCardRequest::where('status', 'pending')->get();

                        if ($pendingRequests->isEmpty()) {
                            Notification::make()
                                ->title('No Pending Requests')
                                ->body('There are no ID card requests with "Pending" status.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $setting = DiscordSetting::find($data['setting_id']);
                        if (! $setting || ! $setting->bot_token || ! $setting->guild_id) {
                            Notification::make()
                                ->title('Discord Setup Incomplete')
                                ->body('Please configure the Discord bot settings first in the Discord Setup page.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $selectedRoleIds = array_filter((array) ($data['role_ids'] ?? []));
                        if (! empty($selectedRoleIds)) {
                            $roleMentions = implode(' ', array_map(fn ($roleId) => "<@&{$roleId}>", $selectedRoleIds));
                        } else {
                            $roleMentions = DiscordService::getItRoleMentionForSetting($setting);
                        }

                        $description = "The following employees have requested an ID card reprint:\n\n";
                        foreach ($pendingRequests as $req) {
                            $code = $req->employee_code ?: 'N/A';
                            $dept = $req->employee_department ?: 'N/A';
                            $notes = $req->notes ? " — *Notes: {$req->notes}*" : '';
                            $description .= "• **{$req->employee_name}** (Code: `{$code}`, Dept: `{$dept}`){$notes}\n";
                        }

                        $embed = [
                            'title' => '🪪 Pending ID Card Reprint Requests',
                            'description' => $description,
                            'color' => 0x3B82F6,
                            'timestamp' => now()->toISOString(),
                            'footer' => [
                                'text' => 'Waldo IT ID Card Requests',
                            ],
                        ];

                        $content = "{$roleMentions} - New ID card request(s) require action:";

                        $success = DiscordService::sendEmbedMessageForSetting(
                            $setting,
                            $data['channel_id'],
                            $embed,
                            $content
                        );

                        if ($success) {
                            Notification::make()
                                ->title('Discord Notification Sent')
                                ->body('Successfully notified the selected roles/channel on Discord.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to Send Notification')
                                ->body('An error occurred while sending message to Discord.')
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
