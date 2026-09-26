<?php

namespace App\Filament\Resources\TipsDepartmentMappings\Pages;

use App\Filament\Resources\TipsDepartmentMappings\TipsDepartmentMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipsDepartmentMapping extends EditRecord
{
    protected static string $resource = TipsDepartmentMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
