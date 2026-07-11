<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class EmployeeSyncService
{
    /**
     * Sync employee data from Google Sheet.
     *
     * @throws \Exception
     */
    public function sync(): int
    {
        $url = 'https://docs.google.com/spreadsheets/d/1i8M_P8KejphnCEFpiUWIhaTvkDZtGqt1_AuWt-vNJGE/export?format=csv&gid=0';

        $response = Http::withoutVerifying()->get($url);
        if ($response->failed()) {
            throw new \Exception('Failed to fetch data from Google Sheet');
        }

        $csvData = $response->body();
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \Exception('Failed to open temporary stream');
        }
        fwrite($stream, $csvData);
        rewind($stream);

        // Skip headers row
        fgetcsv($stream);

        $syncedCount = 0;

        while (($row = fgetcsv($stream)) !== false) {
            // Ensure the row has enough columns (we expect 31 columns)
            if (count($row) < 31) {
                continue;
            }

            $employeeCode = trim($row[3] ?? '');
            if (empty($employeeCode) || $employeeCode === 'Employee Code') {
                continue;
            }

            // Find/Create Department
            $departmentName = trim($row[8] ?? '');
            $department = null;
            if (! empty($departmentName)) {
                $dpRank = is_numeric($row[0] ?? null) ? (int) $row[0] : null;
                $department = Department::updateOrCreate(
                    ['name' => $departmentName],
                    ['rank' => $dpRank]
                );
            }

            // Find/Create Designation
            $designationName = trim($row[9] ?? '');
            $designation = null;
            if ($department !== null && ! empty($designationName)) {
                $designation = Designation::firstOrCreate([
                    'department_id' => $department->id,
                    'name' => $designationName,
                ]);
            }

            // Parse dates
            $joinDate = $this->parseDate($row[7] ?? null, 'Y.m.d');
            $dobAd = $this->parseDate($row[16] ?? null, 'long');

            // Parse numbers
            $dpRank = is_numeric($row[0] ?? null) ? (int) $row[0] : null;
            $rank = is_numeric($row[1] ?? null) ? (int) $row[1] : null;
            $tipsAmount = is_numeric($row[21] ?? null) ? (float) $row[21] : null;
            $pointValue = is_numeric($row[23] ?? null) ? (float) $row[23] : null;

            // Parse booleans
            $tipsBlank = $this->parseBoolean($row[24] ?? null);
            $publishTips = $this->parseBoolean($row[25] ?? null);
            $tipsFixed = $this->parseBoolean($row[26] ?? null);

            Employee::updateOrCreate(
                ['employee_code' => $employeeCode],
                [
                    'department_id' => $department?->id,
                    'designation_id' => $designation?->id,
                    'dp_rank' => $dpRank,
                    'rank' => $rank,
                    'name' => trim($row[4] ?? '') ?: null,
                    'gender' => trim($row[5] ?? '') ?: null,
                    'join_date_formatted' => trim($row[6] ?? '') ?: null,
                    'join_date' => $joinDate,
                    'contact_number' => trim($row[10] ?? '') ?: null,
                    'email' => trim($row[11] ?? '') ?: null,
                    'citizenship_number' => trim($row[12] ?? '') ?: null,
                    'citizenship_issue_date' => trim($row[13] ?? '') ?: null,
                    'citizenship_issue_place' => trim($row[14] ?? '') ?: null,
                    'ssid' => trim($row[15] ?? '') ?: null,
                    'dob_ad' => $dobAd,
                    'dob_bs' => trim($row[17] ?? '') ?: null,
                    'marital_status' => trim($row[19] ?? '') ?: null,
                    'employee_status' => trim($row[20] ?? '') ?: null,
                    'tips_amount' => $tipsAmount,
                    'tips_status' => trim($row[22] ?? '') ?: null,
                    'point_value' => $pointValue,
                    'tips_blank' => $tipsBlank,
                    'publish_tips' => $publishTips,
                    'tips_fixed' => $tipsFixed,
                    'hrms_password' => trim($row[27] ?? '') ?: null,
                    'first_name' => trim($row[28] ?? '') ?: null,
                    'middle_name' => trim($row[29] ?? '') ?: null,
                    'last_name' => trim($row[30] ?? '') ?: null,
                ]
            );

            $syncedCount++;
        }

        fclose($stream);

        return $syncedCount;
    }

    /**
     * Parse date string into standard Y-m-d format.
     */
    private function parseDate(?string $value, string $format): ?string
    {
        $value = trim($value ?? '');
        if (empty($value)) {
            return null;
        }

        try {
            if ($format === 'Y.m.d') {
                $cleaned = str_replace('.', '-', $value);

                return Carbon::parse($cleaned)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parse boolean string.
     */
    private function parseBoolean(?string $value): ?bool
    {
        $value = trim($value ?? '');
        if (empty($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
