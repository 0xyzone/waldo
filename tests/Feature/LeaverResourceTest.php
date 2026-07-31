<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Leaver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaverResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the employee relationship on Leaver model.
     */
    public function test_leaver_has_employee_relationship(): void
    {
        $leaver = new Leaver;
        $relation = $leaver->employee();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
        $this->assertEquals('employee_code', $relation->getOwnerKeyName());
    }

    /**
     * Test the leaver relationship on Employee model.
     */
    public function test_employee_has_leaver_relationship(): void
    {
        $employee = new Employee;
        $relation = $employee->leaver();

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(Leaver::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
        $this->assertEquals('employee_code', $relation->getLocalKeyName());
    }

    /**
     * Test creating a leaver record updates the employee status to "Resigning This Month".
     */
    public function test_creating_leaver_updates_employee_status_to_resigning_this_month(): void
    {
        $employee = Employee::first();
        if (! $employee) {
            $this->markTestSkipped('No existing employee record found.');
        }

        $originalStatus = $employee->employee_status;

        try {
            Leaver::create([
                'employee_id' => $employee->employee_code,
                'leaving_date' => now()->addDays(14)->format('Y-m-d'),
                'remarks' => 'Test resignation',
                'hold_salary' => false,
                'hold_tips' => false,
                'publish_cl' => false,
            ]);

            $employee->refresh();
            $this->assertEquals('Resigning This Month', $employee->employee_status);
        } finally {
            Leaver::where('employee_id', $employee->employee_code)->delete();
            $employee->update(['employee_status' => $originalStatus]);
        }
    }
}
