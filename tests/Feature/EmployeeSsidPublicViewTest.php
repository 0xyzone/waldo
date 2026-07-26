<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSsidPublicViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_employee_ssid_page_can_be_rendered(): void
    {
        $response = $this->get('/employee-ssids');

        $response->assertStatus(200);
        $response->assertSee('Employee SSID Directory');
    }

    public function test_can_search_employee_by_employee_code(): void
    {
        Employee::withoutEvents(function () {
            $emp1 = new Employee;
            $emp1->employee_code = 'CWD001';
            $emp1->name = 'John Doe';
            $emp1->ssid = 'SSID-1001';
            $emp1->save();

            $emp2 = new Employee;
            $emp2->employee_code = 'CWD002';
            $emp2->name = 'Jane Smith';
            $emp2->ssid = 'SSID-1002';
            $emp2->save();
        });

        $response = $this->get('/employee-ssids?search=CWD001');

        $response->assertStatus(200);
        $response->assertSee('CWD001');
        $response->assertSee('John Doe');
        $response->assertSee('SSID-1001');
        $response->assertDontSee('SSID-1002');
    }

    public function test_can_filter_employees_by_department(): void
    {
        $dept1 = Department::create(['name' => 'Engineering', 'is_active' => true]);
        $dept2 = Department::create(['name' => 'Marketing', 'is_active' => true]);

        Employee::withoutEvents(function () use ($dept1, $dept2) {
            $emp1 = new Employee;
            $emp1->employee_code = 'CWD010';
            $emp1->name = 'Alice';
            $emp1->department_id = $dept1->id;
            $emp1->ssid = 'SSID-DEPT1';
            $emp1->save();

            $emp2 = new Employee;
            $emp2->employee_code = 'CWD020';
            $emp2->name = 'Bob';
            $emp2->department_id = $dept2->id;
            $emp2->ssid = 'SSID-DEPT2';
            $emp2->save();
        });

        $response = $this->get('/employee-ssids?department_id='.$dept1->id);

        $response->assertStatus(200);
        $response->assertSee('CWD010');
        $response->assertSee('SSID-DEPT1');
        $response->assertDontSee('SSID-DEPT2');
    }
}
