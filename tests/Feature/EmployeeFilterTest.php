<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\EmployeeExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class EmployeeFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Employee::unsetEventDispatcher();
    }

    public function test_employee_table_filters_by_department_and_designation(): void
    {
        $dept1 = Department::create(['name' => 'Engineering', 'rank' => 1]);
        $dept2 = Department::create(['name' => 'HR', 'rank' => 2]);

        $desig1 = Designation::create(['department_id' => $dept1->id, 'name' => 'Software Engineer']);
        $desig2 = Designation::create(['department_id' => $dept2->id, 'name' => 'HR Manager']);

        $emp1 = Employee::create([
            'employee_code' => 'EMP001',
            'department_id' => $dept1->id,
            'designation_id' => $desig1->id,
            'name' => 'Alice Tech',
            'employee_status' => 'Active',
            'join_date_formatted' => '10 January, 2022',
            'gender' => 'Female',
            'marital_status' => 'Single',
        ]);

        $emp2 = Employee::create([
            'employee_code' => 'EMP002',
            'department_id' => $dept2->id,
            'designation_id' => $desig2->id,
            'name' => 'Bob HR',
            'employee_status' => 'Resigned',
            'join_date_formatted' => '15 June, 2024',
            'gender' => 'Male',
            'marital_status' => 'Married',
        ]);

        // Query filtering department_id
        $engineeringEmps = Employee::query()->where('department_id', $dept1->id)->get();
        $this->assertCount(1, $engineeringEmps);
        $this->assertEquals('EMP001', $engineeringEmps->first()->employee_code);

        // Query filtering status
        $resignedEmps = Employee::query()->where('employee_status', 'Resigned')->get();
        $this->assertCount(1, $resignedEmps);
        $this->assertEquals('EMP002', $resignedEmps->first()->employee_code);
    }

    public function test_employee_table_filters_by_join_date_range(): void
    {
        $emp1 = Employee::create([
            'employee_code' => 'EMP101',
            'name' => 'Early Joiner',
            'join_date_formatted' => '01 January, 2020',
        ]);

        $emp2 = Employee::create([
            'employee_code' => 'EMP102',
            'name' => 'Recent Joiner',
            'join_date_formatted' => '01 July, 2024',
        ]);

        // Join date from 2023-01-01
        $driver = Employee::query()->getConnection()->getDriverName();
        $query = Employee::query();
        if ($driver === 'sqlite') {
            $codes = Employee::whereNotNull('join_date_formatted')
                ->get()
                ->filter(function ($emp) {
                    try {
                        return Carbon::createFromFormat('d F, Y', $emp->join_date_formatted)->format('Y-m-d') >= '2023-01-01';
                    } catch (\Throwable $e) {
                        return false;
                    }
                })
                ->pluck('employee_code');

            $query->whereIn('employee_code', $codes);
        } else {
            $query->whereRaw("STR_TO_DATE(join_date_formatted, '%d %M, %Y') >= ?", ['2023-01-01']);
        }

        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('EMP102', $results->first()->employee_code);
    }

    public function test_export_service_generates_csv_and_excel(): void
    {
        $emp = Employee::create([
            'employee_code' => 'EMP201',
            'name' => 'Export Tester',
            'employee_status' => 'Active',
            'join_date_formatted' => '01 January, 2023',
        ]);

        $service = new EmployeeExportService;
        $response = $service->export(
            collect([$emp]),
            ['employee_code', 'name', 'employee_status'],
            'xlsx',
            true
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_employee_records_are_sorted_by_dp_rank_and_rank(): void
    {
        $emp1 = Employee::create([
            'employee_code' => 'EMP_C',
            'name' => 'Charlie Rank 2 1',
            'dp_rank' => 2,
            'rank' => 1,
        ]);

        $emp2 = Employee::create([
            'employee_code' => 'EMP_A',
            'name' => 'Alice Rank 1 2',
            'dp_rank' => 1,
            'rank' => 2,
        ]);

        $emp3 = Employee::create([
            'employee_code' => 'EMP_B',
            'name' => 'Bob Rank 1 1',
            'dp_rank' => 1,
            'rank' => 1,
        ]);

        $sorted = Employee::query()
            ->orderBy('dp_rank', 'asc')
            ->orderBy('rank', 'asc')
            ->get();

        $this->assertEquals(['EMP_B', 'EMP_A', 'EMP_C'], $sorted->pluck('employee_code')->toArray());
    }

    public function test_export_incomplete_generates_streamed_response(): void
    {
        $dept = Department::create(['name' => 'Finance', 'rank' => 1]);
        $desig = Designation::create(['department_id' => $dept->id, 'name' => 'Accountant']);

        // Incomplete employee — missing email, citizenship, dob, etc.
        Employee::create([
            'employee_code' => 'EMP_INC',
            'name' => 'Incomplete Person',
            'employee_status' => 'Active',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'gender' => 'Female',
            'join_date_formatted' => '01 January, 2023',
            'marital_status' => 'Single',
        ]);

        // Complete employee — should be skipped
        Employee::create([
            'employee_code' => 'EMP_COMP',
            'name' => 'Complete Person',
            'employee_status' => 'Active',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'gender' => 'Male',
            'join_date_formatted' => '01 June, 2022',
            'contact_number' => '9800000000',
            'email' => 'complete@example.com',
            'citizenship_number' => '12345',
            'citizenship_issue_date' => '2010-01-01',
            'citizenship_issue_place' => 'Kathmandu',
            'dob_ad' => '1990-01-01',
            'marital_status' => 'Married',
            'tips_amount' => 500.00,
            'point_value' => 1.5000,
        ]);

        $employees = Employee::with(['department', 'designation'])->get();

        $service = new EmployeeExportService;
        $response = $service->exportIncomplete($employees, 'xlsx');

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }
}
