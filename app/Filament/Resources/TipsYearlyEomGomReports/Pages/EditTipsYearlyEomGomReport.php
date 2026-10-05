<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports\Pages;

use App\Filament\Resources\TipsYearlyEomGomReports\TipsYearlyEomGomReportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTipsYearlyEomGomReport extends EditRecord
{
    protected static string $resource = TipsYearlyEomGomReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
