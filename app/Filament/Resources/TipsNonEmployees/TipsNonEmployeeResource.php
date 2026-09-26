<?php

namespace App\Filament\Resources\TipsNonEmployees;

use App\Filament\Resources\TipsNonEmployees\Pages\CreateTipsNonEmployee;
use App\Filament\Resources\TipsNonEmployees\Pages\EditTipsNonEmployee;
use App\Filament\Resources\TipsNonEmployees\Pages\ListTipsNonEmployees;
use App\Filament\Resources\TipsNonEmployees\Schemas\TipsNonEmployeeForm;
use App\Filament\Resources\TipsNonEmployees\Tables\TipsNonEmployeesTable;
use App\Models\TipsNonEmployee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TipsNonEmployeeResource extends Resource
{
    protected static ?string $model = TipsNonEmployee::class;

    protected static ?string $modelLabel = 'Non-Employee';

    protected static ?string $pluralModelLabel = 'Non-Employees';

    protected static ?string $navigationLabel = 'Non-Employees';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::UserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Tips';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TipsNonEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipsNonEmployeesTable::configure($table);
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
            'index' => ListTipsNonEmployees::route('/'),
            'create' => CreateTipsNonEmployee::route('/create'),
            'edit' => EditTipsNonEmployee::route('/{record}/edit'),
        ];
    }
}
