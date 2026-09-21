<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NametagFine;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NametagFineExportService
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
            'reason' => 'Reason',
            'amount' => 'Fine Amount (NPR)',
            'for_month_year' => 'Target Month/Year',
            'created_by' => 'Initiated By',
            'acknowledged' => 'Acknowledged Status',
            'acknowledged_by' => 'Acknowledged By',
            'acknowledged_at' => 'Acknowledged At',
            'remarks' => 'Remarks',
            'created_at' => 'Recorded At',
        ];
    }

    /**
     * Get cell value for a given column key on a fine record.
     */
    public static function getCellValue(NametagFine $record, string $columnKey): mixed
    {
        return match ($columnKey) {
            'employee_id' => $record->employee_id ?? '-',
            'employee_name' => $record->employee?->name ?? '-',
            'department' => $record->employee?->department?->name ?? '-',
            'designation' => $record->employee?->designation?->name ?? '-',
            'reason' => $record->reason ?? '-',
            'amount' => (float) ($record->amount ?? 500),
            'for_month_year' => ucfirst((string) $record->for_month).' '.$record->for_year,
            'created_by' => $record->creator?->name ?? 'System',
            'acknowledged' => $record->acknowledged ? 'Yes' : 'No',
            'acknowledged_by' => $record->acknowledger?->name ?? '-',
            'acknowledged_at' => $record->acknowledged_at ? $record->acknowledged_at->format('Y-m-d H:i') : '-',
            'remarks' => $record->remarks ?? '-',
            'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i') : '-',
            default => '-',
        };
    }

    /**
     * Export fines collection to CSV or Excel streamed response.
     *
     * @param  Collection<int, NametagFine>  $records
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
        $fileName = 'nametag_fines_report_'.now()->format('Y_m_d_His').'.'.$extension;

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
                    $rowStyle = $record->acknowledged
                        ? (new Style)->setBackgroundColor('D1FAE5')->setFontColor('065F46')
                        : (new Style)->setBackgroundColor('FEE2E2')->setFontColor('991B1B');
                }

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
