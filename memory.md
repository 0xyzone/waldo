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

### 30. Mobile Sidebar Gestures & Tactile Edge Pull Tab (v2.8.7)
- **Problem**: Opening the sidebar on mobile was difficult and took excessive effort. The swipe edge threshold was constrained to only 45px, touches over tables or links were blocked by aggressive interactive element filters, any vertical drift over 8px killed the gesture, and CSS `!important` rules on `.fi-sidebar:not(.fi-sidebar-open)` prevented inline translation styles from updating the sidebar position in real time.
- **Solution**:
  - **Tactile Edge Pull Tab**: Added an orange floating pull tab button (`#mobile-edge-handle`) on the left screen edge that opens the sidebar with a tap or live drag.
  - **Lower Friction & Wider Zone**: Broadened edge swipe threshold to 80px (or 25% of viewport width), reduced initiation trigger to 5px, and removed restrictive filters so swipes over tables, cards, and links initiate immediately.
  - **Real-Time Live Dragging**: Added `.fi-sidebar-dragging` to remove CSS transitions while moving and used `.style.setProperty('translate', ..., 'important')` to override stylesheet rules, providing smooth 1:1 finger tracking.
  - **W3C Pointer Events**: Upgraded gesture listeners to Pointer Events API with touch fallback for universal cross-device responsiveness.

### 31. Human-Readable Entity Resolution for Action Log Foreign Key Values (v2.8.8)
- **Problem**: Modifying foreign key fields (e.g. `designation_id` from 44 to 33) displayed raw numeric IDs in action logs, forcing users to guess which department, designation, employee, or user was affected.
- **Solution**:
  - **`App\Services\ActionLogValueResolver`**: Service mapping `designation_id`, `department_id`, `employee_id`, `user_id`, `candidate_id`, `letter_template_id`, and generic BelongsTo relationships to their human-readable names with per-request in-memory lookup caching.
  - **Enhanced Diff Table (`action-log-changes-table.blade.php`)**: Displays resolved entity names (e.g. "GRA → Driver") with subtle `#ID` badges, and strips trailing `_id` from field column labels (e.g. "Designation" instead of "Designation Id").
  - **Descriptive Action Summaries**: `HasActionLogs` updating event generates human-readable entity change summaries (e.g. "Updated: Designation (GRA → Driver)") rather than raw attribute keys.

### 32. Mobile Screen Touch Blocking by Backdrop Overlay Fix (v2.8.9)
- **Problem**: When tapping or clicking anywhere on mobile screens, a translucent black backdrop overlay with blur (`.fi-sidebar-close-overlay`) appeared, intercepting all clicks and preventing interaction with cards, buttons, tables, and links.
- **Root Cause**:
  - The swipe open gesture listener had an overly broad 80px threshold and a 5px drag trigger. Any regular tap on the left side of the mobile viewport with minimal finger jitter was classified as a drag gesture, forcing `display: block !important` and `pointer-events: auto !important` on `.fi-sidebar-close-overlay`.
  - When the gesture aborted, `cleanupStyles()` removed inline `display`, wiping out Alpine's `display: none` style while `$store.sidebar.isOpen` remained `false`. This left the overlay permanently rendered by default CSS stylesheet rules (`position: fixed; inset: 0; z-index: 30; background-color: rgba(...)`), blocking subsequent screen clicks.
- **Solution**:
  - **CSS Overlay Isolation (`theme.css`)**: Added `.fi-layout:not(:has(.fi-sidebar.fi-sidebar-open)):not(:has(.fi-sidebar.fi-sidebar-dragging)) .fi-sidebar-close-overlay` with `display: none !important; pointer-events: none !important; opacity: 0 !important; visibility: hidden !important` so the overlay can never display or capture pointer events unless the sidebar is open or actively dragging.
  - **Alpine Store Synchronization (`mobile-sidebar-gestures.blade.php`)**: Added a reactive `Alpine.effect` watcher that guarantees the overlay is explicitly hidden whenever `$store.sidebar.isOpen` is `false`, and only made visible when the sidebar is open or actively being dragged.
  - **Gesture Gate Precision**: Restricted open swipe detection strictly to within 25px of the viewport edge (or `#mobile-edge-handle`), increased horizontal trigger to 14px with dominant directional filtering (`absX >= absY * 1.3`), and excluded interactive buttons/links, ensuring standard taps anywhere on the screen pass directly through without interference.

