<?php

namespace App\Filament\Resources\RoyaltyExemptEmployees\Pages;

use App\Filament\Resources\RoyaltyExemptEmployees\RoyaltyExemptEmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoyaltyExemptEmployees extends ListRecords
{
    protected static string $resource = RoyaltyExemptEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
