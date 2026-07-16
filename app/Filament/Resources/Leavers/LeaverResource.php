<?php

namespace App\Filament\Resources\Leavers;

use App\Filament\Resources\Leavers\Pages\CreateLeaver;
use App\Filament\Resources\Leavers\Pages\EditLeaver;
use App\Filament\Resources\Leavers\Pages\ListLeavers;
use App\Filament\Resources\Leavers\Schemas\LeaverForm;
use App\Filament\Resources\Leavers\Tables\LeaversTable;
use App\Models\Leaver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaverResource extends Resource
{
    protected static ?string $model = Leaver::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserMinus;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::UserMinus;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return LeaverForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaversTable::configure($table);
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
            'index' => ListLeavers::route('/'),
            // 'create' => CreateLeaver::route('/create'),
            // 'edit' => EditLeaver::route('/{record}/edit'),
        ];
    }
}
