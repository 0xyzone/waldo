# Changelog

All notable changes to the **Waldo Enterprise Operations Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.9.19] - 2026-10-30

### Fixed
- **Employee Model Royalty Releases Relationship (`app/Models/Employee.php`)**:
  - Added `royaltyReleases()` `HasMany` relation method on the `Employee` model as a direct alias for `royaltyHeldReleases()`, fixing the `BadMethodCallException: Call to undefined method App\Models\Employee::royaltyReleases()` encountered when generating the Held Balances print sheet.

---

## [2.9.18] - 2026-10-30

### Added & Fixed
- **Populate Held Balances from Deductions & Added Printable Sheets for Held Balances and Budget Balance Overview**:
  - **Deducted Amount Held Balance Calculation (`RoyaltyHeldBalanceResource.php`, `ViewRoyaltyHeldBalance.php`, `view-royalty-held-balance.blade.php`)**: Updated the Held Balances resource and detail page to detect and populate employees who have deducted bonus amounts (`(monthly_max_payout - final_payout) > 0`) alongside manual `'Hold'` payout statuses. Respects minimum floor thresholds so the exact withheld bonus difference is tracked and available for release.
  - **Individual Employee Held Balance Print Sheet (`royalty.held-balances.print-employee`, `resources/views/royalty/print-held-balance.blade.php`)**: Added row and header print actions generating an official A4 portrait statement with employee details, summary KPI cards, itemized monthly deduction breakdown, release history, and authorization signatures.
  - **All Held Balances Printable Directory (`royalty.held-balances.print-list`, `resources/views/royalty/print-held-balances-list.blade.php`)**: Added header print action in `ListRoyaltyHeldBalances` producing a summary sheet of all employees with held balances, departments, total held, released amounts, and remaining payable balances.
  - **Royalty Budget Balance Overview Print Sheet (`royalty.balance-overview.print`, `resources/views/royalty/print-balance-overview.blade.php`, `royalty-balance-overview.blade.php`)**: Added a direct "Print Sheet" button alongside "Export Excel" on the 12-Month Royalty Budget Balance Overview page, rendering a landscape print-friendly report with aggregate metrics and tenure balance details.

---

## [2.9.17] - 2026-10-30

### Fixed & Enhanced
- **Print Sheet Margin Safety, Center Alignment & Removal of Signature Column (`resources/views/royalty/print.blade.php`)**:
  - **Printer Margin Safety**: Adjusted `@page` margins to `12mm 10mm` so physical printer hardware margins do not cut off table borders or outer text edges.
  - **Removed Signature Column**: Removed the signature column completely across the table header, rows, and footer totals, allocating full width to employee and financial details.
  - **Center-Aligned Financial & Deduction Columns**: Centered values across **Max Payout**, **Attn Ded.**, **Susp. Ded.**, **Perf Ded.**, **Total Ded.**, and **Net Payout** (both in row items and footer totals).
  - **Increased Text Size**: Bumped table cell font size to `10.5px` (from `9.5px`), headers to `10px`, and meta details for significantly sharper and easier-to-read printouts.

---

## [2.9.16] - 2026-10-30

### Fixed
- **Print Layout Formatting & High-Contrast Table Borders for Royalty Payout Sheet (`resources/views/royalty/print.blade.php`)**:
  - **Fixed Right-Side Clipping**: Streamlined landscape print margins (`margin: 6mm 6mm`), tightened cell padding (`padding: 3px 3px`), adjusted base font to 9.5px, and precisely reallocated table column percentages (totaling 100%) so all 16 columns (including Net Payout and Signature) fit within the printable area without overflowing or getting cut off.
  - **High-Contrast Print Borders**: Added `-webkit-print-color-adjust: exact !important` and `print-color-adjust: exact !important` alongside high-contrast solid black table borders (`border: 1px solid #000 !important; border-collapse: collapse !important;`) so borders are rendered crisp and clear on all printers and PDF exports.

---

## [2.9.15] - 2026-10-30

### Added & Enhanced
- **Employee Suspension Deduction Rule in Royalty Bonus Settings & Calculation Engine**:
  - **Royalty Settings Configuration (`ManageRoyaltyBonusSettings.php`, `manage-royalty-bonus-settings.blade.php`)**: Added `royalty_suspension_amount` (`AppSetting`, default `Rs. 1000.00`) and a dedicated **Employee Suspension Policy** card in the Deduction Rules console (plus Standard/Strict/Lenient preset values).
  - **Database Schema (`2026_10_30_140124_add_suspension_fields_to_royalty_report_items_table.php`, `RoyaltyReportItem.php`)**: Added `suspension_count` and `suspension_deduction_amount` columns to `royalty_report_items`.
  - **Automatic Suspension Month Matching (`RoyaltyCalculationService.php`)**: Queries non-cancelled `EmployeeSuspension` records whose `start_date` falls within the Royalty Report's Billing Month & Year (`1 {month} {year}` to end of month). When an employee has a suspension starting in that billing month, the configured fixed suspension deduction amount is applied once and included in `total_deduction_amount`.
  - **Report Table, Print Sheet & Excel Export (`ViewRoyaltyReport.php`, `print.blade.php`, `RoyaltyCalculationService::exportExcel()`)**: Added `Susp. Ded.` column and totals across the Royalty Report table, printable payout sheet, and Excel export.

