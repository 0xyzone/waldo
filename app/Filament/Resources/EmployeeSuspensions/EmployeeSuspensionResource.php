<?php

namespace App\Filament\Resources\EmployeeSuspensions;

use App\Filament\Resources\EmployeeSuspensions\Pages\CreateEmployeeSuspension;
use App\Filament\Resources\EmployeeSuspensions\Pages\EditEmployeeSuspension;
use App\Filament\Resources\EmployeeSuspensions\Pages\ListEmployeeSuspensions;
use App\Filament\Resources\EmployeeSuspensions\Schemas\EmployeeSuspensionForm;
use App\Filament\Resources\EmployeeSuspensions\Tables\EmployeeSuspensionsTable;
use App\Models\EmployeeSuspension;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeSuspensionResource extends Resource
{
    protected static ?string $model = EmployeeSuspension::class;

    protected static ?string $navigationLabel = 'Suspensions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ExclamationTriangle;

    protected static ?int $navigationSort = 7;

    protected static string|UnitEnum|null $navigationGroup = 'HR & Admin';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return EmployeeSuspensionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeSuspensionsTable::configure($table);
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
            'index' => ListEmployeeSuspensions::route('/'),
            // 'create' => CreateEmployeeSuspension::route('/create'),
            // 'edit' => EditEmployeeSuspension::route('/{record}/edit'),
        ];
    }
}
