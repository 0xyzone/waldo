<?php

namespace App\Jobs;

use App\Models\EmployeePromotion;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncPromotionToSheetJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $promotionId,
        public readonly ?int $userId,
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

        $employee = $promotion->employee->fresh(['department', 'designation']);

        $sheetsService->syncEmployee($employee, ['department_id', 'designation_id']);

        // Send database notification to the triggering user if available
        if ($this->userId) {
            $user = User::find($this->userId);

            if ($user) {
                $toDept = $promotion->toDepartment?->name ?? 'N/A';
                $toDesig = $promotion->toDesignation?->name ?? 'N/A';
                $employeeName = $promotion->employee->name ?? $promotion->employee_id;

                Notification::make()
                    ->title('Promotion Synced to Sheet')
                    ->body("Employee {$promotion->employee_id} — {$employeeName} promoted to {$toDesig}, {$toDept}. Google Sheet updated.")
                    ->success()
                    ->sendToDatabase($user);
            }
        }
    }
}
