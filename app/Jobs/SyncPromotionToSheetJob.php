<?php

namespace App\Jobs;

use App\Models\EmployeePromotion;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncPromotionToSheetJob implements ShouldQueue
{
    use Queueable;

    public $connection = 'database';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $promotionId,
        public readonly ?int $userId,
        public readonly bool $isSyncing = true,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GoogleSheetsService $sheetsService): void
    {
        $promotion = EmployeePromotion::with(['employee.department', 'employee.designation', 'toDepartment', 'toDesignation'])->find($this->promotionId);

        if (! $promotion || ! $promotion->employee) {
            return;
        }

        try {
            $employee = $promotion->employee->fresh(['department', 'designation']);

            $sheetsService->syncEmployee($employee, ['department_id', 'designation_id']);

            // Send notification to the triggering user once completed
            if ($this->userId) {
                $user = User::find($this->userId);

                if ($user) {
                    $toDept = $promotion->toDepartment?->name ?? 'N/A';
                    $toDesig = $promotion->toDesignation?->name ?? 'N/A';
                    $employeeName = $promotion->employee->name ?? $promotion->employee_id;

                    $title = $this->isSyncing ? 'Promotion Synced to Sheet' : 'HRMS Sync Reset in Sheet';
                    $body = $this->isSyncing
                        ? "Employee {$promotion->employee_id} — {$employeeName} ({$toDesig}, {$toDept}) successfully updated in Google Sheet."
                        : "Employee {$promotion->employee_id} — {$employeeName} status updated in Google Sheet.";

                    Notification::make()
                        ->title($title)
                        ->body($body)
                        ->success()
                        ->sendToDatabase($user, isEventDispatched: true)
                        ->broadcast($user);
                }
            }
        } catch (\Throwable $e) {
            Log::error('SyncPromotionToSheetJob failed: '.$e->getMessage(), [
                'promotion_id' => $this->promotionId,
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->userId) {
                $user = User::find($this->userId);

                if ($user) {
                    Notification::make()
                        ->title('Google Sheet Sync Failed')
                        ->body("Failed to sync promotion for {$promotion->employee_id} to Google Sheet: {$e->getMessage()}")
                        ->danger()
                        ->sendToDatabase($user, isEventDispatched: true)
                        ->broadcast($user);
                }
            }

            throw $e;
        }
    }
}
