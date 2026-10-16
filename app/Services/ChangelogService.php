<?php

namespace App\Services;

class ChangelogService
{
    public const CURRENT_VERSION = '2.0.1';

    public static function getCurrentVersion(): string
    {
        $releases = self::getReleases();

        return ! empty($releases) ? $releases[0]['version'] : self::CURRENT_VERSION;
    }

    /**
     * Parse semver components [major, minor, patch] from a version string.
     *
     * @return array{major: int, minor: int, patch: int}
     */
    public static function parseVersion(?string $version = null): array
    {
        $version = $version ?? self::getCurrentVersion();
        if (preg_match('/^v?(\d+)\.(\d+)(?:\.(\d+))?/', $version, $matches)) {
            return [
                'major' => (int) $matches[1],
                'minor' => (int) $matches[2],
                'patch' => isset($matches[3]) ? (int) $matches[3] : 0,
            ];
        }

        return ['major' => 2, 'minor' => 0, 'patch' => 0];
    }

    /**
     * Get a compact version string for narrow UI containers (e.g. collapsed sidebar).
     * Returns "X.Y" when patch is 0, or "X.Y.Z" when patch is non-zero.
     */
    public static function getCompactVersion(?string $version = null): string
    {
        $parts = self::parseVersion($version);

        if ($parts['patch'] > 0) {
            return "{$parts['major']}.{$parts['minor']}.{$parts['patch']}";
        }

        return "{$parts['major']}.{$parts['minor']}";
    }

