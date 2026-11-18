<?php

namespace App\Filament\Resources\OvertimeReports;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\OvertimeReports\Pages\CreateOvertimeReport;
use App\Filament\Resources\OvertimeReports\Pages\EditOvertimeReport;
use App\Filament\Resources\OvertimeReports\Pages\ListOvertimeReports;
use App\Filament\Resources\OvertimeReports\Pages\ViewOvertimeReport;
use App\Models\OvertimeEntry;
use App\Models\OvertimeReport;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OvertimeReportResource extends Resource
{
    use HasResourceShield;

    public static function getShieldResourcePermissions(): array
    {
        return [
            'EnterOtValues' => 'Enter OT Values in OT Sheet',
        ];
    }

    protected static ?string $model = OvertimeReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Overtime';

    protected static ?string $navigationLabel = 'OT Monthly Reports';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('month')
                    ->label('Month')
                    ->options(collect(range(1, 12))->mapWithKeys(fn (int $m) => [$m => Carbon::create()->month($m)->format('F')]))
                    ->default(now()->month)
                    ->required(),
                Select::make('year')
                    ->label('Year')
                    ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn (int $y) => [$y => (string) $y]))
                    ->default(now()->year)
                    ->required(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('period_label')
                    ->label('Period')
                    ->state(fn (OvertimeReport $record): string => Carbon::create($record->year, $record->month, 1)->format('F Y'))
                    ->sortable(['year', 'month'])
                    ->weight('bold'),
                TextColumn::make('total_hours')
                    ->label('Total OT Hours')
                    ->state(fn (OvertimeReport $record): string => number_format(
                        (float) OvertimeEntry::whereMonth('ot_date', $record->month)->whereYear('ot_date', $record->year)->sum('hours'),
                        2
                    ).' hrs'),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->recordActions([
                ViewAction::make()->label('Open Sheet'),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOvertimeReports::route('/'),
            'create' => CreateOvertimeReport::route('/create'),
            'view' => ViewOvertimeReport::route('/{record}'),
            'edit' => EditOvertimeReport::route('/{record}/edit'),
        ];
    }
}
