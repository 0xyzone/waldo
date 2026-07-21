<?php

namespace App\Services;

use App\Helpers\NepaliDate\NepaliDate;
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

        Employee::withoutEvents(function () use ($stream, &$syncedCount) {
            while (($row = fgetcsv($stream)) !== false) {
                // Pad the row to 30 elements to avoid undefined index offsets
                if (count($row) < 30) {
                    $row = array_pad($row, 30, '');
                }

                $employeeCode = trim($row[3] ?? '');
                if (empty($employeeCode) || $employeeCode === 'Employee Code') {
                    continue;
                }

                // Find/Create Department — always sync rank from sheet col[0] (Dp. Rank)
                $departmentName = trim($row[6] ?? '');
                $department = null;
                $dpRank = is_numeric($row[0] ?? null) ? (int) $row[0] : null;
                if (! empty($departmentName)) {
                    $department = Department::updateOrCreate(
                        ['name' => $departmentName],
                        array_filter(['rank' => $dpRank], fn ($v) => $v !== null)
                    );
                }

                // Find/Create Designation — always sync rank from sheet col[1] (Rank).
                // The sheet has no separate designation-rank column; col[1] is the employee rank
                // which also represents the designation's relative ordering within the department.
                // We take the lowest rank value seen for a designation as its canonical rank.
                $designationName = trim($row[7] ?? '');
                $designation = null;
                $desigRank = is_numeric($row[1] ?? null) ? (int) $row[1] : null;
                if ($department !== null && ! empty($designationName)) {
                    $designation = Designation::firstOrCreate([
                        'department_id' => $department->id,
                        'name' => $designationName,
                    ]);

                    // Update rank if the sheet provides one and it is lower than the stored value
                    // (lower = higher priority), so the first/top employee rank defines the designation rank.
                    if ($desigRank !== null) {
                        if ($designation->rank === null || $desigRank < $designation->rank) {
                            $designation->rank = $desigRank;
                            $designation->save();
                        }
                    }
                }

                // Parse dates
                // Column 9 (Join Date yyyy.mm.dd) is intentionally ignored — we only use join_date_formatted (col 8).
                $dobAd = $this->parseDate($row[16] ?? null, 'd F, Y');

                // Parse numbers ($dpRank and $desigRank already computed above for dept/designation sync)
                $tipsAmount = is_numeric($row[20] ?? null) ? (float) $row[20] : null;
                $pointValue = is_numeric($row[22] ?? null) ? (float) $row[22] : null;

                // Parse booleans
                $tipsBlank = $this->parseBoolean($row[23] ?? null);
                $publishTips = $this->parseBoolean($row[24] ?? null);
                $tipsFixed = $this->parseBoolean($row[25] ?? null);

                // Handle dob_bs: Since dob_bs is not synced back to Google Sheet,
                // we should calculate it from dob_ad if empty, or keep the existing DB value.
                $dobBs = trim($row[17] ?? '');
                if (empty($dobBs)) {
                    if (! empty($dobAd)) {
                        try {
                            $dateObj = Carbon::parse($dobAd);
                            $converter = new NepaliDate;
                            $converted = $converter->convertAdToBs($dateObj->year, $dateObj->month, $dateObj->day);
                            if (! empty($converted)) {
                                $dobBs = sprintf('%04d.%02d.%02d', $converted['year'], $converted['month'], $converted['day']);
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }

                    if (empty($dobBs)) {
                        $existingEmployee = Employee::where('employee_code', $employeeCode)->first();
                        if ($existingEmployee) {
                            $dobBs = $existingEmployee->dob_bs;
                        }
                    }
                }

                Employee::updateOrCreate(
                    ['employee_code' => $employeeCode],
                    [
                        'department_id' => $department?->id,
                        'designation_id' => $designation?->id,
                        'dp_rank' => $dpRank,
                        'rank' => $desigRank,
                        'name' => trim($row[4] ?? '') ?: null,
                        'gender' => trim($row[5] ?? '') ?: null,
                        'join_date_formatted' => trim($row[8] ?? '') ?: null,
                        'contact_number' => trim($row[10] ?? '') ?: null,
                        'email' => trim($row[11] ?? '') ?: null,
                        'citizenship_number' => trim($row[12] ?? '') ?: null,
                        'citizenship_issue_date' => trim($row[13] ?? '') ?: null,
                        'citizenship_issue_place' => trim($row[14] ?? '') ?: null,
                        'ssid' => trim($row[15] ?? '') ?: null,
                        'dob_ad' => $dobAd,
                        'dob_bs' => $dobBs ?: null,
                        'marital_status' => trim($row[18] ?? '') ?: null,
                        'employee_status' => trim($row[19] ?? '') ?: null,
                        'tips_amount' => $tipsAmount,
                        'tips_status' => trim($row[21] ?? '') ?: null,
                        'point_value' => $pointValue,
                        'tips_blank' => $tipsBlank,
                        'publish_tips' => $publishTips,
                        'tips_fixed' => $tipsFixed,
                        'hrms_password' => trim($row[26] ?? '') ?: null,
                        'first_name' => trim($row[27] ?? '') ?: null,
                        'middle_name' => trim($row[28] ?? '') ?: null,
                        'last_name' => trim($row[29] ?? '') ?: null,
                    ]
                );

                $syncedCount++;
            }
        });

        fclose($stream);

        return $syncedCount;
    }

    /**
     * Parse date string into standard Y-m-d format.
     *
     * @param  string  $format  The expected input format (e.g. 'd F, Y' for "12 December, 1988").
     */
    private function parseDate(?string $value, string $format): ?string
    {
        $value = trim($value ?? '');
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat($format, $value)?->format('Y-m-d');
        } catch (\Exception) {
            // Fallback: attempt a generic parse for flexibility
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
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
