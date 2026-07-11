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
        $this->sheetsService->syncEmployee($employee);
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        $this->sheetsService->deleteEmployee($employee->employee_code);
    }
}
