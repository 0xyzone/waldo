<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeExportService
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
            'name' => 'Full Name',
            'department' => 'Department',
            'designation' => 'Designation',
            'dp_rank' => 'Department Rank',
            'rank' => 'Overall Rank',
            'employee_status' => 'Status',
            'join_date_formatted' => 'Join Date',
            'gender' => 'Gender',
            'marital_status' => 'Marital Status',
            'dob_ad' => 'Date of Birth (AD)',
            'dob_bs' => 'Date of Birth (BS)',
            'email' => 'Email Address',
            'contact_number' => 'Contact Number',
            'citizenship_number' => 'Citizenship Number',
            'citizenship_issue_date' => 'Citizenship Issue Date',
            'citizenship_issue_place' => 'Citizenship Issue Place',
            'ssid' => 'SSID',
            'tips_amount' => 'Tips Amount (₹)',
            'point_value' => 'Point Value',
            'profile_status' => 'Profile Status',
        ];
    }

    /**
     * Get style corresponding to employee status for Excel formatting.
     */
    public static function getStyleForStatus(string $status, bool $applyStyling): ?Style
    {
        if (! $applyStyling) {
            return null;
        }

        return match (trim($status)) {
            'Active' => (new Style)->setBackgroundColor('DCFCE7')->setFontColor('14532D'),
            'Inactive' => (new Style)->setBackgroundColor('F3F4F6')->setFontColor('374151'),
            'Resigned' => (new Style)->setBackgroundColor('FFE4E6')->setFontColor('9F1239'),
            'Resigning this month', 'Resigning This Month' => (new Style)->setBackgroundColor('F3E8FF')->setFontColor('581C87'),
            'Terminated' => (new Style)->setBackgroundColor('FEE2E2')->setFontColor('991B1B'),
            default => null,
        };
    }

    /**
     * Get value for a given column key on an employee record.
     */
    public static function getCellValue(Employee $employee, string $columnKey): mixed
    {
        return match ($columnKey) {
            'employee_code' => $employee->employee_code,
            'name' => $employee->name,
            'department' => $employee->department?->name ?? '-',
            'designation' => $employee->designation?->name ?? '-',
            'dp_rank' => $employee->dp_rank ?? '-',
            'rank' => $employee->rank ?? '-',
            'employee_status' => $employee->employee_status ?? '-',
            'join_date_formatted' => $employee->join_date_formatted ?? '-',
            'gender' => $employee->gender ?? '-',
            'marital_status' => $employee->marital_status ?? '-',
            'dob_ad' => $employee->dob_ad ? $employee->dob_ad->format('Y-m-d') : '-',
            'dob_bs' => $employee->dob_bs ?? '-',
            'email' => $employee->email ?? '-',
            'contact_number' => $employee->contact_number ?? '-',
            'citizenship_number' => $employee->citizenship_number ?? '-',
            'citizenship_issue_date' => $employee->citizenship_issue_date ?? '-',
            'citizenship_issue_place' => $employee->citizenship_issue_place ?? '-',
            'ssid' => $employee->ssid ?? '-',
            'tips_amount' => $employee->tips_amount !== null ? (float) $employee->tips_amount : '-',
            'point_value' => $employee->point_value !== null ? (float) $employee->point_value : '-',
            'profile_status' => $employee->isIncomplete() ? 'Incomplete' : 'Complete',
            default => '-',
        };
    }

    /**
     * Human-readable labels for the fields checked in the incomplete check.
     *
     * @return array<string, string>
     */
    public static function getIncompleteFieldLabels(): array
    {
        return [
            'designation_id' => 'Designation',
            'name' => 'Name',
            'gender' => 'Gender',
            'join_date_formatted' => 'Join Date',
            'contact_number' => 'Contact Number',
            'email' => 'Email',
            'citizenship_number' => 'Citizenship ID',
            'citizenship_issue_date' => 'Citizenship Issue Date',
            'citizenship_issue_place' => 'Citizenship Issue Place',
            'dob_ad' => 'Date of Birth',
            'marital_status' => 'Marital Status',
            'tips_amount' => 'Tips Amount',
            'point_value' => 'Point Value',
        ];
    }

    /**
     * Export only incomplete active employees with their missing details.
     */
    public function exportIncomplete(Collection $employees, string $format = 'xlsx'): StreamedResponse
    {
        $fieldLabels = static::getIncompleteFieldLabels();

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'incomplete_employees_'.now()->format('Y_m_d_His').'.'.$extension;

        return response()->streamDownload(function () use ($employees, $format, $fieldLabels) {
            $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
            $writer->openToFile('php://output');

            // Header row
            $headerStyle = $format === 'csv' ? null : (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $writer->addRow(Row::fromValues([
                'Code',
                'Name',
                'Department',
                'Designation',
                'Missing Details',
            ], $headerStyle));

            // Sort by dp_rank then rank
            $employees = $employees->sortBy([
                ['dp_rank', 'asc'],
                ['rank', 'asc'],
            ]);

            foreach ($employees as $employee) {
                $missingFields = collect($fieldLabels)
                    ->filter(fn ($label, $field) => empty($employee->$field))
                    ->values()
                    ->implode(', ');

                if (empty($missingFields)) {
                    continue;
                }

                $writer->addRow(Row::fromValues([
                    $employee->employee_code,
                    $employee->name ?? '-',
                    $employee->department?->name ?? '-',
                    $employee->designation?->name ?? '-',
                    $missingFields,
                ]));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export employee collection to CSV or Excel streamed response.
     */
    public function export(Collection $employees, array $selectedColumns, string $format = 'xlsx', bool $applyStyling = true): StreamedResponse
    {
        $available = static::getAvailableColumns();
        $columns = array_intersect_key($available, array_flip($selectedColumns));
        if (empty($columns)) {
            $columns = $available;
        }

        // Sort employees by Department Rank (dp_rank), then Overall Rank (rank)
        $employees = $employees->sortBy([
            ['dp_rank', 'asc'],
            ['rank', 'asc'],
        ]);

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'employees_report_'.now()->format('Y_m_d_His').'.'.$extension;

        return response()->streamDownload(function () use ($employees, $columns, $format, $applyStyling) {
            $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
            $writer->openToFile('php://output');

            // Header Row Styling
            $headerStyle = $format === 'csv' ? null : (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $writer->addRow(Row::fromValues(array_values($columns), $headerStyle));

            // Data Rows
            foreach ($employees as $employee) {
                $rowValues = [];
                foreach (array_keys($columns) as $colKey) {
                    $rowValues[] = static::getCellValue($employee, $colKey);
                }

                $rowStyle = ($format === 'csv')
                    ? null
                    : static::getStyleForStatus((string) $employee->employee_status, $applyStyling);

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export department employees collection to CSV or Excel streamed response.
     */
    public function exportDepartmentEmployees(string $deptName, Collection $employees, string $format = 'xlsx'): StreamedResponse
    {
        $safeDeptName = Str::slug($deptName, '_');

        $columns = [
            'employee_code' => 'Employee Code',
            'name' => 'Full Name',
            'gender' => 'Gender',
            'join_date_formatted' => 'Join Date',
            'designation' => 'Designation',
            'tips_status' => 'Tips Status',
            'contact_number' => 'Contact Number',
            'employee_status' => 'Status',
        ];

        // Sort employees by Department Rank (dp_rank), then Overall Rank (rank), then Name
        $employees = $employees->sortBy([
            ['dp_rank', 'asc'],
            ['rank', 'asc'],
            ['name', 'asc'],
        ]);

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'active_employees_'.$safeDeptName.'_'.now()->format('Y_m_d_His').'.'.$extension;

        return response()->streamDownload(function () use ($employees, $columns, $format) {
            $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
            $writer->openToFile('php://output');

            // Header Row Styling
            $headerStyle = $format === 'csv' ? null : (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('1E293B');

            $writer->addRow(Row::fromValues(array_values($columns), $headerStyle));

            // Data Rows
            foreach ($employees as $employee) {
                $rowValues = [
                    $employee->employee_code,
                    $employee->name,
                    $employee->gender ?? '-',
                    $employee->join_date_formatted ?? '-',
                    $employee->designation?->name ?? ($employee->designation ?? '-'),
                    $employee->tips_status ?? '-',
                    $employee->contact_number ?? '-',
                    $employee->employee_status ?? '-',
                ];

                $rowStyle = ($format === 'csv')
                    ? null
                    : static::getStyleForStatus((string) $employee->employee_status, true);

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
