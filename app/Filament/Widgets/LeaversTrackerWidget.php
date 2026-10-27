<?php

namespace App\Filament\Widgets;

use App\Models\Leaver;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LeaversTrackerWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.leavers-tracker-widget';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get leaver statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatsProperty(): array
    {
        $totalLeavers = Leaver::count();
        $thisMonth = Leaver::whereMonth('leaving_date', Carbon::today()->month)
            ->whereYear('leaving_date', Carbon::today()->year)
            ->count();
        $salaryHolds = Leaver::where('hold_salary', true)->count();
        $tipsHolds = Leaver::where('hold_tips', true)->count();

        return [
            'total' => $totalLeavers,
            'this_month' => $thisMonth,
            'salary_holds' => $salaryHolds,
            'tips_holds' => $tipsHolds,
        ];
    }

    /**
     * Get recent and upcoming leavers.
     */
    public function getRecentLeaversProperty(): Collection
    {
        return Leaver::with(['employee.department', 'employee.designation'])
            ->orderBy('leaving_date', 'desc')
            ->take(4)
            ->get();
    }
}
