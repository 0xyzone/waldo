<?php

namespace App\Filament\Resources\RoyaltyReports;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\RoyaltyReports\Pages\CreateRoyaltyReport;
use App\Filament\Resources\RoyaltyReports\Pages\EditRoyaltyReport;
use App\Filament\Resources\RoyaltyReports\Pages\ListRoyaltyReports;
use App\Filament\Resources\RoyaltyReports\Pages\ViewRoyaltyReport;
use App\Filament\Resources\RoyaltyReports\Schemas\RoyaltyReportForm;
use App\Filament\Resources\RoyaltyReports\Tables\RoyaltyReportsTable;
use App\Models\RoyaltyReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RoyaltyReportResource extends Resource
{
    use HasResourceShield;

    public static function getShieldResourcePermissions(): array
    {
        return [
            'ValidateReport' => 'Validate & Lock Report',
            'UnlockReport' => 'Unlock Report',
        ];
    }

    protected static ?string $model = RoyaltyReport::class;

    protected static ?string $modelLabel = 'Royalty Report';

    protected static ?string $pluralModelLabel = 'Royalty Reports';

    protected static ?string $navigationLabel = 'Royalty Reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Sparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Royalty Bonus Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RoyaltyReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoyaltyReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canEdit(Model $record): bool
    {
        /** @var RoyaltyReport $record */
        if ($record->isValidated()) {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        /** @var RoyaltyReport $record */
        if ($record->isValidated()) {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoyaltyReports::route('/'),
            'create' => CreateRoyaltyReport::route('/create'),
            'view' => ViewRoyaltyReport::route('/{record}'),
            'edit' => EditRoyaltyReport::route('/{record}/edit'),
        ];
    }
}
