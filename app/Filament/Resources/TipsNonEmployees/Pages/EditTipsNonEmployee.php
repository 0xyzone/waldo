<?php

namespace App\Filament\Resources\TipsNonEmployees\Pages;

use App\Filament\Resources\TipsNonEmployees\TipsNonEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipsNonEmployee extends EditRecord
{
    protected static string $resource = TipsNonEmployeeResource::class;

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
