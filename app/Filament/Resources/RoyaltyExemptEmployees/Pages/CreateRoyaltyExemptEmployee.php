<?php

namespace App\Filament\Resources\RoyaltyExemptEmployees\Pages;

use App\Filament\Resources\RoyaltyExemptEmployees\RoyaltyExemptEmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoyaltyExemptEmployee extends CreateRecord
{
    protected static string $resource = RoyaltyExemptEmployeeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
