<?php

namespace App\Filament\Resources\DiscordSettingResource\Pages;

use App\Filament\Resources\DiscordSettingResource\DiscordSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscordSettings extends ListRecords
{
    protected static string $resource = DiscordSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
