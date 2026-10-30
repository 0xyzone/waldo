<?php

namespace App\Filament\Resources\RoyaltyHeldBalances;

use Agroezinger\FilamentShieldEnhanced\Traits\HasResourceShield;
use App\Filament\Resources\RoyaltyHeldBalances\Pages\ListRoyaltyHeldBalances;
use App\Filament\Resources\RoyaltyHeldBalances\Pages\ViewRoyaltyHeldBalance;
use App\Models\Employee;
use App\Models\RoyaltyHeldRelease;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RoyaltyHeldBalanceResource extends Resource
{
    use HasResourceShield;

    public static function getShieldResourcePermissions(): array
    {
        return [
            'view' => 'View Held Balances',
            'release' => 'Release Held Amount',
        ];
    }

    protected static ?string $model = Employee::class;

    protected static ?string $modelLabel = 'Held Balance';

    protected static ?string $pluralModelLabel = 'Royalty Held Balances';

    protected static ?string $navigationLabel = 'Held Balances';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::LockClosed;

    protected static string|UnitEnum|null $navigationGroup = 'Royalty Bonus Management';

    protected static ?string $slug = 'royalty-held-balances';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('royaltyReportItems', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('payout_status', 'Hold')
                            ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
                    });
                })
                    ->orWhereHas('royaltyHeldReleases')
                    ->with([
                        'department',
                        'designation',
                        'royaltyReportItems' => function ($q) {
                            $q->where(function ($sub) {
                                $sub->where('payout_status', 'Hold')
                                    ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
                            })->with('report');
                        },
                        'royaltyHeldReleases',
                    ]);
            })
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('employee_code')
                    ->label('Code')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('designation.name')
                    ->label('Designation')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('total_held')
                    ->label('Total Held')
                    ->state(function (Employee $record): float {
                        return (float) $record->royaltyReportItems->sum(function ($item) {
                            if ($item->payout_status === 'Hold') {
                                return (float) $item->monthly_max_payout;
                            }

                            return max(0.0, (float) $item->monthly_max_payout - (float) $item->final_payout);
                        });
                    })
                    ->money('NPR')
                    ->weight('bold')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('total_released')
                    ->label('Total Released')
                    ->state(fn (Employee $record): float => (float) $record->royaltyHeldReleases->sum('amount'))
                    ->money('NPR')
                    ->color('info')
                    ->sortable(),

                TextColumn::make('remaining_balance')
                    ->label('Remaining Balance')
                    ->state(function (Employee $record): float {
                        $held = (float) $record->royaltyReportItems->sum(function ($item) {
                            if ($item->payout_status === 'Hold') {
                                return (float) $item->monthly_max_payout;
                            }

                            return max(0.0, (float) $item->monthly_max_payout - (float) $item->final_payout);
                        });
                        $released = (float) $record->royaltyHeldReleases->sum('amount');

                        return max(0.0, $held - $released);
                    })
                    ->money('NPR')
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),

                TextColumn::make('held_months_count')
                    ->label('Deduction Months')
                    ->state(fn (Employee $record): int => $record->royaltyReportItems->count())
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View Details'),

                Action::make('printSheet')
                    ->label('Print Sheet')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->url(fn (Employee $record) => route('royalty.held-balances.print-employee', ['employee' => $record->employee_code]))
                    ->openUrlInNewTab(),

                Action::make('releaseAmount')
                    ->label('Release Amount')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->modalHeading(fn (Employee $record) => "Release Held Royalty Bonus - {$record->employee_code} ({$record->name})")
                    ->modalDescription(function (Employee $record) {
                        $held = (float) $record->royaltyReportItems->sum(function ($item) {
                            if ($item->payout_status === 'Hold') {
                                return (float) $item->monthly_max_payout;
                            }

                            return max(0.0, (float) $item->monthly_max_payout - (float) $item->final_payout);
                        });
                        $released = (float) $record->royaltyHeldReleases->sum('amount');
                        $remaining = max(0.0, $held - $released);

                        return 'Current Available Balance: Rs. '.number_format($remaining, 2).'. Management can release full or partial amount according to merit points collected during the year.';
                    })
                    ->visible(function (Employee $record) {
                        $held = (float) $record->royaltyReportItems->sum(function ($item) {
                            if ($item->payout_status === 'Hold') {
                                return (float) $item->monthly_max_payout;
                            }

                            return max(0.0, (float) $item->monthly_max_payout - (float) $item->final_payout);
                        });
                        $released = (float) $record->royaltyHeldReleases->sum('amount');

                        return ($held - $released) > 0;
                    })
                    ->schema(function (Employee $record) {
                        $held = (float) $record->royaltyReportItems->sum(function ($item) {
                            if ($item->payout_status === 'Hold') {
                                return (float) $item->monthly_max_payout;
                            }

                            return max(0.0, (float) $item->monthly_max_payout - (float) $item->final_payout);
                        });
                        $released = (float) $record->royaltyHeldReleases->sum('amount');
                        $remaining = max(0.0, $held - $released);

                        return [
                            Radio::make('release_type')
                                ->label('Release Type')
                                ->options([
                                    'full' => 'Full Release (100% of remaining balance: Rs. '.number_format($remaining, 2).')',
                                    'partial' => 'Partial Release (Calculated / Approved based on merit points)',
                                ])
                                ->default('full')
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) use ($remaining) {
                                    if ($state === 'full') {
                                        $set('amount', $remaining);
                                    }
                                }),

                            TextInput::make('amount')
                                ->label('Amount to Release (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->default($remaining)
                                ->maxValue($remaining)
                                ->minValue(1)
                                ->helperText('Max available: Rs. '.number_format($remaining, 2)),

                            TextInput::make('merit_points')
                                ->label('Merit Points / Score')
                                ->placeholder('e.g. 85 / 100, 92 Merit Points, Grade A')
                                ->required()
                                ->helperText('Annual merit score or criteria used to justify this release amount.'),

                            DatePicker::make('release_date')
                                ->label('Release Date')
                                ->default(now()->toDateString())
                                ->required(),

                            Textarea::make('notes')
                                ->label('Management Approval / Justification Notes')
                                ->placeholder('e.g. Approved during annual December merit evaluation by Management...')
                                ->rows(3)
                                ->required(),
                        ];
                    })
                    ->action(function (array $data, Employee $record) {
                        $amount = (float) ($data['amount'] ?? 0);
                        if ($amount <= 0) {
                            return;
                        }

                        RoyaltyHeldRelease::create([
                            'employee_code' => $record->employee_code,
                            'amount' => $amount,
                            'merit_points' => $data['merit_points'] ?? null,
                            'release_date' => $data['release_date'] ?? now()->toDateString(),
                            'notes' => $data['notes'] ?? null,
                            'released_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Held Amount Released')
                            ->body('Successfully recorded disbursement of Rs. '.number_format($amount, 2)." for {$record->employee_code}.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoyaltyHeldBalances::route('/'),
            'view' => ViewRoyaltyHeldBalance::route('/{record}'),
        ];
    }
}
