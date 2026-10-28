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
  - **Print Report Scope Modal**: Clicking "Print Report" in the widget header opens a dedicated scope modal allowing the user to choose between printing "All Departments" or "Select Departments". Selecting specific departments reveals a searchable multi-select dropdown with checkbox toggles, live staff counters, and Select All / Clear actions.
- **Interactive Department Reports Page**: `GET /reports/departments/view` (`ReportController@departmentsView`)
- **Printable Department Reports**: `GET /reports/departments` (supports `department_id` or multi-department `department_ids`) and `GET /reports/departments/{id}/print`
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
  - `Action`, `CreateAction`, `EditAction`, and `ViewAction` are globally configured in `AppServiceProvider` with `modal(true)`, `slideOver()`, and `modalWidth(Width::SevenExtraLarge)` so that every modal action across all resources and screens opens as a wide slide-over drawer instead of navigating away or popping up.
  - Slide-over modals in `theme.css` use fixed edge-anchored flex positioning with full `100dvh` viewport height, sticky headers, sticky footers, and fluid horizontal slide transitions.
  - On mobile screen sizes (< 640px), all modals display as full-width (`100vw`) and full-height (`100dvh`) edge drawers, completely eliminating centered popup dialogs.
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

### 16. Designation Default Tips & Employee Tips Calculation
- **Designation Baseline**: `default_tips_amount` (decimal 15,2) on `designations` table, model, and form allows setting the standard baseline tips for each designation.
- **Dynamic Expected Tips in Employee Form**: When selecting a designation in `EmployeeForm` or updating `tips_adj`, the form reactively renders the expected tips amount (`designation.default_tips_amount + tips_adj`).
- **Local Persistence & Sync Decoupling**: The rendered `tips_amount` is dehydrated and saved to the local database on employee creation/update (and auto-assigned via `Employee::saving` if null). It is explicitly omitted from Google Sheets push sync (`GoogleSheetsService::$columnMap`), preserving the Google Sheet formula as the sheet's source of truth while providing expected values locally.
- **Bulk Import & Pre-filled Sample Template**:
  - `DesignationImportService::importTipsFile()`: Imports default tips values from CSV or XLSX with flexible column matching (ID, title, department, tips) and currency symbol sanitization.
  - `DesignationImportService::downloadTipsSampleTemplate()`: Generates pre-populated sample templates (CSV & XLSX) containing all existing designations, department names, and currently assigned default tips amounts.
  - `DesignationsTable`: Dedicated `Import Tips` toolbar action with sample template download buttons and overwrite toggles.

### 17. Role Management & Livewire Memory Optimization
- **Schema Scale**: The Role management schema (`RoleResource`, `EnhancedResourcePermissionsForm`, `EnhancedPagePermissionsForm`) contains over 430 granular permissions arranged across navigation groups, tabs, grids, and collapsible sections.
- **Memory Optimization**: Livewire's component state snapshotting and JSON serialization across this large schema consumes ~147MB of memory, exceeding PHP's default `128M` limit. Runtime memory has been raised to `512M` across entry points (`public/index.php`, `AppServiceProvider::boot()`, and `CreateRole` / `EditRole` page `mount()` methods).
- **Sanitized Permission Sync**: In `CreateRole::mutateFormDataBeforeCreate` and `EditRole::mutateFormDataBeforeSave`, extracted permission collections are filtered to strictly non-empty string values before executing `firstOrCreate` and `syncPermissions`.
- **Dedicated Page Routing**: Removed global `modal(true)` override on `CreateAction`/`EditAction` in `AppServiceProvider` and bound `ListRoles` CreateAction and `RoleResource` EditAction to `getUrl('create')` and `getUrl('edit', ...)` respectively, ensuring role operations are routed to dedicated pages where permission arrays are filtered out before SQL insertion.

### 18. Vite & Tailwind Build Optimization
- **Multithreading**: Removed legacy single-thread Rayon limiter (`RAYON_NUM_THREADS=1`) from `package.json`, allowing Tailwind v4's Rust compiler to utilize all available CPU threads in parallel and eliminating cross-platform shell syntax errors on Windows Command Prompt/PowerShell.
- **Build Metrics**: Disabled `reportCompressedSize` in `vite.config.js` to avoid compute-heavy gzip calculations on 30+ font binaries and CSS stylesheets during production builds.
- **Cleaned Source Scanning**: Excluded compiled storage view paths (`storage/framework/views`) from Tailwind `@source` directives in `app.css` to prevent duplicate scanning of compiled PHP view templates.
- **Performance**: Production build time reduced from ~32.16s down to ~5.89s (>5.4x faster).