    /**
     * Calculate next semantic version.
     *
     * @param  string  $type  'major', 'minor', or 'micro'/'patch'
     */
    public static function getNextVersion(string $type = 'micro', ?string $currentVersion = null): string
    {
        $parts = self::parseVersion($currentVersion);
        $type = strtolower($type);

        return match ($type) {
            'major' => ($parts['major'] + 1).'.0.0',
            'minor' => $parts['major'].'.'.($parts['minor'] + 1).'.0',
            'micro', 'patch' => $parts['major'].'.'.$parts['minor'].'.'.($parts['patch'] + 1),
            default => $parts['major'].'.'.$parts['minor'].'.'.($parts['patch'] + 1),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getReleases(): array
    {
        return [
            [
                'version' => '2.0.1',
                'date' => '2026-10-16',
                'title' => 'Promotion Date Filter, Rank Print Sorting & SemVer Fix',
                'type' => 'patch',
                'badge' => 'Bug Fixes & Refinements',
                'badge_color' => 'success',
                'highlights' => 'Added date range filtering on promotions, sorted congratulation printouts by updated designation rank, removed document reference numbers, and resolved major/minor/micro versioning synchronization across collapsed and expanded sidebar views.',
                'changes' => [
                    'added' => [
                        'Promotion Date Range Filter: Filter employee promotions by effective promotion date range with active date indicators.',
                    ],
                    'changed' => [
                        'Promotion Congratulations Print: Records are now ordered by updated designation rank (hierarchical order) with unranked entries at the bottom.',
                        'Clean Congratulations Letterhead: Completely eliminated REF number from congratulatory print header.',
                        'Dynamic Sidebar Version Indicator: Collapsed sidebar now dynamically computes major, minor, and micro versions without static truncation.',
                    ],
                    'fixed' => [
                        'Semantic version synchronization for major, minor, and micro releases in `ChangelogService` and sidebar view.',
                    ],
                ],
            ],
            [
                'version' => '2.0.0',
                'date' => '2026-10-16',
                'title' => 'Major UI Revamp: Mobile-Native Experience & Brand Identity',
                'type' => 'major',
                'badge' => 'Major Upgrade',
                'badge_color' => 'primary',
                'highlights' => 'Complete visual redesign of the Waldo Portal: Plus Jakarta Sans enterprise typography, Vibrant Orange and Fiery Red tech brand identity, universal mobile card-based table transformation eliminating horizontal scroll, tactile micro-interactions, and iOS/Android native app optimization.',
                'changes' => [
                    'added' => [
                        'Universal Responsive Mobile Card Transform: Automatically transforms every standard table row on mobile devices into an elevated, touch-friendly card with neat label-value pairs and dedicated action bars.',
                        'Enterprise Typography: Adopted Plus Jakarta Sans as the system-wide font for crisp readability and modern corporate look.',
                        'Brand Color Palette: High-contrast Vibrant Orange (#f97316) and Fiery Red (#ef4444) tech palette with crisp white surfaces and sleek slate dark mode.',
                        'Native Mobile App Feel: Full iOS & Android safe-area-inset padding, momentum scrolling (-webkit-overflow-scrolling), tap-highlight suppression, and mobile sheet sidebar with rounded corners.',
                        'Tactile Micro-Animations: Added tactile button press scaling, card slide-in animations, smooth modal entrances, and brand gradient topbar accents.',
                    ],
                    'changed' => [
                        'Modernized sidebar active indicators with brand orange gradients and animated live indicator.',
                        'Polished form inputs with rounded-xl containers and brand focus halos.',
                        'Upgraded PWA theme colors and viewport configuration (viewport-fit=cover).',
                    ],
                ],
            ],
            [
                'version' => '1.4.0',
                'date' => '2026-10-16',
                'title' => 'In-App Changelog & Semantic Versioning System',
                'type' => 'minor',
                'badge' => 'New Feature',
                'badge_color' => 'primary',
                'highlights' => 'Introduced in-app interactive release notes viewer, centralized ChangelogService, dynamic sidebar version indicators, and standardized repository CHANGELOG.md.',
                'changes' => [
                    'added' => [
                        'In-App Changelog page (`/kamkaj/changelog`) with version timeline, badge categorizations (`Added`, `Changed`, `Fixed`), and mobile-responsive layout.',
                        'Centralized `ChangelogService` managing release history, semantic versioning, and feature tracking.',
                        'Clickable version badge in the sidebar footer and auth screens linking directly to system release notes.',
                        'Standardized `CHANGELOG.md` adhering to Keep a Changelog specifications in the project root.',
                    ],
                ],
            ],
            [
                'version' => '1.3.0',
                'date' => '2026-10-15',
                'title' => 'SSF Upload Generator & Attendance Reconciliation',
                'type' => 'minor',
                'badge' => 'Module Update',
                'badge_color' => 'primary',
                'highlights' => 'Integrated SSF upload generation, daily attendance reconciliation reports, and template upload persistence.',
                'changes' => [
                    'added' => [
                        'SSF Upload Generator page (`SsfUploadGenerator`) for generating Social Security Fund contribution spreadsheets.',
                        'Daily Attendance Reconciliation reporting pipeline with Excel data exports.',
                        'Persisted uploaded HRMS templates in `storage/app/templates` to maintain clean git deployment states.',
                    ],
                    'fixed' => [
                        'Fixed FileUpload disk configuration and stripped undeclared XML prefixes from row exports.',
                    ],
                ],
            ],
            [
                'version' => '1.2.0',
                'date' => '2026-10-14',
                'title' => 'EOM/GOM 4K Wish Card Generator & Visual Redesign',
                'type' => 'minor',
                'badge' => 'Feature Redesign',
                'badge_color' => 'warning',
                'highlights' => 'Complete overhaul of Employee & Group of the Month wish card generator with 2:1 4K resolution, champagne patterns, and live unscale capture.',
                'changes' => [
                    'added' => [
                        'High-resolution 4K wish card export with 2:1 aspect ratio matching company presentation screens.',
                        'Warm champagne pattern backgrounds and orange glassmorphism styling.',
                        'Live unscale capture ensuring pixel-identical export between preview and downloaded cards.',
                    ],
                    'changed' => [
                        'Simplified wish card typography and streamlined wording layout.',
                    ],
                ],
            ],
            [
                'version' => '1.1.0',
                'date' => '2026-10-10',
                'title' => 'Promotions & Transfers Management with HRMS Sync',
                'type' => 'minor',
                'badge' => 'Module Update',
                'badge_color' => 'primary',
                'highlights' => 'Implemented Employee Promotions and Transfers modules with bulk HRMS sync, batch Google Sheets updates, and notification bundling.',
                'changes' => [
                    'added' => [
                        'Employee Promotions & Transfers tracking with exact HRMS template replication.',
                        'Bulk HRMS sync and unsync actions for employee career milestones.',
                        'Batch Google Sheet synchronization with single summary notification dispatch upon completion.',
                    ],
                    'fixed' => [
                        'Resolved employee lookup issues in tips adjustments by querying against primary key `employee_code`.',
                    ],
                ],
            ],
            [
                'version' => '1.0.0',
                'date' => '2026-09-01',
                'title' => 'Baseline Portal Release - HR & Workforce Management',
                'type' => 'major',
                'badge' => 'Initial Release',
                'badge_color' => 'success',
                'highlights' => 'Core launch of the Waldo Enterprise Operations Portal on Laravel 13 and Filament v5.',
                'changes' => [
                    'added' => [
                        'Filament v5 admin panel (`kamkaj`) with Amber theme, responsive navigation, and Filament Shield RBAC.',
                        'Core Employee Directory with non-incrementing `employee_code` primary keys and status lifecycle tracking.',
                        'Employee Suspension management with auto-expiry scheduler (`suspensions:check-status`).',
                        'Financial Holds, Adjustments ledger, Leavers offboarding, and Terminations module.',
                        'Automated Google Sheets bi-directional synchronization (`employees:sync`).',
                        'Public Employee REST API endpoints with flexible relationship inclusions.',
                    ],
                ],
            ],
        ];
    }

    public static function getLatestRelease(): array
    {
        return self::getReleases()[0];
    }
}
