<?php

namespace App\Filament\Resources\UnofficialNametagRequests;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\UnofficialNametagRequests\Pages\CreateUnofficialNametagRequest;
use App\Filament\Resources\UnofficialNametagRequests\Pages\EditUnofficialNametagRequest;
use App\Filament\Resources\UnofficialNametagRequests\Pages\ListUnofficialNametagRequests;
use App\Filament\Resources\UnofficialNametagRequests\Schemas\UnofficialNametagRequestForm;
use App\Filament\Resources\UnofficialNametagRequests\Tables\UnofficialNametagRequestsTable;
use App\Models\UnofficialNametagRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UnofficialNametagRequestResource extends Resource
{
    use HasResourceShield;

    protected static ?string $model = UnofficialNametagRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Tag;

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Purchase & Store';

    protected static ?string $navigationLabel = 'Unofficial NameTag Requests';

    protected static ?string $pluralModelLabel = 'Unofficial NameTag Requests';

    protected static ?string $modelLabel = 'Unofficial NameTag Request';

    protected static ?string $recordTitleAttribute = 'employee_name';

    public static function form(Schema $schema): Schema
    {
        return UnofficialNametagRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnofficialNametagRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnofficialNametagRequests::route('/'),
            // 'create' => CreateUnofficialNametagRequest::route('/create'),
            // 'edit' => EditUnofficialNametagRequest::route('/{record}/edit'),
        ];
    }
}
