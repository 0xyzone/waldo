<?php

namespace App\Filament\Resources\OvertimeReports\Pages;

use App\Filament\Resources\OvertimeReports\OvertimeReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOvertimeReports extends ListRecords
{
    protected static string $resource = OvertimeReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