### 33. Click-Outside Sidebar Dismissal & Enhanced Gesture Responsiveness (v2.8.10)
- **Problem**:
  - The mobile sidebar did not dismiss when clicking elsewhere on the screen (such as topbar or content outside the sidebar).
  - Edge gestures were excessively constricted to a 25px sliver and excluded elements like buttons, making swipe-to-open hard to trigger.
- **Solution**:
  - **Capture-Phase Click-Outside Dismissal (`mobile-sidebar-gestures.blade.php`)**: Added a global capture-phase click listener that intercepts any click outside `.fi-sidebar` when open and immediately closes the sidebar (`store.close()`), guaranteeing reliable one-tap dismissal anywhere on the screen.
  - **Refined Swipe Initiation (`mobile-sidebar-gestures.blade.php`)**: Expanded edge initiation zone to a natural 45-60px margin (or `#mobile-edge-handle`), lowered movement threshold to 10px, and restricted filtering strictly to form text inputs so edge swipes initiate effortlessly across headers, cards, and page elements.
  - **Auto-Hide Edge Pull Handle (`theme.css`)**: Added `.fi-layout:has(.fi-sidebar.fi-sidebar-open) #mobile-edge-handle` to slide and fade the handle out of view while the sidebar is open.

### 34. Chat UI Modernization, Jitter-Free Alignment & Real-Time Performance (v2.8.11)
- **Problem**:
  - The chat feed felt cramped and congested.
  - Message bubble alignment was irregular, sender names stretched containers, timestamps created awkward gaps, and hover action buttons sat in the same flex row, causing visible layout jitter when hovering messages.
  - Redundant Echo channel events triggered concurrent Livewire requests causing noticeable UI flickering. Sending messages had noticeable lag due to sequential client-to-server rounds.
- **Solution**:
  - **Natural Layout & Alignments (`floating-chat-widget.blade.php`)**: Restructured message feed with balanced widths (`max-w-[84%] sm:max-w-[76%]`), distinct bubble tail corners (`rounded-br-xs` for outgoing, `rounded-bl-xs` for incoming), inline floating timestamps (`float-right`), and clean separation between sender and receiver bubbles.
  - **Zero Jitter Floating Capsule (`floating-chat-widget.blade.php`)**: Moved quick-reaction emojis and message action buttons (reply, forward, pin, delete) into an anchored floating capsule above the message bubble on hover, completely preventing layout shifts.
  - **Modern Date Dividers & De-congested State**: Added elegant date separator chips ('Today', 'Yesterday', formatted dates) and styled deleted messages as subtle dashed border pills.
  - **Anti-Flicker & Realtime Responsiveness**: Deduplicated Echo channel listeners, debounced status and reaction updates, and implemented instantaneous 0ms client-side textarea reset on send.
  - **Sleek Pill Composer**: Modernized the bottom input form into a unified rounded-2xl island with glowing gradient send button, smooth auto-resizing, and quick attachment/mic controls.

### 35. Compact Chat Bubbles & Morphing Dynamic Composer (v2.8.12)
- **Problem**:
  - Chat bubbles had excessive padding and rendered unexpectedly large (102px high for single mentions like `@all` and `@test`), leaving massive empty orange boxes.
  - The message composer area was congested with redundant buttons (separate mic button on the left, Enter mode toggle button on the right, and send button).
- **Solution**:
  - **Compact Message Layout (`floating-chat-widget.blade.php`)**: Reduced bubble padding to `px-2.5 py-1.5`, removed `whitespace-pre-wrap` template newline artifacts, and switched from `float-right` to inline flex wrapping (`flex flex-wrap items-baseline gap-x-2`), cutting single mention/short message bubble height from 102px down to 33px.
  - **Morphing Action Button**: Replaced duplicate buttons with a unified dynamic action button on the far right. Displays a microphone icon when the textarea is empty, and automatically morphs into a glowing send button as soon as text is typed or an attachment is attached.
  - **De-congested Composer Row**: Removed the standalone Enter mode toggle button and left-side mic button, centering the auto-growing input with paperclip attachment and group mention controls.

### 36. Chat Performance Acceleration, Message Pagination & Context Menu Actions (v2.8.13)
- **Problem**:
  - Chat was loading slowly and actions took too long because `User::unreadMessagesCount()` and conversation list ran N+1 database queries across all conversations on every page load and Livewire request.
  - The message query fetched the complete chat history without pagination, inflating Livewire JSON payloads and DOM size.
  - Every message bubble rendered 16+ buttons with full dropdown state in a hover pill container, causing layout jitter, high DOM node count, and accidental triggers on touch screens.
