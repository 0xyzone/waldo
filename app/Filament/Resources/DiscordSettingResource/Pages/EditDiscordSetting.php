<?php

namespace App\Filament\Resources\DiscordSettingResource\Pages;

use App\Filament\Resources\DiscordSettingResource\DiscordSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscordSetting extends EditRecord
{
    protected static string $resource = DiscordSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