### 19. User Management & Bulk Role Assignment
- **Bulk Action**: `assignRoles` in `UsersTable` allows updating roles for multiple selected users simultaneously with automatic deselection on completion.
- **Amendment Modes**:
  - `append`: Preserves existing user roles and adds the newly selected roles using Spatie's `assignRole()`.
  - `replace`: Overwrites all existing roles and assigns only the newly selected roles using `syncRoles()`.
- **Security & Authorization**: Custom permission `AssignRoles` added to `UserResource::getShieldResourcePermissions()`. The action modal prevents non-super-admin users from assigning the `super_admin` role.

### 20. Date Converter Dashboard Widget Redesign & Custom Calendar
- **Widget**: `App\Filament\Widgets\DateConverterWidget` and `resources/views/filament/widgets/date-converter-widget.blade.php`.
- **Aesthetic & Layout**: Redesigned to adhere to Waldo's "Zen Soft Tech" dark/light design system:
  - **Eliminated Double Widget Box & Corner Outlines Globally**: Uncoupled `.fi-wi-widget` from elevated card rules in `theme.css` and made all widget grid wrappers strictly transparent (`border: none`, `box-shadow: none`, `background: transparent`). This ensures custom widgets (`BirthdayListWidget`, `DateConverterWidget`, `DepartmentEmployeeStatsWidget`) render clean, single-layer `rounded-3xl` cards without any secondary outline or ghost background peeking out around the corners.
  - **Height Alignment**: Used full-height flex column stretching so Date Converter aligns top and bottom borders cleanly with sibling widgets.
  - **Custom Interactive Calendar Popover**: Replaced native browser `<input type="date">` with an Alpine.js custom calendar popover featuring Sunday-first day alignment, month/year navigation dropdowns, active date glowing pills, and "Jump to Today" shortcut.
  - **Right-Aligned Date Trigger Icon**: Repositioned the calendar icon to the trailing end of the input inside a dedicated accent pill with active hover glow.
  - **Rich Date Typography**: Replaced raw numeric date formatting with bold localized day and date text (e.g. `Tuesday, October 27, 2026`) accompanied by an ISO helper preview.
  - **Sleek Segmented Switcher**: Refined segmented tab control with active dot pulse indicators and a bidirectional "Swap" button (`⇄`) to toggle direction while carrying over converted values.
  - **Bilingual Nepali Month Selector**: Added full Nepali script month titles (e.g., `01 - Baishakh (बैशाख)`) and custom chevrons.
  - **Ambient Glowing Result Card**: Upgraded output into an ambient glowing card displaying large bold localized typography, interactive copy button with green checkmark confirmation, monospaced numeric date badge, and bilingual weekday badge.

### 21. Dashboard Operations Suite & Shield Widget Permissions
- **Widgets**:
  - `App\Filament\Widgets\WorkforceOverviewWidget` (Sort: 4, `lg: 1`): Real-time metrics for active, suspended, resigning, and tips-on-hold employees, plus male/female diversity distribution bar.
  - `App\Filament\Widgets\PendingOperationsWidget` (Sort: 5, `lg: 1`): Operational queue monitoring pending adjustments, active suspensions, and pending salary increment requests.
  - `App\Filament\Widgets\LeaversTrackerWidget` (Sort: 6, `lg: 1`): Leaver tracking for this month, salary/tips financial holds, and recent exit timeline.
  - `App\Filament\Widgets\PromotionTransferActivityWidget` (Sort: 7, `lg: 1`): Staff advancement metrics, acknowledgement rate, HRMS sync status, and recent promotion records.
- **Visuals**: All 4 widgets adhere to Waldo's "Zen Soft Tech" dark aesthetic (`rounded-3xl`, ambient blur glow, metric stat cards, and full-height alignment).
- **Filament Shield Permissions**:
  - Trait: `BezhanSalleh\FilamentShield\Traits\HasWidgetShield` attached to all widgets.
  - Registered permissions: `View:WorkforceOverviewWidget`, `View:PendingOperationsWidget`, `View:LeaversTrackerWidget`, `View:PromotionTransferActivityWidget`.

### 22. Category Resource Operations Suite (Finance, IT, Purchase & Store)
- **Widgets**:
  - `App\Filament\Widgets\FinanceOverviewWidget` (Sort: 3, `lg: 1`): Real-time financial operations tracking pending and approved adjustments counts, active salary/tips holds, monthly leavers, terminations, and pending HRMS sync.
  - `App\Filament\Widgets\ItOverviewWidget` (Sort: 4, `lg: 1`): IT & hardware systems queue tracking ID card requests across statuses (pending, designed, sent for print, done), biometric allotments/enrollments, and print reports.
  - `App\Filament\Widgets\PurchaseStoreOverviewWidget` (Sort: 5, `lg: 1`): Supply & store tracker monitoring nametag distributions this month, pending unofficial nametag requests, nametag fines amount/count, and unacknowledged fines.
