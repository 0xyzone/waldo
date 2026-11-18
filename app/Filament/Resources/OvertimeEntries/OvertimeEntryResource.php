<?php

namespace App\Filament\Resources\OvertimeEntries;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\OvertimeEntries\Pages\ManageOvertimeEntries;
use App\Models\OtBlock;
use App\Models\OvertimeEntry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class OvertimeEntryResource extends Resource
{
    use HasResourceShield;

    protected static ?string $model = OvertimeEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Overtime';

    protected static ?string $navigationLabel = 'OT Entries';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasEntryAccess();
    }

    public static function canViewAny(): bool
    {
        return static::hasEntryAccess();
    }

    protected static function hasEntryAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(config('filament-shield.super_admin.name', 'super_admin'))
            || $user->can('ViewAny:OvertimeEntry')
            || $user->can('View:OvertimeEntry')
            || $user->can('Update:OvertimeEntry');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper($record->employee_code).' | '.$record->name)
                    ->searchable(['name', 'employee_code'])
                    ->preload()
                    ->required(),
                DatePicker::make('ot_date')
                    ->label('OT Date')
                    ->native(false)
                    ->default(now())
                    ->required(),
                Select::make('ot_block_id')
                    ->label('Block')
                    ->options(fn () => OtBlock::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('hours')
                    ->label('OT Hours')
                    ->numeric()
                    ->step(0.5)
                    ->minValue(0.5)
                    ->maxValue(24)
                    ->required(),
                Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ot_date', 'desc')
            ->columns([
                TextColumn::make('ot_date')
                    ->label('Date')
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('employee_id')
                    ->label('Emp. Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('block.name')
                    ->label('Block')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('hours')
                    ->label('Hours')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                IconColumn::make('is_special_ot')
                    ->label('Special OT (×2)')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('ot_block_id')
                    ->label('Block')
                    ->relationship('block', 'name'),
                TernaryFilter::make('is_special_ot')
                    ->label('OT Type')
                    ->placeholder('All Types')
                    ->trueLabel('Special OT (×2)')
                    ->falseLabel('Normal OT (×1.5)'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOvertimeEntries::route('/'),
        ];
    }
}
