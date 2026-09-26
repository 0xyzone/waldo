<?php

namespace App\Filament\Resources\TipsNonEmployees\Pages;

use App\Filament\Resources\TipsNonEmployees\TipsNonEmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipsNonEmployee extends CreateRecord
{
    protected static string $resource = TipsNonEmployeeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
