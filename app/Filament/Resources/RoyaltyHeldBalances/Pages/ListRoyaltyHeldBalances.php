<?php

namespace App\Filament\Resources\RoyaltyHeldBalances\Pages;

use App\Filament\Resources\RoyaltyHeldBalances\RoyaltyHeldBalanceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRoyaltyHeldBalances extends ListRecords
{
    protected static string $resource = RoyaltyHeldBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printAll')
                ->label('Print Held Balances Sheet')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn (): string => route('royalty.held-balances.print-list'))
                ->openUrlInNewTab(),
        ];
    }
}
