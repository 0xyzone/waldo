<?php

namespace App\Filament\Widgets;

use App\Models\EmployeePromotion;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class PromotionTransferActivityWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.promotion-transfer-activity-widget';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get promotions statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatsProperty(): array
    {
        $total = EmployeePromotion::count();
        $thisMonth = EmployeePromotion::whereMonth('promotion_date', Carbon::today()->month)
            ->whereYear('promotion_date', Carbon::today()->year)
            ->count();

        $acknowledged = EmployeePromotion::where('acknowledged', true)->count();
        $pendingAck = $total - $acknowledged;

        $hrmsSynced = EmployeePromotion::where('hrms_synced', true)->count();
        $pendingHrms = $total - $hrmsSynced;

        return [
            'total' => $total,
            'this_month' => $thisMonth,
            'acknowledged' => $acknowledged,
            'pending_ack' => $pendingAck,
            'hrms_synced' => $hrmsSynced,
            'pending_hrms' => $pendingHrms,
        ];
    }

    /**
     * Get recent promotion activities.
     */
    public function getRecentPromotionsProperty(): Collection
    {
        return EmployeePromotion::with(['employee', 'fromDesignation', 'toDesignation'])
            ->orderBy('promotion_date', 'desc')
            ->take(4)
            ->get();
    }
}