- **Filament Shield Permissions**:
  - Registered permissions: `View:FinanceOverviewWidget`, `View:ItOverviewWidget`, `View:PurchaseStoreOverviewWidget`.

### 23. Universal Action Logging & Dedicated Action Logs Resource (v2.8.0)
- **Model**: `App\Models\ActionLog` (`action_logs` table)
  - Fields: `actionable_type`, `actionable_id` (string supporting both integer IDs and alphanumeric codes like `CWD650`), `user_id`, `user_name`, `action` (`created`, `updated`, `status_changed`, `deleted`), `from_status`, `to_status`, `action_description`, `changes` (JSON attribute diffs with old/new values), `created_at`.
- **Trait**: `App\Traits\HasActionLogs`
  - Attached to all Eloquent models across the application.
  - Automatically captures model creation, attribute modifications (ignoring timestamps and secret fields), status transitions, and deletions without requiring manual hooks.
- **Form Component**: `App\Filament\Components\ActionLogSection`
  - Embedded at the bottom of edit/view forms across all resources.
  - Displays a clean, collapsible "Action Log History" repeater with User, Status Set, Action Log description, and timestamp.
- **Dedicated Central Resource**: `App\Filament\Resources\ActionLogs\ActionLogResource`
  - Located under *System Settings* (Sort: 15).
  - Displays audit trail across every resource in the entire system with search, resource filtering, action type filtering, date range filtering, and a slideover modal inspector with colored attribute diff tables.

### 24. Employee SSID/SSF Public Directory UI Overhaul (v2.8.1)
- **View**: `resources/views/employees/ssid-list.blade.php` (Route: `/employee-ssids`)
- **Fixes & Enhancements**:
  - **Dark Mode Color Mismatch**: Added explicit `class="dark"` and `style="color-scheme: dark;"` to `<html>` and introduced `.directory-input` and `.directory-select` CSS tokens to prevent dark text (`#0f172a`) from rendering on dark inputs.
  - **Padding Collision Elimination**: Corrected search input padding (`padding-left: 2.75rem !important`) so the magnifying glass icon never overlaps placeholder or typed text.
  - **Modern Custom Dropdowns**: Restyled Department and Status `<select>` elements with sleek borders, custom chevron down SVG indicators, and dark-themed `<option>` popup backgrounds (`#0f172a`).
  - **Export Modal Improvements**: Unified export filter inputs and multiselect employee list with dark theme tokens and custom scrollbars.
  - **Interactive Copy Notification**: Connected `#copy-toast` floating glassmorphism banner with emerald check icon confirming copied SSID values.

### 25. Sidebar Slide Animations, Mobile Gestures & Topbar Chat Drawer (v2.8.2)
- **Sidebar Slide Animations (`resources/css/filament/kamkaj/theme.css`)**:
  - Upgraded `.fi-sidebar` with hardware-accelerated cubic-bezier transitions (`transform 320ms cubic-bezier(0.16, 1, 0.3, 1)`, `width 300ms cubic-bezier(0.16, 1, 0.3, 1)`, and `box-shadow 320ms ease`).
  - Mobile off-canvas drawer slides smoothly in and out without snappy jumps.
  - Desktop collapsible state transitions width and item labels smoothly without layout stutter.
  - Overlay `.fi-sidebar-close-overlay` enhanced with smooth opacity and `backdrop-filter: blur(8px)`.
- **Mobile Touch Screen Gestures (`resources/views/filament/mobile-sidebar-gestures.blade.php`)**:
  - Registered via `PanelsRenderHook::BODY_END` in `KamkajPanelProvider.php`.
  - Edge swipe right (`startX <= 45px`) initiates real-time touch dragging with responsive translation, proportional backdrop fade, and an orange glowing edge hint pill.
  - Swipe left when open smoothly closes the drawer with velocity and distance detection.
  - Guarded with vertical scroll lock (`absY > absX`) to prevent interfering with normal page scrolling, form inputs, or open modal windows.
- **Topbar Chat Drawer Overhaul**:
  - Replaced obstructive persistent bottom-right floating button with a top navigation icon button (`resources/views/filament/chat-topbar-button.blade.php`) featuring a real-time unread messages badge and Echo websocket listener.
  - Converted chat launcher into a full-height right-anchored slide-over drawer overlay (`resources/views/livewire/floating-chat-widget.blade.php`) with smooth backdrop blur.
  - Redesigned chat action icons (Reply, Forward, Forwarded indicator) using clean Heroicons across the chat widget.

