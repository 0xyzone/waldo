<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipsReports extends ListRecords
{
    protected static string $resource = TipsReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
