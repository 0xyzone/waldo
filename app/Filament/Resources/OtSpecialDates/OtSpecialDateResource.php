<?php

namespace App\Filament\Resources\OtSpecialDates;

use App\Filament\Resources\OtSpecialDates\Pages\ManageOtSpecialDates;
use App\Models\OtSpecialDate;
use App\Models\OvertimeEntry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OtSpecialDateResource extends Resource
{
    protected static ?string $model = OtSpecialDate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Overtime';

    protected static ?string $navigationLabel = 'Special OT Dates (×2)';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Special OT Date')
                    ->native(false)
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('description')
                    ->label('Reason / Holiday Name')
                    ->placeholder('e.g. Dashain, Public Holiday, Special Event')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Reason / Holiday')
                    ->searchable()
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (OtSpecialDate $record): void {
                        OvertimeEntry::whereDate('ot_date', $record->date)->update(['is_special_ot' => true]);
                    }),
                DeleteAction::make()
                    ->after(function (OtSpecialDate $record): void {
                        OvertimeEntry::whereDate('ot_date', $record->date)->update(['is_special_ot' => false]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOtSpecialDates::route('/'),
        ];
    }
}
