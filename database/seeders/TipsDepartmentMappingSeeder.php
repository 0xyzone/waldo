<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\TipsDepartmentMapping;
use Illuminate\Database\Seeder;

class TipsDepartmentMappingSeeder extends Seeder
{
    /**
     * Seed default Master Settings for Tips Report Pages.
     */
    public function run(): void
    {
        $departments = Department::all()->keyBy(fn ($d) => strtolower(trim($d->name)));

        $defaultMappings = [
            [
                'page_name' => 'PIT',
                'dept_names' => ['pit', 'gaming', 'surveillance'],
                'sort_order' => 1,
            ],
            [
                'page_name' => 'Cage',
                'dept_names' => ['cage'],
                'sort_order' => 2,
            ],
            [
                'page_name' => 'Customer Service',
                'dept_names' => ['customer service', 'reception', 'guest relations', 'gra'],
                'sort_order' => 3,
            ],
            [
                'page_name' => 'F&B',
                'dept_names' => ['food & beverage', 'f&b'],
                'sort_order' => 4,
            ],
            [
                'page_name' => 'Housekeeping',
                'dept_names' => ['housekeeping'],
                'sort_order' => 5,
            ],
            [
                'page_name' => 'Security + Transport',
                'dept_names' => ['security', 'transport', 'security + transport'],
                'sort_order' => 6,
            ],
            [
                'page_name' => 'Kitchen',
                'dept_names' => ['kitchen'],
                'sort_order' => 7,
            ],
            [
                'page_name' => 'Back Office',
                'dept_names' => ['back office', 'human resource', 'hr', 'account & finance', 'account/finance', 'it', 'purchase & store'],
                'sort_order' => 8,
            ],
        ];

        foreach ($defaultMappings as $mapping) {
            $matchedIds = [];
            foreach ($mapping['dept_names'] as $name) {
                if (isset($departments[$name])) {
                    $matchedIds[] = (int) $departments[$name]->id;
                }
            }

            TipsDepartmentMapping::updateOrCreate(
                ['page_name' => $mapping['page_name']],
                [
                    'department_ids' => array_values(array_unique($matchedIds)),
                    'sort_order' => $mapping['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
