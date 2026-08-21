<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    /**
     * Display the interactive API documentation and test console.
     */
    public function index(Request $request): View
    {
        return view('docs.api-docs', [
            'baseUrl' => url('/'),
            'specUrl' => route('api.docs.spec'),
        ]);
    }

    /**
     * Return OpenAPI 3.0.3 specification JSON.
     */
    public function spec(): JsonResponse
    {
        $appUrl = url('/');

        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name', 'Waldo').' REST API',
                'description' => 'Comprehensive documentation and interactive test console for Waldo REST API endpoints.',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'API Support',
                ],
            ],
            'servers' => [
                [
                    'url' => $appUrl,
                    'description' => 'Current Environment Server',
                ],
            ],
            'tags' => [
                [
                    'name' => 'Employees',
                    'description' => 'Employee records, filtering, searching, relations, and detailed profiles',
                ],
                [
                    'name' => 'Fonts',
                    'description' => 'Font assets used across certificate and letter templates',
                ],
            ],
            'paths' => [
                '/api/v1/employees' => [
                    'get' => [
                        'tags' => ['Employees'],
                        'summary' => 'List and filter employees',
                        'description' => 'Retrieve a paginated list of employees with extensive filtering, keyword search across fields, sorting, and relation inclusion.',
                        'operationId' => 'listEmployeesV1',
                        'parameters' => [
                            [
                                'name' => 'search',
                                'in' => 'query',
                                'description' => 'Search across employee code, name, email, phone, SSID, and citizenship number.',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'status',
                                'in' => 'query',
                                'description' => 'Filter by employee status (e.g. Active, Resigned, Terminated, On Leave).',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'department_id',
                                'in' => 'query',
                                'description' => 'Filter by department ID.',
                                'required' => false,
                                'schema' => ['type' => 'integer'],
                            ],
                            [
                                'name' => 'designation_id',
                                'in' => 'query',
                                'description' => 'Filter by designation ID.',
                                'required' => false,
                                'schema' => ['type' => 'integer'],
                            ],
                            [
                                'name' => 'gender',
                                'in' => 'query',
                                'description' => 'Filter by gender (Male, Female, Other).',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'tips_status',
                                'in' => 'query',
                                'description' => 'Filter by tips status.',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'sort_by',
                                'in' => 'query',
                                'description' => 'Field to sort by.',
                                'required' => false,
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['employee_code', 'name', 'join_date_formatted', 'employee_status', 'created_at'],
                                    'default' => 'employee_code',
                                ],
                            ],
                            [
                                'name' => 'sort_order',
                                'in' => 'query',
                                'description' => 'Sort direction.',
                                'required' => false,
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['asc', 'desc'],
                                    'default' => 'asc',
                                ],
                            ],
                            [
                                'name' => 'per_page',
                                'in' => 'query',
                                'description' => 'Number of results per page (1 to 100). Default is 25.',
                                'required' => false,
                                'schema' => [
                                    'type' => 'integer',
                                    'default' => 25,
                                    'minimum' => 1,
                                    'maximum' => 100,
                                ],
                            ],
                            [
                                'name' => 'page',
                                'in' => 'query',
                                'description' => 'Page number for pagination.',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 1],
                            ],
                            [
                                'name' => 'all',
                                'in' => 'query',
                                'description' => 'Set to 1 / true to fetch all matching records without pagination.',
                                'required' => false,
                                'schema' => ['type' => 'boolean', 'default' => false],
                            ],
                            [
                                'name' => 'include_suspensions',
                                'in' => 'query',
                                'description' => 'Include suspensions relation.',
                                'required' => false,
                                'schema' => ['type' => 'boolean'],
                            ],
                            [
                                'name' => 'include_leaver',
                                'in' => 'query',
                                'description' => 'Include leaver record details.',
                                'required' => false,
                                'schema' => ['type' => 'boolean'],
                            ],
                            [
                                'name' => 'include_termination',
                                'in' => 'query',
                                'description' => 'Include termination record details.',
                                'required' => false,
                                'schema' => ['type' => 'boolean'],
                            ],
                            [
                                'name' => 'include_adjustments',
                                'in' => 'query',
                                'description' => 'Include adjustments and tips adjustments.',
                                'required' => false,
                                'schema' => ['type' => 'boolean'],
                            ],
                            [
                                'name' => 'include_all',
                                'in' => 'query',
                                'description' => 'Include all available relations.',
                                'required' => false,
                                'schema' => ['type' => 'boolean'],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful operation returning a collection of employees',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/Employee'],
                                                ],
                                                'links' => ['type' => 'object'],
                                                'meta' => ['type' => 'object'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/v1/employees/{employeeCode}' => [
                    'get' => [
                        'tags' => ['Employees'],
                        'summary' => 'Get employee by code',
                        'description' => 'Retrieve full employee details and all relationship records by their unique employee code.',
                        'operationId' => 'getEmployeeByCodeV1',
                        'parameters' => [
                            [
                                'name' => 'employeeCode',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'The unique code of the employee (e.g. 1001, EMP001).',
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Employee details found',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => ['$ref' => '#/components/schemas/EmployeeDetail'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                'description' => 'Employee not found',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => ['type' => 'boolean', 'example' => false],
                                                'message' => ['type' => 'string', 'example' => "Employee with code '1001' was not found."],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/employees' => [
                    'get' => [
                        'tags' => ['Employees'],
                        'summary' => 'List employees (Unversioned Alias)',
                        'description' => 'Alias route for /api/v1/employees.',
                        'operationId' => 'listEmployeesAlias',
                        'parameters' => [
                            ['$ref' => '#/components/parameters/SearchParam'],
                            ['$ref' => '#/components/parameters/PerPageParam'],
                            ['$ref' => '#/components/parameters/SortByParam'],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful operation',
                            ],
                        ],
                    ],
                ],
                '/api/employees/{employeeCode}' => [
                    'get' => [
                        'tags' => ['Employees'],
                        'summary' => 'Get employee by code (Unversioned Alias)',
                        'description' => 'Alias route for /api/v1/employees/{employeeCode}.',
                        'operationId' => 'getEmployeeAlias',
                        'parameters' => [
                            [
                                'name' => 'employeeCode',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Employee found'],
                            '404' => ['description' => 'Employee not found'],
                        ],
                    ],
                ],
                '/letters/fonts/api' => [
                    'get' => [
                        'tags' => ['Fonts'],
                        'summary' => 'List available fonts',
                        'description' => 'Get list of custom typography fonts uploaded in the system for letter generation.',
                        'operationId' => 'listFonts',
                        'responses' => [
                            '200' => [
                                'description' => 'List of fonts',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'family' => ['type' => 'string'],
                                                    'path' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'parameters' => [
                    'SearchParam' => [
                        'name' => 'search',
                        'in' => 'query',
                        'description' => 'Search term',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                    ],
                    'PerPageParam' => [
                        'name' => 'per_page',
                        'in' => 'query',
                        'description' => 'Per page pagination size',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'default' => 25],
                    ],
                    'SortByParam' => [
                        'name' => 'sort_by',
                        'in' => 'query',
                        'description' => 'Field to sort',
                        'required' => false,
                        'schema' => ['type' => 'string', 'default' => 'employee_code'],
                    ],
                ],
                'schemas' => [
                    'Employee' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'employee_code' => ['type' => 'string', 'example' => '1001'],
                            'name' => ['type' => 'string', 'example' => 'John Doe'],
                            'first_name' => ['type' => 'string', 'example' => 'John'],
                            'last_name' => ['type' => 'string', 'example' => 'Doe'],
                            'email' => ['type' => 'string', 'example' => 'john.doe@example.com'],
                            'contact_number' => ['type' => 'string', 'example' => '+977-9800000000'],
                            'employee_status' => ['type' => 'string', 'example' => 'Active'],
                            'department' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'name' => ['type' => 'string', 'example' => 'Engineering'],
                                ],
                            ],
                            'designation' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'name' => ['type' => 'string', 'example' => 'Senior Developer'],
                                ],
                            ],
                            'ssid' => ['type' => 'string', 'example' => 'SSID12345'],
                            'citizenship_number' => ['type' => 'string', 'example' => '27-01-70-12345'],
                        ],
                    ],
                    'EmployeeDetail' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'employee_code' => ['type' => 'string'],
                            'name' => ['type' => 'string'],
                            'department' => ['type' => 'object'],
                            'designation' => ['type' => 'object'],
                            'suspensions' => ['type' => 'array', 'items' => ['type' => 'object']],
                            'leaver' => ['type' => 'object', 'nullable' => true],
                            'terminated_employee' => ['type' => 'object', 'nullable' => true],
                            'adjustments' => ['type' => 'array', 'items' => ['type' => 'object']],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($spec, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
