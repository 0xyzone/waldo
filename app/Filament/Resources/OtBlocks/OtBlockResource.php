<?php

namespace App\Filament\Resources\OtBlocks;

use App\Filament\Resources\OtBlocks\Pages\ManageOtBlocks;
use App\Models\OtBlock;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OtBlockResource extends Resource
{
    protected static ?string $model = OtBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Overtime';

    protected static ?string $navigationLabel = 'OT Blocks';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Block Name')
                    ->placeholder('e.g. A, B, C, D, Wang, CoCo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Block Name')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('overtime_entries_count')
                    ->counts('overtimeEntries')
                    ->label('Entries'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOtBlocks::route('/'),
        ];
    }
}
