<?php

namespace App\Filament\Widgets;

use App\Models\BiometricAllotment;
use App\Models\IdCardPrintReport;
use App\Models\IdCardRequest;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ItOverviewWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.it-overview-widget';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * Get real-time IT & Systems metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetricsProperty(): array
    {
        $idPending = IdCardRequest::where('status', 'pending')->count();
        $idDesigned = IdCardRequest::where('status', 'designed')->count();
        $idSentForPrint = IdCardRequest::where('status', 'sent for print')->count();
        $idDone = IdCardRequest::where('status', 'done')->count();

        $biometricTotal = BiometricAllotment::count();
        $biometricEnrolled = BiometricAllotment::whereNotNull('enrolled_date')->count();
        $printReportsCount = IdCardPrintReport::count();

        return [
            'id_pending' => $idPending,
            'id_designed' => $idDesigned,
            'id_sent_for_print' => $idSentForPrint,
            'id_done' => $idDone,
            'biometric_total' => $biometricTotal,
            'biometric_enrolled' => $biometricEnrolled,
            'print_reports_count' => $printReportsCount,
            'action_required' => $idPending + $idDesigned,
        ];
    }

    /**
     * Get recent active ID card requests.
     */
    public function getActiveRequestsProperty(): Collection
    {
        return IdCardRequest::where('status', '!=', 'done')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    }
}
