<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DepartmentAndDesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws \Exception
     */
    public function run(): void
    {
        $url = 'https://docs.google.com/spreadsheets/d/1i8M_P8KejphnCEFpiUWIhaTvkDZtGqt1_AuWt-vNJGE/export?format=csv&gid=872246129';

        $response = Http::withoutVerifying()->get($url);
        if ($response->failed()) {
            throw new \Exception('Failed to fetch Index CSV from Google Sheet');
        }

        $csvData = $response->body();
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \Exception('Failed to open temporary stream');
        }
        fwrite($stream, $csvData);
        rewind($stream);

        // Skip header row (Line 0: Department Ranking,,,Department wise Designation,,,)
        fgetcsv($stream);

        // Map column 3 department names to column 0 canonical department names where appropriate
        $mapping = [
            'F&B' => 'Food & Beverage',
            'GRA' => 'Customer Service',
            'Account/Finance' => 'Account & Finance',
            'HR' => 'Human Resource',
        ];

        // We will read the rows and process departments first.
        $rows = [];
        while (($row = fgetcsv($stream)) !== false) {
            $rows[] = $row;

            // Seed department if column 0 (Department Name) is not empty
            $deptName = trim($row[0] ?? '');
            $deptRank = trim($row[1] ?? '');
            if (! empty($deptName) && $deptName !== 'Department') {
                $rank = is_numeric($deptRank) ? (int) $deptRank : null;
                Department::updateOrCreate(
                    ['name' => $deptName],
                    ['rank' => $rank]
                );
            }
        }

        // Process designations
        $currentDept = null;
        foreach ($rows as $row) {
            $deptNameInCol3 = trim($row[3] ?? '');

            if (! empty($deptNameInCol3) && $deptNameInCol3 !== 'Department') {
                // Apply name mapping (e.g. F&B -> Food & Beverage)
                $canonicalName = $mapping[$deptNameInCol3] ?? $deptNameInCol3;

                // Find or create the canonical department
                $currentDept = Department::firstOrCreate(['name' => $canonicalName]);
            }

            // Seed designation if column 4 (Designation Name) is not empty and valid
            $designationName = trim($row[4] ?? '');
            $designationRankVal = trim($row[5] ?? '');

            if ($currentDept !== null && ! empty($designationName) && $designationName !== 'Designation' && $designationName !== 'n/a') {
                $designationRank = is_numeric($designationRankVal) ? (int) $designationRankVal : null;

                Designation::updateOrCreate(
                    [
                        'department_id' => $currentDept->id,
                        'name' => $designationName,
                    ],
                    [
                        'rank' => $designationRank,
                    ]
                );
            }
        }

        fclose($stream);
    }
}
