<?php

namespace App\Filament\Resources\MapUsers;

use App\Filament\Resources\MapUsers\Pages\CreateMapUser;
use App\Filament\Resources\MapUsers\Pages\EditMapUser;
use App\Filament\Resources\MapUsers\Pages\ListMapUsers;
use App\Filament\Resources\MapUsers\Schemas\MapUserForm;
use App\Filament\Resources\MapUsers\Tables\MapUsersTable;
use App\Models\MapUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MapUserResource extends Resource
{
    protected static ?string $model = MapUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationParentItem = 'Biometric Allotments';

    protected static string|UnitEnum|null $navigationGroup = 'IT';

    protected static ?string $recordTitleAttribute = 'setby_name';

    public static function form(Schema $schema): Schema
    {
        return MapUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MapUsersTable::configure($table);
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
            'index' => ListMapUsers::route('/'),
            'create' => CreateMapUser::route('/create'),
            'edit' => EditMapUser::route('/{record}/edit'),
        ];
    }
}
