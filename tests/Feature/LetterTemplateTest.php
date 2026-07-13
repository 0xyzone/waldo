<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LetterTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
