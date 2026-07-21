<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
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

                return [
                    'name' => $department->name,
                    'count' => $departmentActiveCount,
                    'designations' => $designations,
                ];
            })->toArray();
    }
}
