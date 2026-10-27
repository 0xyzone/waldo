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
- **Tips & Financials**: `tips_status` (`Release`, `Hold`), `tips_amount` (calculated via Google Sheet formula `AE+AF` and synced back to DB), `tips_adj` (synced to Sheet col AF), `point_value`, etc.
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

### 7. Employee Promotions
- **Model**: `App\Models\EmployeePromotion`
  - Fields: `employee_id` (FK → `employees.employee_code`), `from_department_id`, `from_designation_id`, `to_department_id`, `to_designation_id`, `promotion_date`, `acknowledged`, `acknowledged_at`, `hrms_synced`, `hrms_synced_at`, `remarks`.
  - Created event: Immediately updates `employee.department_id` / `designation_id` to new values, then dispatches `SyncPromotionToSheetJob` in background.
- **Job**: `App\Jobs\SyncPromotionToSheetJob` (queued, database driver)
  - Calls `GoogleSheetsService::syncEmployee($employee, ['department_id', 'designation_id'])`
  - Sends Filament database notification to the triggering user on completion.
- **Resource**: `App\Filament\Resources\EmployeePromotions\EmployeePromotionResource` (HR & Admin group)
  - Simple resource (ManageRecords) with modal create/edit.
  - Table Actions: **Acknowledge** (one-way, shows when not acknowledged), **Mark HRMS Synced** (reversible toggle), **Congratulation Note** (A4 letterhead print with designation rank sorting and without ref number), **Wish Card** (16:9 1080p downloadable congratulation wish card with modern minimal light-glass design, promotion dates on cards, gap-free full-height grid, single spotlight layout, official Management copy, & max 25 records chunking per card).
  - Bulk Actions: **Acknowledge Selected**, **Mark HRMS Synced**, **Unmark HRMS Synced**, **Print Congratulation Note**, **Wish Card (16:9)**, **Download Selected Excel**.
  - Toolbar Actions: **Import Excel** (`EmployeePromotionImportService`), **Download Excel** (`EmployeePromotionExportService`).
  - Filters: New Department (`to_department_id`), New Designation (`to_designation_id` dependent multi-select), Promotion Date range (`promotion_date_from`, `promotion_date_to`), Acknowledged, HRMS Synced.
  - Row colors: emerald (acknowledged + hrms_synced), amber (acknowledged only).

### 7b. Employee Transfers & Ingestion
- **Model**: `App\Models\EmployeeTransfer`
  - Fields: `employee_id`, `from_department_id`, `from_designation_id`, `to_department_id`, `to_designation_id`, `transfer_date`, `acknowledged`, `acknowledged_at`, `hrms_synced`, `hrms_synced_at`, `remarks`.
- **Resource**: `App\Filament\Resources\EmployeeTransfers\EmployeeTransferResource`
  - Toolbar Actions: **Import Excel** (`EmployeeTransferImportService`), **Download Excel** (`EmployeeTransferExportService`).
- **Import Services**:
  - `App\Services\EmployeePromotionImportService` & `App\Services\EmployeeTransferImportService`.
  - Format mirrors downloaded HRMS change-type Excel export (`username`, `start_date`, `job_title`, `division`).
  - Imports execute quietly via `withoutEvents()` and `saveQuietly()`, deliberately avoiding external Google Sheet synchronization or background worker triggers.

### 8. Reports & Department Analytics
- **Department Statistics Widget**: `App\Filament\Widgets\DepartmentEmployeeStatsWidget`
  - Renders interactive department cards on the dashboard with designation progress bars.
  - Clicking any department card opens a detail modal matching `/reports/departments/view` (average service tenure, gender distribution progress bars, searchable active employee list, print report, and role-protected Excel export).
- **Interactive Department Reports Page**: `GET /reports/departments/view` (`ReportController@departmentsView`)
- **Printable Department Reports**: `GET /reports/departments` and `GET /reports/departments/{id}/print`
- **Department Employees Excel Export**: `GET /reports/departments/{id}/export` (Protected by `HR|super_admin` role)

### 9. Candidates & Document Printing
- **Model**: `App\Models\Candidate`
- **Resource**: `App\Filament\Resources\Candidates\CandidateResource` (HR & Admin group)
- **Features**:
  - Direct row action **Print** in `CandidatesTable` opening an interactive **Print Preview Modal** (`filament.resources.candidates.print-preview-modal`).
  - Supports previewing uploaded multi-image CVs, single-page and bulk printing via in-modal iframe print engine.
  - Standalone printable route: `GET /candidates/{candidate}/print` (`CandidatePrintController@print`).

### 10. Tips Reports & HRMS Attendance Processing
- **Models**: `App\Models\TipsReport`, `App\Models\TipsReportItem`, `App\Models\TipsDepartmentMapping`, `App\Models\TipsNonEmployee`
- **Service**: `App\Services\TipsCalculationService`
- **Resource**: `App\Filament\Resources\TipsReports\TipsReportResource`
- **Attendance Excel Parser**:
  - Parses uploaded HRMS leave/attendance Excel/CSV exports with support for legacy formats and new 2-tier header formats with blank offset rows.
  - Recognizes `Missing Punch In Count` (stored in `tips_report_items.missing_punch_in_count` and displayed in the report table).
  - Explicitly prioritizes direct `Absent Days` (Col I) and `Total Leave` (Col J) values from the HRMS export.
  - Preserves backward compatibility for earlier exports summing LOP / individual leave categories.

