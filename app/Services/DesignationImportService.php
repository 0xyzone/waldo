<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DesignationImportService
{
    /**
     * Download a sample CSV template for importing designation job descriptions.
     */
    public function downloadSampleTemplate(): StreamedResponse
    {
        $fileName = 'designation_job_descriptions_sample.csv';

        return response()->streamDownload(function () {
            $writer = new CsvWriter;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Designation',
                'Job Description',
                'Department',
            ]));

            $writer->addRow(Row::fromValues([
                'General Manager',
                "Oversees daily operations, strategy, and organizational growth.\nCoordinates departmental heads and reports to executive board.",
                'General Management',
            ]));

            $writer->addRow(Row::fromValues([
                'Shift Manager',
                "Manages shift schedules, team members, and daily floor workflow.\nEnsures quality customer service and operational compliance.",
                'Food & Beverage',
            ]));

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Download sample CSV or Excel file populated with existing designations and their default tips values.
     */
    public function downloadTipsSampleTemplate(string $format = 'csv'): StreamedResponse
    {
        $extension = $format === 'xlsx' ? 'xlsx' : 'csv';
        $fileName = 'designations_default_tips_sample_'.now()->format('Y_m_d_His').'.'.$extension;

        $designations = Designation::with('department')
            ->ordered()
            ->get();

        return response()->streamDownload(function () use ($designations, $format) {
            $writer = $format === 'xlsx' ? new XlsxWriter : new CsvWriter;
            $writer->openToFile('php://output');

            $headers = ['ID', 'Designation Title', 'Department', 'Default Tips Amount'];

            $headerStyle = $format === 'xlsx' ? (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B') : null;

            $writer->addRow(Row::fromValues($headers, $headerStyle));

            foreach ($designations as $desig) {
                $tipsVal = $desig->default_tips_amount !== null ? (float) $desig->default_tips_amount : '';
                $writer->addRow(Row::fromValues([
                    $desig->id,
                    $desig->name,
                    $desig->department?->name ?? '',
                    $tipsVal,
                ]));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'xlsx'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'text/csv',
        ]);
    }

    /**
     * Import job descriptions from an uploaded CSV or XLSX file.
     *
     * @return array{
     *     total_rows: int,
     *     updated: int,
     *     skipped: int,
     *     unmatched: int,
     *     unmatched_names: array<string>
     * }
     */
    public function importFile(string $filePath, bool $overwriteExisting = true): array
    {
        $absolutePath = $filePath;
        if (Storage::disk('public')->exists($filePath)) {
            $absolutePath = Storage::disk('public')->path($filePath);
        } elseif (Storage::disk('local')->exists($filePath)) {
            $absolutePath = Storage::disk('local')->path($filePath);
        }

        if (! file_exists($absolutePath)) {
            throw new \RuntimeException("The uploaded file could not be located at: {$absolutePath}");
        }

        $rows = $this->extractRowsFromFile($absolutePath);

        // Safely remove temp file after loading
        @unlink($absolutePath);

        if (empty($rows)) {
            return [
                'total_rows' => 0,
                'updated' => 0,
                'skipped' => 0,
                'unmatched' => 0,
                'unmatched_names' => [],
            ];
        }

        $headerInfo = $this->detectHeaderColumns($rows);

        $designationCol = $headerInfo['designation'];
        $descriptionCol = $headerInfo['description'];
        $departmentCol = $headerInfo['department'];
        $idCol = $headerInfo['id'];
        $tipsCol = $headerInfo['tips'] ?? null;
        $startRow = $headerInfo['start_row'];

        // Preload departments & designations for fast lookup
        $departments = Department::all()->keyBy(fn ($d) => strtolower(trim((string) $d->name)));

        // Preload active & inactive designations
        $designationsByName = Designation::all()->groupBy(fn ($d) => strtolower(trim((string) $d->name)));
        $designationsById = Designation::all()->keyBy('id');

        $updatedCount = 0;
        $skippedCount = 0;
        $unmatchedCount = 0;
        $unmatchedNames = [];
        $totalProcessed = 0;

        DB::transaction(function () use (
            $rows,
            $startRow,
            $designationCol,
            $descriptionCol,
            $departmentCol,
            $idCol,
            $tipsCol,
            $departments,
            $designationsByName,
            $designationsById,
            $overwriteExisting,
            &$updatedCount,
            &$skippedCount,
            &$unmatchedCount,
            &$unmatchedNames,
            &$totalProcessed
        ) {
            $rowCount = count($rows);

            for ($i = $startRow; $i < $rowCount; $i++) {
                $row = $rows[$i];

                $rawId = $idCol !== null ? trim((string) ($row[$idCol] ?? '')) : '';
                $rawDesig = $designationCol !== null ? trim((string) ($row[$designationCol] ?? '')) : '';
                $rawDesc = $descriptionCol !== null ? trim((string) ($row[$descriptionCol] ?? '')) : '';
                $rawDept = $departmentCol !== null ? trim((string) ($row[$departmentCol] ?? '')) : '';

                if ($rawId === '' && $rawDesig === '') {
                    continue;
                }

                $totalProcessed++;

                /** @var Designation|null $matchedDesignation */
                $matchedDesignation = null;

                // 1. Try matching by ID if present
                if ($rawId !== '' && is_numeric($rawId)) {
                    $matchedDesignation = $designationsById->get((int) $rawId);
                }

                // 2. Try matching by Department + Name if department specified
                if (! $matchedDesignation && $rawDesig !== '' && $rawDept !== '') {
                    $matchedDept = $departments->get(strtolower($rawDept));
                    if ($matchedDept) {
                        $group = $designationsByName->get(strtolower($rawDesig));
                        if ($group) {
                            $matchedDesignation = $group->firstWhere('department_id', $matchedDept->id);
                        }
                    }
                }

                // 3. Fallback to matching by designation name
                if (! $matchedDesignation && $rawDesig !== '') {
                    $group = $designationsByName->get(strtolower($rawDesig));
                    if ($group && $group->isNotEmpty()) {
                        $matchedDesignation = $group->first();
                    }
                }

                if (! $matchedDesignation) {
                    $unmatchedCount++;
                    if (! in_array($rawDesig, $unmatchedNames, true) && $rawDesig !== '') {
                        $unmatchedNames[] = $rawDesig;
                    }

                    continue;
                }

                $hasChanges = false;

                // Update description if present
                if ($rawDesc !== '') {
                    $formattedDesc = $this->formatDescriptionForStorage($rawDesc);
                    if ($overwriteExisting || empty($matchedDesignation->job_description)) {
                        $matchedDesignation->job_description = $formattedDesc;
                        $hasChanges = true;
                    }
                }

                // Update tips if present
                if ($tipsCol !== null) {
                    $tipsAmount = $this->sanitizeTipsAmount($row[$tipsCol] ?? null);
                    if ($tipsAmount !== null && ($overwriteExisting || $matchedDesignation->default_tips_amount === null)) {
                        $matchedDesignation->default_tips_amount = $tipsAmount;
                        $hasChanges = true;
                    }
                }

                if ($hasChanges) {
                    $matchedDesignation->save();
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            }
        });

        return [
            'total_rows' => $totalProcessed,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'unmatched' => $unmatchedCount,
            'unmatched_names' => array_slice($unmatchedNames, 0, 10),
        ];
    }

    /**
     * Normalize description string into HTML paragraphs if plain text.
     */
    protected function formatDescriptionForStorage(string $rawDesc): ?string
    {
        $desc = trim($rawDesc);
        if ($desc === '') {
            return null;
        }

        // If it already has HTML formatting, preserve it
        if (str_contains($desc, '<p>') || str_contains($desc, '<br>') || str_contains($desc, '<div>') || str_contains($desc, '<ul>')) {
            return $desc;
        }

        // Convert multi-line plain text to clean paragraphs and line breaks
        $paragraphs = preg_split('/\r?\n\s*\r?\n/', $desc);
        $htmlParts = array_map(function ($para) {
            $cleaned = trim($para);

            return $cleaned !== '' ? '<p>'.nl2br(e($cleaned)).'</p>' : '';
        }, $paragraphs ?: []);

        $html = implode('', array_filter($htmlParts));

        return $html !== '' ? $html : null;
    }

    /**
     * Detect column indices based on header keywords or fallback to default positions.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{
     *     designation: int|null,
     *     description: int|null,
     *     department: int|null,
     *     id: int|null,
     *     start_row: int
     * }
     */
    protected function detectHeaderColumns(array $rows): array
    {
        $firstRow = $rows[0] ?? [];

        $desigCol = null;
        $descCol = null;
        $deptCol = null;
        $idCol = null;
        $isHeaderRow = false;

        foreach ($firstRow as $idx => $cell) {
            $norm = strtolower(trim((string) $cell));
            $norm = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-'], '_', $norm));

            if (in_array($norm, ['designation', 'designation_name', 'name', 'title', 'job_designation', 'designation_title', 'job_title'])) {
                $desigCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['job_description', 'description', 'jd', 'job_desc', 'job_details', 'responsibilities', 'details'])) {
                $descCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['department', 'department_name', 'dept'])) {
                $deptCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['id', 'designation_id'])) {
                $idCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['default_tips_amount', 'default_tips', 'tips_amount', 'tips', 'default_tips_value', 'tips_value', 'amount', 'expected_tips', 'base_tips', 'tips_amount_inr', 'tips_amount_npr'])) {
                $tipsCol = $idx;
                $isHeaderRow = true;
            }
        }

        if ($isHeaderRow) {
            // If designation was not found explicitly, take first available column
            if ($desigCol === null && count($firstRow) > 0) {
                $desigCol = 0;
            }
            if ($descCol === null && count($firstRow) > 1 && $firstRow[1] !== ($firstRow[$tipsCol ?? -1] ?? null)) {
                $descCol = ($desigCol === 0) ? 1 : 0;
            }

            return [
                'designation' => $desigCol,
                'description' => $descCol,
                'department' => $deptCol,
                'id' => $idCol,
                'tips' => $tipsCol ?? null,
                'start_row' => 1,
            ];
        }

        // If no header row detected, assume: Col 0: Designation, Col 1: Job Description, Col 2: Department
        return [
            'designation' => 0,
            'description' => count($firstRow) > 1 ? 1 : null,
            'department' => count($firstRow) > 2 ? 2 : null,
            'id' => null,
            'tips' => null,
            'start_row' => 0,
        ];
    }

    /**
     * Import default tips values from an uploaded CSV or XLSX file.
     *
     * @return array{
     *     total_rows: int,
     *     updated: int,
     *     skipped: int,
     *     unmatched: int,
     *     unmatched_names: array<string>
     * }
     */
    public function importTipsFile(string $filePath, bool $overwriteExisting = true): array
    {
        $absolutePath = $filePath;
        if (Storage::disk('public')->exists($filePath)) {
            $absolutePath = Storage::disk('public')->path($filePath);
        } elseif (Storage::disk('local')->exists($filePath)) {
            $absolutePath = Storage::disk('local')->path($filePath);
        }

        if (! file_exists($absolutePath)) {
            throw new \RuntimeException("The uploaded file could not be located at: {$absolutePath}");
        }

        $rows = $this->extractRowsFromFile($absolutePath);

        // Safely remove temp file after loading
        @unlink($absolutePath);

        if (empty($rows)) {
            return [
                'total_rows' => 0,
                'updated' => 0,
                'skipped' => 0,
                'unmatched' => 0,
                'unmatched_names' => [],
            ];
        }

        $headerInfo = $this->detectTipsHeaderColumns($rows);

        $idCol = $headerInfo['id'];
        $designationCol = $headerInfo['designation'];
        $departmentCol = $headerInfo['department'];
        $tipsCol = $headerInfo['tips'];
        $startRow = $headerInfo['start_row'];

        if ($tipsCol === null) {
            throw new \InvalidArgumentException('Could not detect a tips column in the uploaded file. Please make sure the header contains "Default Tips Amount" or "Tips".');
        }

        // Preload departments & designations for fast lookup
        $departments = Department::all()->keyBy(fn ($d) => strtolower(trim((string) $d->name)));
        $designationsByName = Designation::all()->groupBy(fn ($d) => strtolower(trim((string) $d->name)));
        $designationsById = Designation::all()->keyBy('id');

        $updatedCount = 0;
        $skippedCount = 0;
        $unmatchedCount = 0;
        $unmatchedNames = [];
        $totalProcessed = 0;

        DB::transaction(function () use (
            $rows,
            $startRow,
            $idCol,
            $designationCol,
            $departmentCol,
            $tipsCol,
            $departments,
            $designationsByName,
            $designationsById,
            $overwriteExisting,
            &$updatedCount,
            &$skippedCount,
            &$unmatchedCount,
            &$unmatchedNames,
            &$totalProcessed
        ) {
            $rowCount = count($rows);

            for ($i = $startRow; $i < $rowCount; $i++) {
                $row = $rows[$i];

                $rawId = $idCol !== null ? trim((string) ($row[$idCol] ?? '')) : '';
                $rawDesig = $designationCol !== null ? trim((string) ($row[$designationCol] ?? '')) : '';
                $rawDept = $departmentCol !== null ? trim((string) ($row[$departmentCol] ?? '')) : '';
                $rawTips = $tipsCol !== null ? ($row[$tipsCol] ?? null) : null;

                if ($rawId === '' && $rawDesig === '') {
                    continue;
                }

                $totalProcessed++;

                /** @var Designation|null $matchedDesignation */
                $matchedDesignation = null;

                // 1. Try matching by ID if present
                if ($rawId !== '' && is_numeric($rawId)) {
                    $matchedDesignation = $designationsById->get((int) $rawId);
                }

                // 2. Try matching by Department + Name if department specified
                if (! $matchedDesignation && $rawDesig !== '' && $rawDept !== '') {
                    $matchedDept = $departments->get(strtolower($rawDept));
                    if ($matchedDept) {
                        $group = $designationsByName->get(strtolower($rawDesig));
                        if ($group) {
                            $matchedDesignation = $group->firstWhere('department_id', $matchedDept->id);
                        }
                    }
                }

                // 3. Fallback to matching by designation name
                if (! $matchedDesignation && $rawDesig !== '') {
                    $group = $designationsByName->get(strtolower($rawDesig));
                    if ($group && $group->isNotEmpty()) {
                        $matchedDesignation = $group->first();
                    }
                }

                if (! $matchedDesignation) {
                    $unmatchedCount++;
                    if (! in_array($rawDesig, $unmatchedNames, true) && $rawDesig !== '') {
                        $unmatchedNames[] = $rawDesig;
                    }

                    continue;
                }

                $tipsAmount = $this->sanitizeTipsAmount($rawTips);

                if ($tipsAmount === null) {
                    $skippedCount++;

                    continue;
                }

                if (! $overwriteExisting && $matchedDesignation->default_tips_amount !== null) {
                    $skippedCount++;

                    continue;
                }

                $matchedDesignation->default_tips_amount = $tipsAmount;
                $matchedDesignation->save();
                $updatedCount++;
            }
        });

        return [
            'total_rows' => $totalProcessed,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'unmatched' => $unmatchedCount,
            'unmatched_names' => array_slice($unmatchedNames, 0, 10),
        ];
    }

    /**
     * Detect column indices specifically for tips import.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{
     *     id: int|null,
     *     designation: int|null,
     *     department: int|null,
     *     tips: int|null,
     *     start_row: int
     * }
     */
    protected function detectTipsHeaderColumns(array $rows): array
    {
        $firstRow = $rows[0] ?? [];
        $idCol = null;
        $desigCol = null;
        $deptCol = null;
        $tipsCol = null;
        $isHeaderRow = false;

        foreach ($firstRow as $idx => $cell) {
            $norm = strtolower(trim((string) $cell));
            $norm = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-', '.'], '_', $norm));

            if (in_array($norm, ['id', 'designation_id', 'desig_id'])) {
                $idCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['designation', 'designation_title', 'designation_name', 'name', 'title', 'job_title', 'job_designation'])) {
                $desigCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['department', 'department_name', 'dept', 'dept_name'])) {
                $deptCol = $idx;
                $isHeaderRow = true;
            } elseif (in_array($norm, ['default_tips_amount', 'default_tips', 'tips_amount', 'tips', 'default_tips_value', 'tips_value', 'amount', 'expected_tips', 'base_tips', 'tips_amount_inr', 'tips_amount_npr'])) {
                $tipsCol = $idx;
                $isHeaderRow = true;
            }
        }

        if ($isHeaderRow) {
            return [
                'id' => $idCol,
                'designation' => $desigCol,
                'department' => $deptCol,
                'tips' => $tipsCol,
                'start_row' => 1,
            ];
        }

        // Fallback for headerless files:
        // If 4 columns: [ID, Designation, Department, Tips]
        if (count($firstRow) >= 4) {
            return [
                'id' => 0,
                'designation' => 1,
                'department' => 2,
                'tips' => 3,
                'start_row' => 0,
            ];
        }

        // If 3 columns: [Designation, Department, Tips]
        if (count($firstRow) === 3) {
            return [
                'id' => null,
                'designation' => 0,
                'department' => 1,
                'tips' => 2,
                'start_row' => 0,
            ];
        }

        // If 2 columns: [Designation, Tips]
        return [
            'id' => null,
            'designation' => 0,
            'department' => null,
            'tips' => 1,
            'start_row' => 0,
        ];
    }

    /**
     * Sanitize and extract float amount from raw cell value.
     */
    public function sanitizeTipsAmount(mixed $rawVal): ?float
    {
        if ($rawVal === null) {
            return null;
        }

        $clean = trim((string) $rawVal);
        if ($clean === '' || $clean === '-' || strtolower($clean) === 'null') {
            return null;
        }

        // Remove currency symbols, commas, spaces
        $clean = str_replace([',', ' ', '₹', '$', 'Rs.', 'Rs', 'NPR', 'INR'], '', $clean);
        $clean = preg_replace('/[^\d.]/', '', $clean);

        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return round((float) $clean, 2);
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

        // Auto-detect delimiter
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
            // Strip BOM from first column if present on first row
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
