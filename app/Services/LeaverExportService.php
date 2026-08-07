<?php

namespace App\Services;

use App\Models\Leaver;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaverExportService
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
            'leaving_date' => 'Leaving Date',
            'hold_salary' => 'Hold Salary',
            'hold_tips' => 'Hold Tips',
            'publish_cl' => 'Publish CL',
            'created_at' => 'Recorded At',
        ];
    }

    /**
     * Get value for a given column key on a leaver record.
     */
    public static function getCellValue(Leaver $leaver, string $columnKey): mixed
    {
        return match ($columnKey) {
            'employee_id' => $leaver->employee_id ?? '-',
            'employee_name' => $leaver->employee?->name ?? '-',
            'department' => $leaver->employee?->department?->name ?? '-',
            'designation' => $leaver->employee?->designation?->name ?? '-',
            'leaving_date' => $leaver->leaving_date ? (is_string($leaver->leaving_date) ? $leaver->leaving_date : $leaver->leaving_date->format('Y-m-d')) : '-',
            'hold_salary' => $leaver->hold_salary ? 'Yes' : 'No',
            'hold_tips' => $leaver->hold_tips ? 'Yes' : 'No',
            'publish_cl' => $leaver->publish_cl ? 'Yes' : 'No',
            'created_at' => $leaver->created_at ? $leaver->created_at->format('Y-m-d H:i') : '-',
            default => '-',
        };
    }

    /**
     * Export leavers collection to CSV or Excel streamed response.
     */
    public function export(Collection $leavers, array $selectedColumns, string $format = 'xlsx', bool $applyStyling = true): StreamedResponse
    {
        $available = static::getAvailableColumns();
        $columns = array_intersect_key($available, array_flip($selectedColumns));
        if (empty($columns)) {
            $columns = $available;
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'leavers_report_'.now()->format('Y_m_d_His').'.'.$extension;

        return response()->streamDownload(function () use ($leavers, $columns, $format, $applyStyling) {
            $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
            $writer->openToFile('php://output');

            // Header Row Styling
            $headerStyle = $format === 'csv' ? null : (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $writer->addRow(Row::fromValues(array_values($columns), $headerStyle));

            // Data Rows
            foreach ($leavers as $leaver) {
                $rowValues = [];
                foreach (array_keys($columns) as $colKey) {
                    $rowValues[] = static::getCellValue($leaver, $colKey);
                }

                $rowStyle = ($format === 'csv' || ! $applyStyling)
                    ? null
                    : (new Style)->setBackgroundColor('FFE4E6')->setFontColor('9F1239');

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
