<?php

namespace Database\Seeders;

use App\Models\LetterTemplate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call([
            DepartmentAndDesignationSeeder::class,
        ]);

        if (LetterTemplate::count() === 0) {
            LetterTemplate::create([
                'title' => 'Promotion Letter',
                'content' => '<p><strong>Date:</strong> {{ custom_promotion_date }}</p><p><strong>To,</strong><br>{{ employee_name }}<br>Employee Code: {{ employee_employee_code }}<br>Department: {{ employee_department }}</p><p>Dear {{ employee_name }},</p><p>We are pleased to inform you that you have been promoted to the position of <strong>{{ custom_new_role }}</strong> in the {{ employee_department }} department. Your new salary will be <strong>{{ custom_new_salary }}</strong> per month, effective from <strong>{{ custom_promotion_date }}</strong>.</p><p>We appreciate your dedication and hard work, which has contributed significantly to the growth of our company. We look forward to your continued support and leadership in your new role.</p><p>Sincerely,<br><br><strong>HR Department</strong><br>Waldo HR Solutions</p>',
                'variables' => [
                    ['key' => 'promotion_date', 'type' => 'date', 'dummy' => 'July 15, 2026'],
                    ['key' => 'new_role', 'type' => 'text', 'dummy' => 'Senior Software Engineer'],
                    ['key' => 'new_salary', 'type' => 'number', 'dummy' => '8500'],
                ],
                'margin_top' => 25,
                'margin_bottom' => 25,
                'margin_left' => 20,
                'margin_right' => 20,
            ]);

            LetterTemplate::create([
                'title' => 'Termination Letter',
                'content' => '<p><strong>Date:</strong> {{ custom_termination_date }}</p><p><strong>To,</strong><br>{{ employee_name }}<br>Employee Code: {{ employee_employee_code }}</p><p>Dear {{ employee_name }},</p><p>This letter is to inform you that your employment with Waldo HR Solutions is being terminated effective from <strong>{{ custom_termination_date }}</strong> due to {{ custom_reason }}.</p><p>Please return all company properties before the effective date. Your final settlement and pending payments will be cleared within 7 working days.</p><p>We wish you the best in your future endeavors.</p><p>Sincerely,<br><br><strong>Management</strong><br>Waldo HR Solutions</p>',
                'variables' => [
                    ['key' => 'termination_date', 'type' => 'date', 'dummy' => 'July 15, 2026'],
                    ['key' => 'reason', 'type' => 'text', 'dummy' => 'prolonged absenteeism and performance mismatch'],
                ],
                'margin_top' => 25,
                'margin_bottom' => 25,
                'margin_left' => 20,
                'margin_right' => 20,
            ]);
        }
    }
}
