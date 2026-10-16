<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeePromotion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class EmployeePromotionImportService
{
    /**
     * Import employee promotions from an uploaded Excel (.xlsx) or CSV file.
     * Operates purely at the database model level and NEVER dispatches Google Sheet sync jobs.
     *
     * @param  string  $filePath  Absolute path to the uploaded file
     * @param  bool  $updateExisting  Whether to update records matching employee and promotion_date
     * @return array{
     *     total_rows: int,
     *     imported: int,
     *     updated: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     */
    public function import(string $filePath, bool $updateExisting = false): array
    {
        $rows = $this->extractRowsFromFile($filePath);

        if (empty($rows)) {
            return [
                'total_rows' => 0,
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['The uploaded file is empty or contains no readable data rows.'],
            ];
        }

        // Parse header row
        $headerRow = array_shift($rows);
        $colMap = $this->resolveColumnMap($headerRow);

        $departments = Department::all();
        $designations = Designation::all();
        $employees = Employee::with(['department', 'designation'])->get()->keyBy(fn (Employee $e) => strtoupper(trim((string) $e->employee_code)));

        $total = count($rows);
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // 1-indexed, +1 for header

            $rawUsername = $this->getCellValue($row, $colMap['username'] ?? 0);
            if ($rawUsername === '') {
                $skipped++;

                continue;
            }

            $empCode = strtoupper(trim($rawUsername));
            $employee = $employees->get($empCode);

            if (! $employee) {
                $skipped++;
                $errors[] = "Row {$rowNum}: Employee code '{$empCode}' not found in the employee directory.";

                continue;
            }

            // Resolve promotion date
            $rawDate = $this->getCellValue($row, $colMap['start_date'] ?? 1);
            $promotionDate = $this->parseDate($rawDate);

            if (! $promotionDate) {
                $skipped++;
                $errors[] = "Row {$rowNum}: Invalid or missing promotion date for employee {$empCode}.";

                continue;
            }

            // Resolve target department
            $rawDivision = $this->getCellValue($row, $colMap['division'] ?? 5);
            $toDepartmentId = $this->resolveDepartmentId($rawDivision, $departments);

            // Default to current employee department if unmapped
            if (! $toDepartmentId) {
                $toDepartmentId = $employee->department_id;
            }

            // Resolve target designation
            $rawJobTitle = $this->getCellValue($row, $colMap['job_title'] ?? 3);
            $toDesignationId = $this->resolveDesignationId($rawJobTitle, $toDepartmentId, $designations);

            if (! $toDesignationId) {
                $skipped++;
                $errors[] = "Row {$rowNum}: Could not resolve designation '{$rawJobTitle}' for employee {$empCode}.";

                continue;
            }

            // From department and designation snapshots
            $fromDeptId = $employee->department_id;
            $fromDesigId = $employee->designation_id;

            // Check if record already exists for this employee and promotion date
            $existing = EmployeePromotion::where('employee_id', $empCode)
                ->where('promotion_date', $promotionDate->toDateString())
                ->first();

            if ($existing) {
                if ($updateExisting) {
                    EmployeePromotion::withoutEvents(function () use ($existing, $fromDeptId, $fromDesigId, $toDepartmentId, $toDesignationId) {
                        $existing->updateQuietly([
                            'from_department_id' => $fromDeptId,
                            'from_designation_id' => $fromDesigId,
                            'to_department_id' => $toDepartmentId,
                            'to_designation_id' => $toDesignationId,
                        ]);
                    });
                    $updated++;
                } else {
                    $skipped++;
                }

                continue;
            }

            // Create record quietly without triggering sheet sync or background jobs
            EmployeePromotion::withoutEvents(function () use ($empCode, $fromDeptId, $fromDesigId, $toDepartmentId, $toDesignationId, $promotionDate) {
                $promo = new EmployeePromotion([
                    'employee_id' => $empCode,
                    'from_department_id' => $fromDeptId,
                    'from_designation_id' => $fromDesigId,
                    'to_department_id' => $toDepartmentId,
                    'to_designation_id' => $toDesignationId,
                    'promotion_date' => $promotionDate->toDateString(),
                    'acknowledged' => false,
                    'hrms_synced' => false,
                    'remarks' => 'Imported via Excel',
                ]);
                $promo->saveQuietly();
            });

            $imported++;
        }

        return [
            'total_rows' => $total,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 15), // Show up to 15 concise errors
        ];
    }

    /**
     * Resolve column indexes from header values.
     *
     * @param  array<int, mixed>  $header
     * @return array<string, int>
     */
    protected function resolveColumnMap(array $header): array
    {
        $map = [
            'username' => 0,
            'start_date' => 1,
            'job_title' => 3,
            'division' => 5,
        ];

        foreach ($header as $idx => $val) {
            $slug = Str::slug(trim((string) $val), '_');

            if (in_array($slug, ['username', 'employee_id', 'emp_code', 'employee_code', 'code', 'emp_id'])) {
                $map['username'] = $idx;
            } elseif (in_array($slug, ['start_date', 'promotion_date', 'date', 'effective_date'])) {
                $map['start_date'] = $idx;
            } elseif (in_array($slug, ['job_title', 'to_designation', 'designation', 'new_designation', 'promoted_designation'])) {
                $map['job_title'] = $idx;
            } elseif (in_array($slug, ['division', 'to_department', 'department', 'new_department', 'promoted_department'])) {
                $map['division'] = $idx;
            }
        }

        return $map;
    }

    /**
     * Resolve department ID by division slug or name.
     *
     * @param  Collection<int, Department>  $departments
     */
    public function resolveDepartmentId(?string $rawDivision, $departments): ?int
    {
        if ($rawDivision === null || trim($rawDivision) === '') {
            return null;
        }

        $division = trim($rawDivision);
        $slug = Str::slug($division);

        // Normalize reverse mapping from HRMS
        $reverseMap = [
            'purchase-store' => 'purchase-and-store',
            'food-beverage-service' => 'food-beverage',
            'finance' => 'account-finance',
        ];

        $normalizedSlug = $reverseMap[$slug] ?? $slug;

        foreach ($departments as $dept) {
            if ((string) $dept->id === $division) {
                return $dept->id;
            }
            if (strcasecmp($dept->name, $division) === 0) {
                return $dept->id;
            }
            $deptSlug = Str::slug($dept->name);
            if ($deptSlug === $slug || $deptSlug === $normalizedSlug) {
                return $dept->id;
            }
        }

        return null;
    }

    /**
     * Resolve designation ID by job title slug or name, prioritizing within department.
     *
     * @param  Collection<int, Designation>  $designations
     */
    public function resolveDesignationId(?string $rawJobTitle, ?int $deptId, $designations): ?int
    {
        if ($rawJobTitle === null || trim($rawJobTitle) === '') {
            return null;
        }

        $jobTitle = trim($rawJobTitle);
        $slug = Str::slug($jobTitle);

        // Standard abbreviation expansions and normalizations
        $slugVariants = [
            $slug,
            preg_replace('/^sr-/', 'senior-', $slug),
            preg_replace('/^senior-/', 'sr-', $slug),
            preg_replace('/^jr-/', 'junior-', $slug),
            preg_replace('/^junior-/', 'jr-', $slug),
            preg_replace('/^tr-/', 'trainee-', $slug),
            preg_replace('/^trainee-/', 'tr-', $slug),
            preg_replace('/^asst-/', 'assistant-', $slug),
            preg_replace('/^assistant-/', 'asst-', $slug),
        ];

        // 1. First pass: Search within department
        if ($deptId) {
            foreach ($designations->where('department_id', $deptId) as $desig) {
                if ((string) $desig->id === $jobTitle) {
                    return $desig->id;
                }
                if (strcasecmp($desig->name, $jobTitle) === 0) {
                    return $desig->id;
                }
                $desigSlug = Str::slug($desig->name);
                if (in_array($desigSlug, $slugVariants, true)) {
                    return $desig->id;
                }
            }
        }

        // 2. Second pass: Search across all designations
        foreach ($designations as $desig) {
            if ((string) $desig->id === $jobTitle) {
                return $desig->id;
            }
            if (strcasecmp($desig->name, $jobTitle) === 0) {
                return $desig->id;
            }
            $desigSlug = Str::slug($desig->name);
            if (in_array($desigSlug, $slugVariants, true)) {
                return $desig->id;
            }
        }

        return null;
    }

    /**
     * Safely parse various date formats into Carbon.
     */
    public function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        if (empty($value) || ! is_scalar($value)) {
            return null;
        }

        $str = trim((string) $value);

        // Numeric Excel timestamp
        if (is_numeric($str) && (float) $str > 25569) {
            $unix = ((float) $str - 25569) * 86400;

            return Carbon::createFromTimestampUTC((int) $unix)->startOfDay();
        }

        try {
            return Carbon::parse($str)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Extract cells from raw row.
     */
    protected function getCellValue(array $row, int $index): string
    {
        return isset($row[$index]) ? trim((string) $row[$index]) : '';
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
                        $rawCells = $row->toArray();
                        $cells = array_map(function ($c) {
                            if ($c instanceof \DateTimeInterface) {
                                return $c->format('Y-m-d');
                            }

                            return is_scalar($c) ? $c : '';
                        }, $rawCells);

                        if (! empty(array_filter($cells, fn ($c) => trim((string) $c) !== ''))) {
                            $rows[] = $cells;
                        }
                    }
                    break;
                }
                $reader->close();

                return $rows;
            } catch (\Throwable) {
                // fallback to CSV parser
            }
        }

        // CSV parsing
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        // Strip UTF-8 BOM
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($rows) && isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $data[0]);
            }

            if (! empty(array_filter($data, fn ($c) => trim((string) $c) !== ''))) {
                $rows[] = $data;
            }
        }

        fclose($handle);

        return $rows;
    }
}
