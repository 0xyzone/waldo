<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeApiController extends Controller
{
    /**
     * Display a listing of employees with filtering, searching, and relation includes.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Employee::query()
            ->with(['department', 'designation']);

        // Optional relationship includes
        if ($request->boolean('include_suspensions')) {
            $query->with(['suspensions', 'latestSuspension']);
        }
        if ($request->boolean('include_leaver')) {
            $query->with('leaver');
        }
        if ($request->boolean('include_termination')) {
            $query->with('terminatedEmployee');
        }
        if ($request->boolean('include_adjustments')) {
            $query->with(['adjustments', 'tipsAdjustment']);
        }
        if ($request->boolean('include_all')) {
            $query->with([
                'suspensions',
                'latestSuspension',
                'leaver',
                'terminatedEmployee',
                'adjustments',
                'tipsAdjustment',
            ]);
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('employee_status', $request->query('status'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->query('designation_id'));
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->filled('tips_status')) {
            $query->where('tips_status', $request->query('tips_status'));
        }

        // Search by keyword across code, name, email, phone, ssid, citizenship
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('ssid', 'like', "%{$search}%")
                    ->orWhere('citizenship_number', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'employee_code');
        $sortOrder = $request->query('sort_order', 'asc');
        $allowedSorts = ['employee_code', 'name', 'join_date_formatted', 'employee_status', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, strtolower($sortOrder) === 'desc' ? 'desc' : 'asc');
        }

        // Pagination or All
        if ($request->boolean('all')) {
            return EmployeeResource::collection($query->get());
        }

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        return EmployeeResource::collection($query->paginate($perPage)->withQueryString());
    }

    /**
     * Display the specified employee by employee_code with all relationships.
     */
    public function show(string $employeeCode): EmployeeResource|JsonResponse
    {
        $employee = Employee::with([
            'department',
            'designation',
            'suspensions',
            'latestSuspension',
            'leaver',
            'terminatedEmployee',
            'adjustments',
            'tipsAdjustment',
        ])->where('employee_code', $employeeCode)->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => "Employee with code '{$employeeCode}' was not found.",
            ], 404);
        }

        return new EmployeeResource($employee);
    }
}
