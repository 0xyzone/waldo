<?php

namespace App\Filament\Resources\IdCardPrintReports;

use App\Filament\Resources\IdCardPrintReports\Pages\CreateIdCardPrintReport;
use App\Filament\Resources\IdCardPrintReports\Pages\EditIdCardPrintReport;
use App\Filament\Resources\IdCardPrintReports\Pages\ListIdCardPrintReports;
use App\Filament\Resources\IdCardPrintReports\Pages\ViewIdCardPrintReport;
use App\Filament\Resources\IdCardPrintReports\Schemas\IdCardPrintReportForm;
use App\Filament\Resources\IdCardPrintReports\Tables\IdCardPrintReportsTable;
use App\Models\IdCardPrintReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IdCardPrintReportResource extends Resource
{
    protected static ?string $model = IdCardPrintReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Identification;

    protected static string|UnitEnum|null $navigationGroup = 'IT';

    protected static ?string $navigationLabel = 'ID Card Prints';

    protected static ?string $pluralModelLabel = 'ID Card Prints';

    protected static ?string $modelLabel = 'ID Card Print';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return IdCardPrintReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IdCardPrintReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdCardPrintReports::route('/'),
            // 'create' => CreateIdCardPrintReport::route('/create'),
            'view' => ViewIdCardPrintReport::route('/{record}'),
            // 'edit' => EditIdCardPrintReport::route('/{record}/edit'),
        ];
    }
}
