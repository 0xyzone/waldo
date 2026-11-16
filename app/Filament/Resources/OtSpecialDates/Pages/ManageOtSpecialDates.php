<?php

namespace App\Filament\Resources\OtSpecialDates\Pages;

use App\Filament\Resources\OtSpecialDates\OtSpecialDateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOtSpecialDates extends ManageRecords
{
    protected static string $resource = OtSpecialDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
