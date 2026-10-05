<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports;

use App\Filament\Resources\TipsYearlyEomGomReports\Pages\CreateTipsYearlyEomGomReport;
use App\Filament\Resources\TipsYearlyEomGomReports\Pages\EditTipsYearlyEomGomReport;
use App\Filament\Resources\TipsYearlyEomGomReports\Pages\ListTipsYearlyEomGomReports;
use App\Filament\Resources\TipsYearlyEomGomReports\Pages\ViewTipsYearlyEomGomReport;
use App\Filament\Resources\TipsYearlyEomGomReports\Schemas\TipsYearlyEomGomReportForm;
use App\Filament\Resources\TipsYearlyEomGomReports\Tables\TipsYearlyEomGomReportsTable;
use App\Models\TipsYearlyEomGomReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TipsYearlyEomGomReportResource extends Resource
{
    protected static ?string $model = TipsYearlyEomGomReport::class;

    protected static ?string $modelLabel = 'Yearly EOM & GOM Report';

    protected static ?string $pluralModelLabel = 'Yearly EOM & GOM Reports';

    protected static ?string $navigationLabel = 'Yearly EOM & GOM';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Trophy;

    protected static string|UnitEnum|null $navigationGroup = 'Tips';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return TipsYearlyEomGomReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipsYearlyEomGomReportsTable::configure($table);
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
            'index' => ListTipsYearlyEomGomReports::route('/'),
            'create' => CreateTipsYearlyEomGomReport::route('/create'),
            'view' => ViewTipsYearlyEomGomReport::route('/{record}'),
            'edit' => EditTipsYearlyEomGomReport::route('/{record}/edit'),
        ];
    }
}
