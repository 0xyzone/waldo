<?php

namespace App\Filament\Resources\EmployeeSuspensions\Pages;

use App\Filament\Resources\EmployeeSuspensions\EmployeeSuspensionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSuspensions extends ListRecords
{
    protected static string $resource = EmployeeSuspensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('5xl'),
        ];
    }
}
