<?php

namespace App\Filament\Resources\NametagFines\Pages;

use App\Filament\Resources\NametagFines\NametagFineResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateNametagFine extends CreateRecord
{
    protected static string $resource = NametagFineResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['amount'] = 500;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
