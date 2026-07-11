<?php

namespace App\Services;

use App\Models\Employee;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected Client $client;

    protected Sheets $service;

    protected string $spreadsheetId;

    public function __construct()
    {
        $this->client = new Client;
        $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);

        // Disable SSL verification for local WAMP environment (no CA bundle)
        $guzzle = new \GuzzleHttp\Client(['verify' => false]);
        $this->client->setHttpClient($guzzle);

        $this->service = new Sheets($this->client);
        $this->spreadsheetId = env('GOOGLE_SPREADSHEET_ID', '1i8M_P8KejphnCEFpiUWIhaTvkDZtGqt1_AuWt-vNJGE');
    }

    /**
     * Sync employee record to Google Sheet.
     */
    public function syncEmployee(Employee $employee): void
    {
        try {
            $range = 'Database!A:AE';
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            $rows = $response->getValues() ?: [];

            $rowIndex = -1;
            $existingRow = [];

            // Find the employee in the Sheet by employee_code (index 3)
            foreach ($rows as $idx => $row) {
                if (isset($row[3]) && trim($row[3]) === $employee->employee_code) {
                    $rowIndex = $idx;
                    $existingRow = $row;
                    break;
                }
            }

            // Build new row content, merging with existing row to preserve columns like S. No. or duplicate DOB BS
            $newRow = array_pad($existingRow, 31, '');

            $newRow[0] = (string) ($employee->dp_rank ?? '');
            $newRow[1] = (string) ($employee->rank ?? '');
            // $newRow[2] is S. No., preserved from existingRow or empty if new
            $newRow[3] = (string) $employee->employee_code;
            $newRow[4] = (string) ($employee->name ?? '');
            $newRow[5] = (string) ($employee->gender ?? '');
            $newRow[6] = (string) ($employee->join_date_formatted ?? '');
            $newRow[7] = $employee->join_date ? Carbon::parse($employee->join_date)->format('Y.m.d') : '';
            $newRow[8] = (string) ($employee->department?->name ?? '');
            $newRow[9] = (string) ($employee->designation?->name ?? '');
            $newRow[10] = (string) ($employee->contact_number ?? '');
            $newRow[11] = (string) ($employee->email ?? '');
            $newRow[12] = (string) ($employee->citizenship_number ?? '');
            $newRow[13] = (string) ($employee->citizenship_issue_date ?? '');
            $newRow[14] = (string) ($employee->citizenship_issue_place ?? '');
            $newRow[15] = (string) ($employee->ssid ?? '');
            $newRow[16] = $employee->dob_ad ? Carbon::parse($employee->dob_ad)->format('d F, Y') : '';
            $newRow[17] = (string) ($employee->dob_bs ?? '');
            // $newRow[18] is duplicate DOB BS, preserved from existingRow
            $newRow[19] = (string) ($employee->marital_status ?? '');
            $newRow[20] = (string) ($employee->employee_status ?? '');
            $newRow[21] = $employee->tips_amount !== null ? (string) $employee->tips_amount : '';
            $newRow[22] = (string) ($employee->tips_status ?? '');
            $newRow[23] = $employee->point_value !== null ? (string) $employee->point_value : '';
            $newRow[24] = $employee->tips_blank ? 'TRUE' : 'FALSE';
            $newRow[25] = $employee->publish_tips ? 'TRUE' : 'FALSE';
            $newRow[26] = $employee->tips_fixed ? 'TRUE' : 'FALSE';
            $newRow[27] = (string) ($employee->hrms_password ?? '');
            $newRow[28] = (string) ($employee->first_name ?? '');
            $newRow[29] = (string) ($employee->middle_name ?? '');
            $newRow[30] = (string) ($employee->last_name ?? '');

            // Convert all elements to strings to match Sheets API requirements
            $newRow = array_map('strval', $newRow);

            if ($rowIndex !== -1) {
                // Update existing row
                // Google Sheets ranges are 1-based, so row index 0 is A1
                $sheetRowNumber = $rowIndex + 1;
                $updateRange = "Database!A{$sheetRowNumber}:AE{$sheetRowNumber}";
                $body = new ValueRange(['values' => [$newRow]]);
                $this->service->spreadsheets_values->update(
                    $this->spreadsheetId,
                    $updateRange,
                    $body,
                    ['valueInputOption' => 'USER_ENTERED']
                );
            } else {
                // Append new row
                $appendRange = 'Database!A:AE';
                $body = new ValueRange(['values' => [$newRow]]);
                $this->service->spreadsheets_values->append(
                    $this->spreadsheetId,
                    $appendRange,
                    $body,
                    ['valueInputOption' => 'USER_ENTERED']
                );
            }
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
            $range = 'Database!A:AE';
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            $rows = $response->getValues() ?: [];

            $rowIndex = -1;
            foreach ($rows as $idx => $row) {
                if (isset($row[3]) && trim($row[3]) === $employeeCode) {
                    $rowIndex = $idx;
                    break;
                }
            }

            if ($rowIndex !== -1) {
                $sheetRowNumber = $rowIndex + 1;
                $clearRange = "Database!A{$sheetRowNumber}:AE{$sheetRowNumber}";
                $clearRequest = new ClearValuesRequest;
                $this->service->spreadsheets_values->clear(
                    $this->spreadsheetId,
                    $clearRange,
                    $clearRequest
                );
            }
        } catch (\Exception $e) {
            Log::error('Google Sheets Delete Failed: '.$e->getMessage());
        }
    }
}
