<?php

namespace App\Jobs;

use App\Models\EmployeeTransfer;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncTransferToSheetJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $transferId,
        public readonly ?int $userId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GoogleSheetsService $sheetsService): void
    {
        $transfer = EmployeeTransfer::with(['employee.department', 'employee.designation', 'toDepartment', 'toDesignation'])->find($this->transferId);

        if (! $transfer || ! $transfer->employee) {
            return;
        }

        $employee = $transfer->employee->fresh(['department', 'designation']);

        $sheetsService->syncEmployee($employee, ['department_id', 'designation_id']);

        // Send database notification to the triggering user if available
        if ($this->userId) {
            $user = User::find($this->userId);

            if ($user) {
                $toDept = $transfer->toDepartment?->name ?? 'N/A';
                $toDesig = $transfer->toDesignation?->name ?? 'N/A';
                $employeeName = $transfer->employee->name ?? $transfer->employee_id;

                Notification::make()
                    ->title('Transfer Synced to Sheet')
                    ->body("Employee {$transfer->employee_id} — {$employeeName} transferred to {$toDesig}, {$toDept}. Google Sheet updated.")
                    ->success()
                    ->sendToDatabase($user);
            }
        }
    }
}
