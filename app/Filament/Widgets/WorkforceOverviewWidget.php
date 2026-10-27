<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class WorkforceOverviewWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.workforce-overview-widget';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get real-time workforce metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetricsProperty(): array
    {
        $total = Employee::count();
        $active = Employee::where('employee_status', 'Active')->count();
        $suspended = Employee::where('employee_status', 'Suspended')->count();
        $resigning = Employee::where('employee_status', 'Resigning This Month')->count();
        $tipsHold = Employee::where('tips_status', 'Hold')->count();

        $male = Employee::where('gender', 'Male')->count();
        $female = Employee::where('gender', 'Female')->count();
        $otherGender = max(0, $total - ($male + $female));

        $malePercent = $total > 0 ? round(($male / $total) * 100, 1) : 0;
        $femalePercent = $total > 0 ? round(($female / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'active' => $active,
            'suspended' => $suspended,
            'resigning' => $resigning,
            'tips_hold' => $tipsHold,
            'male' => $male,
            'female' => $female,
            'other_gender' => $otherGender,
            'male_percent' => $malePercent,
            'female_percent' => $femalePercent,
        ];
    }
}