---

## [2.9.14] - 2026-10-30

### Enhanced
- **Selected Employee Profile Summary Card in Employee Suspension Form (`app/Filament/Resources/EmployeeSuspensions/Schemas/EmployeeSuspensionForm.php`)**:
  - Added a reactive **Selected Employee Profile** card above the suspension details form that displays a brief overview of the selected employee (**Employee Code**, **Full Name**, **Department**, **Designation**, **Employee Status**, **Tips Status**, **Join Date**, and **Contact Number**) in both create and edit views/modals.
  - Enabled `->live()` on the `employee_id` selector and added department context (`CODE | Name [Department]`) to the dropdown option labels.

---

## [2.9.13] - 2026-10-30

### Fixed & Enhanced
- **Independent Per-Employee Royalty Payout Status (`royalty_status`)**:
  - **Database Migration (`2026_10_30_124624_add_royalty_status_to_employees_table.php`)**: Added `royalty_status` (`VARCHAR(20)`, default `'Release'`) to the `employees` table so each employee has a dedicated Royalty payout status completely independent of `tips_status` and `employee_status`.
  - **Employee Profile, Table & Export (`Employee.php`, `EmployeeForm.php`, `EmployeesTable.php`, `EmployeeExportService.php`)**: Added `Royalty Status` (`Release` / `Hold`) selector in the Employee form, a `Royalty: Release/Hold` badge on Employee cards, `Release Royalty` / `Hold Royalty` bulk actions, and export column support.
  - **Decoupled Report Calculation & Sync (`RoyaltyCalculationService.php`, `ViewRoyaltyReport.php`)**: Removed `tips_status` and `employee_status === 'Suspended'` checks from `RoyaltyCalculationService::generate()`. Report items now use each employee's independent `royalty_status` (and preserve existing item statuses when recalculating a report), while toggling `Release` / `Hold` in `ViewRoyaltyReport::togglePayoutStatus()` also updates the employee's `royalty_status`.

---

## [2.9.12] - 2026-10-30

### Enhanced
- **Collapsed-by-Default Action Log Entries with Summary Heading Rows (`app/Filament/Components/ActionLogSection.php`)**:
  - **Collapsed by Default**: Added `->collapsed()` to the `ActionLogSection` repeater so individual action log entries start collapsed by default while keeping the history compact.
  - **Detailed Header Brief (`formatItemLabel()`)**: Configured `->itemLabel()` with `->truncateItemLabel(false)` to display a full-width summary row in each repeater item header showing:
    - Color-coded **Status / Action Badge** (`Updated`, `Created`, `Approved`, `Rejected`, etc.)
    - **Action Log Brief** (`action_description`, e.g., `Updated: Reference`)
    - **Field Diff Count Pill** (e.g., `1 field diff`)
    - **Done By** user attribution (`by Sumina Manandhar`)
    - Right-aligned **Timestamp** (`Oct 28, 2026 06:25:49 PM`)
  - **Unified Component Usage**: Updated `IdCardRequestForm` and `UnofficialNametagRequestForm` to use `ActionLogSection::make('actionLogs')`.

---

## [2.9.11] - 2026-10-30

### Added & Enhanced
- **Inline Designation Annual Budget Editor in Royalty Bonus Settings (`ManageRoyaltyBonusSettings.php`, `manage-royalty-bonus-settings.blade.php`)**:
  - Replaced the static Annual Budget column in the Designation Eligibility Matrix with an inline numeric input (`Rs.`) supporting instant save on `Enter`, `Tab`, or blur via `updateDesignationBudget(int $id, mixed $amount)`.
  - Automatically recalculates and updates the designation's **Monthly Max** (`ceil((budget / 12) / 100) * 100`), **Monthly Min** (`Monthly Max / 2`), and the top **Monthly Max Pool** KPI in real time.

---

## [2.9.10] - 2026-10-30

### Fixed
- **Royalty Print Sheet Last-Page-Only Totals Row (`resources/views/royalty/print.blade.php`)**:
  - Moved the `TOTAL` summary row into the end of `<tbody>` (instead of `<tfoot>`, which browsers repeat as `table-footer-group` on every printed page) and added `display: table-row-group !important` in `@media print`, ensuring the totals row appears only on the final printed page.

---

## [2.9.9] - 2026-10-30

### Fixed & Enhanced
- **Net Missing Thumb Count Formula (`Missing Thumb − Absent Days`)**:
  - **Attendance Parser Update (`RoyaltyCalculationService.php`)**: Updated `parseAttendanceFile()` so `missing_thumb_count` is calculated as `max(0, (int) round($missingPunchIn - $cleanAbsentDays))`, subtracting absent days from the raw HRMS missing punch count since absent days are already included in raw missing punch totals.
  - **UI & Tooltip Clarification (`manage-royalty-bonus-settings.blade.php`, `ViewRoyaltyReport.php`)**: Updated the Missing Thumb Punches policy card description and report table tooltip to reflect `Net Missing Thumb Count = Raw Missing Punch − Absent Days`.

