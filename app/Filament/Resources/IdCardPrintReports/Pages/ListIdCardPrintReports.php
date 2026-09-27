<?php

namespace App\Filament\Resources\IdCardPrintReports\Pages;

use App\Filament\Resources\IdCardPrintReports\IdCardPrintReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIdCardPrintReports extends ListRecords
{
    protected static string $resource = IdCardPrintReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New ID Card Batch')
                ->icon('heroicon-m-plus'),
        ];
    }
}
