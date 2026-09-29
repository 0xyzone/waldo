<?php

namespace App\Filament\Resources\ManualAttendanceEmployees\Pages;

use App\Filament\Resources\ManualAttendanceEmployees\ManualAttendanceEmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateManualAttendanceEmployee extends CreateRecord
{
    protected static string $resource = ManualAttendanceEmployeeResource::class;

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
