<?php

namespace App\Filament\Resources\ManualAttendanceStaffs\Pages;

use App\Filament\Resources\ManualAttendanceStaffs\ManualAttendanceStaffResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateManualAttendanceStaff extends CreateRecord
{
    protected static string $resource = ManualAttendanceStaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