### 26. Retirement of Dedicated Chat Page (v2.8.3)
- **Removed Dedicated Chat Page**: Removed `app/Filament/Pages/Chat.php` and its associated view `resources/views/filament/pages/chat.blade.php`.
- **Sidebar Cleanup**: Removed "Chat" from the sidebar navigation menu, giving full focus to Dashboard and domain operational groups.
- **Route & Notification Updates**: Retired route `/kamkaj/chat`. Database mentions and web push notifications now link directly to `/kamkaj` where the global drawer handles conversations.
- **Drawer Links**: Removed obsolete full-screen page toggle links from inside the chat drawer.
- **Unit Test Alignment**: Updated `tests/Unit/ShieldEnhancedPermissionsTest.php` to align with active Filament pages.

### 27. Sidebar Close Slide Animation, Modal Slide & Click-Outside, and Chat Close Fix (v2.8.4)
- **Sidebar Closing Slide Animation (`resources/css/filament/kamkaj/theme.css`)**:
  - Tailwind CSS v4 compiles `-translate-x-full` and `translate-x-0` into CSS `translate: -100% 0` and `translate: 0 0`.
  - Added dual CSS transitions on `.fi-sidebar`: `transition: translate 350ms cubic-bezier(0.16, 1, 0.3, 1), transform 350ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 350ms ease !important; will-change: translate, transform;`.
  - Explicitly styled `.fi-sidebar:not(.fi-sidebar-open)` with both `translate: -100% 0 !important; transform: translateX(-100%) !important;` so mobile drawer closes with the identical smooth slide as it opens.
- **Modal Slide Animations & Click-Outside Handling**:
  - Restored pointer events on `.fi-modal-window-ctn` (`pointer-events: auto !important; cursor: pointer;`) with child `.fi-modal-window` set to `pointer-events: auto !important; cursor: default;`.
  - Added delegated backdrop listener in `resources/views/filament/modal-click-outside.blade.php` (registered via `PanelsRenderHook::BODY_END` in `KamkajPanelProvider.php`) ensuring clicks anywhere outside the dialog window close the active modal via Alpine `$data(modal).close()`.
  - Added cubic-bezier slide transitions to all modals: centered modals slide up into place and slide down on close, and slide-over drawers smoothly slide to and from the right edge.
- **Chat Drawer Close Button Fix (`app/Livewire/FloatingChatWidget.php`)**:
  - Implemented explicit `closeWidget(): void` method setting `$this->isOpen = false` without inverting state.
  - Bound close button, backdrop, and Escape listener directly to `closeWidget()`, resolving the `@entangle('isOpen')` vs `wire:click="toggleWidget"` race condition that previously caused the drawer to toggle back open.

### 28. Navigation Crash Guard for Remote Filament Component Caches (v2.8.5)
- **Problem**: When a Filament page class (such as `App\Filament\Pages\Chat`) is deleted, remote or production environments running `php artisan filament:cache-components` continue to reference the class in `bootstrap/cache/filament/panels/kamkaj.php`. When `NavigationManager::mountNavigation()` iterates through cached pages, PHP throws a fatal error: `Class "App\Filament\Pages\Chat" not found` on every request.
- **Solution**:
  - Created a backward-compatible stub `App\Filament\Pages\Chat` extending `Filament\Pages\Page`.
  - Configured `shouldRegisterNavigation(): bool => false` so it registers no sidebar or navigation items.
  - Added redirect to `/kamkaj` on `mount()`.
  - Excluded `Chat::class` in `config/filament-shield.php` to prevent permission bloat.

### 29. Action Log Attribute Modifications (Old & New Values) Fix (v2.8.6)
- **Root Cause**: `ActionLogInfolist.php` used `TextEntry::make('changes')->formatStateUsing(...)`. In Filament v5, when state is an associative array, `TextEntry` treats it as a list of items and invokes `formatStateUsing` separately for each key-value pair. The formatter was receiving `['old' => ..., 'new' => ...]` as `$state` where `$field` was string `'old'` / `'new'` and `$val` was scalar, causing `is_array($val)` to fail and rendering duplicate tables showing `Field: Old | null | null` and `Field: New | null | null`.
- **Solution**:
  - Replaced `TextEntry` with `ViewEntry::make('changes')` in `ActionLogInfolist.php`.
  - Created `resources/views/filament/components/action-log-changes-table.blade.php`: renders a unified, beautiful comparison table showing the actual field names with old and new values, boolean true/false badges, null labels, numbers, and formatted dates.
  - Embedded `action-log-changes-table` into `ActionLogSection`'s repeater schema, allowing users to see field diffs directly within individual resource view/edit forms.
  - Enhanced `HasActionLogs::bootHasActionLogs()` updating hook to properly normalize `DateTimeInterface` instances and strict boolean comparisons.






