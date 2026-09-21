<?php

namespace App\Filament\Resources\NametagDistributions;

use App\Filament\Resources\NametagDistributions\Pages\CreateNametagDistribution;
use App\Filament\Resources\NametagDistributions\Pages\EditNametagDistribution;
use App\Filament\Resources\NametagDistributions\Pages\ListNametagDistributions;
use App\Filament\Resources\NametagDistributions\Schemas\NametagDistributionForm;
use App\Filament\Resources\NametagDistributions\Tables\NametagDistributionsTable;
use App\Models\NametagDistribution;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NametagDistributionResource extends Resource
{
    protected static ?string $model = NametagDistribution::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Tag;

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Purchase & Store';

    protected static ?string $navigationLabel = 'NameTag Distributions';

    protected static ?string $pluralModelLabel = 'NameTag Distributions';

    protected static ?string $modelLabel = 'NameTag Distribution';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return NametagDistributionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NametagDistributionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNametagDistributions::route('/'),
            // 'create' => CreateNametagDistribution::route('/create'),
            // 'edit' => EditNametagDistribution::route('/{record}/edit'),
        ];
    }
}
