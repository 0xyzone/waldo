<?php

namespace App\Filament\Resources\NametagReturns\Pages;

use App\Filament\Resources\NametagReturns\NametagReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNametagReturn extends CreateRecord
{
    protected static string $resource = NametagReturnResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['received_by'])) {
            $data['received_by'] = auth()->id();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