---

## [2.9.8] - 2026-10-30

### Fixed
- **Royalty Reports Schema Fix (`notes`, `total_payout`, `total_deductions`) & Generation Sync**:
  - **Database Migration (`add_notes_and_totals_to_royalty_reports_table.php`)**: Added `notes` (`text()->nullable()`), `total_payout` (`decimal(14, 2)`), and `total_deductions` (`decimal(14, 2)`) columns to `royalty_reports`, resolving `SQLSTATE[42S22]: Unknown column 'notes' in 'field list'` when creating or updating a Royalty Bonus report.
  - **Model & Calculation Sync (`RoyaltyReport.php`, `RoyaltyCalculationService.php`, `ViewRoyaltyReport.php`)**: Added `notes`, `total_payout`, and `total_deductions` to `RoyaltyReport::$fillable`, updated `RoyaltyCalculationService::generate()` to persist `status = 'generated'` and aggregate payout/deduction totals on the parent report, and updated `ViewRoyaltyReport::toggleExempt()` to recalculate 12-month tenure balances via `recalculateItem()`.

---

## [2.9.7] - 2026-10-30

### Fixed & Enhanced
- **Input Icon/Prefix Padding Fix & Live Eligible/Excluded Role Counts in Royalty Settings**:
  - **Theme CSS Input Padding Override Fix (`resources/css/filament/kamkaj/theme.css`)**: Scoped standalone input default padding rules with `:not([class*="pl-"])` / `:not([class*="pr-"])` so custom left and right padding classes are no longer overridden by `padding: 0.55rem 0.875rem !important`.
  - **Sidebar & Designation Search Bar Icon Clearance**: Applied explicit left/right padding (`!pl-9`, `padding-left: 2.25rem !important`) on the sidebar menu search bar (`sidebar-search.blade.php`), designation title search input (`manage-royalty-bonus-settings.blade.php`), and balance overview search input (`royalty-balance-overview.blade.php`) so search icons never overlap with placeholder text.
  - **Penalty Currency Prefix (`Rs.`) Alignment**: Added `z-10` elevation to the `Rs.` prefix labels and increased input left padding (`!pl-10` / `2.5rem`) on Absent Days, Late In/Early Out, and Missing Thumb penalty inputs so `Rs.` is never hidden behind numeric field values.
  - **Live Eligible & Excluded Role Counts**: Added computed properties (`excludedDesignationsCount`, `excludedPercentage`, `scopedAllCount`, `scopedEligibleCount`, `scopedExcludedCount`) in `ManageRoyaltyBonusSettings.php` and displayed both Eligible and Excluded counts across the Status Filter Tabs (`All (X)`, `Eligible (Y)`, `Excluded (Z)`), the Top Executive KPI strip, the Designation Eligibility Matrix header badges, and the table footer summary.

---

## [2.9.6] - 2026-10-29

### Added & Enhanced
- **Full-Screen Split Studio Layout, Policy Presets & Zero-Scroll UX for Royalty Settings**:
  - **Full-Screen Canvas Expansion**: Eliminated artificial width constraints (`max-w-5xl`) and configured `getMaxContentWidth(): ?string { return 'full'; }` so the settings workspace stretches across the full display.
  - **Side-by-Side Split Studio**: Restructured the layout into an ergonomic 2-column studio: Deduction Policy Console on the left (col-span-5) and Designation Eligibility Matrix on the right (col-span-7). Both consoles fit comfortably above the fold, completely eliminating the need to scroll down to configure job role eligibility.
  - **Segmented View Mode Switcher**: Added responsive layout toggle buttons in the studio header supporting **Split Studio** (side-by-side), **Designations Only** (expands matrix to 100% full width), and **Rules Only** (presents deduction cards in a 2x2 grid).
  - **1-Click Policy Presets**: Added instant preset triggers (`Standard`, `Strict`, `Lenient`) to apply balanced, pre-configured deduction rates, grace thresholds, and floor rules with 1 click.
  - **Executive KPI Dashboard**: Added 4 real-time stat cards to the top banner displaying Total Roles, Participating Active Roles with percentage badges, Monthly Max Pool Cap, and Guaranteed Payout Floor status.
  - **Ergonomic Refinements**: Added sticky "Save Rules" triggers at the top of the policy console, aligned currency prefixes, and streamlined Livewire toggle switches.

---

## [2.9.5] - 2026-10-29

