<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BiometricAllotment\Pages\ListBiometricAllotments;
use App\Filament\Resources\BiometricAllotment\Schemas\BiometricAllotmentForm;
use App\Filament\Resources\BiometricAllotment\Tables\BiometricAllotmentsTable;
use App\Models\BiometricAllotment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BiometricAllotmentResource extends Resource
{
    protected static ?string $model = BiometricAllotment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::FingerPrint;

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'IT';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BiometricAllotmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BiometricAllotmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBiometricAllotments::route('/'),
        ];
    }
}
