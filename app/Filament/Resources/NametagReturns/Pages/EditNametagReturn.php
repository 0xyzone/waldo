<?php

namespace App\Filament\Resources\NametagReturns\Pages;

use App\Filament\Resources\NametagReturns\NametagReturnResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNametagReturn extends EditRecord
{
    protected static string $resource = NametagReturnResource::class;

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