### Added & Enhanced
- **Search Icon Alignment Fix, 12-Month Join Date Tenure Budget & Royalty Balance Overview**:
  - **Search Icon Centering & Z-Index Realignment**: Fixed search icon positioning in `sidebar-search.blade.php`, `manage-royalty-bonus-settings.blade.php`, and `royalty-balance-overview.blade.php` using `pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3` so icons are always vertically centered and visible above input backgrounds.
  - **12-Month Join Date Tenure Engine**: Integrated `calculateTenureCycle()` in `RoyaltyCalculationService` to compute personal 12-month tenure cycles (Year 1, 2, ..., Month 1-12) calculated from each employee's exact joining date (`join_date_formatted`).
  - **Tenure Budget Capping & Audit Balance Tracking**: Added migration `add_tenure_and_budget_balance_to_royalty_report_items_table` capturing `join_date`, `tenure_year`, `tenure_start`, `tenure_end`, `tenure_month`, `starting_tenure_budget`, `consumed_tenure_budget`, and `remaining_budget`. Payouts in `generate()` and `recalculateItem()` are automatically capped at the available 12-month tenure balance.
  - **Royalty Budget Balance Overview Module**: Created a dedicated overview page (`/kamkaj/royalty-bonus/balance-overview`) featuring 4 KPI stat cards, multi-scope report filtering, department selectors, health status pill toggles (`Healthy`, `Low Budget <20%`, `Exhausted`), and an interactive slide-over modal detailing month-by-month historical payouts in the current 12-month tenure.
  - **View Royalty Report & Excel Export Updates**: Added `Join Date`, `Tenure Period`, and `Remaining Budget` columns to `ViewRoyaltyReport` and OpenSpout Excel exports, with a direct "Balance Overview" header action.

---

## [2.9.4] - 2026-10-29

### Added & Enhanced
- **Sticky Sidebar Resource Menu Search with Real-Time Filtering**:
  - **Sticky Top Search Input**: Embedded at the very top of `.fi-sidebar-nav` using `PanelsRenderHook::SIDEBAR_NAV_START` with glassmorphic backdrop blur and pinned sticky positioning (`sticky -top-8 z-20`) so it remains readily accessible while scrolling through lengthy menu structures.
  - **Zero-Latency Client-Side Filtering**: High-performance Alpine.js component (`sidebarMenuSearch`) that scans and matches menu item labels and group headers on every keystroke, hiding non-matching elements instantaneously without server roundtrips.
  - **Auto-Expansion of Matching Groups**: Automatically uncollapses and reveals parent navigation groups containing matching resources, and restores their previous collapsed/open state seamlessly when the search is cleared.
  - **Empty-State Messaging**: When no resource menus match the search query, displays a clean feedback card ("No resource menu is available for '<query>'") along with a 1-click "Clear search" action.
  - **Keyboard Shortcuts & Power Navigation**: Full keyboard control including `Ctrl+K` / `⌘K` to focus the search bar from anywhere on the page, `Esc` to clear and reset the sidebar, and `Enter` to navigate directly to the first visible matching resource.
  - **Collapsed Desktop Sidebar Compatibility**: Gracefully transitions to a compact search icon button when the desktop sidebar is collapsed, clicking which smoothly expands the sidebar and focuses the search input.

---

## [2.9.3] - 2026-10-29

### Added & Enhanced
- **Designation Royalty Bonus Calculation Toggle & Settings Page Manager**:
  - **Designation Schema Toggle (`is_royalty_eligible`)**: Added `is_royalty_eligible` boolean flag to designations table with model casts and scope `scopeRoyaltyEligible()`.
  - **Inline Table Toggle & Filter**: Added interactive `ToggleColumn` and `TernaryFilter` in `DesignationsTable` allowing direct one-click inclusion/exclusion of designations from Royalty Bonus calculation.
  - **Form Configuration**: Added "Include in Royalty Bonus Calculation" toggle to `DesignationForm` under the Royalty Bonus Configuration section.
  - **Royalty Settings Designation Manager**: Added comprehensive real-time management section in `ManageRoyaltyBonusSettings` with instant Livewire toggle switches, search filtering, department selector, and bulk enable/disable actions.
  - **Calculation Engine Filtering**: Updated `RoyaltyCalculationService::generate()` to strictly include employees holding designations with `is_royalty_eligible` enabled.

---

## [2.9.2] - 2026-10-29

### Enhanced & Streamlined
- **Streamlined 3-Column Performance Deduction Upload & Deduction List Scoping**:
  - **Streamlined 3-Column Format**: Standardized performance deduction Excel template to strictly 3 columns: `Employee Code`, `Percentage`, and `Remarks`.
  - **Strict Deduction Scoping**: Performance deductions strictly apply only to employees listed in the uploaded file; all unlisted eligible employees automatically default to 0% deduction.
  - **Remarks Preservation & Display**: Performance remarks from the uploaded file are saved directly into the report items and displayed in the Filament report table with hover tooltips.
  - **Sample Template Updated**: Regenerated sample Excel file and updated download endpoint and form helper text to reflect the exact 3-column format.

---

## [2.9.1] - 2026-10-29

