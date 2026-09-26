<?php

namespace App\Filament\Resources\TipsReports;

use App\Filament\Resources\TipsReports\Pages\CreateTipsReport;
use App\Filament\Resources\TipsReports\Pages\EditTipsReport;
use App\Filament\Resources\TipsReports\Pages\ListTipsReports;
use App\Filament\Resources\TipsReports\Pages\ViewTipsReport;
use App\Filament\Resources\TipsReports\Schemas\TipsReportForm;
use App\Filament\Resources\TipsReports\Tables\TipsReportsTable;
use App\Models\TipsReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TipsReportResource extends Resource
{
    protected static ?string $model = TipsReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyBangladeshi;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentCurrencyBangladeshi;

    protected static string|UnitEnum|null $navigationGroup = 'Tips';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TipsReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipsReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canEdit(Model $record): bool
    {
        if ($record instanceof TipsReport && $record->isValidated()) {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipsReports::route('/'),
            'create' => CreateTipsReport::route('/create'),
            'view' => ViewTipsReport::route('/{record}'),
            'edit' => EditTipsReport::route('/{record}/edit'),
        ];
    }
}
