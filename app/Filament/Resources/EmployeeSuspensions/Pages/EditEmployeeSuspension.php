<?php

namespace App\Filament\Resources\EmployeeSuspensions\Pages;

use App\Filament\Resources\EmployeeSuspensions\EmployeeSuspensionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeSuspension extends EditRecord
{
    protected static string $resource = EmployeeSuspensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
