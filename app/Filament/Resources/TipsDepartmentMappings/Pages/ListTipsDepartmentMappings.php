<?php

namespace App\Filament\Resources\TipsDepartmentMappings\Pages;

use App\Filament\Resources\TipsDepartmentMappings\TipsDepartmentMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipsDepartmentMappings extends ListRecords
{
    protected static string $resource = TipsDepartmentMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
