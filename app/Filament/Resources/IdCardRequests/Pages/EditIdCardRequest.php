<?php

namespace App\Filament\Resources\IdCardRequests\Pages;

use App\Filament\Resources\IdCardRequests\IdCardRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIdCardRequest extends EditRecord
{
    protected static string $resource = IdCardRequestResource::class;

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
