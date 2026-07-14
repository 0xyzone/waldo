<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\WelcomeUserMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** Holds the plain-text password before it gets hashed. */
    protected string $plainPassword = '';

    /**
     * Intercept form data before the record is created so we can
     * capture the plain-text password (the model will hash it on save).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainPassword = $data['password'];

        return $data;
    }

    protected function afterCreate(): void
    {
        // Fire the Registered event so Laravel sends the email verification link
        event(new Registered($this->record));

        // Send the welcome email with the credentials
        Mail::to($this->record->email)
            ->send(new WelcomeUserMail($this->record, $this->plainPassword));
    }
}
