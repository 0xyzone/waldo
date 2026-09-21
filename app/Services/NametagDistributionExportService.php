<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NametagDistribution;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NametagDistributionExportService
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
            'date' => 'Distribution Date',
            'status' => 'Status',
            'remarks' => 'Remarks',
            'created_at' => 'Recorded At',
        ];
    }

    /**
     * Get cell value for a given column key on a distribution record.
     */
    public static function getCellValue(NametagDistribution $record, string $columnKey): mixed
    {
        return match ($columnKey) {
            'employee_id' => $record->employee_id ?? '-',
            'employee_name' => $record->employee?->name ?? '-',
            'department' => $record->employee?->department?->name ?? '-',
            'designation' => $record->employee?->designation?->name ?? '-',
            'date' => $record->date ? $record->date->format('Y-m-d') : '-',
            'status' => $record->status ?? '-',
            'remarks' => $record->remarks ?? '-',
            'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i') : '-',
            default => '-',
        };
    }

    /**
     * Export distributions collection to CSV or Excel streamed response.
     *
     * @param  Collection<int, NametagDistribution>  $records
     * @param  array<int, string>  $selectedColumns
     */
    public function export(Collection $records, array $selectedColumns, string $format = 'xlsx', bool $applyStyling = true): StreamedResponse
    {
        $available = static::getAvailableColumns();
        $columns = array_intersect_key($available, array_flip($selectedColumns));
        if (empty($columns)) {
            $columns = $available;
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'nametag_distributions_report_'.now()->format('Y_m_d_His').'.'.$extension;

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
                    $rowStyle = match ($record->status) {
                        'Pending' => (new Style)->setBackgroundColor('FEF3C7')->setFontColor('92400E'),
                        'Printed' => (new Style)->setBackgroundColor('DBEAFE')->setFontColor('1E40AF'),
                        'Released' => (new Style)->setBackgroundColor('D1FAE5')->setFontColor('065F46'),
                        default => null,
                    };
                }

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
