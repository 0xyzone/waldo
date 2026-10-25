<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\UserReactivationOtpService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn (User $record): bool => $record->id === auth()->id() || ! (bool) $record->is_active)
                    ->tooltip(fn (User $record): ?string => $record->id === auth()->id()
                        ? 'You cannot deactivate your own account.'
                        : (! (bool) $record->is_active ? 'Reactivation requires Super Admin OTP verification. Use the Reactivate button in Actions.' : 'Click to deactivate account.')
                    )
                    ->afterStateUpdated(function (User $record, bool $state): void {
                        Notification::make()
                            ->title('User Status Updated')
                            ->body("User {$record->name} is now ".($state ? 'Active' : 'Inactive').'.')
                            ->color($state ? 'success' : 'danger')
                            ->send();
                    }),
                TextColumn::make('email_verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Account Status')
                    ->placeholder('All Users')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
                SelectFilter::make('role')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Registered From')
                            ->native(false),
                        DatePicker::make('created_to')
                            ->label('Registered To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->recordActions([
                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (User $record): bool => ! (bool) $record->is_active && (UserResource::canShield('ToggleActive') || (auth()->user()?->hasRole('super_admin') ?? false)))
                    ->modalHeading(fn (User $record): string => "Reactivate {$record->name}")
                    ->modalDescription(function (User $record, UserReactivationOtpService $otpService): HtmlString {
                        $masked = $otpService->getMaskedEmail();

                        return new HtmlString("A 6-digit security OTP code has been dispatched to <strong>{$masked}</strong>. Please enter the OTP to reactivate this account.");
                    })
                    ->modalSubmitActionLabel('Verify & Reactivate')
                    ->modalWidth('md')
                    ->mountUsing(function (Action $action, User $record, UserReactivationOtpService $otpService): void {
                        try {
                            $otpInfo = $otpService->generateAndSendOtp($record, auth()->user(), isResend: false);
                            if ($otpInfo['status'] === 'sent') {
                                Notification::make()
                                    ->title('OTP Dispatched')
                                    ->body("A 6-digit OTP code was sent to {$otpInfo['masked_email']}.")
                                    ->info()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Active OTP Available')
                                    ->body("An active OTP was already sent to {$otpInfo['masked_email']}. Cooldown active ({$otpInfo['cooldown']}s remaining).")
                                    ->info()
                                    ->send();
                            }
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Rate Limit Active')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        }
                    })
                    ->extraModalActions([
                        Action::make('resendOtp')
                            ->label('Resend Code')
                            ->icon('heroicon-o-arrow-path')
                            ->color('gray')
                            ->action(function (User $record, UserReactivationOtpService $otpService): void {
                                try {
                                    $otpInfo = $otpService->generateAndSendOtp($record, auth()->user(), isResend: true);
                                    Notification::make()
                                        ->title('New OTP Dispatched')
                                        ->body("A fresh 6-digit OTP code was sent to {$otpInfo['masked_email']}. Please wait {$otpInfo['cooldown']}s before requesting another.")
                                        ->success()
                                        ->send();
                                } catch (ValidationException $e) {
                                    Notification::make()
                                        ->title('Cannot Resend Yet')
                                        ->body($e->getMessage())
                                        ->warning()
                                        ->send();
                                }
                            }),
                    ])
                    ->form([
                        TextInput::make('otp')
                            ->label('6-Digit OTP Code')
                            ->placeholder('••••••')
                            ->required()
                            ->length(6)
                            ->numeric()
                            ->autocomplete(false)
                            ->autofocus()
                            ->helperText('Valid for 10 minutes. Check the configured admin email inbox.')
                            ->extraInputAttributes(['style' => 'letter-spacing: 0.35em; font-family: monospace; font-size: 1.25rem; text-align: center; font-weight: bold;']),
                    ])
                    ->action(function (array $data, User $record, UserReactivationOtpService $otpService): void {
                        $result = $otpService->verifyOtp($record, $data['otp']);

                        if ($result['status'] !== 'success') {
                            Notification::make()
                                ->title('Verification Failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();

                            throw ValidationException::withMessages([
                                'otp' => $result['message'],
                            ]);
                        }

                        User::$allowReactivationWithoutOtp = true;
                        try {
                            $record->update(['is_active' => true]);
                        } finally {
                            User::$allowReactivationWithoutOtp = false;
                        }

                        Notification::make()
                            ->title('User Successfully Reactivated')
                            ->body("Account {$record->name} ({$record->email}) is now Active.")
                            ->success()
                            ->send();
                    }),
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => "Deactivate {$record->name}")
                    ->modalDescription('Are you sure you want to deactivate this account? They will be immediately blocked from accessing the panel.')
                    ->modalSubmitActionLabel('Deactivate Account')
                    ->visible(fn (User $record): bool => (bool) $record->is_active && $record->id !== auth()->id() && (UserResource::canShield('ToggleActive') || (auth()->user()?->hasRole('super_admin') ?? false)))
                    ->action(function (User $record): void {
                        $record->update(['is_active' => false]);

                        Notification::make()
                            ->title('Account Deactivated')
                            ->body("User {$record->name} is now Inactive.")
                            ->danger()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
                Impersonate::make()
                    ->redirectTo(Dashboard::getUrl())
                    ->visible(fn (): bool => UserResource::canShield('Impersonate') || (auth()->user()?->hasRole('super_admin') ?? false)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
