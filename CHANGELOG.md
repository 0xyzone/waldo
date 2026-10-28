# Changelog

All notable changes to the **Waldo Enterprise Operations Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.8.1] - 2026-10-28

### Fixed & Enhanced
- **Employee SSID/SSF Directory Dark Theme & Dropdown UI Overhaul**:
  - **High-Contrast Input Contrast**: Resolved color mismatch in dark mode where typed query text in the search bar blended invisibly into the dark container background.
  - **Padding Collision Fix**: Fixed search input icon padding overlap to prevent the magnifying glass from encroaching on placeholders and query text.
  - **Modern Custom Dropdown Chevrons**: Replaced clunky native OS select dropdowns with custom chevron-down indicators, sleek borders, and styled option popups.
  - **Modal Dark Theme Integration**: Enhanced export modal inputs, filters, and scrollable employee selector.
  - **Interactive Copy Notifications**: Implemented floating copy toast alert with green check badge when copying employee SSIDs.

---

## [2.8.0] - 2026-10-28

### Added
- **Universal Action Logging System**:
  - **`HasActionLogs` Trait**: Polymorphic model trait that automatically logs record creations, deletions, status transitions, and individual attribute modifications (with before/after diffs in JSON).
  - **In-Form Action Log History**: Embedded `ActionLogSection` component at the bottom of edit/view forms across all application resources (Biometric Allotments, Candidates, Employees, Leavers, Promotions, Transfers, Suspensions, Salary Increments, Tips Reports, Nametag Distributions, Nametag Fines, Settings, etc.).
  - **Dedicated Action Logs Resource (`ActionLogResource`)**: Central audit trail resource under *System Settings* featuring searchable and filterable log history by resource type, action, user, and date range, with a detailed slideover inspector displaying full attribute diffs.
  - **Unified `action_logs` Migration**: Created polymorphic `action_logs` table with composite indexing and backfilled existing history from legacy ID card request and unofficial nametag logs.

---

## [2.7.1] - 2026-10-27

### Fixed
- **Resolved QueryException in Finance Overview Widget**: Removed query on non-existent `amount` column in `adjustments` table (`select sum(amount)`), replacing it with approved adjustments count (`adjustments_approved`) and total adjustments count to align with database schema.

---

## [2.7.0] - 2026-10-27

### Added
- **Category Resource Operations Suite (3 New Widgets)**: Created dedicated dashboard widgets covering Finance, IT & Systems, and Purchase & Store navigation groups:
  - **Finance Operations Widget (`FinanceOverviewWidget`)**: Real-time hub tracking pending adjustments (count & total amount in NPR), active salary/tips holds, monthly leavers, terminations, and pending HRMS sync with direct resource navigation.
  - **IT & Systems Hub Widget (`ItOverviewWidget`)**: Dedicated operational view monitoring ID card requests across lifecycles (pending design, designed, in print queue, completed), biometric allotments/enrollments, print batches, and user mapping.
  - **Purchase & Store Hub Widget (`PurchaseStoreOverviewWidget`)**: Inventory and supply queue tracking monthly nametag distribution, pending unofficial nametag requests, total nametag fines amount/count, and unacknowledged fines.
- **Filament Shield Widget Permissions**: Registered Shield permissions (`View:FinanceOverviewWidget`, `View:ItOverviewWidget`, `View:PurchaseStoreOverviewWidget`) with `HasWidgetShield` integration for role-based category visibility.

---

## [2.6.0] - 2026-10-27

### Added
- **Dashboard Operations Suite (4 New Widgets)**: Created 4 high-fidelity widgets matching Waldo's "Zen Soft Tech" dark aesthetic (`rounded-3xl`, ambient blur glows, custom metric pills, and quick action links):
  - **Workforce Overview Widget (`WorkforceOverviewWidget`)**: Real-time staff statistics tracking Active, Suspended, Resigning This Month, and Tips on Hold counts, along with male/female diversity distribution bars.
  - **Pending Operations Widget (`PendingOperationsWidget`)**: Operational queue hub tracking pending financial adjustments, ongoing employee suspensions, and pending salary increment requests with action status pills.
  - **Offboarding & Leavers Tracker Widget (`LeaversTrackerWidget`)**: Dedicated exit management overview highlighting monthly leaver counts, salary/tips holds, and a recent leaver activity timeline.
  - **Promotions & Growth Widget (`PromotionTransferActivityWidget`)**: Staff advancement tracker monitoring monthly promotions, acknowledgement completion rates, HRMS sync statuses, and recent promotion milestones.
