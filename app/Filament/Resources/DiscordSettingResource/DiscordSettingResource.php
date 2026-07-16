<?php

namespace App\Filament\Resources\DiscordSettingResource;

use App\Filament\Resources\DiscordSettingResource\Pages\CreateDiscordSetting;
use App\Filament\Resources\DiscordSettingResource\Pages\EditDiscordSetting;
use App\Filament\Resources\DiscordSettingResource\Pages\ListDiscordSettings;
use App\Filament\Resources\DiscordSettingResource\Schemas\DiscordSettingForm;
use App\Filament\Resources\DiscordSettingResource\Tables\DiscordSettingsTable;
use App\Models\DiscordSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class DiscordSettingResource extends Resource
{
    protected static ?string $model = DiscordSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Cog6Tooth;

    protected static ?string $navigationLabel = 'Discord Setup';

    protected static ?string $modelLabel = 'Discord Setup';

    protected static ?string $pluralModelLabel = 'Discord Setup';

    protected static string|UnitEnum|null $navigationGroup = 'IT';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return DiscordSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscordSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscordSettings::route('/'),
            'create' => CreateDiscordSetting::route('/create'),
            'edit' => EditDiscordSetting::route('/{record}/edit'),
        ];
    }
}
