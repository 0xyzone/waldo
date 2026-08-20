<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeSuspension;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSuspensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_suspension_updates_employee_status_to_suspended_and_tips_status_to_hold(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP001',
            'name' => 'John Doe',
            'employee_status' => 'Active',
            'tips_status' => 'Release',
        ]);

        $suspension = EmployeeSuspension::create([
            'employee_id' => $employee->employee_code,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(5),
            'reason' => 'Disciplinary investigation',
            'attachments' => ['suspension-attachments/notice.pdf', 'suspension-attachments/statement.png'],
            'status' => 'active',
        ]);

        $employee->refresh();

        $this->assertEquals('Suspended', $employee->employee_status);
        $this->assertEquals('Hold', $employee->tips_status);
        $this->assertCount(2, $suspension->attachments);
    }

    public function test_scheduler_uplifts_expired_suspension_and_restores_employee_status_to_active_without_altering_tips_status(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP002',
            'name' => 'Jane Smith',
            'employee_status' => 'Active',
            'tips_status' => 'Release',
        ]);

        // Create an active suspension whose end date is yesterday
        $suspension = EmployeeSuspension::create([
            'employee_id' => $employee->employee_code,
            'start_date' => Carbon::yesterday()->subDays(3),
            'end_date' => Carbon::yesterday(),
            'reason' => 'Completed suspension period',
            'status' => 'active',
        ]);

        // Employee was set to Suspended and tips to Hold by event or state
        $employee->update([
            'employee_status' => 'Suspended',
            'tips_status' => 'Hold',
        ]);

        // Run the scheduled status check command
        $this->artisan('suspensions:check-status')
            ->assertSuccessful();

        $suspension->refresh();
        $employee->refresh();

        $this->assertEquals('completed', $suspension->status);
        $this->assertEquals('Active', $employee->employee_status);
        $this->assertEquals('Hold', $employee->tips_status); // Tips status must remain Hold as requested
    }

    public function test_employee_relationship_with_suspensions(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP003',
            'name' => 'Alex Brown',
            'employee_status' => 'Active',
            'tips_status' => 'Release',
        ]);

        EmployeeSuspension::create([
            'employee_id' => $employee->employee_code,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(3),
            'reason' => 'Initial suspension',
            'status' => 'active',
        ]);

        $this->assertCount(1, $employee->suspensions);
        $this->assertNotNull($employee->latestSuspension);
        $this->assertEquals('Initial suspension', $employee->latestSuspension->reason);
    }
}
