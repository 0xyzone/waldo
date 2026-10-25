<?php

namespace App\Filament\Resources\MonthlyManualAttendances\Pages;

use App\Filament\Resources\MonthlyManualAttendances\MonthlyManualAttendanceResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyManualAttendance extends EditRecord
{
    protected static string $resource = MonthlyManualAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_checklist')
                ->label('Open Checklist')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->url(fn (): string => $this->getResource()::getUrl('view', ['record' => $this->getRecord()])),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