- **Solution**:
  - **Single SQL Query Unread Counts (`User.php`, `FloatingChatWidget.php`)**: Replaced iterative queries with a single indexed SQL join in `unreadMessagesCount()` and batch pre-computed unread counts grouped by conversation ID before rendering the conversation list.
  - **Message Limit & Dynamic Pagination**: Limited active message feed loading to the latest 50 messages (`->latest('id')->take(50)->get()->reverse()->values()`) with an inline "Load earlier messages" action.
  - **Secondary-Click & 2-Second Mobile Long-Press Context Menu (`floating-chat-widget.blade.php`)**:
    - Completely removed the hover action pill from message bubbles.
    - Added an Alpine context menu triggered via secondary mouse click (`@contextmenu.prevent="openContextMenu($event, msg)"`) on desktop or a 2-second press (`@touchstart`, `@touchend`, `@touchmove` with >8px cancel check and `navigator.vibrate(35)`) on mobile devices.
    - Clamped menu positioning within chat bounds and included emoji reaction shortcuts, reply, forward, copy, pin, and delete options.
  - **In-Memory Caching (`Message.php`)**: Memoized regex parsing for `@mentions` and message delivery statuses to avoid repetitive database and regex computation per message.
  - **Self-Event Socket Filter**: Prevented redundant Livewire full-page re-renders when the current authenticated user initiates reactions or message deletions.

### 37. App-Wide Page Load Acceleration, Socket Timeout Elimination & Desktop Context Menu Fix (v2.8.14)
- **Problem**:
  - Entire application rendered sluggishly on every page transition. `WebsocketStatusIndicator` performed synchronous network socket checks to `127.0.0.1:8080` with dual 0.8s blocking connection timeouts whenever Reverb was not running, introducing a forced 1.6-second delay on every HTTP request.
  - `FloatingChatWidget` was rendered in full (all 3,400 lines of Blade HTML and all conversation queries) on every page load even when the chat drawer was closed (`isOpen === false`).
  - The chat context menu was not triggering on desktop right-click:
    - CSS `transform: translate-x-0` on the drawer panel caused `position: fixed` elements to be positioned relative to the drawer panel rather than the window, pushing the context menu ~1500px offscreen to the right on desktop monitors.
    - Blade `@js()` inside HTML event attributes generated unescaped double quotes, corrupting the attribute string and causing Alpine JavaScript syntax errors.
- **Solution**:
  - **Socket Check Optimization (`WebsocketStatusIndicator.php`)**: Cached Reverb socket status for 30s (`Cache::remember('reverb_status_check', 30, ...)`) and reduced probe timeout to 0.1s, immediately cutting 1.6+ seconds of latency off every page load in the app.
  - **Lazy-Loaded Chat Drawer (`FloatingChatWidget.php`, `floating-chat-widget.blade.php`)**: Added `@if ($isOpen)` around drawer markup and short-circuited `getConversations()` and `getMessagesProperty()` when closed. Closed chat now executes 0 queries and outputs 0 DOM nodes.
  - **Desktop Context Menu Coordinates & Anchoring**:
    - Anchored context menu with `absolute` positioning relative to `drawerPanel` (`clientX - panelRect.left`, `clientY - panelRect.top`), with boundary clamping ensuring it opens right under the cursor on all screen sizes.
    - Eliminated inline `@js()` double-quote syntax conflicts by passing scalar IDs and extracting text via DOM lookup on copy.

### 38. Instant Bottom Chat Loading, Sticky Date Headers, Waveform Voice Player & Changelog Guard (v2.8.15)
- **Problem**:
  - Opening a chat caused it to scroll visibly and slowly from top to bottom instead of loading instantly pinned at the bottom.
  - Date dividers were static, scrolling off the screen rather than staying pinned to the top while reading messages in that date range.
  - The voice note player UI was completely blank/broken, showing no media controls or seek bar, and failing to play audio.
  - Accessing the `/kamkaj/changelog` page resulted in a fatal 500 error (`TypeError: htmlspecialchars(): Argument #1 ($string) must be of type string, array given`) due to array-formatted highlights in `ChangelogService`.
