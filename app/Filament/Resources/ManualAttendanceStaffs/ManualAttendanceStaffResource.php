<?php

namespace App\Filament\Resources\ManualAttendanceStaffs;

use App\Filament\Resources\ManualAttendanceStaffs\Pages\CreateManualAttendanceStaff;
use App\Filament\Resources\ManualAttendanceStaffs\Pages\EditManualAttendanceStaff;
use App\Filament\Resources\ManualAttendanceStaffs\Pages\ListManualAttendanceStaffs;
use App\Filament\Resources\ManualAttendanceStaffs\Schemas\ManualAttendanceStaffForm;
use App\Filament\Resources\ManualAttendanceStaffs\Tables\ManualAttendanceStaffsTable;
use App\Models\ManualAttendanceStaff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ManualAttendanceStaffResource extends Resource
{
    protected static ?string $model = ManualAttendanceStaff::class;

    protected static ?string $slug = 'manual-attendance-staff';

    protected static ?string $modelLabel = 'Manual Attendance Staff';

    protected static ?string $pluralModelLabel = 'Manual Attendance Staff';

    protected static ?string $navigationLabel = 'Manual Attendance Staff';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::UserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Roster Management';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return ManualAttendanceStaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManualAttendanceStaffsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManualAttendanceStaffs::route('/'),
            'create' => CreateManualAttendanceStaff::route('/create'),
            'edit' => EditManualAttendanceStaff::route('/{record}/edit'),
        ];
    }
}
