<?php

namespace App\Filament\Resources\Adjustments;

use App\Filament\Resources\Adjustments\Pages\CreateAdjustment;
use App\Filament\Resources\Adjustments\Pages\EditAdjustment;
use App\Filament\Resources\Adjustments\Pages\ListAdjustments;
use App\Filament\Resources\Adjustments\Schemas\AdjustmentForm;
use App\Filament\Resources\Adjustments\Tables\AdjustmentsTable;
use App\Models\Adjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdjustmentResource extends Resource
{
    protected static ?string $model = Adjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Calculator;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public static function getNavigationBadge(): ?string
    {
        $count = Adjustment::query()
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pending Adjustments';
    }

    public static function form(Schema $schema): Schema
    {
        return AdjustmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdjustmentsTable::configure($table);
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
            'index' => ListAdjustments::route('/'),
            // 'create' => CreateAdjustment::route('/create'),
            // 'edit' => EditAdjustment::route('/{record}/edit'),
        ];
    }
}
