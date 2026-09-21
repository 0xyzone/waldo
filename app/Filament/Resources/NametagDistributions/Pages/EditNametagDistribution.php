<?php

namespace App\Filament\Resources\NametagDistributions\Pages;

use App\Filament\Resources\NametagDistributions\NametagDistributionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNametagDistribution extends EditRecord
{
    protected static string $resource = NametagDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