- **Filament Shield Widget Permissions**: Registered Shield permissions (`View:WorkforceOverviewWidget`, `View:PendingOperationsWidget`, `View:LeaversTrackerWidget`, `View:PromotionTransferActivityWidget`) with `HasWidgetShield` integration for role-based widget access control.

---

## [2.5.13] - 2026-10-27

### Fixed
- **Global Elimination of Nested Widget Corner Outlines**: Uncoupled `.fi-wi-widget` from the elevated card border rules in `theme.css`. Because all custom dashboard widgets (`BirthdayListWidget`, `DateConverterWidget`, `DepartmentEmployeeStatsWidget`) render their own `rounded-3xl` cards, the outer `.fi-wi-widget` wrapper has been made strictly transparent with zero borders and zero shadows globally, eliminating the secondary outline previously peeking around the corners of every widget on the dashboard.

---

## [2.5.12] - 2026-10-27

### Fixed
- **Eliminated Double Widget Box**: Added `.fi-wi-clean` and `[data-widget-card]` zero-border/transparent styling in `theme.css`. This prevents the outer `.fi-wi-widget` grid wrapper from rendering a second empty card box beneath `DateConverterWidget`.
- **Harmonious Height Alignment**: Configured full-height flex column stretching so the Date Converter card aligns top and bottom borders cleanly with sibling widgets.

### Improved
- **Custom Interactive Calendar Popover**: Replaced native browser `<input type="date">` with an Alpine.js custom calendar popover featuring Sunday-first day alignment, month/year navigation dropdowns, active date glowing pills, and "Jump to Today" shortcut.
- **Right-Aligned Date Trigger Icon**: Repositioned the calendar icon to the trailing end of the input inside a dedicated accent pill with active hover glow.
- **Rich Date Typography**: Replaced raw numeric date formatting with bold localized day and date text (e.g. `Tuesday, October 27, 2026`) accompanied by an ISO helper preview.

---

## [2.5.11] - 2026-10-27

### Improved
- **Date Converter Widget Overhaul**: Redesigned the Date Converter dashboard widget (`DateConverterWidget`) with Waldo's premium "Zen Soft Tech" dark/light design system:
  - **Sleek Segmented Switcher**: Replaced the harsh red-orange pill buttons with modern segmented tab controls featuring active pulse dot indicators.
  - **Quick Action Controls**: Added an instant "Today" reset button with sparkle icon and a bidirectional "Swap" button (`⇄`) to seamlessly toggle direction while carrying over converted dates.
  - **Native Dark Theme Picker**: Fixed Chromium Windows styling with `[color-scheme:light] dark:[color-scheme:dark]` and calendar icon adornment.
  - **Bilingual Nepali Month Selector**: Added full Nepali script month titles (e.g., `01 - Baishakh (बैशाख)`) and custom chevrons.
  - **Ambient Glowing Result Card**: Upgraded output into an ambient glowing card displaying large bold localized typography, an interactive copy button with green checkmark confirmation, monospaced numeric date badge, and bilingual weekday badge.

---

## [2.5.10] - 2026-10-27

### Added
- **Bulk Role Assignment for Users**: Added an `Assign Roles` bulk action to the Users table (`UsersTable`), enabling administrators to assign multiple roles to any selected group of users at once.
- **Append vs Replace Role Amendment**: Built an assignment mode selector in the bulk action modal:
  - **Append**: Keeps the users' current roles and merges the newly selected roles.
  - **Replace**: Overwrites and syncs only the newly selected roles.
- **Security & Shield Integration**: Registered `AssignRoles` custom permission in `UserResource::getShieldResourcePermissions()` and restricted assigning the `super_admin` role to super administrators only.

---

## [2.5.9] - 2026-10-27

### Fixed
- **Removed Global Action `modal(true)` Override**: Removed the forced `modal(true)` call from `CreateAction`, `EditAction`, and `ViewAction` in `AppServiceProvider`. Now, resources with full dedicated create/edit page routes (such as Roles, Employees, and Candidates) correctly navigate to their dedicated page URLs instead of forcing a raw modal that bypassed page lifecycle hooks and triggered `Array to string conversion` query errors.
- **Explicit Role Page URL Routing**: Bound `CreateAction` on `ListRoles` and `EditAction` on `RoleResource` explicitly to `RoleResource::getUrl('create')` and `RoleResource::getUrl('edit', ...)` respectively.
- **Guard Name Safety Fallback**: Ensured `afterCreate()` and `afterSave()` in `CreateRole` and `EditRole` safely fall back to `Utils::getFilamentAuthGuard()` if `guard_name` is missing from submitted form data.

