<?php

namespace App\Filament\Resources\EmployeeTransfers;

use App\Filament\Resources\EmployeeTransfers\Pages\ManageEmployeeTransfers;
use App\Filament\Resources\EmployeeTransfers\Schemas\EmployeeTransferForm;
use App\Filament\Resources\EmployeeTransfers\Tables\EmployeeTransfersTable;
use App\Models\EmployeeTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeTransferResource extends Resource
{
    protected static ?string $model = EmployeeTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Transfers';

    protected static ?string $modelLabel = 'Transfer';

    protected static ?string $pluralModelLabel = 'Transfers';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return EmployeeTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmployeeTransfers::route('/'),
        ];
    }
}
