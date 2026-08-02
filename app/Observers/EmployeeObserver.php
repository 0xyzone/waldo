<?php

namespace App\Observers;

use App\Models\Employee;
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
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        $this->sheetsService->deleteEmployee($employee->employee_code);
    }
}
