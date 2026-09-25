<?php

namespace App\Filament\Resources\ScheduleRuns\Pages;

use App\Filament\Resources\ScheduleRuns\ScheduleRunResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScheduleRun extends EditRecord
{
    protected static string $resource = ScheduleRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
