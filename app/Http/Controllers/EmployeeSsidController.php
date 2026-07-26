<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeSsidController extends Controller
{
    /**
     * Display a listing of employee SSIDs with search functionality.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $departmentId = $request->input('department_id');

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

        // Default ordering by employee_code
        $employees = $query->orderBy('employee_code', 'asc')->paginate(20)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $totalCount = Employee::count();
        $withSsidCount = Employee::whereNotNull('ssid')->where('ssid', '!=', '')->count();

        return view('employees.ssid-list', [
            'employees' => $employees,
            'departments' => $departments,
            'search' => $search,
            'selectedDepartment' => $departmentId,
            'totalCount' => $totalCount,
            'withSsidCount' => $withSsidCount,
        ]);
    }
}