- **Solution**:
  - **Instant Bottom Chat Loading (`floating-chat-widget.blade.php`)**: Removed the `scroll-smooth` Tailwind utility from `floatingFeed` and updated Alpine's `scrollToBottom(smooth = false)` method and event hooks (`floating-conversation-changed`, `scroll-floating-chat`, and conversation/open watchers) to jump directly to `scrollTop = scrollHeight` without delay or visible top-down animation.
  - **Sticky Date Range Headers (`floating-chat-widget.blade.php`)**:
    - Grouped messages collection by date (`$messages->groupBy(...)`).
    - Wrapped each date group into a relative `.date-group` container with a sticky `top-0 z-20` date pill. As a result, the active date remains pinned to the top of the chat view during scrolling and is naturally displaced when another date range enters the viewport.
  - **Interactive Waveform Voice Player (`floating-chat-widget.blade.php`)**:
    - Restored the voice player Alpine component definition with direct SVG play/pause controls (eliminating `<template x-if>` tag dropping).
    - Seeded deterministic, natural human-speech waveform bar visualization based on message ID, supplemented by `AudioContext` PCM decode analysis.
    - Implemented interactive waveform seeking (`@click.stop="handleBarClick($event)"`), synchronized elapsed/duration time display, and speed cycling (`1x`, `1.5x`, `2x`).
  - **Changelog TypeError Guard (`changelog.blade.php`)**: Updated the changelog Blade view to safely check whether `$release['highlights']` is a string or array, rendering release descriptions and formatting array highlights with clean bullet points.

### 39. Topbar Chat Drawer Toggle Binding, Livewire Single-Root Fix & Gesture Aria-Hidden Removal (v2.8.16)
- **Problem**:
  - Clicking the chat icon in the topbar did nothing; the slide-over chat modal failed to open.
  - In `floating-chat-widget.blade.php`, `<script>` tags were positioned before the root `<div>`, creating multiple root elements in Livewire 3 and breaking `$wire` method bindings.
  - `FloatingChatWidget` lacked Livewire `#[On]` event listener attributes for `toggle-floating-chat`, `open-floating-chat`, and `close-floating-chat`.
  - The browser console reported an accessibility warning: `Blocked aria-hidden on an element because its descendant retained focus` due to `aria-hidden="true"` on `#mobile-sidebar-gesture-root` containing the focusable `#mobile-edge-handle` button.
- **Solution**:
  - **Livewire 3 Single-Root Structure (`floating-chat-widget.blade.php`)**: Moved `<script>` tags inside the component's single root `<div>`, restoring `$wire` reactivity and Livewire DOM reconciliation.
  - **Livewire Event Listeners (`FloatingChatWidget.php`, `chat-topbar-button.blade.php`)**:
    - Added `#[On('toggle-floating-chat')]` to `toggleWidget()`, `#[On('open-floating-chat')]` to `openWidget()`, and `#[On('close-floating-chat')]` to `closeWidget()`.
    - Enhanced topbar chat button to dual-dispatch to both the native window event and the Livewire client event bus (`window.Livewire.dispatch('toggle-floating-chat')`).
  - **Resolved Console Accessibility Warning (`mobile-sidebar-gestures.blade.php`)**: Removed `aria-hidden="true"` from `#mobile-sidebar-gesture-root`.

### 40. Topbar Chat Drawer Instant Slideover & Alpine Event Race Fix (v2.8.17)
- **Problem**:
  - Clicking the chat icon in the topbar still failed to open the slide-over chat modal.
  - The drawer root container had a hardcoded inline `style="display: none;"` in `floating-chat-widget.blade.php`, which prevented Alpine's `x-show="isOpen"` transition from displaying the drawer.
  - A race condition existed where dual-dispatching both `window.dispatchEvent` and `window.Livewire.dispatch('toggle-floating-chat')` caused Livewire to invoke both `openWidget()` and `toggleWidget()` in the same request batch, immediately toggling `isOpen` back to `false` and closing the drawer.
- **Solution**:
  - **Removed Hardcoded Inline Style (`floating-chat-widget.blade.php`)**: Removed `style="display: none;"` from the drawer container, allowing Alpine's `x-show="isOpen"` and `x-cloak` to control visibility smoothly without inline style overrides.
  - **Eliminated Dispatch Race Condition (`chat-topbar-button.blade.php`)**: Kept clean single-dispatch `window.dispatchEvent(new CustomEvent('toggle-floating-chat'))` on the topbar button, allowing Alpine to toggle `isOpen` instantly with 0ms latency and notify Livewire via `$wire.openWidget()` / `$wire.closeWidget()`.
  - **Synchronized Livewire State (`FloatingChatWidget.php`)**: Removed redundant `#[On('toggle-floating-chat')]` from `toggleWidget()` to prevent double-execution, while maintaining clean `#[On('open-floating-chat')]` and `#[On('close-floating-chat')]` handlers and resetting modal states reliably.

