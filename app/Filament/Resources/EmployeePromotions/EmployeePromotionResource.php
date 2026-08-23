<?php

namespace App\Filament\Resources\EmployeePromotions;

use App\Filament\Resources\EmployeePromotions\Pages\ManageEmployeePromotions;
use App\Filament\Resources\EmployeePromotions\Schemas\EmployeePromotionForm;
use App\Filament\Resources\EmployeePromotions\Tables\EmployeePromotionsTable;
use App\Models\EmployeePromotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmployeePromotionResource extends Resource
{
    protected static ?string $model = EmployeePromotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Promotions';

    protected static ?string $modelLabel = 'Promotion';

    protected static ?string $pluralModelLabel = 'Promotions';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return EmployeePromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeePromotionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmployeePromotions::route('/'),
        ];
    }
}
