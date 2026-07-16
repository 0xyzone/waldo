<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Leaver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class LeaverResourceTest extends TestCase
{
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
}
