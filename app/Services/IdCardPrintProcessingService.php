<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\IdCardPrintReport;
use App\Models\IdCardPrintReportItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class IdCardPrintProcessingService
{
    /**
     * Process the uploaded CSV or XLSX file and populate report items.
     */
    public function processUploadedFile(IdCardPrintReport $report): int
    {
        if (! $report->csv_file_path) {
            return 0;
        }

        $absolutePath = Storage::disk('public')->path($report->csv_file_path);
        if (! file_exists($absolutePath)) {
            return 0;
        }

        $rows = $this->extractRowsFromFile($absolutePath);
        if (empty($rows)) {
            return 0;
        }

        // Identify header columns
        $headerInfo = $this->detectHeaderColumns($rows);
        if ($headerInfo === null) {
            // If no clear header found, assume standard 0 => code, 1 => name, 2 => depart
            $codeCol = 0;
            $nameCol = 1;
            $deptCol = 2;
            $startRow = 0;
        } else {
            $codeCol = $headerInfo['code'];
            $nameCol = $headerInfo['name'];
            $deptCol = $headerInfo['depart'];
            $startRow = $headerInfo['start_row'];
        }

        // Preload employees indexed by normalized employee_code
        $employees = Employee::with(['department', 'designation'])
            ->get()
            ->keyBy(fn ($e) => strtoupper(trim((string) $e->employee_code)));

        // Also build a numeric index (e.g. '123' => Employee) for robust matching
        $employeesByNumeric = [];
        foreach ($employees as $code => $emp) {
            $numOnly = preg_replace('/[^0-9]/', '', $code);
            if ($numOnly !== '') {
                $employeesByNumeric[$numOnly] = $emp;
            }
        }

        $now = now();
        $itemsToInsert = [];

        for ($r = $startRow; $r < count($rows); $r++) {
            $cells = $rows[$r];
            if (empty($cells)) {
                continue;
            }

            $rawCode = $codeCol !== null && isset($cells[$codeCol]) ? trim((string) $cells[$codeCol]) : '';
            if ($rawCode === '') {
                continue;
            }

            $csvName = $nameCol !== null && isset($cells[$nameCol]) ? trim((string) $cells[$nameCol]) : null;
            $csvDepart = $deptCol !== null && isset($cells[$deptCol]) ? trim((string) $cells[$deptCol]) : null;

            // Find matching employee
            $upperCode = strtoupper($rawCode);
            $matchedEmp = $employees->get($upperCode);

            // Fallback numeric matching if direct code didn't hit
            if (! $matchedEmp) {
                $codeDigits = preg_replace('/[^0-9]/', '', $rawCode);
                if ($codeDigits !== '' && isset($employeesByNumeric[$codeDigits])) {
                    $matchedEmp = $employeesByNumeric[$codeDigits];
                }
            }

            if ($matchedEmp) {
                $employeeCode = $matchedEmp->employee_code;
                $employeeName = $matchedEmp->name ?: trim(($matchedEmp->first_name ?? '').' '.($matchedEmp->last_name ?? ''));
                $department = $matchedEmp->department?->name ?? $csvDepart;
                $designation = $matchedEmp->designation?->name ?? null;
            } else {
                $employeeCode = $rawCode;
                $employeeName = $csvName ?: $rawCode;
                $department = $csvDepart ?: 'General';
                $designation = null;
            }

            $itemsToInsert[] = [
                'id_card_print_report_id' => $report->id,
                'employee_code' => $employeeCode,
                'employee_name' => $employeeName ?: 'Unknown Employee',
                'department' => $department,
                'designation' => $designation,
                'status' => 'sent for print',
                'csv_name' => $csvName,
                'csv_department' => $csvDepart,
                'notes' => null,
                'status_updated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($report, $itemsToInsert) {
            $report->items()->delete();

            if (! empty($itemsToInsert)) {
                foreach (array_chunk($itemsToInsert, 250) as $chunk) {
                    IdCardPrintReportItem::insert($chunk);
                }
            }

            $report->update([
                'total_records' => count($itemsToInsert),
            ]);
        });

        return count($itemsToInsert);
    }

    /**
     * Resynchronize employee details (name, department, designation) from DB.
     */
    public function syncFromEmployeeDb(IdCardPrintReport $report): int
    {
        $items = $report->items()->get();
        if ($items->isEmpty()) {
            return 0;
        }

        $employees = Employee::with(['department', 'designation'])
            ->get()
            ->keyBy(fn ($e) => strtoupper(trim((string) $e->employee_code)));

        $updatedCount = 0;
        foreach ($items as $item) {
            $matchedEmp = $employees->get(strtoupper(trim((string) $item->employee_code)));
            if ($matchedEmp) {
                $name = $matchedEmp->name ?: trim(($matchedEmp->first_name ?? '').' '.($matchedEmp->last_name ?? ''));
                $item->update([
                    'employee_name' => $name ?: $item->employee_name,
                    'department' => $matchedEmp->department?->name ?? $item->department,
                    'designation' => $matchedEmp->designation?->name ?? $item->designation,
                ]);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Extract raw rows from either CSV or XLSX.
     *
     * @return array<int, array<int, mixed>>
     */
    protected function extractRowsFromFile(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($ext, ['xlsx', 'xls'])) {
            try {
                $reader = new XlsxReader;
                $reader->open($filePath);

                $rows = [];
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $cells = $row->toArray();
                        if (! empty(array_filter($cells, fn ($c) => trim((string) $c) !== ''))) {
                            $rows[] = $cells;
                        }
                    }
                    break;
                }
                $reader->close();

                return $rows;
            } catch (\Throwable) {
                // fallback to CSV if xlsx reader failed
            }
        }

        // CSV parsing
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [];
        }

        // Auto-detect delimiter
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        // Strip UTF-8 BOM if present
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        // Parse first line
        $firstCells = str_getcsv($firstLine, $delimiter);
        if (! empty($firstCells)) {
            $rows[] = $firstCells;
        }

        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (! empty(array_filter($cells, fn ($c) => trim((string) $c) !== ''))) {
                $rows[] = $cells;
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Detect header row and column positions for code, name, depart.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{code: ?int, name: ?int, depart: ?int, start_row: int}|null
     */
    protected function detectHeaderColumns(array $rows): ?array
    {
        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $row = $rows[$i];
            $codeCol = null;
            $nameCol = null;
            $deptCol = null;

            foreach ($row as $idx => $rawVal) {
                $val = strtolower(trim((string) $rawVal));

                // Code detection
                if (in_array($val, ['code', 'emp code', 'emp_code', 'employee code', 'employee_code', 'id', 'empid', 'emp_id']) || str_contains($val, 'code')) {
                    $codeCol = $idx;
                }

                // Name detection
                if (in_array($val, ['name', 'emp name', 'emp_name', 'employee name', 'employee_name', 'full name', 'fullname']) || (str_contains($val, 'name') && ! str_contains($val, 'code'))) {
                    $nameCol = $idx;
                }

                // Depart detection
                if (in_array($val, ['depart', 'department', 'dept', 'dept name', 'department name']) || str_contains($val, 'depart') || str_contains($val, 'dept')) {
                    $deptCol = $idx;
                }
            }

            // If at least code column is found
            if ($codeCol !== null) {
                return [
                    'code' => $codeCol,
                    'name' => $nameCol,
                    'depart' => $deptCol,
                    'start_row' => $i + 1,
                ];
            }
        }

        return null;
    }
}
