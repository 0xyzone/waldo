<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function birthdays(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $month = $request->integer('month');
        $year = $request->integer('year');

        // Joined before the 10th of the previous month relative to selected month/year.
        // Let's construct that cut-off date:
        $selectedDate = Carbon::createFromDate($year, $month, 1);
        $previousMonth = $selectedDate->copy()->subMonth();
        $cutoffDate = $previousMonth->copy()->setDay(10)->format('Y-m-d');

        $employees = Employee::with(['department', 'designation'])
            ->whereIn('employee_status', ['Active', 'Resigning this month'])
            ->whereMonth('dob_ad', $month)
            ->get()
            ->filter(function ($emp) use ($cutoffDate) {
                // join_date_formatted is stored as "dd Month, yyyy" string — parse it for comparison
                if (empty($emp->join_date_formatted)) {
                    return false;
                }
                try {
                    $cleanedDate = str_replace(',', '', $emp->join_date_formatted);

                    return Carbon::parse($cleanedDate)->format('Y-m-d') < $cutoffDate;
                } catch (\Exception) {
                    return false;
                }
            })
            ->map(function ($emp) {
                $emp->dob_day = $emp->dob_ad ? $emp->dob_ad->day : 99;

                return $emp;
            })
            // Sorting by Department Rank, Overall Rank, then Day of Birth
            ->sortBy([
                ['dp_rank', 'asc'],
                ['rank', 'asc'],
                ['dob_day', 'asc'],
            ]);

        $monthName = $selectedDate->format('F');

        return view('reports.birthdays', [
            'employees' => $employees,
            'monthName' => $monthName,
            'year' => $year,
        ]);
    }

    /**
     * Generate the printable department stats report.
     */
    public function departments(Request $request)
    {
        $departments = Department::where('is_active', true)
            ->with(['designations' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($department) {
                $departmentActiveCount = Employee::where('department_id', $department->id)
                    ->where('employee_status', 'Active')
                    ->count();

                $designations = $department->designations->map(function ($designation) {
                    $designationActiveCount = Employee::where('designation_id', $designation->id)
                        ->where('employee_status', 'Active')
                        ->count();

                    return [
                        'name' => $designation->name,
                        'count' => $designationActiveCount,
                    ];
                });

                return [
                    'name' => $department->name,
                    'count' => $departmentActiveCount,
                    'designations' => $designations,
                ];
            });

        return view('reports.departments', [
            'departments' => $departments,
        ]);
    }

    /**
     * View interactive department stats page.
     */
    public function viewDepartments(Request $request)
    {
        $departments = Department::where('is_active', true)
            ->with(['designations' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($department) {
                $departmentActiveCount = Employee::where('department_id', $department->id)
                    ->where('employee_status', 'Active')
                    ->count();

                $designations = $department->designations->map(function ($designation) {
                    $designationActiveCount = Employee::where('designation_id', $designation->id)
                        ->where('employee_status', 'Active')
                        ->count();

                    return [
                        'name' => $designation->name,
                        'count' => $designationActiveCount,
                    ];
                });

                // Fetch active employees with their designations
                $employees = Employee::with('designation')
                    ->where('department_id', $department->id)
                    ->where('employee_status', 'Active')
                    ->get()
                    ->map(function ($emp) {
                        return [
                            'code' => $emp->employee_code,
                            'name' => $emp->name,
                            'gender' => $emp->gender ?? 'N/A',
                            'join_date' => $emp->join_date_formatted ?? 'N/A',
                            'status' => $emp->employee_status,
                            'designation' => $emp->designation ? $emp->designation->name : 'N/A',
                            'tips_status' => $emp->tips_status ?? 'N/A',
                            'join_years' => $emp->join_date_formatted
                                ? (function () use ($emp) {
                                    try {
                                        return Carbon::parse($emp->join_date_formatted)->diffInYears(now());
                                    } catch (\Exception) {
                                        return 0;
                                    }
                                })()
                                : 0,
                        ];
                    });

                // Precompute statistics for the interactive detail view modal
                $totalEmp = $employees->count();
                $males = $employees->where('gender', 'Male')->count();
                $females = $employees->where('gender', 'Female')->count();
                $others = $totalEmp - $males - $females;

                $genderStats = [
                    'male_percent' => $totalEmp > 0 ? round(($males / $totalEmp) * 100) : 0,
                    'female_percent' => $totalEmp > 0 ? round(($females / $totalEmp) * 100) : 0,
                    'other_percent' => $totalEmp > 0 ? round(($others / $totalEmp) * 100) : 0,
                    'male_count' => $males,
                    'female_count' => $females,
                ];

                $avgTenure = $employees->avg('join_years') ?? 0;
                $avgTenureFormatted = round($avgTenure, 1);

                $tipsStats = $employees->groupBy('tips_status')->map(fn ($group) => $group->count())->toArray();

                return [
                    'name' => $department->name,
                    'count' => $departmentActiveCount,
                    'designations' => $designations,
                    'employees' => $employees->toArray(),
                    'stats' => [
                        'gender' => $genderStats,
                        'avg_tenure' => $avgTenureFormatted,
                        'tips' => $tipsStats,
                    ],
                ];
            });

        $totalEmployees = Employee::where('employee_status', 'Active')->count();
        $totalDepartments = $departments->count();

        $totalActiveDesignations = 0;
        $departments->each(function ($dept) use (&$totalActiveDesignations) {
            $totalActiveDesignations += collect($dept['designations'])->filter(fn ($d) => $d['count'] > 0)->count();
        });

        $avgEmployeesPerDept = $totalDepartments > 0 ? round($totalEmployees / $totalDepartments, 1) : 0;

        $sortedDepartments = $departments->sortByDesc('count');
        $topDepartmentName = $sortedDepartments->first() ? $sortedDepartments->first()['name'] : 'N/A';
        $topDepartmentCount = $sortedDepartments->first() ? $sortedDepartments->first()['count'] : 0;

        return view('reports.departments-view', [
            'departments' => $departments,
            'totalEmployees' => $totalEmployees,
            'totalDepartments' => $totalDepartments,
            'totalActiveDesignations' => $totalActiveDesignations,
            'avgEmployeesPerDept' => $avgEmployeesPerDept,
            'topDepartmentName' => $topDepartmentName,
            'topDepartmentCount' => $topDepartmentCount,
        ]);
    }
}