### 11. In-App Changelog & Semantic Versioning
- **Service**: `App\Services\ChangelogService` (`CURRENT_VERSION = '2.5.0'`). Provides `getCurrentVersion()`, `getCompactVersion()` (for collapsed sidebar), `parseVersion()`, and `getNextVersion()`.
- **Page**: `App\Filament\Pages\Changelog` (`/kamkaj/changelog`).
- **Sidebar Footer**: Dynamic version pill in `KamkajPanelProvider` linking directly to `/kamkaj/changelog`, supporting dynamic major, minor, and micro/patch displays.
- **Artisan Command**: `php artisan version:bump [micro|minor|major]` (`BumpVersionCommand`).
- **Changelog File**: `CHANGELOG.md` in root adhering to Keep a Changelog specifications.

### 12. Manual Attendance Management (Roster Management)
- **Manual Attendance Staff**:
  - **Model**: `App\Models\ManualAttendanceStaff` (`manual_attendance_staff` table)
  - **Resource**: `App\Filament\Resources\ManualAttendanceStaffs\ManualAttendanceStaffResource` (Slug: `manual-attendance-staff`, Navigation Group: `Roster Management`).
  - **Purpose**: Designate employees whose monthly attendance is maintained manually in HRMS instead of biometric clocks, with active status switches, notes, and department/designation previews.
- **Monthly Manual Attendance Checklist**:
  - **Models**: `App\Models\MonthlyManualAttendance` (`monthly_manual_attendances`) & `App\Models\MonthlyManualAttendanceItem` (`monthly_manual_attendance_items`).
  - **Resource**: `App\Filament\Resources\MonthlyManualAttendances\MonthlyManualAttendanceResource` (Slug: `monthly-manual-attendances`, Navigation Group: `Roster Management`).
  - **Features**: Period-based (Month & Year) checklist automatically populated from active `ManualAttendanceStaff`. Custom interactive view page (`view-monthly-manual-attendance`) with live KPI metric cards (Total Staff, Updated in HRMS, Pending, Progress Rate), department filter pills, status switches (All, Pending, Updated), CheckboxColumn `Attendance Done` updating `updated_at_hrms` & `updated_by`, "Sync Active Staff", "Add Staff", and bulk mark actions. Row clicks on the listing table directly navigate to this checklist view (`recordUrl` & `recordAction('view_checklist')`).
- **Global Slide-Over Modals & Mobile Card Action Triggering**:
  - `CreateAction` and `EditAction` globally configured in `AppServiceProvider` with `modal(true)`, `slideOver()`, and `modalWidth(Width::SevenExtraLarge)` so that creating and editing records across all resources opens a wide 7XL slide-over drawer instead of navigating to separate pages.
  - Mobile card views on stacked tables (`.fi-ta-table-stacked-on-mobile > tbody > tr`) automatically delegate taps to trigger the record's primary action (`view_checklist`, `view`, or `edit`) or column action.
  - Bulk action checkboxes on mobile card view styled with unsqueezed dimensions (`1.125rem`, `flex-shrink: 0`) and stripped of redundant `"Select Record"` text labels. Pointer events on `.fi-ta-cell-label` are ignored so card taps pass cleanly through.

### 13. Filament Shield Enhanced & Granular Authorization
- **Package**: `agroezinger/filament-shield-enhanced`
- **Role Management**: Published `App\Filament\Resources\Roles\RoleResource` using `EnhancedResourcePermissionsForm::make()` and `EnhancedPagePermissionsForm::make()`.
- **Navigation Group Clustering**: Configured `group_by_navigation => true` so that all resources and pages are grouped into sub-tabs matching the panel's navigation structure (`HR & Admin`, `Roster Management`, `IT`, `Finance`, `Tips`, `Purchase & Store`, `Filament Shield`, `System Settings`).
- **Fine-Grained Custom Permissions**:
  - `HasResourceShield` on resources declaring custom actions (`EmployeeResource`, `EmployeePromotionResource`, `EmployeeTransferResource`, `SalaryIncrementRequestResource`, `NametagFineResource`, `TipsReportResource`, `UserResource`, `IdCardRequestResource`).
  - `HasPageShield` on function pages (`SsfUploadGenerator`, `Chat`, `Changelog`).
  - Custom permissions tab: `access_letters_and_templates`, `access_employee_ssf_ids`, `access_salary_calculator`, `export_department_reports`.

### 14. Unofficial NameTag Requests (Purchase & Store)
- **Models**: `App\Models\UnofficialNametagRequest` (`unofficial_nametag_requests`) & `App\Models\UnofficialNametagRequestLog` (`unofficial_nametag_request_logs`).
- **Resource**: `App\Filament\Resources\UnofficialNametagRequests\UnofficialNametagRequestResource` (Navigation Group: `Purchase & Store`, Sort: 3).
- **Core Functionality**:
  - Records requests for existing employees (`source = employee`) with database search/autofill, or external non-employee persons (`source = custom`).
  - `name_on_tag`: Captures specific display name to be physically printed on the tag.
  - `remarks`: Reason or special printing/distribution instructions.
  - Automated Action Log History: Tracks status changes, user stamps, and timestamps.

### 15. Global Datepicker Configuration (Sunday-First Calendar)
- **Framework Components**: Filament `DatePicker` and `DateTimePicker` are globally configured via `configureUsing` in `AppServiceProvider::boot()` with `firstDayOfWeek(7)` (Sunday).
- **Behavior**: All date and datetime pickers across form schemas, action modals, and table filters display calendars starting on Sunday by default without needing manual per-field settings.
- **Flatpickr Integration**: Flatpickr calendar instances in the document studio layout explicitly default `firstDayOfWeek: 0` (Sunday).


