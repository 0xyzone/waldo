<?php

namespace App\Services;

use App\Models\TerminatedEmployee;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TerminatedEmployeeExportService
{
    /**
     * Get list of all available columns for export.
     *
     * @return array<string, string>
     */
    public static function getAvailableColumns(): array
    {
        return [
            'employee_id' => 'Employee Code',
            'employee_name' => 'Full Name',
            'department' => 'Department',
            'designation' => 'Designation',
            'termination_date' => 'Date of Termination',
            'last_working_date' => 'Last Date of Working',
            'reason' => 'Reason',
            'created_at' => 'Recorded At',
        ];
    }

    /**
     * Get value for a given column key on a terminated employee record.
     */
    public static function getCellValue(TerminatedEmployee $record, string $columnKey): mixed
    {
        return match ($columnKey) {
            'employee_id' => $record->employee_id ?? '-',
            'employee_name' => $record->employee?->name ?? '-',
            'department' => $record->employee?->department?->name ?? '-',
            'designation' => $record->employee?->designation?->name ?? '-',
            'termination_date' => $record->termination_date ? (is_string($record->termination_date) ? $record->termination_date : $record->termination_date->format('Y-m-d')) : '-',
            'last_working_date' => $record->last_working_date ? (is_string($record->last_working_date) ? $record->last_working_date : $record->last_working_date->format('Y-m-d')) : '-',
            'reason' => $record->reason ?? '-',
            'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i') : '-',
            default => '-',
        };
    }

    /**
     * Export terminated employees collection to CSV or Excel streamed response.
     */
    public function export(Collection $records, array $selectedColumns, string $format = 'xlsx', bool $applyStyling = true): StreamedResponse
    {
        $available = static::getAvailableColumns();
        $columns = array_intersect_key($available, array_flip($selectedColumns));
        if (empty($columns)) {
            $columns = $available;
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'terminated_employees_report_'.now()->format('Y_m_d_His').'.'.$extension;

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

                $rowStyle = ($format === 'csv' || ! $applyStyling)
                    ? null
                    : (new Style)->setBackgroundColor('FEE2E2')->setFontColor('991B1B');

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
