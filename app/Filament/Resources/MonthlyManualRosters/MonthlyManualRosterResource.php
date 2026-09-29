<?php

namespace App\Filament\Resources\MonthlyManualRosters;

use App\Filament\Resources\MonthlyManualRosters\Pages\CreateMonthlyManualRoster;
use App\Filament\Resources\MonthlyManualRosters\Pages\EditMonthlyManualRoster;
use App\Filament\Resources\MonthlyManualRosters\Pages\ListMonthlyManualRosters;
use App\Filament\Resources\MonthlyManualRosters\Pages\ViewMonthlyManualRoster;
use App\Filament\Resources\MonthlyManualRosters\Schemas\MonthlyManualRosterForm;
use App\Filament\Resources\MonthlyManualRosters\Tables\MonthlyManualRostersTable;
use App\Models\MonthlyManualRoster;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class MonthlyManualRosterResource extends Resource
{
    protected static ?string $model = MonthlyManualRoster::class;

    protected static ?string $modelLabel = 'Monthly Manual Roster';

    protected static ?string $pluralModelLabel = 'Monthly Manual Rosters';

    protected static ?string $navigationLabel = 'Monthly Manual Rosters';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Roster Management';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return MonthlyManualRosterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonthlyManualRostersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyManualRosters::route('/'),
            'create' => CreateMonthlyManualRoster::route('/create'),
            'view' => ViewMonthlyManualRoster::route('/{record}'),
            'edit' => EditMonthlyManualRoster::route('/{record}/edit'),
        ];
    }
}