### Added & Enhanced
- **Royalty Held Balances Ledger & Annual Merit Release**:
  - **Royalty Held Balances Resource**: Added a dedicated management resource (`RoyaltyHeldBalanceResource`) under the Royalty Bonus Management group. Tracks each employee's total accumulated held bonuses across monthly reports, total releases disbursed, and current remaining held balance.
  - **Annual Merit Release Action**: Interactive release modal enabling management to disburse held funds in full or partially based on merit points collected during the year, recording justification notes and audit metadata.
  - **View Details Page**: Comprehensive view featuring employee profile, stat overview cards, month-by-month historical breakdown of held bonuses with deduction details, and release ledger with revert capability.
  - **HRMS Attendance Format Standardization**: Updated `RoyaltyCalculationService::parseAttendanceFile` to fully mirror the Tips HRMS attendance parser, supporting two-row category headers, LOP/absent used balance summing, and biometric punch count mapping.
  - **Performance Deduction Sample Excel**: Added pre-formatted sample Excel template at `public/samples/royalty_performance_deduction_sample.xlsx` and dynamic download endpoint (`/royalty/samples/performance-deduction`) linked directly from the report upload form.

---

## [2.9.0] - 2026-10-29

### Added
- **Royalty Bonus Management Module**:
  - **Designation Royalty Budget Configuration**: Added `royalty_annual_budget`, `royalty_monthly_max`, and `royalty_monthly_min` to the designations schema. Inputting annual budget automatically calculates monthly max (`ceil((annual_budget / 12) / 100) * 100`) and monthly minimum (`monthly_max / 2`) with real-time reactive calculations.
  - **Royalty Reports**: Implemented monthly generation of royalty bonus distribution reports for eligible staff (`Active`, `Resigning This Month`, `Suspended`), skipping resigned and terminated staff.
  - **Excel File Parsers**: Support for uploading biometric attendance reports (Absent Days, Late In / Early Out, Missing Thumbs) and performance appraisal percentage deduction files.
  - **Global Royalty Rules & Settings**: Dedicated settings page under Royalty Bonus Management to configure absent bulk vs. per-day penalties, tardiness grace thresholds, missing thumb penalties, and minimum payout floor protection.
  - **Royalty Exempt Staff**: Master exemption resource and interactive per-report toggle enabling employees to receive 100% max payout regardless of attendance metrics.
  - **Validation & Locking**: Audit-compliant validation workflow that locks reports and enables controlled unlocking.
  - **Spreadsheet Export & Landscape Printing**: OpenSpout Excel export streaming and A4 landscape printable payout sheets with signatures matching Tips styling.

---

## [2.8.18] - 2026-10-29

### Fixed
- **Chat Conversation Flex Layout & Unclosed Message DOM Tag Fix**:
  - **Fixed Unclosed float-msg DOM Container**: Added missing closing `</div>` for individual message wrappers inside the `@foreach` loop in `floating-chat-widget.blade.php`, eliminating nested flex-row distortion that caused messages to render horizontally side-by-side.
  - **Pinned Composer Layout Restored**: Restored proper vertical flex layout and full-width bottom pinning for the capsule composer input bar across all devices.

---

## [2.8.17] - 2026-10-29

### Fixed
- **Topbar Chat Drawer Instant Slideover & Alpine Event Race Fix**:
  - **Slide-over Inline Style Override Removed**: Removed hardcoded `style="display: none;"` on the drawer root container in `floating-chat-widget.blade.php` so Alpine's `x-show` and `x-cloak` handle transitions smoothly without being locked hidden.
  - **Event Race Condition Eliminated**: Streamlined topbar chat launcher event dispatching to eliminate race condition where dual-dispatching Livewire and native DOM events caused back-to-back open and `toggleWidget` calls that immediately re-closed the modal.
  - **Modal State Synchronization**: Updated `toggleWidget`, `openWidget`, and `closeWidget` in `FloatingChatWidget` to reliably sync state with Alpine and mark pending messages delivered without race condition regressions.

---

## [2.8.16] - 2026-10-29

### Fixed
- **Topbar Chat Drawer Toggle Binding, Livewire Single-Root Fix & Gesture Aria-Hidden Removal**:
  - **Topbar Chat Launcher Binding**: Added Livewire `#[On('toggle-floating-chat')]`, `#[On('open-floating-chat')]`, and `#[On('close-floating-chat')]` attributes to `FloatingChatWidget`, and enhanced `chat-topbar-button.blade.php` to dual-dispatch events to both native DOM and the Livewire event bus.
  - **Livewire 3 Single Root Element Enforced**: Moved top `<script>` definitions inside the single root `<div>` element in `floating-chat-widget.blade.php`, fixing multi-root DOM diffing issues that broke Livewire `$wire` binding.
  - **Blocked aria-hidden Console Warning Removed**: Removed `aria-hidden="true"` from `#mobile-sidebar-gesture-root` which was conflicting with the focusable `#mobile-edge-handle` button and causing browser console accessibility warnings.

---

## [2.8.15] - 2026-10-29

