<?php

namespace App\Filament\Resources\UnofficialNametagRequests\Pages;

use App\Filament\Resources\UnofficialNametagRequests\UnofficialNametagRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUnofficialNametagRequest extends EditRecord
{
    protected static string $resource = UnofficialNametagRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