---

## [2.5.8] - 2026-10-27

### Changed
- **Removed Single-Thread Rayon Limitation**: Removed `RAYON_NUM_THREADS=1` from `package.json` build script. The Tailwind v4 Rust engine now utilizes all available CPU cores via multi-threading, cutting Vite build times from ~32s down to ~5.8s and restoring native cross-platform build execution on Windows Command Prompt and PowerShell.
- **Vite Build Report Optimization**: Set `reportCompressedSize: false` in `vite.config.js` to skip calculating gzip metrics for 30+ bundled font and CSS assets during production builds.
- **Cleaned Tailwind Source Paths**: Removed redundant `@source '../../storage/framework/views/*.php'` directive in `resources/css/app.css` to prevent duplicate scanning of cached compiled blade views.

---

## [2.5.7] - 2026-10-27

### Changed
- **Raised Request Memory Limit**: Configured runtime PHP memory limit to 512M across application bootstrap (`public/index.php`, `AppServiceProvider`) and role resource pages (`CreateRole`, `EditRole`) to resolve 500 internal server errors caused by memory exhaustion when Livewire snapshots the 400+ permission schema.
- **Sanitized Role Permissions Filtering**: Filtered raw flattened role permissions to ensure only valid non-empty string permission names are passed to `firstOrCreate` and `syncPermissions`.

---

## [2.5.6] - 2026-10-27

### Added
- **Designation Default Tips Import**: Added a dedicated `Import Tips` action to the Designations table toolbar allowing bulk import of default tips amounts from CSV or Excel (`.xlsx`) files with overwrite control.
- **Pre-filled Sample Template Downloads**: Built sample file generation (in both `.csv` and `.xlsx` formats) that automatically pre-populates all existing designations, associated departments, and their current default tips amounts.

### Changed
- **Multi-Format Header & Value Parsing**: Added resilient column detection for designation ID, title, department, and tips columns with currency stripping and support for headerless spreadsheets.

---

## [2.5.5] - 2026-10-27

### Added
- **Designation Default Tips Amount**: Added a `default_tips_amount` field to designations (`designations` table, model, form schema, table columns, and export service) to store the baseline expected tips for each designation.
- **Automatic Tips Default on Model Save**: Added model saving hook in `Employee` to auto-populate `tips_amount` from the designation's `default_tips_amount` plus adjustment whenever creating or saving an employee without explicit tips.

### Changed
- **Reactive Expected Tips Rendering**: Configured `designation_id` and `tips_adj` in `EmployeeForm` to dynamically calculate and render the expected tips amount (`default_tips_amount + tips_adj`) during employee creation and editing.
- **Local DB Tips Persistence**: Dehydrated `tips_amount` so the expected tips amount is properly stored in the local database while remaining decoupled from Google Sheets push synchronization.

---

## [2.5.4] - 2026-10-27

### Added
- **Department Overview Print Scope Modal**: Added an interactive modal popup to the Print Report button on the Department Overview widget, providing options to print either all departments or choose specific ones.
- **Searchable Multi-Department Dropdown**: Built a custom multi-select department dropdown with live search, individual checkbox toggles, Select All / Clear quick actions, and selection counter badges.

### Changed
- **Multi-Department Filtering Route**: Enhanced `ReportController@departments` to parse and filter by `department_ids` (as array or comma-delimited string) for targeted printable reports.

---

## [2.5.3] - 2026-10-27

### Changed
- **Global Action Slide-Over Modals**: Configured base `Action` and `ViewAction` (alongside `CreateAction` and `EditAction`) with `slideOver()` globally in `AppServiceProvider` so that every modal action opens as a slide-over drawer across all screens.
- **Edge-Anchored Slide-Over CSS**: Upgraded slide-over modal styling in `theme.css` with a fixed flex container, full `100dvh` viewport height, sticky headers and footers, and smooth horizontal slide animations.
- **Mobile Full-Width Drawer Support**: Ensured that on mobile viewports (< 640px), all modals display as full-width (100vw) full-height edge-to-edge sliding drawers, completely replacing centered popup dialogs.