### 42. Royalty Bonus Management Module (v2.9.0)
- **Problem & Requirements**:
  - Need for monthly royalty bonus calculation and distribution sheets based on employee designations, attendance records, and performance appraisal percentage deductions.
  - Eligible employees include `Active`, `Resigning This Month`, and `Suspended` employees. Resigned, terminated, and inactive staff must be excluded.
  - Designation royalty bonus configuration: User specifies `royalty_annual_budget`. The system must automatically determine:
    - Monthly Max Payout = `ceil((annual_budget / 12) / 100) * 100` (e.g., 8333.33 -> 8400).
    - Monthly Min Payout = `monthly_max_payout / 2`.
  - Excel file uploads when generating reports: Attendance summary (Absent Days, Late In / Early Out, Missing Thumbs) and Performance Deduction % report.
  - Deduction rules: Performance deduction applied to baseline max payout first, followed by attendance deductions (absent flat/per-day penalties, grace thresholds for late/early and missing biometric thumbs).
  - Payout floor: Net payout floored at Monthly Min Payout unless disabled in settings.
  - Dedicated Exempt Staff master list and interactive per-report toggle granting 100% max payout without deductions.
  - Audit validation & lock with one-click unlock capability.
  - OpenSpout Excel export and A4 landscape printable payout sheets with signatures matching Tips styling.
- **Solution**:
  - **Database Migrations & Models**:
    - `designations`: Added `royalty_annual_budget`, `royalty_monthly_max`, and `royalty_monthly_min` with automatic lifecycle calculation.
    - `royalty_exempt_employees`: Master list of exempt employees referencing `employees.employee_code`.
    - `royalty_reports`: Stores report period, cutoff date, uploaded file paths, status (`draft`, `generated`, `validated`), totals, and validator timestamps.
    - `royalty_report_items`: Stores per-employee payout breakdown, attendance counts, performance %, total deductions, and net payout.
  - **Calculation Engine (`RoyaltyCalculationService.php`)**:
    - High-performance Excel/CSV readers for attendance and performance files with flexible column header matching.
    - Full deduction pipeline with minimum floor enforcement and fast recalculation.
    - OpenSpout streaming Excel export (`exportExcel()`).
  - **Filament Resources & Pages**:
    - `RoyaltyReportResource` (`ListRoyaltyReports`, `CreateRoyaltyReport`, `EditRoyaltyReport`, `ViewRoyaltyReport`).
    - `ViewRoyaltyReport`: Summary stats cards, department tabs, interactive table with instant exemption toggle, and header actions (Validate & Lock, Unlock, Recalculate, Download Excel, Print).
    - `RoyaltyExemptEmployeeResource`: Manage exempt employees master list.
    - `ManageRoyaltyBonusSettings`: Global settings page for absent, late/early, missing thumb rules, and floor protection.
  - **Print Layout (`royalty/print.blade.php`, `RoyaltyReportPrintController.php`)**:
    - Professional A4 landscape payout sheets with department filter, attendance/deduction columns, net payout highlights, and HR/Finance/Management signature blocks.

### 43. Royalty Held Balances Ledger & Annual Merit Release (v2.9.1)
- **Problem & Requirements**:
  - Need to track cumulative held royalty bonus amounts for employees who were suspended or had their bonuses held across monthly reports.
  - Requirement to view the detailed month-by-month historical list of what amount was held in what month for any employee.
  - Ability for management to release held amounts (full release or partial release based on merit points collected during the year, typically executed in December), with justification notes and audit logging.
  - Requirement for the attendance file upload logic in Royalty Bonus to match the exact same HRMS file uploaded in the Tips module.
  - Requirement for a sample performance percentage deduction Excel template.
