<?php

namespace App\Filament\Resources\MonthlyManualAttendances;

use App\Filament\Resources\MonthlyManualAttendances\Pages\CreateMonthlyManualAttendance;
use App\Filament\Resources\MonthlyManualAttendances\Pages\EditMonthlyManualAttendance;
use App\Filament\Resources\MonthlyManualAttendances\Pages\ListMonthlyManualAttendances;
use App\Filament\Resources\MonthlyManualAttendances\Pages\ViewMonthlyManualAttendance;
use App\Filament\Resources\MonthlyManualAttendances\Schemas\MonthlyManualAttendanceForm;
use App\Filament\Resources\MonthlyManualAttendances\Tables\MonthlyManualAttendancesTable;
use App\Models\MonthlyManualAttendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MonthlyManualAttendanceResource extends Resource
{
    protected static ?string $model = MonthlyManualAttendance::class;

    protected static ?string $modelLabel = 'Monthly Manual Attendance';

    protected static ?string $pluralModelLabel = 'Monthly Manual Attendances';

    protected static ?string $navigationLabel = 'Monthly Manual Attendances';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Roster Management';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return MonthlyManualAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonthlyManualAttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyManualAttendances::route('/'),
            'create' => CreateMonthlyManualAttendance::route('/create'),
            'view' => ViewMonthlyManualAttendance::route('/{record}'),
            'edit' => EditMonthlyManualAttendance::route('/{record}/edit'),
        ];
    }
}