---

## [2.5.2] - 2026-10-27

### Changed
- **Global Sunday-First Calendar Default**: Configured `DatePicker` and `DateTimePicker` components globally in `AppServiceProvider` via `configureUsing` so that all datepicker fields start the week on Sunday (`firstDayOfWeek(7)`).
- **Streamlined Resource Form Schemas**: Removed manual `firstDayOfWeek(0)` calls from Candidate, Employee, and Biometric Allotment schemas to inherit the global Sunday-first configuration cleanly.
- **Flatpickr Global Default**: Ensured Flatpickr calendar instances in the document studio layout explicitly default `firstDayOfWeek` to 0 (Sunday).

---

## [2.5.1] - 2026-10-26

### Fixed
- **Mobile Selection Checkbox**: Removed the artificial `"Select Record"` text label and desktop width constraints from `.fi-ta-selection-cell` in mobile card view, giving bulk checkboxes dedicated, unsqueezed dimensions (`1.125rem` / `flex-shrink: 0`).
- **Mobile Record Card Action Triggering**: Configured global dynamic record actions resolving to `view_checklist`, `view`, or `edit`, and added a mobile tap delegator so tapping stacked record cards smoothly activates their primary view/edit action or navigation target.
- **Card Label Tap Passthrough**: Set `.fi-ta-cell-label` to `pointer-events: none` on mobile so touching column labels forwards clicks cleanly to the record card.

---

## [2.5.0] - 2026-10-26

### Added
- **Global 7XL Slide-Over Modals**: Configured `CreateAction` and `EditAction` globally in `AppServiceProvider` with `modal(true)`, `slideOver()`, and `modalWidth(Width::SevenExtraLarge)` so that creating and editing records across all resources opens a wide slide-over modal drawer instead of navigating to separate pages.

### Changed
- **Checklist Row Click Routing**: Clicking rows on `MonthlyManualAttendancesTable` and `MonthlyManualRostersTable` now opens the interactive checklist view directly via `recordUrl` and `recordAction` instead of opening the edit form.

---

## [2.4.1] - 2026-10-26

### Fixed
- **Manual Attendance Checkbox Column Alignment**: Added `alignCenter()` to `is_attendance_updated` on `ViewMonthlyManualAttendance` and to `is_roster_updated` on `ViewMonthlyManualRoster`.
- **Table Cell & Checkbox Flex Centering**: Added `.fi-ta-cell.fi-align-center` and `.fi-ta-checkbox.fi-align-center` styling to `theme.css` to properly align checkbox containers and row checkboxes across both light and dark modes.

---

## [2.4.0] - 2026-10-25

### Added
- **Unofficial NameTag Requests Resource**: Added new standalone resource under `Purchase & Store` to record and track nametag requests for existing employees or non-employee personnel.
- **Autofill & Custom Source Handling**: Features toggle to search and autofill details from the employee database or enter external personnel manually.
- **Custom Name on Tag**: Added explicit field to capture exact name/text to be printed on the nametag.
- **List View Status Editing**: Enabled interactive `SelectColumn` for real-time status transitions directly from the table (`Pending`, `In Progress`, `Ready`, `Distributed`, `Cancelled`).
- **Automated Action Logging**: Preserves full audit history of status transitions with user, timestamp, and action description.

### Fixed
- **RoleResource Navigation Fatal Error**: Fixed `Call to undefined method BezhanSalleh\FilamentShield\Support\Utils::isResourceNavigationRegistered()` by delegating navigation calls to `FilamentShieldPlugin::get()`.

---

## [2.3.0] - 2026-10-25

