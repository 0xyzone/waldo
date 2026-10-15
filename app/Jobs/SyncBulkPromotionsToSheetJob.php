<?php

namespace App\Jobs;

use App\Models\EmployeePromotion;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncBulkPromotionsToSheetJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new bulk sync job instance.
     *
     * @param  array<int>  $promotionIds
     */
    public function __construct(
        public readonly array $promotionIds,
        public readonly ?int $userId,
        public readonly bool $isSyncing = true,
    ) {
        $this->onConnection('database');
    }

    /**
     * Execute the bulk job.
     * Syncs all employee records to Google Sheets and sends only ONE consolidated notification once completed.
     */
    public function handle(GoogleSheetsService $sheetsService): void
    {
        if (empty($this->promotionIds)) {
            return;
        }

        $promotions = EmployeePromotion::with(['employee.department', 'employee.designation'])
            ->whereIn('id', $this->promotionIds)
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($promotions as $promotion) {
            if (! $promotion->employee) {
                continue;
            }

            try {
                $employee = $promotion->employee->fresh(['department', 'designation']);
                $sheetsService->syncEmployee($employee, ['department_id', 'designation_id']);
                $successCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Bulk Google Sheet sync failed for promotion {$promotion->id}: ".$e->getMessage());
            }
        }

        // Send ONLY ONE consolidated push notification to the user once EVERYTHING is completed
        if ($this->userId && $successCount > 0) {
            $user = User::find($this->userId);

            if ($user) {
                $title = $this->isSyncing ? 'Promotions Synced to Sheet' : 'HRMS Sync Reset in Sheet';
                $body = "Successfully updated {$successCount} employee record(s) in Google Sheet.";
                if ($failedCount > 0) {
                    $body .= " ({$failedCount} failed)";
                }

                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->success()
                    ->sendToDatabase($user, isEventDispatched: true)
                    ->broadcast($user);
            }
        }
    }
}
