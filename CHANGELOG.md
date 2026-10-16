# Changelog

All notable changes to the **Waldo Enterprise Operations Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
