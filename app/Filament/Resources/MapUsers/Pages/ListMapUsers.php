<?php

namespace App\Filament\Resources\MapUsers\Pages;

use App\Filament\Resources\MapUsers\MapUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMapUsers extends ListRecords
{
    protected static string $resource = MapUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
