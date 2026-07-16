<?php

namespace App\Filament\Resources\Leavers\Pages;

use App\Filament\Resources\Leavers\LeaverResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeavers extends ListRecords
{
    protected static string $resource = LeaverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
