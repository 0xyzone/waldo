<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\GeneratedLetter;
use App\Models\LetterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GeneratedLetterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
    }

    public function test_can_save_generated_letter(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP001',
            'name' => 'John Doe',
            'gender' => 'Male',
        ]);

        $template = LetterTemplate::create([
            'title' => 'Promotion Letter',
            'content' => 'Dear {{ employee_name }}, congratulations!',
            'variables' => [],
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_left' => 20,
            'margin_right' => 20,
        ]);

        $response = $this->postJson(route('letters.save-generated'), [
            'letters' => [
                [
                    'letter_template_id' => $template->id,
                    'employee_code' => $employee->employee_code,
                    'template_title' => $template->title,
                    'employee_name' => 'Mr. John Doe',
                    'content' => '<p>Dear Mr. John Doe, congratulations!</p>',
                    'custom_values' => ['role' => 'Manager'],
                    'margins' => ['top' => 25, 'bottom' => 25, 'left' => 20, 'right' => 20],
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('generated_letters', [
            'letter_template_id' => $template->id,
            'employee_code' => 'EMP001',
            'employee_name' => 'Mr. John Doe',
            'template_title' => 'Promotion Letter',
        ]);
    }

    public function test_can_access_generated_history_listing_page(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP002',
            'name' => 'Jane Smith',
            'gender' => 'Female',
        ]);

        $generated = GeneratedLetter::create([
            'employee_code' => $employee->employee_code,
            'template_title' => 'Offer Letter',
            'employee_name' => 'Miss Jane Smith',
            'content' => '<p>Offer letter content</p>',
            'custom_values' => [],
            'margins' => ['top' => 25, 'bottom' => 25, 'left' => 20, 'right' => 20],
        ]);

        $response = $this->get(route('letters.history'));

        $response->assertStatus(200);
        $response->assertSee('Generated History');
        $response->assertSee('Miss Jane Smith');
        $response->assertSee('Offer Letter');
    }

    public function test_can_view_single_generated_letter_page(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP003',
            'name' => 'Alex Brown',
            'gender' => 'Male',
        ]);

        $generated = GeneratedLetter::create([
            'employee_code' => $employee->employee_code,
            'template_title' => 'Termination Letter',
            'employee_name' => 'Mr. Alex Brown',
            'content' => '<p>Notice of termination</p>',
            'custom_values' => [],
            'margins' => ['top' => 25, 'bottom' => 25, 'left' => 20, 'right' => 20],
        ]);

        $response = $this->get(route('letters.history.show', $generated->id));

        $response->assertStatus(200);
        $response->assertSee('Termination Letter');
        $response->assertSee('Notice of termination');
    }

    public function test_can_access_edit_generated_letter_page(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP005',
            'name' => 'Michael Scott',
            'gender' => 'Male',
        ]);

        $generated = GeneratedLetter::create([
            'employee_code' => $employee->employee_code,
            'template_title' => 'Warning Letter',
            'employee_name' => 'Mr. Michael Scott',
            'content' => '<p>Warning letter content</p>',
        ]);

        $response = $this->get(route('letters.history.edit', $generated->id));

        $response->assertStatus(200);
        $response->assertSee('Edit Generated Letter');
        $response->assertSee('Mr. Michael Scott');
    }

    public function test_can_update_generated_letter(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP006',
            'name' => 'Pam Beesly',
            'gender' => 'Female',
        ]);

        $generated = GeneratedLetter::create([
            'employee_code' => $employee->employee_code,
            'template_title' => 'Original Title',
            'employee_name' => 'Miss Pam Beesly',
            'content' => '<p>Original content</p>',
            'margins' => ['top' => 25, 'bottom' => 25, 'left' => 20, 'right' => 20],
        ]);

        $response = $this->put(route('letters.history.update', $generated->id), [
            'template_title' => 'Updated Title',
            'employee_name' => 'Miss Pam Beesly Halpert',
            'content' => '<p>Updated content</p>',
            'margin_top' => 30,
            'margin_bottom' => 30,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        $response->assertRedirect(route('letters.history'));
        $this->assertDatabaseHas('generated_letters', [
            'id' => $generated->id,
            'template_title' => 'Updated Title',
            'employee_name' => 'Miss Pam Beesly Halpert',
            'content' => '<p>Updated content</p>',
        ]);
    }

    public function test_can_delete_generated_letter(): void
    {
        $employee = Employee::create([
            'employee_code' => 'EMP004',
            'name' => 'Sarah Connor',
            'gender' => 'Female',
        ]);

        $generated = GeneratedLetter::create([
            'employee_code' => $employee->employee_code,
            'template_title' => 'Experience Letter',
            'employee_name' => 'Miss Sarah Connor',
            'content' => '<p>Experience certificate content</p>',
        ]);

        $response = $this->delete(route('letters.history.destroy', $generated->id));

        $response->assertRedirect(route('letters.history'));
        $this->assertDatabaseMissing('generated_letters', [
            'id' => $generated->id,
        ]);
    }
}
