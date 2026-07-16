<?php

namespace App\Filament\Resources\Leavers\Pages;

use App\Filament\Resources\Leavers\LeaverResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeaver extends EditRecord
{
    protected static string $resource = LeaverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
