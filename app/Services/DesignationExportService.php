<?php

namespace App\Services;

use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DesignationExportService
{
    /**
     * Get list of all available columns for export.
     *
     * @return array<string, string>
     */
    public static function getAvailableColumns(): array
    {
        return [
            'department' => 'Department',
            'name' => 'Designation Title',
            'rank' => 'Sort Rank',
            'active_staff' => 'Active Staff Count',
            'is_active' => 'Active Status',
            'has_description' => 'JD Added?',
            'job_description' => 'Job Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get value for a given column key on a designation record.
     */
    public static function getCellValue(Designation $record, string $columnKey): mixed
    {
        return match ($columnKey) {
            'department' => $record->department?->name ?? '-',
            'name' => $record->name ?? '-',
            'rank' => $record->rank !== null ? $record->rank : '-',
            'active_staff' => $record->relationLoaded('employees')
                ? $record->employees->where('employee_status', 'Active')->count()
                : Employee::where('designation_id', $record->id)->where('employee_status', 'Active')->count(),
            'is_active' => $record->is_active ? 'Active' : 'Inactive',
            'has_description' => ! empty($record->job_description) ? 'Yes' : 'No',
            'job_description' => static::formatJobDescription($record->job_description),
            'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i') : '-',
            'updated_at' => $record->updated_at ? $record->updated_at->format('Y-m-d H:i') : '-',
            default => '-',
        };
    }

    /**
     * Format rich-text job description to clean plain text for spreadsheet export.
     */
    public static function formatJobDescription(?string $jobDescription): string
    {
        if (empty($jobDescription)) {
            return '-';
        }

        $text = str_replace(
            ['<br>', '<br/>', '<br />', '</p>', '</li>', '</h1>', '</h2>', '</h3>'],
            ["\n", "\n", "\n", "\n\n", "\n", "\n\n", "\n\n", "\n\n"],
            $jobDescription
        );

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize excess line breaks
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text) ?: '-';
    }

    /**
     * Export designations collection to CSV or Excel streamed response.
     *
     * @param  Collection<int, Designation>  $records
     * @param  array<string>  $selectedColumns
     */
    public function export(Collection $records, array $selectedColumns, string $format = 'xlsx', bool $applyStyling = true): StreamedResponse
    {
        $available = static::getAvailableColumns();
        $columns = array_intersect_key($available, array_flip($selectedColumns));
        if (empty($columns)) {
            $columns = $available;
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'designations_report_'.now()->format('Y_m_d_His').'.'.$extension;

        return response()->streamDownload(function () use ($records, $columns, $format, $applyStyling) {
            $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
            $writer->openToFile('php://output');

            // Header Row Styling
            $headerStyle = $format === 'csv' ? null : (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $writer->addRow(Row::fromValues(array_values($columns), $headerStyle));

            // Data Rows
            foreach ($records as $record) {
                $rowValues = [];
                foreach (array_keys($columns) as $colKey) {
                    $rowValues[] = static::getCellValue($record, $colKey);
                }

                $rowStyle = null;
                if ($format !== 'csv' && $applyStyling) {
                    if (! $record->is_active) {
                        $rowStyle = (new Style)->setBackgroundColor('FEE2E2')->setFontColor('991B1B');
                    } elseif (empty($record->job_description)) {
                        $rowStyle = (new Style)->setBackgroundColor('FEF3C7')->setFontColor('92400E');
                    } else {
                        $rowStyle = (new Style)->setBackgroundColor('F0FDF4')->setFontColor('166534');
                    }
                }

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
