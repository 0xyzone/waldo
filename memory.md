# Project Memory & Guidelines

## Core Strict Instructions
1. **No Dummy/Temporary Data**: Never create fake, mock, dummy, or temporary records in the database for testing, seeding, or demonstration purposes. Always utilize real existing records when needed.
2. **No Test Execution**: Do not run unit, feature, or integration tests (`php artisan test`, `phpunit`, etc.) unless explicitly commanded by the user.
3. **Continuous Documentation Updates**: Whenever modifications, new features, or architectural adjustments are made to the codebase, keep this `memory.md` updated with high-level overviews and structural context.

---

## Project Overview
- **Application**: Waldo (Internal HR & Employee Management Portal)
- **Framework**: Laravel 13 with PHP 8.4
- **Admin Panel**: Filament v5 (Panel ID: `kamkaj`, Path: `/kamkaj`)
- **Styling**: TailwindCSS v4
- **Roles & Permissions**: BezhanSalleh/FilamentShield (`super_admin`, `HR`, `Finance`, `IT`, etc.)

---

## Architecture & Domain Modules

### 1. Employees & Core Management
- **Model**: `App\Models\Employee` (Primary Key: `employee_code`, non-incrementing string)
- **Statuses**: `Active`, `Inactive`, `Suspended`, `Resigned`, `Resigning This Month`, `Terminated`.
- **Tips & Financials**: `tips_status` (`Release`, `Hold`), `tips_amount`, `point_value`, etc.
- **Resource**: `App\Filament\Resources\Employees\EmployeeResource`
  - Table Record Actions: Compact dropdown `ActionGroup` containing View, Edit, **Suspend** (with date range, reason & attachments modal), and **Terminate** (with date & reason modal) to prevent card button overflow.

### 2. Employee Suspensions
- **Model**: `App\Models\EmployeeSuspension`
  - Fields: `employee_id` (FK to `employees.employee_code`), `start_date`, `end_date`, `reason`, `attachments` (JSON array of file paths), `status` (`active`, `completed`, `cancelled`).
  - Saved event: Sets employee `employee_status` to `Suspended` and `tips_status` to `Hold`.
- **Resource**: `App\Filament\Resources\EmployeeSuspensions\EmployeeSuspensionResource` (HR & Admin group)
  - Features: Multi-file attachments (`suspension-attachments/` on `public` disk), status filter, Uplift action.
- **Scheduler**: `CheckSuspensionStatusCommand` (`suspensions:check-status`)
  - Runs daily via `routes/console.php`.
  - Automatically marks expired suspensions (`end_date < today`) as `completed` and resets employee `employee_status` back to `Active` (leaving `tips_status` as `Hold`).

### 3. Adjustments & Financial Holds
- **Model**: `App\Models\Adjustment` / `AdjustmentResource` (Finance group).
- **Statuses**: `pending` (Amber row highlight), `approved` (Emerald row highlight), `rejected` (Red row highlight), `cancelled` (Gray row highlight).
- **Tabs**: Dedicated list view tabs for `All`, `Pending`, `Approved`, `Rejected`, and `Cancelled` with real-time count badges.

### 4. Offboarding & Leavers
- **Leavers**: `App\Models\Leaver` / `LeaverResource` (Finance group). Tracks last dates, salary holds, tips holds, CL balance publishing, and offboard action setting status to `Resigned`.
- **Terminated Employees**: `App\Models\TerminatedEmployee` / `TerminatedEmployeeResource` (Finance group). Records termination date, last working date, reason; triggers employee status to `Terminated`.

### 5. Employee API Service
- **Endpoints**:
  - `GET /api/v1/employees` (or `/api/employees`): List employees with filtering (`status`, `department_id`, `designation_id`, `gender`, `tips_status`), keyword search (`search`), sorting (`sort_by`, `sort_order`), pagination (`per_page`, `page`), or full collection fetch (`all=true`).
  - `GET /api/v1/employees/{employeeCode}` (or `/api/employees/{employeeCode}`): Fetch single employee details with all relationships.
- **Relationship Inclusions via Query Params**:
  - `include_suspensions=1`, `include_leaver=1`, `include_termination=1`, `include_adjustments=1`, or `include_all=1`.
- **Resource Transformer**: `App\Http\Resources\EmployeeResource`.
- **Controller**: `App\Http\Controllers\Api\EmployeeApiController`.

### 6. Background Commands & Integration
- `employees:sync` (Every 15 minutes) - Syncs employee records from Google Sheets.
- `biometrics:sync` (Hourly) - Syncs biometric devices/attendance allotments.
- `suspensions:check-status` (Daily) - Checks and updates suspension life-cycle statuses.


