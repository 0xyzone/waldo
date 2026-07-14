<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\WelcomeUserMail;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $plainPassword = '';

        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data) use (&$plainPassword): array {
                    $plainPassword = $data['password'];

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
