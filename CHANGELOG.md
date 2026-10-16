# Changelog

All notable changes to the **Waldo Enterprise Operations Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
