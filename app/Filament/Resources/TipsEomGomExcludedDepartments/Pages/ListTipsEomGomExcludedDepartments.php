<?php

namespace App\Filament\Resources\TipsEomGomExcludedDepartments\Pages;

use App\Filament\Resources\TipsEomGomExcludedDepartments\TipsEomGomExcludedDepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipsEomGomExcludedDepartments extends ListRecords
{
    protected static string $resource = TipsEomGomExcludedDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Exclude Department')
                ->modalHeading('Exclude Department from EOM/GOM Reports'),
        ];
    }
}
