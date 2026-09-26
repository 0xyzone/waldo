<?php

namespace Database\Seeders;

use App\Models\TipsNonEmployee;
use Illuminate\Database\Seeder;

class TipsNonEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nonEmployees = [
            [
                'code' => '001',
                'name' => 'Jit Man Tamang',
                'designation' => 'Garud Security',
                'tips_percentage' => 100,
                'distribution_amount' => 500,
                'is_active' => true,
            ],
            [
                'code' => '002',
                'name' => 'Sher Bahadur Shahi',
                'designation' => 'Garud Security',
                'tips_percentage' => 100,
                'distribution_amount' => 500,
                'is_active' => true,
            ],
            [
                'code' => '003',
                'name' => 'Prakash Pandey',
                'designation' => 'Garud Security',
                'tips_percentage' => 100,
                'distribution_amount' => 500,
                'is_active' => true,
            ],
            [
                'code' => '004',
                'name' => 'Bal Bahadur Rimal',
                'designation' => 'Garud Security',
                'tips_percentage' => 100,
                'distribution_amount' => 500,
                'is_active' => true,
            ],
            [
                'code' => '005',
                'name' => 'Ms. Kumari Sijok',
                'designation' => 'Care Taker',
                'tips_percentage' => 100,
                'distribution_amount' => 2500,
                'is_active' => true,
            ],
            [
                'code' => '006',
                'name' => 'Ms. Dhana Maya Dhami',
                'designation' => 'Care Taker',
                'tips_percentage' => 100,
                'distribution_amount' => 2500,
                'is_active' => true,
            ],
        ];

        foreach ($nonEmployees as $emp) {
            TipsNonEmployee::updateOrCreate(
                ['code' => $emp['code']],
                $emp
            );
        }
    }
}
