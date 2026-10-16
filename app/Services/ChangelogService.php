<?php

namespace App\Services;

class ChangelogService
{
    public const CURRENT_VERSION = '2.0.6';

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
                'version' => '2.0.6',
                'date' => '2026-10-16',
                'title' => 'Rich Structured Record Card Layout for Promotion Wish Cards',
                'type' => 'patch',
                'badge' => 'Card Refinement',
                'badge_color' => 'primary',
                'highlights' => 'Redesigned the interior layout of each employee card in the promotion wish card to eliminate hollow empty spaces. Introduced circular monogram avatars, integrated metadata lines, an executive two-column career elevation deck in the center, and a bottom metadata bar with the promotion date and advancement tag.',
                'changes' => [
                    'changed' => [
                        'Structured Card Anatomy: Replaced sparse top-bottom alignment with a balanced 3-tier card layout (Avatar + Header, Centered Elevation Deck, and Footer Status Bar) eliminating barren gaps.',
                        'Monogram Avatar Badges: Each employee card features a styled initial monogram icon matching the organization’s executive palette.',
                        'Centered Elevation Deck: Career progression is highlighted as the centerpiece of each card with distinct previous and promoted designation columns and a directional arrow badge.',
                        'Bottom Date & Status Bar: Clean footer line displaying the effective promotion date with a calendar icon and milestone badge.',
                    ],
                ],
            ],
            [
                'version' => '2.0.5',
                'date' => '2026-10-16',
                'title' => 'Modern Minimal Light-Glass Wish Card Redesign',
                'type' => 'patch',
                'badge' => 'Design Overhaul',
                'badge_color' => 'success',
                'highlights' => 'Re-architected the promotion congratulation wish card with a modern minimal light glass concept. Replaced heavy dark cards with a calm slate and oyster-ivory palette, added promotion dates to all employee cards, eliminated awkward bottom spacing with a balanced full-height grid, and streamlined the layout without detached footers.',
                'changes' => [
                    'changed' => [
                        'Minimal Light-Glass Palette: Soft, non-glaring slate and ivory background with clean translucent frosted glass cards and sharp, high-contrast typography.',
                        'Promotion Date Integration: Every employee card in the multi-grid now explicitly shows their effective promotion date alongside department and employee code.',
                        'Gap-Free Full-Height Grid: Re-engineered CSS grid rows to stretch and fill the exact 1080p canvas with balanced padding, eliminating empty bottom gaps.',
                        'Streamlined Footer: Eliminated the detached bottom footer card, seamlessly integrating management wishes into the header and adding a minimal unobtrusive watermark line.',
                    ],
                ],
            ],
            [
                'version' => '2.0.4',
                'date' => '2026-10-16',
                'title' => 'Glassmorphism Promotion Wish Card Redesign',
                'type' => 'patch',
                'badge' => 'UI Redesign',
                'badge_color' => 'primary',
                'highlights' => 'Redesigned the 16:9 1080p promotion wish card from the ground up utilizing authentic glassmorphism techniques. Features a calm obsidian-slate canvas with ambient mesh glows, translucent frosted glass cards, and full integration of the official Management congratulatory memo copy from the print letterhead.',
                'changes' => [
                    'changed' => [
                        'Glassmorphic Aesthetic: Replaced the high-contrast bright canvas with a calm, executive obsidian slate canvas with translucent frosted glass panels and refined golden/amber accents.',
                        'Official Management Copy: Integrated the verbatim Management congratulatory text and closing dedication from the promotion print letterhead across both single spotlight and multi-grid cards.',
                        'Spotlight Redesign: Formatted the single-recipient view into a sleek executive recognition hero card with prominent typography, progression tiles, and management dedication.',
                        'Clean Non-Flashy Cards: Streamlined multi-record cards with subtle borders, muted metadata, and balanced 5-column layout for up to 25 names.',
                    ],
                ],
            ],
            [
                'version' => '2.0.3',
                'date' => '2026-10-16',
                'title' => '16:9 1080p Promotion Congratulation Wish Card',
                'type' => 'patch',
                'badge' => 'New Feature',
                'badge_color' => 'success',
                'highlights' => 'Introduced a high-resolution 16:9 (1920x1080) downloadable congratulatory wish card for promotions, featuring a dedicated spotlight layout for single recipients, multi-card chunking (max 25 records per card), bright and cheerful aesthetics, and client-side 1080p rasterization.',
                'changes' => [
                    'added' => [
                        '16:9 1080p Congratulation Wish Card: Generates exact 1920x1080 pixel celebratory wish cards for selected promotion records, accessible via individual row action and bulk action.',
                        'Single-Recipient Spotlight Layout: Automatically formats single records into a personalized celebratory hero layout with prominent employee badge, role progression arrows, and warm personal wishes.',
                        'Multi-Record Pagination (Max 25): Automatically chunks large promotion batches into cards containing at most 25 names with an interactive card switcher and batch download.',
                        '1:1 Download Engine: High-resolution client-side export to JPG or PNG with auto-scaled preview modes (Fit, 50%, 100%).',
                        'Clean & Bright Aesthetic: Crisp white and warm ivory canvas with cheerful, welcoming copy and zero dark glares.',
                    ],
                ],
            ],
            [
                'version' => '2.0.2',
                'date' => '2026-10-16',
                'title' => 'Dependent Multi-Select Designation Filters',
                'type' => 'patch',
                'badge' => 'Filter Enhancement',
                'badge_color' => 'primary',
                'highlights' => 'Transformed designation filters into dynamic, department-dependent multi-select components across Employee Promotions, Transfers, and Employees tables. The designation filter dynamically shows only related designations for the chosen department and is hidden/disabled until a department is selected.',
                'changes' => [
                    'added' => [
                        'Dependent Designation Filtering: Designation options reactively query and show only designations belonging to the currently selected department.',
                        'Multi-Select Designations: Table filters now support multi-select for designations, allowing records from multiple designations within a department to be filtered simultaneously.',
                        'Conditional Visibility & Guarding: Designation filter is cleanly hidden and disabled when no department has been selected.',
                        'Automatic Cascading Reset: Changing or clearing the department automatically resets selected designations.',
                    ],
                ],
            ],
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
