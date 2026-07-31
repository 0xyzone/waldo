<?php

namespace App\Filament\Resources\TerminatedEmployees\Pages;

use App\Filament\Resources\TerminatedEmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTerminatedEmployees extends ListRecords
{
    protected static string $resource = TerminatedEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('6xl'),
        ];
    }
}