### Added
- **Shield Enhanced Integration**: Installed and configured `agroezinger/filament-shield-enhanced` to intuitively cluster role permissions into sub-tabs by panel navigation groups (`HR & Admin`, `Roster Management`, `IT`, `Finance`, `Tips`, `Purchase & Store`, `Filament Shield`, `System Settings`).
- **Fine-Grained Resource Permissions**: Refactored hardcoded role checks into granular Shield permissions on Resources:
  - `EmployeeResource`: `Onboard:Employee`, `Suspend:Employee`, `Terminate:Employee`, `ToggleManager:Employee`.
  - `EmployeePromotionResource`: `SyncHrms:EmployeePromotion`, `ImportPromotions:EmployeePromotion`.
  - `EmployeeTransferResource`: `SyncHrms:EmployeeTransfer`, `ImportTransfers:EmployeeTransfer`.
  - `SalaryIncrementRequestResource`: `HrAcknowledge:SalaryIncrementRequest`, `FinanceAcknowledge:SalaryIncrementRequest`.
  - `NametagFineResource`: `HrAcknowledge:NametagFine`, `FinanceAcknowledge:NametagFine`.
  - `TipsReportResource`: `ValidateReport:TipsReport`, `UnlockReport:TipsReport`.
  - `UserResource`: `ToggleActive:User`, `ConfigureOtpEmail:User`, `Impersonate:User`.
  - `IdCardRequestResource`: `SendDiscordNotification:IdCardRequest`.
- **Fine-Grained Function Pages Permissions**: Added `HasPageShield` and multi-action permission declarations for pages:
  - `SsfUploadGenerator`: `Page:View:SsfUploadGenerator`, `Page:Download:SsfUploadGenerator`, `Page:Generate:SsfUploadGenerator`.
  - `Chat`: `Page:View:Chat`, `Page:CreateGroupChat:Chat`.
  - `Changelog`: `Page:View:Changelog` (categorized under System Settings).
- **Custom Application Capabilities**: Configured custom permission tokens for external tools including `access_letters_and_templates`, `access_employee_ssf_ids`, `access_salary_calculator`, and `export_department_reports`.

---

## [2.2.0] - 2026-10-25

### Added
- **Manual Attendance Staff Resource**: Added standalone resource under Roster Management allowing administrators to designate and manage staff members whose monthly attendance is maintained manually in HRMS. Includes employee preview details (department, designation) and active/inactive status switches.
- **Monthly Manual Attendance Checklist Resource**: Added month-based checklist resource under Roster Management that automatically populates all designated active manual attendance staff upon monthly record generation.
- **Live Attendance KPI & Department Filters**: Built dedicated view page with real-time statistics (Total Staff, Updated in HRMS, Pending Entry, Progress %), department filter pills, status switches (All, Pending, Updated), and single-click checkbox status toggling.
- **Batch & Synchronization Actions**: Added header actions to sync newly designated staff, add individual employees to existing periods, and bulk mark attendances as done or pending.

---

## [2.1.2] - 2026-10-23

### Added
- **Mark as Left Action**: Added section header action to the Biometric Allotment form allowing mapped users to transition status to "Left Job" with optional remarks and automatic `set_by` tracking.
- **Mark as Not Required Action**: Added section header action allowing mapped users to transition status to "Bio Not Required" with optional remarks and automatic `set_by` tracking.

### Changed
- **Mobile Header Action Optimization**: Applied `labeledFrom('md')` and compact `size('sm')` with tooltips across all Biometric Allotment section header actions (`Mark as Done`, `Mark as Left`, `Mark as Not Required`, `Call`), ensuring buttons gracefully collapse into sleek icon buttons on mobile viewports to prevent wrapping or clustering.

---

## [2.1.1] - 2026-10-22

### Fixed
- **Reverb WebSocket Hostname Config**: Restored missing `hostname` configuration key in `config/reverb.php`, resolving `ErrorException: Undefined array key "hostname"` when starting the Reverb WebSocket daemon.
- **cPanel Watchdog Resilience**: Added CloudLinux alt-php (`/opt/alt/php84/usr/bin/php`, `/opt/alt/php83/usr/bin/php`) and system PHP (`/usr/local/bin/php`) candidate paths to `scripts/run_reverb.sh`.
- **Health Check Robustness**: Added `stream_socket_client` fallback and increased probe timeout in `WebsocketStatusIndicator` for servers with restrictive socket configs.

---

## [2.1.0] - 2026-10-16

### Added
- **Excel & CSV Import for Promotions**: Added toolbar action to upload and import promotions from Excel (.xlsx) and CSV files matching the HRMS change-type format, enabling seamless transfer of entries between production and local environments.
- **Excel & CSV Import for Transfers**: Added toolbar action to upload and import employee transfers from Excel (.xlsx) and CSV files matching the HRMS change-type format.
- **Silent Database Ingestion**: Guaranteed zero Google Sheets updates or background queue jobs during import via Eloquent `withoutEvents()` and `saveQuietly()`, fulfilling data isolation requirements.
- **Intelligent Division & Job Title Mapping**: Auto-normalization of HRMS slug variants and cross-department designation resolution.
- **Update Existing Toggle**: Optional switch to quietly update existing records matching employee code and effective date.

