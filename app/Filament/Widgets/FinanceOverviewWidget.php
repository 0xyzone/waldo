<?php

namespace App\Filament\Widgets;

use App\Models\Adjustment;
use App\Models\EmployeePromotion;
use App\Models\EmployeeTransfer;
use App\Models\Leaver;
use App\Models\TerminatedEmployee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class FinanceOverviewWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.finance-overview-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get real-time metrics across all Finance resources.
     *
     * @return array<string, mixed>
     */
    public function getMetricsProperty(): array
    {
        $pendingAdjustmentsCount = Adjustment::where('status', 'pending')->count();
        $approvedAdjustmentsCount = Adjustment::where('status', 'approved')->count();
        $pendingAdjustmentsSum = Adjustment::where('status', 'pending')->sum('amount');

        $salaryHolds = Leaver::where('hold_salary', true)->count();
        $tipsHolds = Leaver::where('hold_tips', true)->count();
        $thisMonthLeavers = Leaver::whereMonth('leaving_date', Carbon::today()->month)
            ->whereYear('leaving_date', Carbon::today()->year)
            ->count();

        $totalTerminations = TerminatedEmployee::count();
        $pendingPromotionsHrms = EmployeePromotion::where('hrms_synced', false)->count();
        $pendingTransfersHrms = EmployeeTransfer::where('hrms_synced', false)->count();

        return [
            'adjustments_pending' => $pendingAdjustmentsCount,
            'adjustments_approved' => $approvedAdjustmentsCount,
            'adjustments_pending_sum' => $pendingAdjustmentsSum,
            'salary_holds' => $salaryHolds,
            'tips_holds' => $tipsHolds,
            'leavers_this_month' => $thisMonthLeavers,
            'total_terminations' => $totalTerminations,
            'pending_hrms_sync' => $pendingPromotionsHrms + $pendingTransfersHrms,
        ];
    }
}
