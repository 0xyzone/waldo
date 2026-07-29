<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\IdCardRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdCardRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_id_card_request_with_notes(): void
    {
        $request = IdCardRequest::create([
            'source' => 'custom',
            'employee_code' => 'EMP101',
            'employee_name' => 'John LostCard',
            'employee_designation' => 'Analyst',
            'employee_department' => 'Finance',
            'notes' => 'Lost ID card during travel. Urgent replacement needed.',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('id_card_requests', [
            'id' => $request->id,
            'employee_code' => 'EMP101',
            'employee_name' => 'John LostCard',
            'notes' => 'Lost ID card during travel. Urgent replacement needed.',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('id_card_request_logs', [
            'id_card_request_id' => $request->id,
            'to_status' => 'pending',
            'action_description' => "Request created with status 'pending'",
        ]);
    }

    public function test_can_create_id_card_request_from_existing_employee(): void
    {
        Employee::withoutEvents(function () {
            $emp = new Employee;
            $emp->employee_code = 'EMP202';
            $emp->name = 'Jane Existing';
            $emp->save();
        });

        $request = IdCardRequest::create([
            'source' => 'employee',
            'employee_code' => 'EMP202',
            'employee_name' => 'Jane Existing',
            'employee_designation' => 'Senior Developer',
            'employee_department' => 'IT',
            'notes' => 'Card damaged.',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('id_card_requests', [
            'id' => $request->id,
            'source' => 'employee',
            'employee_code' => 'EMP202',
            'employee_name' => 'Jane Existing',
        ]);
    }

    public function test_automatic_action_log_recorded_when_status_changes(): void
    {
        $user = User::factory()->create(['name' => 'IT Staff Member']);
        $this->actingAs($user);

        $request = IdCardRequest::create([
            'source' => 'custom',
            'employee_code' => 'EMP404',
            'employee_name' => 'Status Log Tester',
            'status' => 'pending',
        ]);

        // Status update to 'designed'
        $request->update(['status' => 'designed']);

        // Status update to 'sent for print'
        $request->update(['status' => 'sent for print']);

        // Status update to 'done'
        $request->update(['status' => 'done']);

        $this->assertCount(4, $request->actionLogs);

        $this->assertDatabaseHas('id_card_request_logs', [
            'id_card_request_id' => $request->id,
            'user_name' => 'IT Staff Member',
            'from_status' => 'pending',
            'to_status' => 'designed',
        ]);

        $this->assertDatabaseHas('id_card_request_logs', [
            'id_card_request_id' => $request->id,
            'user_name' => 'IT Staff Member',
            'from_status' => 'designed',
            'to_status' => 'sent for print',
        ]);

        $this->assertDatabaseHas('id_card_request_logs', [
            'id_card_request_id' => $request->id,
            'user_name' => 'IT Staff Member',
            'from_status' => 'sent for print',
            'to_status' => 'done',
        ]);
    }
}