- **Solution**:
  - **Database Migration & Models (`RoyaltyHeldRelease.php`, `RoyaltyReportItem.php`)**:
    - Added `payout_status` (`Release`, `Hold`) and `held_reason` to `royalty_report_items`. Suspended staff automatically default to `Hold`.
    - Created `royalty_held_releases` table tracking disbursements with `amount`, `merit_points`, `release_date`, `notes`, and `released_by`.
    - Added `royaltyReportItems` and `royaltyHeldReleases` relationships to `Employee`.
  - **Royalty Held Balances Resource (`RoyaltyHeldBalanceResource.php`)**:
    - Placed under `Royalty Bonus Management`.
    - Lists all employees with held bonuses, displaying Total Held, Total Released, Remaining Balance, and count of held months.
    - View page (`ViewRoyaltyHeldBalance.php`, `view-royalty-held-balance.blade.php`) shows profile banner, 4 stat cards, full month-by-month breakdown of held bonuses, and historical release transactions with revert capability.
    - "Release Held Amount" modal action allows full or partial merit-based disbursement.
  - **HRMS Attendance Parser Compatibility (`RoyaltyCalculationService.php`)**:
    - Enhanced `parseAttendanceFile()` to match `TipsCalculationService::parseHrmsFile()` with two-row category/header detection, LOP/absent used balance summing, and punch counts.
  - **Performance Upload Sample File**:
    - Generated sample Excel template at `public/samples/royalty_performance_deduction_sample.xlsx` and dynamic download endpoint (`/royalty/samples/performance-deduction`).
    - Added direct download link in `RoyaltyReportForm.php` helper text.

### 44. Streamlined 3-Column Performance Deduction Upload & Deduction List Scoping (v2.9.2)
- **Problem & Requirements**:
  - The user requested the performance deduction upload file to strictly have only 3 columns: `Employee Code`, `Percentage` (e.g. `10%` or `10`), and `Remarks`.
  - The deduction must strictly apply ONLY to the uploaded list. Any eligible active employee not listed in the file must receive 0% deduction.
  - The downloadable sample Excel template must reflect this exact 3-column format.
- **Solution**:
  - **3-Column Sample Template Generation (`RoyaltyCalculationService.php`, `public/samples/royalty_performance_deduction_sample.xlsx`)**:
    - Standardized `downloadPerformanceSample()` and regenerated physical sample spreadsheet with headers `['Employee Code', 'Percentage', 'Remarks']`.
    - Updated `RoyaltyReportForm.php` helper text with direct sample download link highlighting the 3 required columns and scope behavior.
  - **Flexible 3-Column Parser (`parsePerformanceFile()`)**:
    - Enhanced parser to identify Employee Code, Percentage (`percentage`, `deduction`, `number of percentage`, `perf %`, etc.), and Remarks (`remarks`, `reason`, `notes`, etc.) with positional fallbacks `[0 => Code, 1 => Percentage, 2 => Remarks]`.
    - Returns structured array `array<string, array{percent: float, remarks: ?string}>`.
  - **Strict Deduction Scoping (`generate()`)**:
    - Configured report generator so `$perfInfo = $performanceData[$code] ?? null`. If an employee code is not present in the uploaded performance file, their performance deduction percent strictly defaults to `0.0%` and remarks to `null`.
    - Deduced remarks from the uploaded file are saved directly into `royalty_report_items.remarks` and displayed in the Filament table with hover tooltips.
  - **Database Attribute Harmonization**:
    - Updated report viewing and print export queries to match exact database column names (`department`, `designation`, `performance_deduction_percent`, `performance_deduction_amount`) and added backward-compatible accessors to `RoyaltyReportItem`.

### 45. Designation Royalty Bonus Calculation Toggle & Settings Page Manager (v2.9.3)
- **Problem & Requirements**:
  - Need for a toggle option on designations to determine whether that designation is to be calculated in Royalty Bonus management.
  - Ability to manage this designation eligibility both directly from the Designations table and from the Royalty Bonus Settings page.
  - Report calculation engine must only include employees whose designations have this toggle enabled.
