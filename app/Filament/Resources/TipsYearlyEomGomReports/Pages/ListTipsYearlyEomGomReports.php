<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports\Pages;

use App\Filament\Resources\TipsYearlyEomGomReports\TipsYearlyEomGomReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipsYearlyEomGomReports extends ListRecords
{
    protected static string $resource = TipsYearlyEomGomReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Generate Yearly Report')
                ->modalHeading('Generate Yearly EOM & GOM Report'),
        ];
    }
}
