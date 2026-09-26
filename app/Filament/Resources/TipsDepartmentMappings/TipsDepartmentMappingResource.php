<?php

namespace App\Filament\Resources\TipsDepartmentMappings;

use App\Filament\Resources\TipsDepartmentMappings\Pages\CreateTipsDepartmentMapping;
use App\Filament\Resources\TipsDepartmentMappings\Pages\EditTipsDepartmentMapping;
use App\Filament\Resources\TipsDepartmentMappings\Pages\ListTipsDepartmentMappings;
use App\Filament\Resources\TipsDepartmentMappings\Schemas\TipsDepartmentMappingForm;
use App\Filament\Resources\TipsDepartmentMappings\Tables\TipsDepartmentMappingsTable;
use App\Models\TipsDepartmentMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TipsDepartmentMappingResource extends Resource
{
    protected static ?string $model = TipsDepartmentMapping::class;

    protected static ?string $modelLabel = 'Page Department Mapping';

    protected static ?string $navigationLabel = 'Master Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Cog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Tips';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TipsDepartmentMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipsDepartmentMappingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipsDepartmentMappings::route('/'),
            'create' => CreateTipsDepartmentMapping::route('/create'),
            'edit' => EditTipsDepartmentMapping::route('/{record}/edit'),
        ];
    }
}