### Fixed & Enhanced
- **Instant Bottom Chat Loading, Sticky Date Headers, Interactive Waveform Voice Player & Changelog Guard**:
  - **Instant Bottom Chat Loading**: Removed `scroll-smooth` class from the floating chat feed container and tuned `scrollToBottom(false)` in Alpine so conversations load instantly pinned at the bottom without animating from top to bottom.
  - **Sticky Date Headers**: Grouped messages by date into `.date-group` containers with sticky `top-0` date pills, ensuring date indicators stick to the top during scrolling until pushed by another date range.
  - **Interactive Waveform Voice Note Player**: Restored the voice player UI with direct SVG play/pause controls, deterministic seed-based waveform bar visualization, `AudioContext` PCM decoding, interactive click-to-seek, and 1x/1.5x/2x playback speed cycling.
  - **Changelog TypeError Fix**: Resolved `TypeError: htmlspecialchars(): Argument #1 ($string) must be of type string, array given` in `resources/views/filament/pages/changelog.blade.php` by properly rendering release descriptions and formatting array highlights safely.

---

## [2.8.14] - 2026-10-29

### Fixed & Enhanced
- **App-Wide Page Load Acceleration, Socket Timeout Elimination & Desktop Context Menu Fix**:
  - **Eliminated 1.6s Blocking Socket Timeout**: Fixed `WebsocketStatusIndicator` which probed port 8080 with dual 0.8s blocking connection timeouts on every single HTTP request across the application, adding a 30s cache layer and reducing the loopback probe to 0.1s.
  - **Lazy-Loaded Chat Drawer**: Wrapped the 3,400-line chat drawer in an `@if ($isOpen)` guard and added open-state checks to `getConversations()` and `getMessagesProperty()`, ensuring closed chat consumes 0 database queries and 0 DOM nodes on all app pages.
  - **Desktop Secondary-Click Context Menu Fix**: Fixed context menu coordinates being calculated in viewport space while inside a CSS transformed container (`translate-x-0`), which previously displaced the menu 1500px offscreen to the right on desktop displays. Coordinates are now accurately anchored relative to the drawer panel with boundary clamping.
  - **Clean Context Menu Directives**: Removed inline `@js`-generated double quotes from HTML event attributes to eliminate JavaScript syntax parsing errors on text messages.

---

## [2.8.13] - 2026-10-29

### Added & Enhanced
- **Chat Performance Acceleration, Message Pagination & Context Menu Actions**:
  - **Eliminated N+1 Unread Count Queries**: Replaced the loop querying every single participant conversation with a single indexed SQL join in `User::unreadMessagesCount()`, and batch pre-calculated unread counts for all conversations in one grouped query in `FloatingChatWidget`.
  - **Message Limit & Dynamic Pagination**: Limited initial message feed load to the latest 50 messages, slashing Livewire response payload size and DOM complexity by up to 90%, with an inline "Load earlier messages" trigger.
  - **Secondary-Click & 2-Second Mobile Long-Press Context Menu**: Removed the layout-shifting, 16-button hover pill from every message bubble in favor of a sleek floating context menu opened via right-click on desktop or holding for 2 seconds on mobile (with tactile haptic feedback).
  - **Delivery and Mention Caching**: In-memory memoization for mentioned user IDs and delivery statuses in `Message` model, preventing thousands of repetitive regex executions and relationship lookups.
  - **Filtered Self-Events in Echo**: Prevented sender Livewire full re-renders on self reaction or deletion socket events.

---

## [2.8.12] - 2026-10-28

### Added & Enhanced
- **Compact Chat Bubbles & Morphing Dynamic Composer**:
  - **Compact Message Bubbles**: Eliminated `whitespace-pre-wrap` template gaps and replaced `float-right` with inline flex wrapping, reducing short message bubble height from 102px down to a crisp 33px.
  - **Reduced Bubble Padding**: Tuned bubble padding to compact `px-2.5 py-1.5` for a space-efficient chat experience.
  - **Dynamic Morphing Action Button**: The action button dynamically displays a microphone icon when the textarea is empty, and automatically morphs into a glowing send button as soon as text is typed or an attachment is selected.
  - **Streamlined Bottom Composer**: Removed the redundant Enter-toggle button and duplicate mic button from the composer row, giving full focus and room to the input textarea with attachment and mention controls.

---

## [2.8.11] - 2026-10-28

### Added & Enhanced
- **Chat UI Modernization, Jitter-Free Alignment & Real-Time Performance**:
  - **Natural Message Alignments**: Restructured chat feed layout with balanced widths (`max-w-[84%] sm:max-w-[76%]`), distinct bubble tail corners (`rounded-br-xs` for outgoing, `rounded-bl-xs` for incoming), inline floating timestamps, and clean separation between sender and receiver bubbles.
  - **Zero Jitter Floating Actions Capsule**: Moved quick-reaction emojis and message action buttons (reply, forward, pin, delete) into an anchored floating capsule above the message bubble on hover, completely preventing layout shifts.
  - **Modern Date Dividers & De-congested State**: Added elegant date separator chips ('Today', 'Yesterday', formatted dates) and styled deleted messages as subtle dashed border pills.
  - **Anti-Flicker & Realtime Responsiveness**: Deduplicated Echo channel listeners, debounced status and reaction updates, and implemented instantaneous 0ms client-side textarea reset on send.
  - **Sleek Pill Composer**: Modernized the bottom input form into a unified rounded-2xl island with glowing gradient send button, smooth auto-resizing, and quick attachment/mic controls.

