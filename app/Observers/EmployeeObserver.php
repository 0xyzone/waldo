<?php

namespace App\Observers;

use App\Models\Employee;
use App\Services\EmployeeSyncService;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Log;

class EmployeeObserver
{
    protected GoogleSheetsService $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    /**
     * Handle the Employee "saved" event.
     */
    public function saved(Employee $employee): void
    {
        Log::info('EmployeeObserver saved triggered', [
            'code' => $employee->employee_code,
            'wasRecentlyCreated' => $employee->wasRecentlyCreated,
            'changes' => $employee->getChanges(),
        ]);

        // On fresh insert sync everything; on update only sync the changed columns
        $changedFields = $employee->wasRecentlyCreated
            ? null
            : array_keys($employee->getChanges());

        $this->sheetsService->syncEmployee($employee, $changedFields);

        // Run sync back from Google Sheet to project
        if (! app()->runningUnitTests()) {
            try {
                Log::info('Running backward sync from Google Sheet to DB...');
                app(EmployeeSyncService::class)->sync();
            } catch (\Exception $e) {
                Log::error('Backward sync failed: '.$e->getMessage());
            }
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        $this->sheetsService->deleteEmployee($employee->employee_code);
    }
}
