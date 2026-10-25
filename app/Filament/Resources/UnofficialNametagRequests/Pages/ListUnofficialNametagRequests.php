<?php

namespace App\Filament\Resources\UnofficialNametagRequests\Pages;

use App\Filament\Resources\UnofficialNametagRequests\UnofficialNametagRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnofficialNametagRequests extends ListRecords
{
    protected static string $resource = UnofficialNametagRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
