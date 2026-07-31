<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\TerminatedEmployee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerminatedEmployeeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the employee relationship on TerminatedEmployee model.
     */
    public function test_terminated_employee_has_employee_relationship(): void
    {
        $record = new TerminatedEmployee;
        $relation = $record->employee();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
        $this->assertEquals('employee_code', $relation->getOwnerKeyName());
    }

    /**
     * Test the terminatedEmployee relationship on Employee model.
     */
    public function test_employee_has_terminated_employee_relationship(): void
    {
        $employee = new Employee;
        $relation = $employee->terminatedEmployee();

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(TerminatedEmployee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
        $this->assertEquals('employee_code', $relation->getLocalKeyName());
    }

    /**
     * Test creating a termination record updates the employee status to "Terminated".
     */
    public function test_creating_termination_record_updates_employee_status_to_terminated(): void
    {
        $employee = Employee::first();
        if (! $employee) {
            $this->markTestSkipped('No existing employee record found.');
        }

        $originalStatus = $employee->employee_status;

        try {
            TerminatedEmployee::create([
                'employee_id' => $employee->employee_code,
                'last_working_date' => now()->format('Y-m-d'),
                'termination_date' => now()->format('Y-m-d'),
                'reason' => 'Performance reasons',
            ]);

            $employee->refresh();
            $this->assertEquals('Terminated', $employee->employee_status);
        } finally {
            TerminatedEmployee::where('employee_id', $employee->employee_code)->delete();
            $employee->update(['employee_status' => $originalStatus]);
        }
    }
}
