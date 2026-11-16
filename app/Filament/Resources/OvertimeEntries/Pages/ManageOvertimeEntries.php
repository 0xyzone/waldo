<?php

namespace App\Filament\Resources\OvertimeEntries\Pages;

use App\Filament\Resources\OvertimeEntries\OvertimeEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOvertimeEntries extends ManageRecords
{
    protected static string $resource = OvertimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
