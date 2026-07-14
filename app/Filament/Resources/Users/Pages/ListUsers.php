<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\WelcomeUserMail;
use Filament\Actions\CreateAction;
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
                }),
        ];
    }
}
