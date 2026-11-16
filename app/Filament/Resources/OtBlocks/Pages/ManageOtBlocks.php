<?php

namespace App\Filament\Resources\OtBlocks\Pages;

use App\Filament\Resources\OtBlocks\OtBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOtBlocks extends ManageRecords
{
    protected static string $resource = OtBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
