<?php

namespace App\Filament\Resources\IdCardRequests;

use App\Filament\Resources\IdCardRequests\Pages\CreateIdCardRequest;
use App\Filament\Resources\IdCardRequests\Pages\EditIdCardRequest;
use App\Filament\Resources\IdCardRequests\Pages\ListIdCardRequests;
use App\Filament\Resources\IdCardRequests\Schemas\IdCardRequestForm;
use App\Filament\Resources\IdCardRequests\Tables\IdCardRequestsTable;
use App\Models\IdCardRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IdCardRequestResource extends Resource
{
    protected static ?string $model = IdCardRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Identification;

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = 'IT';

    protected static ?string $navigationLabel = 'ID Card Requests';

    protected static ?string $pluralModelLabel = 'ID Card Requests';

    protected static ?string $recordTitleAttribute = 'employee_name';

    public static function form(Schema $schema): Schema
    {
        return IdCardRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IdCardRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdCardRequests::route('/'),
            'create' => CreateIdCardRequest::route('/create'),
            'edit' => EditIdCardRequest::route('/{record}/edit'),
        ];
    }
}
