<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\ManualAttendanceStaff;
use App\Models\MonthlyManualAttendance;
use App\Models\MonthlyManualAttendanceItem;
use PHPUnit\Framework\TestCase;

class ManualAttendanceStaffAndMonthlyAttendanceTest extends TestCase
{
    public function test_manual_attendance_staff_has_correct_fillable_and_casts(): void
    {
        $model = new ManualAttendanceStaff;

        $this->assertEquals('manual_attendance_staff', $model->getTable());
        $this->assertContains('employee_code', $model->getFillable());
        $this->assertContains('is_active', $model->getFillable());
        $this->assertContains('notes', $model->getFillable());
        $this->assertContains('created_by', $model->getFillable());
        $this->assertEquals('boolean', $model->getCasts()['is_active'] ?? null);
    }

    public function test_monthly_manual_attendance_has_correct_fillable_and_computed_properties(): void
    {
        $attendance = new MonthlyManualAttendance([
            'year' => 2026,
            'month' => 10,
            'total_employees' => 25,
            'completed_count' => 20,
        ]);

        $this->assertEquals('monthly_manual_attendances', $attendance->getTable());
        $this->assertEquals('October', $attendance->month_name);
        $this->assertEquals('October 2026', $attendance->period);
        $this->assertEquals(5, $attendance->pending_count);
        $this->assertEquals(80, $attendance->completion_percentage);
    }

    public function test_monthly_manual_attendance_zero_employees_returns_zero_percent(): void
    {
        $attendance = new MonthlyManualAttendance([
            'year' => 2026,
            'month' => 11,
            'total_employees' => 0,
            'completed_count' => 0,
        ]);

        $this->assertEquals('November 2026', $attendance->period);
        $this->assertEquals(0, $attendance->completion_percentage);
        $this->assertEquals(0, $attendance->pending_count);
    }

    public function test_monthly_manual_attendance_item_has_correct_fillable_and_casts(): void
    {
        $item = new MonthlyManualAttendanceItem;

        $this->assertEquals('monthly_manual_attendance_items', $item->getTable());
        $this->assertContains('monthly_manual_attendance_id', $item->getFillable());
        $this->assertContains('employee_code', $item->getFillable());
        $this->assertContains('employee_name', $item->getFillable());
        $this->assertContains('department', $item->getFillable());
        $this->assertContains('designation', $item->getFillable());
        $this->assertContains('is_attendance_updated', $item->getFillable());
        $this->assertContains('updated_at_hrms', $item->getFillable());
        $this->assertContains('updated_by', $item->getFillable());
        $this->assertContains('notes', $item->getFillable());
        $this->assertEquals('boolean', $item->getCasts()['is_attendance_updated'] ?? null);
    }

    public function test_employee_model_has_manual_attendance_staff_relationship(): void
    {
        $employee = new Employee;
        $this->assertTrue(method_exists($employee, 'manualAttendanceStaff'));
    }
}
