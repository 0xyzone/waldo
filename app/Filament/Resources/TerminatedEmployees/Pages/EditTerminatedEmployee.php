<?php

namespace App\Filament\Resources\TerminatedEmployees\Pages;

use App\Filament\Resources\TerminatedEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTerminatedEmployee extends EditRecord
{
    protected static string $resource = TerminatedEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
