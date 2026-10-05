<?php

namespace App\Filament\Resources\TipsEomGomExcludedDepartments;

use App\Filament\Resources\TipsEomGomExcludedDepartments\Pages\ListTipsEomGomExcludedDepartments;
use App\Filament\Resources\TipsEomGomExcludedDepartments\Schemas\TipsEomGomExcludedDepartmentForm;
use App\Filament\Resources\TipsEomGomExcludedDepartments\Tables\TipsEomGomExcludedDepartmentsTable;
use App\Models\TipsEomGomExcludedDepartment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TipsEomGomExcludedDepartmentResource extends Resource
{
    protected static ?string $model = TipsEomGomExcludedDepartment::class;

    protected static ?string $modelLabel = 'Excluded Department';

    protected static ?string $pluralModelLabel = 'EOM/GOM Excluded Departments';

    protected static ?string $navigationLabel = 'EOM/GOM Excluded Departments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::NoSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Tips';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return TipsEomGomExcludedDepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipsEomGomExcludedDepartmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipsEomGomExcludedDepartments::route('/'),
        ];
    }
}
