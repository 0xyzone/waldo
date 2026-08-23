<?php

namespace Tests\Feature;

use App\Jobs\SyncPromotionToSheetJob;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeePromotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmployeePromotionTest extends TestCase
{
    use RefreshDatabase;

    private function makeEmployee(string $code = 'EMP001'): Employee
    {
        $dept = Department::create(['name' => 'Operations', 'rank' => 1, 'is_active' => true]);
        $desig = Designation::create(['department_id' => $dept->id, 'name' => 'Executive', 'rank' => 1, 'is_active' => true]);

        return Employee::create([
            'employee_code' => $code,
            'name' => 'Test Employee',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'employee_status' => 'Active',
        ]);
    }

    public function test_creating_promotion_updates_employee_department_and_designation(): void
    {
        Queue::fake();

        $employee = $this->makeEmployee();

        $newDept = Department::create(['name' => 'Finance', 'rank' => 2, 'is_active' => true]);
        $newDesig = Designation::create(['department_id' => $newDept->id, 'name' => 'Manager', 'rank' => 1, 'is_active' => true]);

        EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'from_designation_id' => $employee->designation_id,
            'to_department_id' => $newDept->id,
            'to_designation_id' => $newDesig->id,
            'promotion_date' => now()->toDateString(),
        ]);

        $employee->refresh();

        $this->assertEquals($newDept->id, $employee->department_id);
        $this->assertEquals($newDesig->id, $employee->designation_id);
    }

    public function test_creating_promotion_dispatches_sync_job(): void
    {
        Queue::fake();

        $employee = $this->makeEmployee('EMP002');

        $newDept = Department::create(['name' => 'HR', 'rank' => 3, 'is_active' => true]);
        $newDesig = Designation::create(['department_id' => $newDept->id, 'name' => 'HR Officer', 'rank' => 1, 'is_active' => true]);

        EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'from_designation_id' => $employee->designation_id,
            'to_department_id' => $newDept->id,
            'to_designation_id' => $newDesig->id,
            'promotion_date' => now()->toDateString(),
        ]);

        Queue::assertPushed(SyncPromotionToSheetJob::class);
    }

    public function test_promotion_only_changes_designation_when_department_not_changed(): void
    {
        Queue::fake();

        $employee = $this->makeEmployee('EMP003');
        $originalDeptId = $employee->department_id;

        $newDesig = Designation::create(['department_id' => $originalDeptId, 'name' => 'Senior Executive', 'rank' => 2, 'is_active' => true]);

        EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'from_designation_id' => $employee->designation_id,
            'to_department_id' => null,
            'to_designation_id' => $newDesig->id,
            'promotion_date' => now()->toDateString(),
        ]);

        $employee->refresh();

        $this->assertEquals($originalDeptId, $employee->department_id, 'Department should remain unchanged');
        $this->assertEquals($newDesig->id, $employee->designation_id);
    }

    public function test_acknowledge_action_sets_acknowledged_and_timestamp(): void
    {
        Queue::fake();

        $employee = $this->makeEmployee('EMP004');

        $promotion = EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'from_designation_id' => $employee->designation_id,
            'to_department_id' => $employee->department_id,
            'to_designation_id' => $employee->designation_id,
            'promotion_date' => now()->toDateString(),
        ]);

        $promotion->refresh();

        $this->assertFalse($promotion->acknowledged);
        $this->assertNull($promotion->acknowledged_at);

        $promotion->update([
            'acknowledged' => true,
            'acknowledged_at' => now(),
        ]);

        $promotion->refresh();

        $this->assertTrue($promotion->acknowledged);
        $this->assertNotNull($promotion->acknowledged_at);
    }

    public function test_hrms_synced_toggle_sets_and_clears_correctly(): void
    {
        Queue::fake();

        $employee = $this->makeEmployee('EMP005');

        $promotion = EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'from_designation_id' => $employee->designation_id,
            'to_department_id' => $employee->department_id,
            'to_designation_id' => $employee->designation_id,
            'promotion_date' => now()->toDateString(),
        ]);

        $promotion->update(['hrms_synced' => true, 'hrms_synced_at' => now()]);
        $promotion->refresh();

        $this->assertTrue($promotion->hrms_synced);
        $this->assertNotNull($promotion->hrms_synced_at);

        $promotion->update(['hrms_synced' => false, 'hrms_synced_at' => null]);
        $promotion->refresh();

        $this->assertFalse($promotion->hrms_synced);
        $this->assertNull($promotion->hrms_synced_at);
    }

    public function test_employee_promotions_relationship(): void
    {
        Queue::fake();

        $employee = $this->makeEmployee('EMP006');

        EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'from_designation_id' => $employee->designation_id,
            'promotion_date' => now()->toDateString(),
        ]);

        EmployeePromotion::create([
            'employee_id' => $employee->employee_code,
            'from_department_id' => $employee->department_id,
            'promotion_date' => now()->addMonth()->toDateString(),
        ]);

        $this->assertCount(2, $employee->promotions);
    }
}
