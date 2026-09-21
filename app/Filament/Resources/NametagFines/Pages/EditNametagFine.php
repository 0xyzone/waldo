<?php

namespace App\Filament\Resources\NametagFines\Pages;

use App\Filament\Resources\NametagFines\NametagFineResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNametagFine extends EditRecord
{
    protected static string $resource = NametagFineResource::class;

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