- **Solution**:
  - **Database & Model (`designations.is_royalty_eligible`, `Designation.php`)**:
    - Created migration `2026_10_29_171500_add_is_royalty_eligible_to_designations_table.php` adding boolean column `is_royalty_eligible` (default `false`).
    - Added `is_royalty_eligible` to `$fillable`, boolean cast, and query scope `scopeRoyaltyEligible()`.
  - **Designations Table & Form Integration (`DesignationsTable.php`, `DesignationForm.php`)**:
    - Added `ToggleColumn::make('is_royalty_eligible')` labeled "Royalty Bonus" with sorting and alignment, plus `TernaryFilter` for quick status filtering.
    - Added "Include in Royalty Bonus Calculation" toggle to `DesignationForm` under the Royalty Bonus Configuration section.
  - **Royalty Settings Designation Manager (`ManageRoyaltyBonusSettings.php`, `manage-royalty-bonus-settings.blade.php`)**:
    - Added interactive designation manager with search bar, department dropdown filter, status filter (All, Eligible, Excluded), and bulk enable/disable actions.
    - Integrated real-time Livewire toggle switches (`toggleDesignation()`) for 1-click status updating with instant toast notifications.
    - Added summary stats displaying total eligible designations and cumulative monthly max budget.
  - **Calculation Engine Filtering (`RoyaltyCalculationService.php`)**:
    - Updated `generate()` to filter employees with `whereHas('designation', fn ($q) => $q->where('is_royalty_eligible', true))`, ensuring only staff with eligible designations are calculated in royalty bonus monthly reports.

### 46. Sticky Sidebar Resource Menu Search with Real-Time Filtering (v2.9.4)
- **Problem & Requirements**:
  - The application has accumulated dozens of resource modules and menu groups, making manual navigation slow and tedious.
  - User requested a sticky search bar at the top of the sidebar navigation to quickly filter resource menus.
  - The search bar must stick to the top when scrolling down the sidebar.
  - When typing, menu items and groups in the sidebar must filter in real time to show only matching results.
  - If no matching resource menu is found, an empty-state message indicating no resource menu is available must be displayed.
- **Solution**:
  - **Render Hook Integration (`app/Providers/Filament/KamkajPanelProvider.php`)**:
    - Registered `PanelsRenderHook::SIDEBAR_NAV_START` to render `filament.components.sidebar-search`.
  - **Sticky Component UI (`resources/views/filament/components/sidebar-search.blade.php`)**:
    - Placed directly inside `.fi-sidebar-nav` right above navigation groups.
    - Uses `sticky -top-8 z-20 -mx-2 px-2 pb-2.5 pt-1.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-200/70 dark:border-white/10` to remain pinned to the top as users scroll through lengthy menus.
  - **Zero-Latency Client-Side DOM Filtering (`Alpine.js - sidebarMenuSearch`)**:
    - Scans `.fi-sidebar-item` labels and `.fi-sidebar-group` headers on input.
    - Hides non-matching items and empty groups while preserving parent groups that contain at least one match.
    - Temporarily auto-expands collapsed parent groups when a child item matches, restoring their original state when search is cleared.
  - **Empty-State Feedback & Reset**:
    - Renders an interactive empty state card when 0 results match: `No resource menu found for "<query>"` with an instant "Clear search" button.
  - **Keyboard Shortcuts & Collapsed Sidebar Mode**:
    - Added global shortcut `Ctrl+K` / `⌘K` to focus the search bar from anywhere in the panel, `Esc` to clear, and `Enter` to navigate directly to the first visible result.
    - When the desktop sidebar is collapsed (`$store.sidebar.isOpen === false`), displays a compact search icon button that expands the sidebar and focuses the input.

### 47. Search Icon Alignment Fix, 12-Month Join Date Tenure Budget & Royalty Balance Overview (v2.9.5)
- **Problem & Requirements**:
  - The search icon in both the sidebar search bar and the royalty bonus settings page was not properly aligned (hidden under input background due to missing z-index and DOM position).
  - In Royalty Bonus, each employee's join date determines their total budget for a 12-month tenure starting from their joining date.
  - A Balance Overview page was needed for the generated data to see how much budget is remaining after the calculations.
