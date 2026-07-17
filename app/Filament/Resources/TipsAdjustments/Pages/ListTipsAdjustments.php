<?php

namespace App\Filament\Resources\TipsAdjustments\Pages;

use App\Filament\Resources\TipsAdjustments\TipsAdjustmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipsAdjustments extends ListRecords
{
    protected static string $resource = TipsAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->modalWidth('6xl'),
        ];
    }
}
