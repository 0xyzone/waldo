<?php

namespace App\Filament\Resources\NametagReturns;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\NametagReturns\Pages\CreateNametagReturn;
use App\Filament\Resources\NametagReturns\Pages\EditNametagReturn;
use App\Filament\Resources\NametagReturns\Pages\ListNametagReturns;
use App\Filament\Resources\NametagReturns\Schemas\NametagReturnForm;
use App\Filament\Resources\NametagReturns\Tables\NametagReturnsTable;
use App\Models\NametagReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NametagReturnResource extends Resource
{
    use HasResourceShield;

    protected static ?string $model = NametagReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ArrowUturnLeft;

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Purchase & Store';

    protected static ?string $navigationLabel = 'NameTag Returns';

    protected static ?string $pluralModelLabel = 'NameTag Returns';

    protected static ?string $modelLabel = 'NameTag Return';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return NametagReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NametagReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNametagReturns::route('/'),
            // 'create' => CreateNametagReturn::route('/create'),
            // 'edit' => EditNametagReturn::route('/{record}/edit'),
        ];
    }
}
