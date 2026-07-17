<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the isIncomplete method on Employee model.
     */
    public function test_is_incomplete_method_identifies_missing_fields(): void
    {
        $department = Department::create(['name' => 'HR', 'rank' => 1]);
        $designation = Designation::create(['department_id' => $department->id, 'name' => 'Manager']);

        $employee = new Employee;

        // Set all required fields
        $employee->employee_code = 'EMP001';
        $employee->department_id = $department->id;
        $employee->designation_id = $designation->id;
        $employee->name = 'John Doe';
        $employee->gender = 'Male';
        $employee->join_date_formatted = '15 January, 2024';
        $employee->contact_number = '1234567890';
        $employee->email = 'john@example.com';
        $employee->citizenship_number = '987654';
        $employee->citizenship_issue_date = '2020-01-01';
        $employee->citizenship_issue_place = 'Place';
        $employee->dob_ad = now()->subYears(30);
        $employee->marital_status = 'Single';
        $employee->tips_amount = 100.00;
        $employee->point_value = 1.0000;

        // At this point, no check field is null, so it should return false (not incomplete)
        $this->assertFalse($employee->isIncomplete());

        // Make one check field null
        $employee->contact_number = null;

        $incompleteFields = $employee->isIncomplete();
        $this->assertNotFalse($incompleteFields);
        $this->assertContains('contact_number', $incompleteFields);
    }

    /**
     * Test scopeIsIncomplete query scope filters correctly.
     */
    public function test_is_incomplete_scope_filters_correctly(): void
    {
        $department = Department::create(['name' => 'HR', 'rank' => 1]);
        $designation = Designation::create(['department_id' => $department->id, 'name' => 'Manager']);

        // 1. Create a complete employee record in DB
        $completeEmployee = new Employee;
        $completeEmployee->employee_code = 'EMP001';
        $completeEmployee->department_id = $department->id;
        $completeEmployee->designation_id = $designation->id;
        $completeEmployee->name = 'John Complete';
        $completeEmployee->gender = 'Male';
        $completeEmployee->join_date_formatted = '15 January, 2024';
        $completeEmployee->contact_number = '1234567890';
        $completeEmployee->email = 'complete@example.com';
        $completeEmployee->citizenship_number = '987654';
        $completeEmployee->citizenship_issue_date = '2020-01-01';
        $completeEmployee->citizenship_issue_place = 'Place';
        $completeEmployee->dob_ad = now()->subYears(30);
        $completeEmployee->marital_status = 'Single';
        $completeEmployee->tips_amount = 100.00;
        $completeEmployee->point_value = 1.0000;
        $completeEmployee->save();

        // 2. Create an incomplete employee record in DB (e.g. missing designation_id)
        $incompleteEmployee = new Employee;
        $incompleteEmployee->employee_code = 'EMP002';
        $incompleteEmployee->department_id = $department->id;
        $incompleteEmployee->name = 'Jane Incomplete';
        $incompleteEmployee->gender = 'Female';
        $incompleteEmployee->join_date_formatted = '15 January, 2024';
        $incompleteEmployee->contact_number = '9876543210';
        $incompleteEmployee->email = 'incomplete@example.com';
        $incompleteEmployee->citizenship_number = '123456';
        $incompleteEmployee->citizenship_issue_date = '2021-01-01';
        $incompleteEmployee->citizenship_issue_place = 'Place';
        $incompleteEmployee->dob_ad = now()->subYears(25);
        $incompleteEmployee->marital_status = 'Married';
        $incompleteEmployee->tips_amount = 150.00;
        $incompleteEmployee->point_value = 1.2000;
        // designation_id is left null
        $incompleteEmployee->save();

        // Query using our scope
        $incompleteEmployees = Employee::query()->isIncomplete()->get();

        $this->assertCount(1, $incompleteEmployees);
        $this->assertEquals('EMP002', $incompleteEmployees->first()->employee_code);
    }
}
