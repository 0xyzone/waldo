<?php

namespace App\Filament\Resources\TipsAdjustments\Pages;

use App\Filament\Resources\TipsAdjustments\TipsAdjustmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipsAdjustment extends EditRecord
{
    protected static string $resource = TipsAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
