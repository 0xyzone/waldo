<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\EmployeeSyncService;
use App\Services\GoogleSheetsService;
use Database\Seeders\DepartmentAndDesignationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmployeeSyncTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test employee synchronization from Google Sheet CSV.
     */
    public function test_can_sync_employees_from_google_sheet(): void
    {
        $csvHeader = 'Dp. Rank,Rank,S. No.,Employee Code, Name  ,Gender,Join Date (dd mmmm, yyyy),Join Date (yyyy.mm.dd),Department,Designation,Contact number, Email ,Citizenship Number,Citizenship Issue Date,Citizenship issue place, SSID ,DOB AD,DOB BS,DOB BS,Maritial Status,Employee Status,Tips Amount,Tips Status,Point Value,Tips Blank?,Publish Tips?,Tips Fixed?,hrms password,first name,middle name,last name';
        $csvRow1 = '14,4,3,CWD015,Sarmila Bhandari,Female,"01 January, 2024",2024.01.01,HouseKeeping,Attendant,9865914116,sarmila@test.com,713018/53,2020-01-01,Place,SSID123,"19 September, 1995",2052.06.03,"3 Asoj, 2052",Married,Active,100.50,Release,1.0000,FALSE,TRUE,TRUE,password123,Sarmila,,Bhandari';
        $csvData1 = $csvHeader."\n".$csvRow1;

        $csvRow1Updated = '14,4,3,CWD015,Sarmila Bhandari Updated,Female,"01 January, 2024",2024.01.01,HouseKeeping,Attendant,9865914116,sarmila_updated@test.com,713018/53,2020-01-01,Place,SSID123,"19 September, 1995",2052.06.03,"3 Asoj, 2052",Married,Resigned,200.00,Hold,1.5000,TRUE,FALSE,FALSE,password321,Sarmila,,Bhandari';
        $csvData2 = $csvHeader."\n".$csvRow1Updated;

        // Mock HTTP response sequence
        Http::fake([
            'docs.google.com/*' => Http::sequence()
                ->push($csvData1, 200)
                ->push($csvData2, 200),
        ]);

        $syncService = new EmployeeSyncService;
        $count = $syncService->sync();

        $this->assertEquals(1, $count);

        // Assert Department and Designation were created
        $this->assertDatabaseHas('departments', [
            'name' => 'HouseKeeping',
            'rank' => 14,
        ]);

        $department = Department::where('name', 'HouseKeeping')->first();
        $this->assertNotNull($department);

        $this->assertDatabaseHas('designations', [
            'department_id' => $department->id,
            'name' => 'Attendant',
        ]);

        $designation = Designation::where('name', 'Attendant')->first();
        $this->assertNotNull($designation);

        // Fetch employee and assert attributes using model casts
        $employee = Employee::where('employee_code', 'CWD015')->first();
        $this->assertNotNull($employee);

        $this->assertEquals('CWD015', $employee->employee_code);
        $this->assertEquals($department->id, $employee->department_id);
        $this->assertEquals($designation->id, $employee->designation_id);
        $this->assertEquals(14, $employee->dp_rank);
        $this->assertEquals(4, $employee->rank);
        $this->assertEquals('Sarmila Bhandari', $employee->name);
        $this->assertEquals('Female', $employee->gender);
        $this->assertEquals('01 January, 2024', $employee->join_date_formatted);
        $this->assertEquals('2024-01-01', $employee->join_date->format('Y-m-d'));
        $this->assertEquals('9865914116', $employee->contact_number);
        $this->assertEquals('sarmila@test.com', $employee->email);
        $this->assertEquals('713018/53', $employee->citizenship_number);
        $this->assertEquals('2020-01-01', $employee->citizenship_issue_date);
        $this->assertEquals('Place', $employee->citizenship_issue_place);
        $this->assertEquals('SSID123', $employee->ssid);
        $this->assertEquals('1995-09-19', $employee->dob_ad->format('Y-m-d'));
        $this->assertEquals('2052.06.03', $employee->dob_bs);
        $this->assertEquals('Married', $employee->marital_status);
        $this->assertEquals('Active', $employee->employee_status);
        $this->assertEquals('100.50', $employee->tips_amount);
        $this->assertEquals('Release', $employee->tips_status);
        $this->assertEquals('1.0000', $employee->point_value);
        $this->assertFalse($employee->tips_blank);
        $this->assertTrue($employee->publish_tips);
        $this->assertTrue($employee->tips_fixed);
        $this->assertEquals('password123', $employee->hrms_password);
        $this->assertEquals('Sarmila', $employee->first_name);
        $this->assertNull($employee->middle_name);
        $this->assertEquals('Bhandari', $employee->last_name);

        // Run sync again for update
        $count2 = $syncService->sync();
        $this->assertEquals(1, $count2);

        // Verify count of employees in table remains 1
        $this->assertEquals(1, Employee::count());

        $employeeUpdated = $employee->fresh();
        $this->assertEquals('Sarmila Bhandari Updated', $employeeUpdated->name);
        $this->assertEquals('sarmila_updated@test.com', $employeeUpdated->email);
        $this->assertEquals('Resigned', $employeeUpdated->employee_status);
        $this->assertEquals('200.00', $employeeUpdated->tips_amount);
        $this->assertEquals('Hold', $employeeUpdated->tips_status);
        $this->assertEquals('1.5000', $employeeUpdated->point_value);
        $this->assertTrue($employeeUpdated->tips_blank);
        $this->assertFalse($employeeUpdated->publish_tips);
        $this->assertFalse($employeeUpdated->tips_fixed);
        $this->assertEquals('password321', $employeeUpdated->hrms_password);
    }

    /**
     * Test department and designation seeding from Google Sheet.
     */
    public function test_can_seed_departments_and_designations(): void
    {
        $csvHeader = 'Department Ranking,,,Department wise Designation,,,';
        $csvRow1 = 'Department,Rank,,Department,Designation,Rank';
        $csvRow2 = 'Management,1,,Gaming,Shift Manager,1';
        $csvRow3 = 'Purchase and store,2,,,Asst. Shift Manager,2';
        $csvRow4 = ',,,,Pit Manager 2,3';
        $csvRow5 = ',,,F&B,F&B Manager,1';
        $csvRow6 = ',,,,Captain,2';
        $csvData = implode("\n", [$csvHeader, $csvRow1, $csvRow2, $csvRow3, $csvRow4, $csvRow5, $csvRow6]);

        Http::fake([
            'docs.google.com/*' => Http::response($csvData, 200),
        ]);

        $this->seed(DepartmentAndDesignationSeeder::class);

        $this->assertDatabaseHas('departments', [
            'name' => 'Management',
            'rank' => 1,
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'Purchase and store',
            'rank' => 2,
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'Food & Beverage',
        ]);

        $gamingDept = Department::where('name', 'Gaming')->first();
        $this->assertNotNull($gamingDept);

        $this->assertDatabaseHas('designations', [
            'department_id' => $gamingDept->id,
            'name' => 'Shift Manager',
            'rank' => 1,
        ]);

        $this->assertDatabaseHas('designations', [
            'department_id' => $gamingDept->id,
            'name' => 'Pit Manager 2',
            'rank' => 3,
        ]);
    }

    /**
     * Test that Employee model modifications trigger the Google Sheets sync via Observer.
     */
    public function test_observer_syncs_to_google_sheet_on_save_and_delete(): void
    {
        // Mock GoogleSheetsService
        $sheetsServiceMock = \Mockery::mock(GoogleSheetsService::class);

        // Expect syncEmployee to be called when creating/saving employee
        $sheetsServiceMock->shouldReceive('syncEmployee')
            ->once()
            ->with(\Mockery::on(function (Employee $employee) {
                return $employee->employee_code === 'CWD999';
            }), \Mockery::any());

        // Expect deleteEmployee to be called when deleting employee
        $sheetsServiceMock->shouldReceive('deleteEmployee')
            ->once()
            ->with('CWD999');

        $this->app->instance(GoogleSheetsService::class, $sheetsServiceMock);

        // Create an employee (should trigger save event and call syncEmployee)
        $employee = new Employee;
        $employee->employee_code = 'CWD999';
        $employee->name = 'Test Observer Employee';
        $employee->save();

        // Delete the employee (should trigger delete event and call deleteEmployee)
        $employee->delete();
    }
}
