<?php

namespace App\Filament\Resources\SalaryIncrementRequests;

use App\Filament\Resources\SalaryIncrementRequests\Pages\ListSalaryIncrementRequests;
use App\Filament\Resources\SalaryIncrementRequests\Schemas\SalaryIncrementRequestForm;
use App\Filament\Resources\SalaryIncrementRequests\Tables\SalaryIncrementRequestsTable;
use App\Models\SalaryIncrementRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SalaryIncrementRequestResource extends Resource
{
    protected static ?string $model = SalaryIncrementRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Banknotes;

    protected static string|UnitEnum|null $navigationGroup = 'HR & Admin';

    protected static ?string $navigationLabel = 'Salary Increment Requests';

    protected static ?string $pluralModelLabel = 'Salary Increment Requests';

    protected static ?string $modelLabel = 'Salary Increment Request';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return SalaryIncrementRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalaryIncrementRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalaryIncrementRequests::route('/'),
        ];
    }
}
