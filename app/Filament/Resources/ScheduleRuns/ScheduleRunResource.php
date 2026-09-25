<?php

namespace App\Filament\Resources\ScheduleRuns;

use App\Filament\Resources\ScheduleRuns\Pages\ListScheduleRuns;
use App\Filament\Resources\ScheduleRuns\Schemas\ScheduleRunForm;
use App\Filament\Resources\ScheduleRuns\Tables\ScheduleRunsTable;
use App\Models\ScheduleRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScheduleRunResource extends Resource
{
    protected static ?string $model = ScheduleRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Schedule Runs';

    protected static ?string $modelLabel = 'Schedule Run';

    protected static ?string $pluralModelLabel = 'Schedule Runs';

    protected static string|UnitEnum|null $navigationGroup = 'System Settings';

    protected static ?int $navigationSort = 20;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScheduleRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduleRunsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduleRuns::route('/'),
        ];
    }
}
