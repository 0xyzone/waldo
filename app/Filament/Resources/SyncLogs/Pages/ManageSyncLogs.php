<?php

namespace App\Filament\Resources\SyncLogs\Pages;

use App\Filament\Resources\SyncLogs\SyncLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSyncLogs extends ManageRecords
{
    protected static string $resource = SyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
