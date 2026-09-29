<?php

namespace App\Filament\Resources\ManualAttendanceEmployees;

use App\Filament\Resources\ManualAttendanceEmployees\Pages\CreateManualAttendanceEmployee;
use App\Filament\Resources\ManualAttendanceEmployees\Pages\EditManualAttendanceEmployee;
use App\Filament\Resources\ManualAttendanceEmployees\Pages\ListManualAttendanceEmployees;
use App\Filament\Resources\ManualAttendanceEmployees\Schemas\ManualAttendanceEmployeeForm;
use App\Filament\Resources\ManualAttendanceEmployees\Tables\ManualAttendanceEmployeesTable;
use App\Models\ManualAttendanceEmployee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ManualAttendanceEmployeeResource extends Resource
{
    protected static ?string $model = ManualAttendanceEmployee::class;

    protected static ?string $modelLabel = 'Manual Attendance Employee';

    protected static ?string $pluralModelLabel = 'Manual Attendance Employees';

    protected static ?string $navigationLabel = 'Manual Attendance Employees';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Roster Management';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return ManualAttendanceEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManualAttendanceEmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManualAttendanceEmployees::route('/'),
            'create' => CreateManualAttendanceEmployee::route('/create'),
            'edit' => EditManualAttendanceEmployee::route('/{record}/edit'),
        ];
    }
}
