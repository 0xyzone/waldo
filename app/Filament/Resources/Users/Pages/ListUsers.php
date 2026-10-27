<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\WelcomeUserMail;
use App\Models\AppSetting;
use App\Services\UserReactivationOtpService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $plainPassword = '';

        return [
            Action::make('configureOtpEmail')
                ->label('Reactivation OTP Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->visible(fn (): bool => UserResource::canShield('ConfigureOtpEmail') || (auth()->user()?->hasRole('super_admin') ?? false))
                ->modalHeading('Configure Reactivation OTP Email')
                ->modalDescription(function (UserReactivationOtpService $otpService): string {
                    $current = $otpService->getConfiguredEmail();

                    return "Current recipient: {$current}. Specify the email address that will receive OTP security codes for reactivating user accounts.";
                })
                ->modalSubmitActionLabel('Save Email')
                ->modalWidth('md')
                ->form([
                    TextInput::make('email')
                        ->label('OTP Destination Email')
                        ->email()
                        ->required()
                        ->default(fn (UserReactivationOtpService $otpService): string => $otpService->getConfiguredEmail())
                        ->placeholder('admin@company.com')
                        ->helperText('All reactivation OTP codes will be routed to this inbox.'),
                ])
                ->action(function (array $data): void {
                    AppSetting::set('user_reactivation_otp_email', trim($data['email']));

                    Notification::make()
                        ->title('Reactivation OTP Email Configured')
                        ->body("OTP codes will now be sent to {$data['email']}.")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data) use (&$plainPassword): array {
                    $plainPassword = Str::password(12);
                    $data['password'] = $plainPassword;
                    $data['must_change_password'] = true;

                    return $data;
                })
                ->after(function ($record) use (&$plainPassword) {
                    event(new Registered($record));

                    Mail::to($record->email)
                        ->send(new WelcomeUserMail($record, $plainPassword));
                    
                    $role = Role::findByName('basic');
                    if($role) {
                        $record->assignRole($role);
                    } else {
                        Role::create([
                            'name' => 'basic',
                            'guard_name' => 'web',
                        ]);
                        $record->assignRole('basic');
                    }
                }),
        ];
    }
}
