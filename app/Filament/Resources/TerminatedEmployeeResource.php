<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TerminatedEmployees\Pages\CreateTerminatedEmployee;
use App\Filament\Resources\TerminatedEmployees\Pages\EditTerminatedEmployee;
use App\Filament\Resources\TerminatedEmployees\Pages\ListTerminatedEmployees;
use App\Filament\Resources\TerminatedEmployees\Schemas\TerminatedEmployeeForm;
use App\Filament\Resources\TerminatedEmployees\Tables\TerminatedEmployeesTable;
use App\Models\TerminatedEmployee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TerminatedEmployeeResource extends Resource
{
    protected static ?string $model = TerminatedEmployee::class;

    protected static ?string $navigationLabel = 'Terminated Employees';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserMinus;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::UserMinus;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return TerminatedEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TerminatedEmployeesTable::configure($table);
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
            'index' => ListTerminatedEmployees::route('/'),
            // 'create' => CreateTerminatedEmployee::route('/create'),
            // 'edit' => EditTerminatedEmployee::route('/{record}/edit'),
        ];
    }
}
