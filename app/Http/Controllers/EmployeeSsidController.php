<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class EmployeeSsidController extends Controller
{
    /**
     * Display a listing of employee SSIDs with search functionality.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $departmentId = $request->input('department_id');
        $employeeStatus = $request->input('status');

        $query = Employee::with(['department', 'designation']);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('ssid', 'like', "%{$search}%");
            });
        }

        if (! empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($employeeStatus)) {
            $query->where('employee_status', $employeeStatus);
        }

        // Default ordering by employee_code
        $employees = $query->orderBy('employee_code', 'asc')->paginate(20)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $statuses = Employee::whereNotNull('employee_status')
            ->where('employee_status', '!=', '')
            ->distinct()
            ->orderBy('employee_status')
            ->pluck('employee_status');

        // All employees for export modal employee selection dropdown
        $allEmployeesForExport = Employee::select('employee_code', 'name', 'first_name', 'last_name', 'department_id', 'employee_status')
            ->with('department:id,name')
            ->orderBy('employee_code', 'asc')
            ->get();

        $totalCount = Employee::count();
        $withSsidCount = Employee::whereNotNull('ssid')->where('ssid', '!=', '')->count();

        return view('employees.ssid-list', [
            'employees' => $employees,
            'departments' => $departments,
            'statuses' => $statuses,
            'allEmployeesForExport' => $allEmployeesForExport,
            'search' => $search,
            'selectedDepartment' => $departmentId,
            'selectedStatus' => $employeeStatus,
            'totalCount' => $totalCount,
            'withSsidCount' => $withSsidCount,
        ]);
    }

    /**
     * Export SSID report to formatted Excel file.
     */
    public function exportExcel(Request $request)
    {
        $departmentId = $request->input('department_id');
        $employeeStatus = $request->input('employee_status');
        $employeeIds = array_filter((array) $request->input('employee_ids', []));

        $query = Employee::with(['department', 'designation']);

        if (! empty($employeeIds)) {
            $query->whereIn('employee_code', $employeeIds);
        } else {
            if (! empty($departmentId)) {
                $query->where('department_id', $departmentId);
            }
            if (! empty($employeeStatus)) {
                $query->where('employee_status', $employeeStatus);
            }
        }

        $employees = $query->orderBy('dp_rank', 'asc')
            ->orderBy('rank', 'asc')
            ->orderBy('employee_code', 'asc')
            ->get();

        $deptName = 'All Departments';
        if (! empty($departmentId)) {
            $dept = Department::find($departmentId);
            if ($dept) {
                $deptName = $dept->name;
            }
        }

        $statusLabel = ! empty($employeeStatus) ? $employeeStatus : 'All Statuses';
        if (! empty($employeeIds)) {
            $selectionLabel = count($employeeIds).' Selected Employees';
        } else {
            $selectionLabel = "{$deptName} | Status: {$statusLabel}";
        }

        $fileName = 'employee_ssid_report_'.now()->format('Y_m_d_His').'.xlsx';

        return response()->streamDownload(function () use ($employees, $selectionLabel) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Styles
            $titleStyle = (new Style)
                ->setFontBold()
                ->setFontSize(16)
                ->setFontColor('0F172A');

            $metaStyle = (new Style)
                ->setFontSize(10)
                ->setFontColor('475569');

            $headerStyle = (new Style)
                ->setFontBold()
                ->setFontColor('FFFFFF')
                ->setBackgroundColor('0F172A');

            $disclaimerStyle = (new Style)
                ->setFontItalic()
                ->setFontSize(9)
                ->setFontColor('B91C1C');

            // Title Row
            $writer->addRow(Row::fromValues(['EMPLOYEE SSID & DIRECTORY REPORT'], $titleStyle));
            $writer->addRow(Row::fromValues(['Generated On: '.now()->format('F j, Y - h:i A').' | Filter: '.$selectionLabel], $metaStyle));
            $writer->addRow(Row::fromValues([])); // Blank row

            // Table Headers
            $headers = ['S.N.', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'SSID', 'Status'];
            $writer->addRow(Row::fromValues($headers, $headerStyle));

            // Data Rows
            $index = 1;
            foreach ($employees as $employee) {
                $status = (string) ($employee->employee_status ?? 'Active');
                $rowStyle = match (trim($status)) {
                    'Active' => (new Style)->setBackgroundColor('DCFCE7')->setFontColor('14532D'),
                    'Resigned' => (new Style)->setBackgroundColor('FFE4E6')->setFontColor('9F1239'),
                    'Resigning this month', 'Resigning This Month' => (new Style)->setBackgroundColor('F3E8FF')->setFontColor('581C87'),
                    'Terminated' => (new Style)->setBackgroundColor('FEE2E2')->setFontColor('991B1B'),
                    default => null,
                };

                $name = $employee->name ?? trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) ?: '-';

                $rowValues = [
                    $index++,
                    $employee->employee_code ?? '-',
                    $name,
                    $employee->department?->name ?? '-',
                    $employee->designation?->name ?? '-',
                    $employee->ssid ?? '-',
                    $status,
                ];

                $writer->addRow(Row::fromValues($rowValues, $rowStyle));
            }

            // Summary & Disclaimer
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues([
                'DISCLAIMER: The information contained in this report is generated from system records and may be subject to updates. Please verify with HR if any information is mistaken or incomplete.',
            ], $disclaimerStyle));

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
