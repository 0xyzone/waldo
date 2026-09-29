<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\ManualAttendanceEmployee;
use App\Models\MonthlyManualRoster;
use App\Models\MonthlyManualRosterItem;
use PHPUnit\Framework\TestCase;

class ManualAttendanceAndRosterTest extends TestCase
{
    public function test_manual_attendance_employee_has_correct_fillable_and_casts(): void
    {
        $model = new ManualAttendanceEmployee;

        $this->assertEquals('manual_attendance_employees', $model->getTable());
        $this->assertContains('employee_code', $model->getFillable());
        $this->assertContains('is_active', $model->getFillable());
        $this->assertContains('notes', $model->getFillable());
        $this->assertContains('created_by', $model->getFillable());
        $this->assertEquals('boolean', $model->getCasts()['is_active'] ?? null);
    }

    public function test_monthly_manual_roster_has_correct_fillable_and_computed_properties(): void
    {
        $roster = new MonthlyManualRoster([
            'year' => 2026,
            'month' => 9,
            'total_employees' => 20,
            'completed_count' => 15,
        ]);

        $this->assertEquals('monthly_manual_rosters', $roster->getTable());
        $this->assertEquals('September', $roster->month_name);
        $this->assertEquals('September 2026', $roster->period);
        $this->assertEquals(5, $roster->pending_count);
        $this->assertEquals(75, $roster->completion_percentage);
    }

    public function test_monthly_manual_roster_zero_employees_returns_zero_percent(): void
    {
        $roster = new MonthlyManualRoster([
            'year' => 2026,
            'month' => 10,
            'total_employees' => 0,
            'completed_count' => 0,
        ]);

        $this->assertEquals('October 2026', $roster->period);
        $this->assertEquals(0, $roster->completion_percentage);
        $this->assertEquals(0, $roster->pending_count);
    }

    public function test_monthly_manual_roster_item_has_correct_fillable_and_casts(): void
    {
        $item = new MonthlyManualRosterItem;

        $this->assertEquals('monthly_manual_roster_items', $item->getTable());
        $this->assertContains('monthly_manual_roster_id', $item->getFillable());
        $this->assertContains('employee_code', $item->getFillable());
        $this->assertContains('employee_name', $item->getFillable());
        $this->assertContains('department', $item->getFillable());
        $this->assertContains('designation', $item->getFillable());
        $this->assertContains('is_roster_updated', $item->getFillable());
        $this->assertContains('updated_at_hrms', $item->getFillable());
        $this->assertContains('updated_by', $item->getFillable());
        $this->assertContains('notes', $item->getFillable());
        $this->assertEquals('boolean', $item->getCasts()['is_roster_updated'] ?? null);
    }

    public function test_employee_model_has_manual_attendance_relationship(): void
    {
        $employee = new Employee;
        $this->assertTrue(method_exists($employee, 'manualAttendance'));
    }
}
