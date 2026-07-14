<?php

namespace App\Filament\Resources\MapUsers\Pages;

use App\Filament\Resources\MapUsers\MapUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMapUser extends EditRecord
{
    protected static string $resource = MapUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
