<?php

namespace App\Filament\Widgets;

use App\Models\NametagDistribution;
use App\Models\NametagFine;
use App\Models\NametagReturn;
use App\Models\UnofficialNametagRequest;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class PurchaseStoreOverviewWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.purchase-store-overview-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get real-time Purchase & Store metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetricsProperty(): array
    {
        $distributedTotal = NametagDistribution::count();
        $distributedThisMonth = NametagDistribution::whereMonth('date', Carbon::today()->month)
            ->whereYear('date', Carbon::today()->year)
            ->count();

        $finesTotalCount = NametagFine::count();
        $finesTotalAmount = NametagFine::sum('amount');
        $finesPendingAck = NametagFine::where('acknowledged', false)->count();

        $tagRequestsPending = UnofficialNametagRequest::where('status', 'pending')->count();
        $tagRequestsActive = UnofficialNametagRequest::where('status', '!=', 'done')->count();

        $returnsTotal = NametagReturn::count();
        $returnsThisMonth = NametagReturn::whereMonth('return_date', Carbon::today()->month)
            ->whereYear('return_date', Carbon::today()->year)
            ->count();

        return [
            'distributed_total' => $distributedTotal,
            'distributed_this_month' => $distributedThisMonth,
            'fines_count' => $finesTotalCount,
            'fines_amount' => $finesTotalAmount,
            'fines_pending_ack' => $finesPendingAck,
            'requests_pending' => $tagRequestsPending,
            'requests_active' => $tagRequestsActive,
            'returns_total' => $returnsTotal,
            'returns_this_month' => $returnsThisMonth,
        ];
    }

    /**
     * Get recent active unofficial nametag requests.
     */
    public function getActiveRequestsProperty(): Collection
    {
        return UnofficialNametagRequest::where('status', '!=', 'done')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    }
}
