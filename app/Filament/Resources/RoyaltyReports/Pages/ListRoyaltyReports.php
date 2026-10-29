<?php

namespace App\Filament\Resources\RoyaltyReports\Pages;

use App\Filament\Resources\RoyaltyReports\RoyaltyReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoyaltyReports extends ListRecords
{
    protected static string $resource = RoyaltyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Generate Royalty Report'),
        ];
    }
}