---

## [2.8.10] - 2026-10-28

### Added & Enhanced
- **Click-Outside Sidebar Dismissal & Enhanced Gesture Responsiveness**:
  - **Global Click-Outside Dismissal**: Added a capture-phase global click listener that immediately closes the mobile sidebar when clicking anywhere on the screen outside the sidebar (including topbar, cards, and backdrop overlay).
  - **Refined Edge Gesture Zone**: Tuned the edge swipe margin to a natural 45-60px mobile touch zone with a 10px initiation trigger and directional dominance filtering, restoring effortless swipe-to-open mechanics.
  - **Bi-directional Finger Dragging**: Maintained 1:1 finger tracking when swiping right to open or swiping left to close with velocity-assisted snap animations.
  - **Edge Pull Handle Auto-Hide**: Configured CSS to automatically hide the orange edge pull tab (`#mobile-edge-handle`) while the sidebar is open to keep navigation clean.

---

## [2.8.9] - 2026-10-28

### Fixed & Enhanced
- **Fix Mobile Screen Touch Blocking by Sidebar Backdrop Overlay**:
  - **Strict Backdrop Isolation**: Added CSS rules guaranteeing `.fi-sidebar-close-overlay` is strictly hidden (`display: none !important; pointer-events: none !important; opacity: 0 !important; visibility: hidden !important`) whenever the sidebar is not open or actively dragging.
  - **Alpine Store Synchronization**: Added real-time reactive Alpine effect watcher that synchronizes overlay visibility with `$store.sidebar.isOpen` across all page navigations, viewport resizes, and component life-cycles.
  - **Swipe Activation Precision**: Restricted open gesture triggering strictly to within 25px of the viewport edge (or the edge pull handle `#mobile-edge-handle`) and raised the horizontal threshold to 14px with dominant directional checks, preventing taps anywhere on the screen from triggering drags.
  - **Restored Complete Mobile Interactivity**: Users can now tap cards, links, tables, and buttons freely across the entire mobile screen without triggering backdrops.

---

## [2.8.8] - 2026-10-28

### Added & Enhanced
- **Human-Readable Entity Resolution for Action Log Foreign Key Values**:
  - **Entity Name Resolution**: Built `App\Services\ActionLogValueResolver` to resolve foreign key IDs (such as `designation_id`, `department_id`, `employee_id`, `user_id`, `candidate_id`, `letter_template_id`) to their actual human-readable names (e.g. "Senior Barista", "Food & Beverage", "Sunil Thakur") with per-request in-memory lookup caching.
  - **Action Log Diffs Presentation**: Updated `action-log-changes-table.blade.php` to display resolved entity names in clear font with the numeric `#ID` attached as a subtle subtext label, providing instant clarity on exactly what was changed.
  - **Clean Field Labels**: Cleaned column names by removing trailing `_id` (e.g. displaying "Designation" instead of "Designation Id") while keeping the raw database field identifier in mono subtext.
  - **Descriptive Action Summaries**: Updated `HasActionLogs` updating event to format human-readable summaries (e.g. "Updated: Designation (GRA → Driver)") rather than raw attribute keys.

---

## [2.8.7] - 2026-10-28

### Added & Enhanced
- **Mobile Sidebar Swipe Gestures & Tactile Edge Pull Tab Overhaul**:
  - **Tactile Edge Pull Tab**: Added an orange floating pull tab (`#mobile-edge-handle`) on the left screen edge on mobile viewports. Users can tap it to open the sidebar immediately or drag it to slide the sidebar open.
  - **Effortless Swipe Triggering**: Expanded the left edge activation zone from 45px to 80px (or 25% of viewport width) and reduced the swipe initiation threshold from 12px to 5px with forgiving vertical motion tolerance.
  - **Removed Restrictive Element Filters**: Swiping over tables, cards, and links now initiates smoothly without being aborted (only active form inputs like textareas and dropdowns are excluded).
  - **Real-Time Finger Tracking**: Fixed CSS `!important` stylesheet conflicts during dragging by utilizing `.style.setProperty('translate', ..., 'important')` and adding `.fi-sidebar-dragging` to disable transitions while moving, providing 1:1 synchronous finger response.
  - **Unified Pointer Events**: Migrated gesture detection to the W3C Pointer Events API (`pointerdown`, `pointermove`, `pointerup`) with touch fallback for universal support across touchscreens, trackpads, and devtools.

---

## [2.8.6] - 2026-10-28

