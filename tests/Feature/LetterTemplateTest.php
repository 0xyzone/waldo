<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LetterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LetterTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test a letter template can be created and stored.
     */
    public function test_can_create_letter_template(): void
    {
        $template = LetterTemplate::create([
            'title' => 'Promotion Letter',
            'content' => 'Dear {{ employee_name }}, you have been promoted to {{ custom_new_role }} ({{ custom_employment_type }}).',
            'variables' => [
                ['key' => 'new_role', 'type' => 'text', 'dummy' => 'Manager'],
                ['key' => 'employment_type', 'type' => 'dropdown', 'dummy' => 'Permanent', 'options' => 'Permanent, Contract, Intern'],
            ],
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_left' => 20,
            'margin_right' => 20,
        ]);

        $this->assertDatabaseHas('letter_templates', [
            'id' => $template->id,
            'title' => 'Promotion Letter',
            'margin_top' => 25,
            'margin_left' => 20,
        ]);

        $variables = $template->fresh()->variables;
        $this->assertEquals('new_role', $variables[0]['key']);
        $this->assertEquals('Manager', $variables[0]['dummy']);
        $this->assertEquals('employment_type', $variables[1]['key']);
        $this->assertEquals('dropdown', $variables[1]['type']);
        $this->assertEquals('Permanent, Contract, Intern', $variables[1]['options']);
    }

    /**
     * Test the standalone letter generator page can be accessed and contains data.
     */
    public function test_can_access_letter_generator_page(): void
    {
        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        // Create template
        $template = LetterTemplate::create([
            'title' => 'Promotion Letter',
            'content' => 'Dear {{ employee_name }}',
            'variables' => [],
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_left' => 20,
            'margin_right' => 20,
        ]);

        // Create employee
        Employee::withoutEvents(function () {
            $employee = new Employee;
            $employee->employee_code = 'CWD001';
            $employee->name = 'Sarmila Bhandari';
            $employee->save();
        });

        $response = $this->get(route('letters.generate'));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
        $response->assertViewHas('employees');

        $response->assertSee('Promotion Letter');
        $response->assertSee('Sarmila Bhandari');
    }

    /**
     * Test the templates index listing page can be accessed.
     */
    public function test_can_access_letters_index_page(): void
    {
        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        $template = LetterTemplate::create([
            'title' => 'Offer Letter',
            'content' => 'Offer content',
            'variables' => [],
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_left' => 20,
            'margin_right' => 20,
        ]);

        $response = $this->get(route('letters.index'));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
        $response->assertSee('Offer Letter');
    }

    /**
     * Test a letter template can be updated.
     */
    public function test_can_update_letter_template(): void
    {
        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        $template = LetterTemplate::create([
            'title' => 'Offer Letter',
            'content' => '<p>Dear {{ employee_name }}</p>',
            'variables' => [
                ['key' => 'salary', 'type' => 'number', 'dummy' => '5000'],
            ],
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_left' => 20,
            'margin_right' => 20,
        ]);

        $response = $this->put(route('letters.update', $template->id), [
            'title' => 'Updated Offer Letter',
            'content' => '<p>Dear {{ employee_name }}</p>',
            'variables' => [
                ['key' => 'salary', 'type' => 'number', 'dummy' => '6000'],
                ['key' => 'start_date', 'type' => 'date', 'dummy' => '2026-07-20'],
            ],
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_left' => 20,
            'margin_right' => 20,
        ]);

        $response->assertRedirect(route('letters.index'));
        $this->assertDatabaseHas('letter_templates', [
            'id' => $template->id,
            'title' => 'Updated Offer Letter',
        ]);

        $updatedVariables = $template->fresh()->variables;
        $this->assertCount(2, $updatedVariables);
        $this->assertEquals('salary', $updatedVariables[0]['key']);
        $this->assertEquals('6000', $updatedVariables[0]['dummy']);
        $this->assertEquals('start_date', $updatedVariables[1]['key']);
    }
}
