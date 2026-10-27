<?php

namespace App\Filament\Widgets;

use App\Models\Adjustment;
use App\Models\EmployeeSuspension;
use App\Models\SalaryIncrementRequest;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class PendingOperationsWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.pending-operations-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get real-time pending operation queues.
     *
     * @return array<string, mixed>
     */
    public function getQueuesProperty(): array
    {
        $pendingAdjustments = Adjustment::where('status', 'pending')->count();
        $activeSuspensions = EmployeeSuspension::where('status', 'active')->count();
        $pendingIncrements = SalaryIncrementRequest::where('status', 'pending')->count();

        $totalActionRequired = $pendingAdjustments + $activeSuspensions + $pendingIncrements;

        return [
            'total' => $totalActionRequired,
            'adjustments' => $pendingAdjustments,
            'suspensions' => $activeSuspensions,
            'increments' => $pendingIncrements,
        ];
    }
}