### Fixed & Enhanced
- **Action Log Attribute Modifications (Old & New Values) Display**:
  - **Fixed Attribute Modifications Infolist View**: Resolved an issue in `ActionLogInfolist` where Filament's `TextEntry` treated associative diff arrays as item lists, passing each element (`['old' => ..., 'new' => ...]`) into the formatter and causing field names to show as "Old" / "New" with `null` values. Replaced with `ViewEntry` using a clean `action-log-changes-table` Blade component that renders proper field names, before/after comparisons, boolean badges (`true`/`false`), and formatted values.
  - **In-Form Record View Field Diffs**: Integrated the changes diff table into `ActionLogSection`'s repeater schema across all admin panel resources (Employees, Candidates, Promotions, Transfers, Leavers, Suspensions, etc.), allowing users to view exact before/after field values directly within each record's edit or view form.
  - **Normalized Value Tracking**: Enhanced `HasActionLogs` model trait to cleanly normalize `DateTimeInterface` instances (`Y-m-d H:i:s`), strict boolean equality comparisons, and JSON scalars before computing diffs.

---

## [2.8.5] - 2026-10-28

### Fixed & Stability
- **Navigation Crash Guard for Remote Filament Component Caches**:
  - Restored a lightweight `App\Filament\Pages\Chat` stub with `shouldRegisterNavigation(): bool => false` and a redirect to `/kamkaj`.
  - Resolves fatal `Class "App\Filament\Pages\Chat" not found` errors triggered on remote environments (such as staging/production) where `php artisan filament:cache-components` had cached page classes from before the page was removed.
  - Excluded `Chat::class` in `config/filament-shield.php` to prevent redundant role permission generation.

---

## [2.8.4] - 2026-10-28

### Added & Fixed
- **Sidebar Closing Slide Animation, Modal Slide & Click-Outside, and Chat Close Button**:
  - **Sidebar Close Slide Animation**: Configured dual `translate` and `transform` CSS transitions on `.fi-sidebar` using `cubic-bezier(0.16, 1, 0.3, 1)` matching Tailwind CSS v4's property output, ensuring the sidebar slides out to the left smoothly when closed rather than snapping shut abruptly.
  - **Modal Slide-In & Slide-Out Animations**: Implemented cubic-bezier slide transitions across all modal dialogs. Standard dialogs slide up into the viewport on open and down on close, while slide-over drawers smoothly translate from and to the screen edge.
  - **Modal Click-Outside Close**: Restored pointer events on `.fi-modal-window-ctn` (`pointer-events: auto !important`) and installed a delegated backdrop click-outside handler (`resources/views/filament/modal-click-outside.blade.php`), ensuring clicks anywhere outside the dialog window immediately and smoothly close the modal.
  - **Chat Drawer Close Button Fix**: Added an explicit `closeWidget(): void` method in `FloatingChatWidget.php` and bound it directly to the drawer close button, backdrop, and Escape listener, fixing the Livewire `@entangle` vs `wire:click="toggleWidget"` state inversion race condition.

---

## [2.8.3] - 2026-10-28

### Removed & Cleaned
- **Retired Dedicated Chat Page in Favor of Global Chat Drawer**:
  - **Removed Full-Page Chat Resource**: Deleted `app/Filament/Pages/Chat.php` and its associated view `resources/views/filament/pages/chat.blade.php`.
  - **Sidebar Menu Declutter**: Removed the redundant "Chat" navigation link from the main sidebar navigation under Dashboard.
  - **Route & Notification Cleanup**: Retired `/kamkaj/chat` route and updated database and web push notification handlers to link cleanly to `/kamkaj` where the global topbar chat drawer operates.
  - **Widget Link Streamlining**: Removed full-view popup transition buttons from the quick chat drawer header and conversation list footer.
  - **Permission Test Alignment**: Updated `tests/Unit/ShieldEnhancedPermissionsTest.php` to match active Filament pages.

---

## [2.8.2] - 2026-10-28

### Added & Enhanced
- **Sidebar Slide Animations & Mobile Screen Gestures**:
  - **Smooth Cubic-Bezier Slide Transitions**: Enhanced `.fi-sidebar` with hardware-accelerated transform, width, and box-shadow cubic-bezier animations (`cubic-bezier(0.16, 1, 0.3, 1)`) across desktop collapsible modes and mobile viewports.
  - **Mobile Touch Screen Gestures**: Added edge swipe detection (`resources/views/filament/mobile-sidebar-gestures.blade.php`) where swiping from the left screen edge (`<= 45px`) smoothly slides the sidebar open with real-time finger translation and a subtle tactile edge indicator.
  - **Swipe Left to Close**: Enabled intuitive right-to-left swipe gestures while the sidebar drawer is open to slide it closed, guarded by a vertical scroll lock to prevent interfering with normal page scrolling.
  - **Topbar Chat Drawer Overhaul**: Replaced the persistent floating bottom-right chat bubble with a top navigation icon button (`filament.chat-topbar-button`) with live unread badge, opening a full-height right-anchored slide-over drawer that never obstructs page controls.
  - **Chat Action Icons Redesign**: Replaced distorted and inconsistent custom SVG paths for Reply, Forward, and Forwarded indicators with clean, modern Heroicons across both the floating drawer widget and the main chat page.

---

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
