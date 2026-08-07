<?php

namespace App\Services;

use App\Models\Adjustment;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdjustmentExportService
{
    /**
     * Get list of all available columns for export.
     *
     * @return array<string, string>
     */
    public static function getAvailableColumns(): array
    {
        return [
            'employee_code' => 'Employee Code',
            'employee_name' => 'Full Name',
            'department' => 'Department',
            'designation' => 'Designation',
            'type' => 'Adjustment Type',
            'for_month' => 'Target Month',
            'status' => 'Status',
            'notes_by_hr' => 'HR Notes',
            'notes_by_finance' => 'Finance Notes',
            'created_at' => 'Created Date',
        ];
    }

    /**
     * Get value for a given column key on an adjustment record.
     */
    public static function getCellValue(Adjustment $adjustment, string $columnKey): mixed
    {
        return match ($columnKey) {
            'employee_code' => $adjustment->employee?->employee_code ?? $adjustment->employee_id ?? '-',
            'employee_name' => $adjustment->employee?->name ?? '-',
            'department' => $adjustment->employee?->department?->name ?? '-',
            'designation' => $adjustment->employee?->designation?->name ?? '-',
            'type' => match ($adjustment->type) {
                'add' => 'Addition (+)',
                'subtract' => 'Deduction (-)',
                default => $adjustment->type ?? '-',
            },
            'for_month' => ucfirst((string) $adjustment->for_month),
            'status' => ucfirst((string) $adjustment->status),
            'notes_by_hr' => $adjustment->notes_by_hr ?? '-',
            'notes_by_finance' => $adjustment->notes_by_finance ?? '-',
            'created_at' => $adjustment->created_at ? $adjustment->created_at->format('Y-m-d H:i') : '-',
            default => '-',
        };
    }

    /**
     * Export adjustments collection to CSV or Excel streamed response.
     */
    public function export(Collection $adjustments, array $selectedColumns, string $format = 'xlsx', bool $applyStyling = true): StreamedResponse
    {
        $available = static::getAvailableColumns();
        $columns = array_intersect_key($available, array_flip($selectedColumns));
        if (empty($columns)) {
            $columns = $available;
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'adjustments_report_'.now()->format('Y_m_d_His').'.'.$extension;

        return response()->streamDownload(function () use ($adjustments, $columns, $format, $applyStyling) {
            $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
            $writer->openToFile('php://output');

            // Header Row Styling
            $headerStyle = $format === 'csv' ? null : (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $writer->addRow(Row::fromValues(array_values($columns), $headerStyle));

            // Data Rows
            foreach ($adjustments as $adjustment) {
                $rowValues = [];
                foreach (array_keys($columns) as $colKey) {
                    $rowValues[] = static::getCellValue($adjustment, $colKey);
                }

                $rowStyle = ($format === 'csv' || ! $applyStyling)
                    ? null
                    : match ($adjustment->type) {
                        'add' => (new Style)->setBackgroundColor('DCFCE7')->setFontColor('14532D'),
                        'subtract' => (new Style)->setBackgroundColor('FEE2E2')->setFontColor('991B1B'),
                        default => null,
                    };

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
