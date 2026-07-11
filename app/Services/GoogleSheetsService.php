<?php

namespace App\Services;

use App\Models\Employee;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected Client $client;

    protected Sheets $service;

    protected string $spreadsheetId;

    /**
     * Map of Employee model attribute -> sheet column index (0-based).
     * Columns NOT listed here (S. No. = 2, duplicate DOB BS = 18) are never touched.
     *
     * @var array<string, int>
     */
    protected array $columnMap = [
        'dp_rank' => 0,
        'rank' => 1,
        // 2 = S. No. (never written)
        'employee_code' => 3,
        'name' => 4,
        'gender' => 5,
        'join_date_formatted' => 6,
        'join_date' => 7,  // formatted as Y.m.d
        'department_id' => 8,  // resolved to department name
        'designation_id' => 9,  // resolved to designation name
        'contact_number' => 10,
        'email' => 11,
        'citizenship_number' => 12,
        'citizenship_issue_date' => 13,
        'citizenship_issue_place' => 14,
        'ssid' => 15,
        'dob_ad' => 16, // formatted as d F, Y
        'dob_bs' => 17,
        // 18 = duplicate DOB BS (never written)
        'marital_status' => 19,
        'employee_status' => 20,
        'tips_amount' => 21,
        'tips_status' => 22,
        'point_value' => 23,
        'tips_blank' => 24,
        'publish_tips' => 25,
        'tips_fixed' => 26,
        'hrms_password' => 27,
        'first_name' => 28,
        'middle_name' => 29,
        'last_name' => 30,
    ];

    public function __construct()
    {
        $this->client = new Client;
        $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
        $this->spreadsheetId = env('GOOGLE_SPREADSHEET_ID', '1i8M_P8KejphnCEFpiUWIhaTvkDZtGqt1_AuWt-vNJGE');
    }

    /**
     * Sync specific changed fields of an employee record to Google Sheet.
     *
     * @param  array<string>|null  $changedFields  Model attribute names that changed.
     *                                             Pass null to sync all managed columns (e.g. new record or manual full sync).
     */
    public function syncEmployee(Employee $employee, ?array $changedFields = null): void
    {
        try {
            // Find the sheet row for this employee
            $sheetRowNumber = $this->findSheetRow($employee->employee_code);

            if ($sheetRowNumber === null) {
                // Employee not found in sheet — append a full new row
                $this->appendEmployee($employee);

                return;
            }

            // Determine which columns to update
            if ($changedFields === null) {
                // Full sync: all managed columns
                $fieldsToSync = array_keys($this->columnMap);
            } else {
                // Partial sync: only the changed fields that have a column mapping
                $fieldsToSync = array_filter(
                    $changedFields,
                    fn (string $f) => isset($this->columnMap[$f])
                );
            }

            if (empty($fieldsToSync)) {
                return;
            }

            // Build individual ValueRange objects for each changed column
            $data = [];
            foreach ($fieldsToSync as $field) {
                $colIndex = $this->columnMap[$field];
                $colLetter = $this->columnLetter($colIndex);
                $cellRange = "Database!{$colLetter}{$sheetRowNumber}";
                $data[] = new ValueRange([
                    'range' => $cellRange,
                    'values' => [[$this->resolveFieldValue($employee, $field)]],
                ]);
            }

            $body = new BatchUpdateValuesRequest([
                'valueInputOption' => 'USER_ENTERED',
                'data' => $data,
            ]);

            $this->service->spreadsheets_values->batchUpdate(
                $this->spreadsheetId,
                $body
            );
        } catch (\Exception $e) {
            Log::error('Google Sheets Sync Failed: '.$e->getMessage());
        }
    }

    /**
     * Delete employee record from Google Sheet (clears the row values).
     */
    public function deleteEmployee(string $employeeCode): void
    {
        try {
            $sheetRowNumber = $this->findSheetRow($employeeCode);

            if ($sheetRowNumber === null) {
                return;
            }

            $clearRange = "Database!A{$sheetRowNumber}:AE{$sheetRowNumber}";
            $clearRequest = new ClearValuesRequest;
            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId,
                $clearRange,
                $clearRequest
            );
        } catch (\Exception $e) {
            Log::error('Google Sheets Delete Failed: '.$e->getMessage());
        }
    }

    /**
     * Find the 1-based row number of an employee in the Database sheet.
     * Returns null if not found.
     */
    protected function findSheetRow(string $employeeCode): ?int
    {
        // Only fetch column D (employee_code) for efficiency
        $response = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            'Database!D:D'
        );
        $rows = $response->getValues() ?: [];

        foreach ($rows as $idx => $row) {
            if (isset($row[0]) && trim($row[0]) === $employeeCode) {
                return $idx + 1; // 1-based
            }
        }

        return null;
    }

    /**
     * Append a brand-new employee row to the sheet.
     */
    protected function appendEmployee(Employee $employee): void
    {
        $newRow = array_fill(0, 31, '');

        foreach ($this->columnMap as $field => $colIndex) {
            $newRow[$colIndex] = $this->resolveFieldValue($employee, $field);
        }

        $newRow = array_map('strval', $newRow);

        $body = new ValueRange(['values' => [$newRow]]);
        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            'Database!A:AE',
            $body,
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    /**
     * Resolve the sheet cell value for a given model field.
     */
    protected function resolveFieldValue(Employee $employee, string $field): string
    {
        return match ($field) {
            'join_date' => $employee->join_date
                                    ? Carbon::parse($employee->join_date)->format('Y.m.d')
                                    : '',
            'dob_ad' => $employee->dob_ad
                                    ? Carbon::parse($employee->dob_ad)->format('d F, Y')
                                    : '',
            'department_id' => (string) ($employee->department?->name ?? ''),
            'designation_id' => (string) ($employee->designation?->name ?? ''),
            'tips_blank' => $employee->tips_blank ? 'TRUE' : 'FALSE',
            'publish_tips' => $employee->publish_tips ? 'TRUE' : 'FALSE',
            'tips_fixed' => $employee->tips_fixed ? 'TRUE' : 'FALSE',
            'tips_amount' => $employee->tips_amount !== null ? (string) $employee->tips_amount : '',
            'point_value' => $employee->point_value !== null ? (string) $employee->point_value : '',
            default => (string) ($employee->$field ?? ''),
        };
    }

    /**
     * Convert a 0-based column index to a spreadsheet column letter (A, B, ..., Z, AA, ...).
     */
    protected function columnLetter(int $index): string
    {
        $letter = '';
        $index++;  // make 1-based for the algorithm
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
