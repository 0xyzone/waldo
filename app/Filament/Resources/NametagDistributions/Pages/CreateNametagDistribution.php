<?php

namespace App\Filament\Resources\NametagDistributions\Pages;

use App\Filament\Resources\NametagDistributions\NametagDistributionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNametagDistribution extends CreateRecord
{
    protected static string $resource = NametagDistributionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
