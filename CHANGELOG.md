# Changelog

All notable changes to the **Waldo Enterprise Operations Portal** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