---

## [2.0.6] - 2026-10-16

### Changed
- **Rich Structured Record Card Layout**: Redesigned the interior of each employee promotion card to eliminate barren gaps and empty vertical whitespace.
- **Monogram Avatar Badges**: Added styled circular monogram initial avatars to employee cards for an executive, personalized presentation.
- **Centered Career Elevation Deck**: Replaced thin bottom bars with a dedicated, centered 2-column career elevation container showing previous role, directional arrow badge, and promoted designation.
- **Metadata Status Footer**: Added a dedicated bottom line on each card with a calendar date badge and career advancement tag.

---

## [2.0.5] - 2026-10-16

### Changed
- **Modern Minimal Light-Glass Wish Card Redesign**: Complete architectural design overhaul of the promotion wish card using a minimal light glass concept. Built on a soft, calm slate-ivory canvas with clean translucent frosted glass panels and high-contrast typography.
- **Promotion Date Display**: Added the effective promotion date to every individual card within the multi-record grid.
- **Gap-Free Full-Height Grid**: Re-engineered CSS grid to proportionally stretch all rows to fill the 1080p canvas, completely resolving bottom spacing gaps.
- **Streamlined Minimal Header & Footer**: Removed awkward floating footer container, seamlessly weaving the Management congratulatory note into the header and adding a subtle, unobtrusive watermark line at the bottom.

---

## [2.0.4] - 2026-10-16

### Changed
- **Glassmorphic Wish Card Redesign**: Fully redesigned the 16:9 1080p promotion wish card from the ground up using authentic glassmorphism techniques. Features a calm obsidian-slate canvas with ambient mesh glows, translucent frosted glass cards, and zero harsh brightness or flashy elements.
- **Official Management Copy Integration**: Integrated the verbatim Management congratulatory text and closing dedication from the promotion print letterhead across both single spotlight and multi-grid cards.
- **Spotlight & Multi Card Refinement**: Formatted single-recipient view into an executive recognition card with career progression tiles and streamlined multi-record cards (max 25 names) with balanced 5-column glass layout.

---

## [2.0.3] - 2026-10-16

### Added
- **16:9 1080p Promotion Congratulation Wish Card**: Full high-resolution (1920x1080) celebratory wish card generator available via both row and bulk actions in Employee Promotions.
- **Dedicated Single Spotlight Design**: When a single promotion record is chosen, renders a personalized celebratory spotlight layout with prominent employee badge, role progression comparison, and welcoming personal wishes.
- **Multi-Card Pagination (Max 25)**: When multiple records are selected, cards are automatically chunked into at most 25 names per card (e.g. 30 records chunk into Card 1 with 25 and Card 2 with 5), with interactive card switcher tabs and batch export.
- **Client-Side High-Res Rasterization**: Instant 1:1 image download as crisp JPG or PNG using unscaled html2canvas rendering with interactive zoom preview controls (Fit, 50%, 100%).
- **Clean & Bright Aesthetic**: Pure white and warm ivory canvas styling with cheerful, welcoming copy and zero dark glares.

---

## [2.0.2] - 2026-10-16

### Added
- **Dependent Multi-Select Designation Filters**: Table filters for designations (in Employee Promotions, Employee Transfers, and Employees) now dynamically display only the designations related to the chosen department.
- **Multi-Select Capability**: Users can select multiple designations simultaneously to filter tables across career milestones and employee directories.
- **Conditional Visibility & Auto-Reset**: The designation filter is automatically hidden and disabled until a department is selected, and automatically resets if the selected department changes or is cleared.

---

## [2.0.1] - 2026-10-16

### Added
- **Promotion Date Range Filter**: Added a comprehensive date range filter (`From Date` and `To Date`) to the Employee Promotions table with active indicator tags for easy filtering and reset.

### Changed
- **Rank-Sorted Promotion Congratulations**: Printed congratulatory announcements now sort promoted employees strictly by their updated designation rank in ascending hierarchical order, with unranked designations positioned at the bottom.
- **Reference Number Removal**: Completely stripped internal reference number (`REF: WLD/HR/PROMO/...`) from the promotion congratulations print letterhead for a cleaner presentation.
- **Dynamic Compact Versioning**: Fixed collapsed desktop sidebar version pill to dynamically calculate and display major, minor, and micro versions rather than truncating to a static value.

