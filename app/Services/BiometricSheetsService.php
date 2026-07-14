<?php

namespace App\Services;

use App\Models\BiometricAllotment;
use App\Models\Department;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BiometricSheetsService
{
    protected Client $client;

    protected Sheets $service;

    protected string $spreadsheetId;

    protected string $sheetName = "Don't Modify Without Permission";

    /**
     * Map of BiometricAllotment model attribute -> sheet column index (0-based).
     *
     * @var array<string, int>
     */
    protected array $columnMap = [
        'code' => 0,
        'name' => 1,
        'department_id' => 2, // resolved to department name
        'status' => 3,
        'enrolled_date' => 4,
        'set_by' => 5,
        'old_checkout_device' => 6,
        'new_checkin' => 7,
        'new_checkout' => 8,
        'shift' => 9,
        'remarks' => 10,
    ];

    public function __construct()
    {
        $this->client = new Client;
        if (config('app.env') === 'local') {
            $this->client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        }
        $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
        $this->spreadsheetId = env('GOOGLE_BIOMETRIC_SPREADSHEET_ID', '15yIpkm8tXifnXvhPC1kw8GCxO2aWzl12NNKswqJ_fts');
    }

    /**
     * Pull biometric allotments from Google Sheets to database.
     */
    public function sync(): int
    {
        $range = "'{$this->sheetName}'!A2:K";
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues() ?: [];

        $syncedCount = 0;

        // Run without triggering model events during pull sync to prevent infinite loops
        BiometricAllotment::withoutEvents(function () use ($rows, &$syncedCount) {
            foreach ($rows as $row) {
                if (count($row) < 11) {
                    $row = array_pad($row, 11, '');
                }

                $code = trim($row[0] ?? '');
                if (empty($code) || strtolower($code) === 'code') {
                    continue;
                }

                // Resolve department by name
                $departmentName = trim($row[2] ?? '');
                $department = null;
                if (! empty($departmentName)) {
                    $department = Department::firstOrCreate(['name' => $departmentName]);
                }

                // Parse enrolled date
                $enrolledDate = null;
                $dateStr = trim($row[4] ?? '');
                if (! empty($dateStr)) {
                    try {
                        $enrolledDate = Carbon::parse($dateStr)->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::warning('Failed to parse enrolled date: '.$dateStr);
                    }
                }

                BiometricAllotment::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => trim($row[1] ?? '') ?: null,
                        'department_id' => $department?->id,
                        'status' => trim($row[3] ?? '') ?: null,
                        'enrolled_date' => $enrolledDate,
                        'set_by' => trim($row[5] ?? '') ?: null,
                        'old_checkout_device' => filter_var($row[6] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'new_checkin' => filter_var($row[7] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'new_checkout' => filter_var($row[8] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'shift' => trim($row[9] ?? '') ?: null,
                        'remarks' => trim($row[10] ?? '') ?: null,
                    ]
                );

                $syncedCount++;
            }
        });

        return $syncedCount;
    }

    /**
     * Push biometric allotment updates back to Google Sheet.
     */
    public function syncAllotment(BiometricAllotment $allotment, ?array $changedFields = null): void
    {
        Log::info('BiometricSheetsService syncAllotment triggered', [
            'code' => $allotment->code,
            'changedFields' => $changedFields,
        ]);

        try {
            $sheetRowNumber = $this->findSheetRow($allotment->code);

            Log::info('BiometricSheetsService findSheetRow result', [
                'row' => $sheetRowNumber,
            ]);

            if ($sheetRowNumber === null) {
                $this->appendAllotment($allotment);

                return;
            }

            if ($changedFields === null) {
                $fieldsToSync = array_keys($this->columnMap);
            } else {
                $fieldsToSync = array_filter(
                    $changedFields,
                    fn (string $f) => isset($this->columnMap[$f])
                );
            }

            if (empty($fieldsToSync)) {
                return;
            }

            $data = [];
            foreach ($fieldsToSync as $field) {
                $colIndex = $this->columnMap[$field];
                $colLetter = $this->columnLetter($colIndex);
                $cellRange = "'{$this->sheetName}'!{$colLetter}{$sheetRowNumber}";
                $data[] = new ValueRange([
                    'range' => $cellRange,
                    'values' => [[$this->resolveFieldValue($allotment, $field)]],
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
            Log::error('Google Sheets Biometric Sync Failed: '.$e->getMessage());
        }
    }

    /**
     * Clear row values from Google Sheet on deletion.
     */
    public function deleteAllotment(string $code): void
    {
        try {
            $sheetRowNumber = $this->findSheetRow($code);
            if ($sheetRowNumber === null) {
                return;
            }

            $clearRange = "'{$this->sheetName}'!A{$sheetRowNumber}:K{$sheetRowNumber}";
            $clearRequest = new ClearValuesRequest;
            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId,
                $clearRange,
                $clearRequest
            );
        } catch (\Exception $e) {
            Log::error('Google Sheets Biometric Delete Failed: '.$e->getMessage());
        }
    }

    /**
     * Find sheet row by code.
     */
    protected function findSheetRow(string $code): ?int
    {
        $response = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            "'{$this->sheetName}'!A:A"
        );
        $rows = $response->getValues() ?: [];

        foreach ($rows as $idx => $row) {
            if (isset($row[0]) && trim($row[0]) === $code) {
                return $idx + 1; // 1-based
            }
        }

        return null;
    }

    /**
     * Find first empty row in Column A.
     */
    protected function findFirstEmptyRow(): int
    {
        $response = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            "'{$this->sheetName}'!A:A"
        );
        $rows = $response->getValues() ?: [];

        for ($i = 1; $i < count($rows); $i++) {
            $val = isset($rows[$i][0]) ? trim($rows[$i][0]) : '';
            if ($val === '') {
                return $i + 1;
            }
        }

        return count($rows) + 1;
    }

    /**
     * Append a new row to the sheet.
     */
    protected function appendAllotment(BiometricAllotment $allotment): void
    {
        $targetRow = $this->findFirstEmptyRow();
        $newRow = array_fill(0, 11, '');

        foreach ($this->columnMap as $field => $colIndex) {
            $newRow[$colIndex] = $this->resolveFieldValue($allotment, $field);
        }

        $newRow = array_map('strval', $newRow);

        $body = new ValueRange(['values' => [$newRow]]);
        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            "'{$this->sheetName}'!A{$targetRow}:K{$targetRow}",
            $body,
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    /**
     * Resolve the sheet cell value for a given model field.
     */
    protected function resolveFieldValue(BiometricAllotment $allotment, string $field): string
    {
        return match ($field) {
            'enrolled_date' => $allotment->enrolled_date
                ? Carbon::parse($allotment->enrolled_date)->format('n/j/Y') // e.g. 5/24/2025 matches sheet
                : '',
            'department_id' => (string) ($allotment->department?->name ?? ''),
            'old_checkout_device' => $allotment->old_checkout_device ? 'TRUE' : 'FALSE',
            'new_checkin' => $allotment->new_checkin ? 'TRUE' : 'FALSE',
            'new_checkout' => $allotment->new_checkout ? 'TRUE' : 'FALSE',
            default => (string) ($allotment->$field ?? ''),
        };
    }

    /**
     * Convert 0-based column index to letter.
     */
    protected function columnLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
