<?php

namespace App\Filament\Resources\RoyaltyHeldBalances\Pages;

use App\Filament\Resources\RoyaltyHeldBalances\RoyaltyHeldBalanceResource;
use Filament\Resources\Pages\ListRecords;

class ListRoyaltyHeldBalances extends ListRecords
{
    protected static string $resource = RoyaltyHeldBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
