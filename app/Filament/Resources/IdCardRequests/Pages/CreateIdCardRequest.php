<?php

namespace App\Filament\Resources\IdCardRequests\Pages;

use App\Filament\Resources\IdCardRequests\IdCardRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIdCardRequest extends CreateRecord
{
    protected static string $resource = IdCardRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