### Fixed
- **Semantic Versioning Synchronization**: Added dynamic version resolution and semver parsing in `ChangelogService`, ensuring major, minor, and micro/patch version bumps propagate automatically across the application.

---

## [2.0.0] - 2026-10-16

### Added
- **Universal Responsive Mobile Card Transform**: Every standard table row across the entire portal automatically transforms on mobile devices into a high-contrast, touch-friendly card with neat label-value pairs and dedicated action bars, completely eliminating horizontal scrolling.
- **Enterprise Typography (Plus Jakarta Sans)**: Replaced default system typography with Plus Jakarta Sans across the entire portal for a clean, sharp, corporate aesthetic.
- **Brand Identity & Color System**: Shifted the portal theme to a high-contrast Vibrant Orange (`#f97316`) and Fiery Red (`#ef4444`) modern tech palette with crisp white surfaces and slate dark mode.
- **Native Mobile Application Experience**: Added native iOS/Android safe-area-inset padding, momentum touch scrolling, tap-highlight suppression, and mobile sheet sidebar with rounded corners.
- **Tactile Micro-Interactions**: Tactile button press scaling (`active:scale-[0.96]`), smooth card slide-in animations, modal entrance transitions, and topbar brand gradient accent.

### Changed
- Refactored sidebar active indicators with brand orange gradients and live status ping.
- Enhanced form inputs with rounded-xl containers, smooth focus halo transitions, and responsive touch padding.
- Upgraded PWA theme color to brand orange (`#ea580c`) and enabled `viewport-fit=cover`.

---

## [1.4.0] - 2026-10-16

### Added
- **In-App Changelog System**: Interactive release notes viewer page (`/kamkaj/changelog`) with version timeline, badge categorizations (`Added`, `Changed`, `Fixed`), and mobile-responsive layout.
- **Centralized Changelog Service**: `App\Services\ChangelogService` managing release history, semantic versioning, and feature tracking.
- **Dynamic Version Indicators**: Dynamic version badge in sidebar footer linking directly to the Changelog.
- **Standardized Repository Changelog**: `CHANGELOG.md` adhering to Keep a Changelog specifications.

---

## [1.3.0] - 2026-10-15

### Added
- **SSF Upload Generator**: Dedicated generator page (`SsfUploadGenerator`) for preparing Social Security Fund contribution spreadsheets.
- **Daily Attendance Reconciliation**: Reporting pipeline with Excel data exports.
- **Template Upload Persistence**: Persisted uploaded HRMS templates in `storage/app/templates` to maintain clean git states on deployments.

### Fixed
- Fixed FileUpload disk configuration and stripped undeclared XML prefixes from row exports.

---

## [1.2.0] - 2026-10-14

### Added
- **EOM/GOM 4K Wish Card Generator**: Complete redesign with 2:1 4K resolution matching presentation screens.
- **Champagne & Glassmorphism Styling**: Warm champagne pattern backgrounds and orange glassmorphism styling.
- **Live Unscale Capture**: Ensures pixel-identical export between preview and downloaded cards.

### Changed
- Simplified wish card typography and streamlined wording layout.

---

## [1.1.0] - 2026-10-10

### Added
- **Promotions & Transfers Management**: Employee Promotions and Transfers tracking with exact HRMS template replication.
- **Bulk HRMS Actions**: Bulk sync and unsync actions for employee career milestones.
- **Batch Google Sheets Synchronization**: Single summary notification dispatch upon completion of bulk promotions sync.

### Fixed
- Resolved employee lookup issues in tips adjustments by querying against primary key `employee_code`.

---

## [1.0.0] - 2026-09-01

### Added
- **Core Workforce Management**: Baseline launch on Laravel 13 and Filament v5 (`kamkaj` panel) with Amber theme.
- **Employee Directory**: Non-incrementing `employee_code` primary keys and status lifecycle tracking.
- **Employee Suspensions**: Suspension lifecycle tracking with auto-expiry scheduler (`suspensions:check-status`).
- **Financial Holds & Adjustments**: Adjustments ledger, Leavers offboarding, and Terminations module.
- **Bi-Directional Google Sheets Sync**: Automated sync for employee records (`employees:sync`).
- **Public REST API**: Employee REST API endpoints with flexible relationship inclusions.