- **Solution**:
  - **Search Icon Realignment (`sidebar-search.blade.php`, `manage-royalty-bonus-settings.blade.php`, `royalty-balance-overview.blade.php`)**:
    - Wrapped magnifying glass SVG in `pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-gray-400 dark:text-gray-500`, guaranteeing perfect vertical centering and elevation above input backgrounds.
  - **12-Month Join Date Tenure Calculation Engine (`RoyaltyCalculationService.php`)**:
    - Created `calculateTenureCycle(?string $rawJoinDate, ?Carbon $referenceDate = null)` to map any reference date to the employee's personal 12-month tenure cycle (Tenure Year, Start Date, End Date, and Tenure Month 1-12) calculated from `join_date_formatted`.
  - **Database & Model Fields (`add_tenure_and_budget_balance_to_royalty_report_items_table.php`, `RoyaltyReportItem.php`)**:
    - Added columns: `join_date`, `tenure_year`, `tenure_start`, `tenure_end`, `tenure_month`, `starting_tenure_budget`, `consumed_tenure_budget`, and `remaining_budget`.
  - **Tenure Budget Capping (`generate()`, `recalculateItem()`)**:
    - Queries previous payouts in the same 12-month tenure cycle.
    - Caps proposed monthly payouts so the employee never exceeds their total 12-month tenure budget, updating `consumed_tenure_budget` and `remaining_budget` with automated audit remarks.
  - **Royalty Budget Balance Overview Module (`RoyaltyBalanceOverview.php`, `royalty-balance-overview.blade.php`)**:
    - Dedicated management page under `Royalty Bonus Management` (slug: `/royalty-bonus/balance-overview`, icon: `Heroicon::Scale`).
    - 4 KPI Stat Summary Cards: Total 12-Mo Budget, Total Utilized, Total Remaining Budget, and Overall Utilization %.
    - Multi-filter bar: Search input with aligned icon, Report scope selector (Cumulative 12-Month Balances vs. individual reports), Department filter, and Budget status pill toggles (`All`, `Healthy`, `Low Budget <20%`, `Exhausted`).
    - Interactive data table showing Join Date, 12-Mo Tenure Cycle, 12-Mo Budget, Utilized, Remaining Budget, visual usage progress bar, and status badge.
    - Interactive slide-over modal detailing the employee's month-by-month calculation breakdown across reports in the current 12-month tenure cycle.
    - Export to Excel feature via `RoyaltyReportPrintController::exportBalanceOverview()`.
  - **Report Table & Print Enhancements (`ViewRoyaltyReport.php`, `print.blade.php`)**:
    - Added `Join Date`, `Tenure Period`, and `Remaining Budget` columns to `ViewRoyaltyReport`.
    - Added "Balance Overview" header action button linking directly to the Balance Overview page.

### 48. Full-Screen Split Studio Layout & Zero-Scroll UX for Royalty Settings (v2.9.6)
- **Problem & Requirements**:
  - The Royalty Bonus Settings page had artificial width limits (`max-w-5xl`) and vertically stacked deduction form cards, forcing users to scroll past ~1500px of content just to access the designations table and save button.
  - User requested the settings page be full screen, the layout be much more convenient to eliminate excessive vertical scrolling, and the UI be more interesting, modern, and engaging.
- **Solution**:
  - **Full-Screen Width Studio (`ManageRoyaltyBonusSettings.php`, `manage-royalty-bonus-settings.blade.php`)**:
    - Added `getMaxContentWidth(): ?string { return 'full'; }` to the page class and replaced the narrow centered container with `w-full space-y-5`, taking full advantage of widescreen monitors.
  - **Side-by-Side Split Studio**:
    - Structured the page into a 2-column layout:
      - **Left Console (col-span-5)**: Compact Deduction Policy console containing Absent Days, Late In & Early Out, Missing Biometrics (Thumb), and Guaranteed Minimum Floor Protection, with an independent scroll container and sticky "Save Rules" triggers.
      - **Right Console (col-span-7)**: Designation Participation Matrix featuring live search, department selector, status tabs (`All`, `Eligible`, `Excluded`), bulk enable/disable actions, and a table with sticky headers and internal scroll container (`max-h-[580px]`).
    - Both consoles sit side-by-side above the fold, completely eliminating vertical page scrolling.
  - **Segmented View Mode Switcher**:
    - Added view tabs (`Split Studio`, `Designations Only`, `Rules Only`) controlled by Livewire property `public string $viewMode = 'split'`. Allows users to expand the designation table or deduction cards to 100% full width on demand.
  - **1-Click Policy Presets**:
    - Added `applyPreset(string $preset)` with `⚡ Standard`, `🛡️ Strict`, and `🌿 Lenient` triggers that populate balanced deductions, grace thresholds, and floor rules in 1 click.
  - **Executive KPI Dashboard**:
    - Added 4 real-time stat cards in the header strip: Total Roles, Eligible Roles with live percentage pill, Monthly Max Pool Cap, and Guaranteed Floor status.
  - **Refined Aesthetics & Ergonomics**:
    - Cleaned up currency prefixes (`Rs.`) to avoid overlaps, polished icon avatars, and added animated Livewire toggle switches.


