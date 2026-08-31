<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class DepartmentEmployeeStatsWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.department-employee-stats-widget';

    protected static ?int $sort = 3;

    // Full column span to let us constraint layout max-width in blade
    protected int|string|array $columnSpan = 'full';

    /**
     * Get statistics of departments and their designations.
     */
    public function getStatsProperty(): array
    {
        return Department::where('is_active', true)
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

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'count' => $departmentActiveCount,
                    'designations' => $designations->toArray(),
                    'employees' => $employees->toArray(),
                    'stats' => [
                        'gender' => $genderStats,
                        'avg_tenure' => $avgTenureFormatted,
                    ],
                ];
            })->toArray();
    }
}
