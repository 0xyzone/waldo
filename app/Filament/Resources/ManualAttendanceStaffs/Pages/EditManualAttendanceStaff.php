<?php

namespace App\Filament\Resources\ManualAttendanceStaffs\Pages;

use App\Filament\Resources\ManualAttendanceStaffs\ManualAttendanceStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManualAttendanceStaff extends EditRecord
{
    protected static string $resource = ManualAttendanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
