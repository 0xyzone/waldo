<?php

namespace App\Filament\Resources\RoyaltyHeldBalances\Pages;

use App\Filament\Resources\RoyaltyHeldBalances\RoyaltyHeldBalanceResource;
use App\Models\RoyaltyHeldRelease;
use App\Models\RoyaltyReportItem;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Collection;

class ViewRoyaltyHeldBalance extends ViewRecord
{
    protected static string $resource = RoyaltyHeldBalanceResource::class;

    protected string $view = 'filament.resources.royalty-held-balances.pages.view-royalty-held-balance';

    public function getTotalHeldProperty(): float
    {
        return (float) RoyaltyReportItem::where('employee_code', $this->record->employee_code)
            ->where(function ($q) {
                $q->where('payout_status', 'Hold')
                    ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
            })
            ->get()
            ->sum(function ($item) {
                if ($item->payout_status === 'Hold') {
                    return (float) $item->monthly_max_payout;
                }

                return max(0.0, (float) $item->monthly_max_payout - (float) $item->final_payout);
            });
    }

    public function getTotalReleasedProperty(): float
    {
        return (float) RoyaltyHeldRelease::where('employee_code', $this->record->employee_code)
            ->sum('amount');
    }

    public function getRemainingBalanceProperty(): float
    {
        return max(0.0, $this->totalHeld - $this->totalReleased);
    }

    public function getHeldReportItemsProperty(): Collection
    {
        return RoyaltyReportItem::where('employee_code', $this->record->employee_code)
            ->where(function ($q) {
                $q->where('payout_status', 'Hold')
                    ->orWhereRaw('(monthly_max_payout - final_payout) > 0');
            })
            ->with('report')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getReleasesProperty(): Collection
    {
        return RoyaltyHeldRelease::where('employee_code', $this->record->employee_code)
            ->with('releaser')
            ->orderBy('release_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function deleteRelease(int $releaseId): void
    {
        $release = RoyaltyHeldRelease::where('employee_code', $this->record->employee_code)
            ->where('id', $releaseId)
            ->first();

        if ($release) {
            $amount = $release->amount;
            $release->delete();

            Notification::make()
                ->title('Release Reverted')
                ->body('Reverted release of Rs. '.number_format($amount, 2).'. Balance has been restored.')
                ->warning()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printSheet')
                ->label('Print Held Sheet')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('royalty.held-balances.print-employee', ['employee' => $this->record->employee_code]))
                ->openUrlInNewTab(),

            Action::make('releaseAmount')
                ->label('Release Held Amount')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->visible(fn () => $this->remainingBalance > 0)
                ->modalHeading("Release Held Royalty Bonus - {$this->record->employee_code} ({$this->record->name})")
                ->modalDescription(function () {
                    return 'Current Available Balance: Rs. '.number_format($this->remainingBalance, 2).'. Management can release full or partial amount according to merit points collected during the year.';
                })
                ->schema(function () {
                    $remaining = $this->remainingBalance;

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
                ->action(function (array $data) {
                    $amount = (float) ($data['amount'] ?? 0);
                    if ($amount <= 0) {
                        return;
                    }

                    RoyaltyHeldRelease::create([
                        'employee_code' => $this->record->employee_code,
                        'amount' => $amount,
                        'merit_points' => $data['merit_points'] ?? null,
                        'release_date' => $data['release_date'] ?? now()->toDateString(),
                        'notes' => $data['notes'] ?? null,
                        'released_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Held Amount Released')
                        ->body('Successfully recorded disbursement of Rs. '.number_format($amount, 2)." for {$this->record->employee_code}.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
