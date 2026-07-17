<?php

namespace App\Filament\Resources\TipsAdjustments;

use App\Filament\Resources\TipsAdjustments\Pages\CreateTipsAdjustment;
use App\Filament\Resources\TipsAdjustments\Pages\EditTipsAdjustment;
use App\Filament\Resources\TipsAdjustments\Pages\ListTipsAdjustments;
use App\Filament\Resources\TipsAdjustments\Schemas\TipsAdjustmentForm;
use App\Filament\Resources\TipsAdjustments\Tables\TipsAdjustmentsTable;
use App\Models\TipsAdjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TipsAdjustmentResource extends Resource
{
    protected static ?string $model = TipsAdjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CurrencyDollar;

    protected static ?string $recordTitleAttribute = 'employee_id';

    protected static string|UnitEnum|null $navigationGroup = 'HR & Admin';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TipsAdjustmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipsAdjustmentsTable::configure($table);
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
            'index' => ListTipsAdjustments::route('/'),
            'create' => CreateTipsAdjustment::route('/create'),
            'edit' => EditTipsAdjustment::route('/{record}/edit'),
        ];
    }
}
