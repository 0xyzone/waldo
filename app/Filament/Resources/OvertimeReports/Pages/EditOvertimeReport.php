<?php

namespace App\Filament\Resources\OvertimeReports\Pages;

use App\Filament\Resources\OvertimeReports\OvertimeReportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOvertimeReport extends EditRecord
{
    protected static string $resource = OvertimeReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
