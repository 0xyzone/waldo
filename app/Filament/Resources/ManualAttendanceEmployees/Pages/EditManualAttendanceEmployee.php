<?php

namespace App\Filament\Resources\ManualAttendanceEmployees\Pages;

use App\Filament\Resources\ManualAttendanceEmployees\ManualAttendanceEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManualAttendanceEmployee extends EditRecord
{
    protected static string $resource = ManualAttendanceEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Remove from Manual Attendance'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
