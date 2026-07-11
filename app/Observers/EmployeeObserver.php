<?php

namespace App\Observers;

use App\Models\Employee;
use App\Services\GoogleSheetsService;

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
