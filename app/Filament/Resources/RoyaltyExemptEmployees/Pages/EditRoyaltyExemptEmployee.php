<?php

namespace App\Filament\Resources\RoyaltyExemptEmployees\Pages;

use App\Filament\Resources\RoyaltyExemptEmployees\RoyaltyExemptEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoyaltyExemptEmployee extends EditRecord
{
    protected static string $resource = RoyaltyExemptEmployeeResource::class;

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
