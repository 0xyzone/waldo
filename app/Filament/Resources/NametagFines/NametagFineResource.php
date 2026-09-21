<?php

namespace App\Filament\Resources\NametagFines;

use App\Filament\Resources\NametagFines\Pages\CreateNametagFine;
use App\Filament\Resources\NametagFines\Pages\EditNametagFine;
use App\Filament\Resources\NametagFines\Pages\ListNametagFines;
use App\Filament\Resources\NametagFines\Schemas\NametagFineForm;
use App\Filament\Resources\NametagFines\Tables\NametagFinesTable;
use App\Models\NametagFine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NametagFineResource extends Resource
{
    protected static ?string $model = NametagFine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ExclamationTriangle;

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Purchase & Store';

    protected static ?string $navigationLabel = 'NameTag Fines';

    protected static ?string $pluralModelLabel = 'NameTag Fines';

    protected static ?string $modelLabel = 'NameTag Fine';

    protected static ?string $recordTitleAttribute = 'employee_id';

    public static function form(Schema $schema): Schema
    {
        return NametagFineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NametagFinesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNametagFines::route('/'),
            // 'create' => CreateNametagFine::route('/create'),
            // 'edit' => EditNametagFine::route('/{record}/edit'),
        ];
    }
}
