<?php

namespace App\Filament\Resources\RoyaltyExemptEmployees;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\RoyaltyExemptEmployees\Pages\CreateRoyaltyExemptEmployee;
use App\Filament\Resources\RoyaltyExemptEmployees\Pages\EditRoyaltyExemptEmployee;
use App\Filament\Resources\RoyaltyExemptEmployees\Pages\ListRoyaltyExemptEmployees;
use App\Filament\Resources\RoyaltyExemptEmployees\Schemas\RoyaltyExemptEmployeeForm;
use App\Filament\Resources\RoyaltyExemptEmployees\Tables\RoyaltyExemptEmployeesTable;
use App\Models\RoyaltyExemptEmployee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RoyaltyExemptEmployeeResource extends Resource
{
    use HasResourceShield;

    protected static ?string $model = RoyaltyExemptEmployee::class;

    protected static ?string $modelLabel = 'Exempt Employee';

    protected static ?string $pluralModelLabel = 'Exempt Employees';

    protected static ?string $navigationLabel = 'Exempt Staff';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserMinus;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::UserMinus;

    protected static string|UnitEnum|null $navigationGroup = 'Royalty Bonus Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RoyaltyExemptEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoyaltyExemptEmployeesTable::configure($table);
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
            'index' => ListRoyaltyExemptEmployees::route('/'),
            'create' => CreateRoyaltyExemptEmployee::route('/create'),
            'edit' => EditRoyaltyExemptEmployee::route('/{record}/edit'),
        ];
    }
}
