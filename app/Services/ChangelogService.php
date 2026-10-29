<?php

namespace App\Services;

class ChangelogService
{
    public const CURRENT_VERSION = '2.8.18';

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
                'version' => '2.8.18',
                'date' => '2026-10-29',
                'title' => 'Chat Conversation Flex Layout & Unclosed Message DOM Tag Fix',
                'type' => 'micro',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'description' => 'Fixed broken horizontal message layout and misaligned input composer by restoring a missing closing div tag on message bubbles inside the date-grouped message loop in the floating chat widget.',
                'highlights' => [
                    'Fixed Unclosed float-msg DOM Container: Added missing closing </div> for individual message wrappers inside the @foreach loop in floating-chat-widget.blade.php, preventing nested flex-row distortion and horizontal message grouping.',
                    'Pinned Composer Layout Restored: Restored vertical flex orientation and full-width bottom pinning for the capsule composer input bar across all devices.',
                ],
            ],
            [
                'version' => '2.8.17',
                'date' => '2026-10-29',
                'title' => 'Topbar Chat Drawer Instant Slideover & Alpine Event Race Fix',
                'type' => 'micro',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'description' => 'Fixed the topbar chat drawer not opening by removing conflicting inline style="display: none;" on the drawer container, eliminating double-dispatch event race conditions between Livewire and Alpine, and ensuring instant client-side modal transitions.',
                'highlights' => [
                    'Slide-over Inline Style Override Removed: Removed hardcoded style="display: none;" on the drawer root container so Alpine\'s x-show and x-cloak handle transitions smoothly without being locked hidden.',
                    'Event Race Condition Eliminated: Streamlined topbar chat launcher event dispatching to eliminate race condition where dual-dispatching Livewire and native DOM events caused back-to-back open and toggleWidget calls that immediately re-closed the modal.',
                    'Modal State Synchronization: Updated toggleWidget, openWidget, and closeWidget in FloatingChatWidget to reliably sync state with Alpine and mark pending messages delivered without race condition regressions.',
                ],
            ],
            [
                'version' => '2.8.16',
                'date' => '2026-10-29',
                'title' => 'Topbar Chat Drawer Toggle Binding, Livewire Single-Root Fix & Gesture Aria-Hidden Removal',
                'type' => 'micro',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'description' => 'Fixed topbar chat icon not opening the chat drawer by moving script tags inside the single root Livewire element, adding Livewire #[On(\'toggle-floating-chat\')] listeners to FloatingChatWidget, dual-dispatching events from topbar buttons, and removing blocked aria-hidden on mobile gesture container to resolve browser console warnings.',
                'highlights' => [
                    'Topbar Chat Launcher Binding: Added Livewire #[On("toggle-floating-chat")], #[On("open-floating-chat")], and #[On("close-floating-chat")] attributes to FloatingChatWidget and dual-dispatched through both native window CustomEvents and Livewire client event bus from chat-topbar-button.',
                    'Livewire 3 Single Root Element Enforced: Moved component scripts inside a single root <div> element in floating-chat-widget.blade.php, eliminating multi-root DOM diffing failure that severed $wire communication.',
                    'Blocked aria-hidden Console Warning Removed: Removed aria-hidden="true" from #mobile-sidebar-gesture-root which was conflicting with the focusable #mobile-edge-handle button and causing browser console accessibility warnings.',
                ],
            ],
            [
                'version' => '2.8.15',
                'date' => '2026-10-29',
                'title' => 'Instant Bottom Chat Loading, Sticky Date Headers, Interactive Waveform Voice Player & Changelog Guard',
                'type' => 'micro',
                'badge' => 'Chat UX & Bug Fix',
                'badge_color' => 'primary',
                'description' => 'Fixed chat feeds animating top-to-bottom on open by eliminating smooth-scroll and enforcing instant bottom pinning, implemented sticky date pills that stick to the top during message scrolling until the next date range, restored interactive waveform voice note players with direct SVG controls and speed cycling, and resolved a TypeError on the changelog page.',
                'highlights' => [
                    'Instant Bottom Chat Loading: Removed scroll-smooth class from floating chat feed and updated Alpine scrollToBottom to jump directly to the bottom without animating from top to bottom on conversation open.',
                    'Sticky Date Headers: Grouped messages into relative date-group containers with sticky top-0 date pills, so the active date remains pinned to the top of the viewport until scrolled into a different date range.',
                    'Interactive Waveform Voice Player: Restored the voice note player with direct SVG play/pause controls, deterministic seed-based waveform bar rendering, AudioContext PCM decoding, interactive click-to-seek, and 1x/1.5x/2x playback speed cycling.',
                    'Changelog Page TypeError Guard: Fixed TypeError in changelog view by supporting array highlights and rendering release descriptions, resolving 500 error on /kamkaj/changelog.',
                ],
            ],
            [
                'version' => '2.8.14',
                'date' => '2026-10-29',
                'title' => 'App-Wide Page Load Acceleration, Socket Timeout Elimination & Desktop Context Menu Fix',
                'type' => 'micro',
                'badge' => 'Perf & Bug Fix',
                'badge_color' => 'success',
                'description' => 'Eliminated a 1.6-second blocking network socket stall on every page load across the entire application, lazy-rendered the chat drawer so closed chat consumes 0 queries and 0 DOM nodes, and fixed desktop secondary-click context menu positioning and syntax errors.',
                'highlights' => [
                    'Eliminated 1.6s Blocking Socket Timeout: Fixed WebsocketStatusIndicator probing port 8080 with dual 0.8s blocking connection timeouts on every single HTTP request by caching status for 30s and using a 0.1s socket check.',
                    'Lazy-Loaded Chat Drawer: Wrapped the 3,400-line chat drawer in an @if ($isOpen) guard and added open-state checks to getConversations() and getMessagesProperty(), ensuring closed chat consumes 0 database queries and 0 DOM nodes on app pages.',
                    'Desktop Secondary-Click Context Menu Fix: Fixed context menu coordinates being calculated in viewport space while inside a CSS transformed container (which displaced the menu 1500px offscreen on desktop), anchoring it relative to the drawer panel with automatic boundary clamping.',
                    'Eliminated Attribute Escaping Errors: Replaced inline @js-generated double quotes in context menu directives with clean scalar parameters and DOM-based text extraction.',
                ],
            ],
            [
                'version' => '2.8.13',
                'date' => '2026-10-29',
                'title' => 'Chat Performance Acceleration, Message Pagination & Context Menu Actions',
                'type' => 'micro',
                'badge' => 'Chat Perf & UX',
                'badge_color' => 'primary',
                'description' => 'Massively accelerated chat responsiveness by eliminating N+1 database queries, batching unread counts, paginating message history to 50 items with dynamic loading, and replacing cluttering hover action pills with a sleek secondary-click (right-click) and mobile 2-second long-press context menu.',
                'highlights' => [
                    'Eliminated N+1 Unread Count Queries: Replaced the loop querying every single participant conversation with a single indexed SQL join, and batch pre-calculated unread counts for all conversations in one grouped query.',
                    'Message Limit & Pagination: Limited initial message load to the latest 50 messages (reducing Livewire DOM weight by up to 90%) and added a smooth "Load earlier messages" button.',
                    'Secondary-Click & Mobile Long-Press Context Menu: Replaced the layout-shifting and DOM-heavy hover action pill on every bubble with an elegant, clamped floating context menu triggered via desktop right-click or a 2-second mobile press with haptic feedback.',
                    'Delivery and Mention Cache Optimizations: Cached mentioned user IDs and delivery statuses in in-memory properties, avoiding thousands of redundant regex and relationship lookups per render cycle.',
                    'Self-Event Livewire Refresh Filtering: Filtered sender deletion and reaction events so client-side actions do not trigger unnecessary server round-trips.',
                ],
            ],
            [
                'version' => '2.8.12',
                'date' => '2026-10-28',
                'title' => 'Compact Chat Bubbles & Morphing Dynamic Composer',
                'type' => 'micro',
                'badge' => 'Chat UX',
                'badge_color' => 'primary',
                'description' => 'Reduced message bubble padding and eliminated white-space expansion for short mentions and text. Streamlined the composer bar with a dynamic action button that morphs from microphone to send button when text is entered.',
                'highlights' => [
                    'Compact Message Bubbles: Eliminated whitespace-pre-wrap template gaps and replaced float-right with inline flex wrapping, reducing short message bubble height from 102px down to a crisp 33px.',
                    'Reduced Bubble Padding: Tuned bubble padding to compact px-2.5 py-1.5 for a space-efficient chat experience.',
                    'Dynamic Morphing Action Button: The action button dynamically displays a microphone icon when the textarea is empty, and automatically morphs into a glowing send button as soon as text is typed or an attachment is selected.',
                    'Streamlined Bottom Composer: Removed the redundant Enter-toggle button and duplicate mic button from the composer row, giving full focus and room to the input textarea with attachment and mention controls.',
                ],
            ],
            [
                'version' => '2.8.11',
                'date' => '2026-10-28',
                'title' => 'Chat UI Modernization, Jitter-Free Alignment & Real-Time Performance',
                'type' => 'micro',
                'badge' => 'Chat UX',
                'badge_color' => 'primary',
                'description' => 'Completely redesigned the floating chat feed with natural message bubble alignments, date separator chips, floating hover action/reaction capsule, and zero-latency client-side sending without screen flicker.',
                'highlights' => [
                    'Natural Message Alignments: Restructured chat feed layout with balanced widths (max-w-[84%] sm:max-w-[76%]), distinct bubble tail corners, inline floating timestamps, and clean separation between sender and receiver bubbles.',
                    'Zero Jitter Floating Actions Capsule: Moved quick-reaction emojis and message action buttons (reply, forward, pin, delete) into an anchored floating capsule above the message bubble on hover, completely preventing layout shifts.',
                    'Modern Date Dividers & De-congested State: Added elegant date separator chips (Today, Yesterday, formatted dates) and styled deleted messages as subtle dashed border pills.',
                    'Anti-Flicker & Realtime Responsiveness: Deduplicated Echo channel listeners, debounced status and reaction updates, and implemented instantaneous 0ms client-side textarea reset on send.',
                    'Sleek Pill Composer: Modernized the bottom input form into a unified rounded-2xl island with glowing gradient send button, smooth auto-resizing, and quick attachment/mic controls.',
                ],
            ],
            [
                'version' => '2.8.10',
                'date' => '2026-10-28',
                'title' => 'Click-Outside Sidebar Dismissal & Enhanced Gesture Responsiveness',
                'type' => 'micro',
                'badge' => 'Mobile UX',
                'badge_color' => 'primary',
                'description' => 'Enabled instant sidebar dismissal when clicking anywhere outside the open sidebar on mobile screens, and refined swipe thresholds and gesture mechanics for seamless finger tracking.',
                'highlights' => [
                    'Global Click-Outside Dismissal: Added a capture-phase click listener that immediately closes the mobile sidebar when clicking anywhere on the screen outside the sidebar (including topbar, cards, and backdrop overlay).',
                    'Refined Edge Gesture Zone: Tuned the edge swipe margin to a natural 45-60px mobile touch zone with a 10px initiation trigger and directional dominance filtering, restoring effortless swipe-to-open mechanics.',
                    'Bi-directional Finger Dragging: Maintained 1:1 finger tracking when swiping right to open or swiping left to close with velocity-assisted snap animations.',
                    'Edge Pull Handle Auto-Hide: Configured CSS to automatically hide the orange edge pull tab (#mobile-edge-handle) while the sidebar is open to keep navigation clean.',
                ],
            ],
            [
                'version' => '2.8.9',
                'date' => '2026-10-28',
                'title' => 'Fix Mobile Screen Touch Blocking by Sidebar Backdrop Overlay',
                'type' => 'micro',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'description' => 'Eliminated the stuck translucent black overlay on mobile screens by strictly gating sidebar swipe gestures to the extreme edge, preventing accidental micro-drags during regular taps, and enforcing strict CSS and Alpine synchronization for .fi-sidebar-close-overlay.',
                'highlights' => [
                    'Strict Overlay Isolation: Added CSS rules guaranteeing .fi-sidebar-close-overlay is forced to display: none !important and pointer-events: none !important whenever the sidebar is not open or actively dragging.',
                    'Alpine Store Synchronization: Added real-time reactive Alpine effect watcher that synchronizes overlay visibility with $store.sidebar.isOpen across all page navigations, viewport resizes, and component life-cycles.',
                    'Swipe Activation Precision: Restricted open gesture triggering strictly to within 25px of the viewport edge (or the edge pull handle) and raised the horizontal threshold to 14px with dominant directional checks, preventing taps anywhere on the screen from triggering drags.',
                    'Restored Complete Mobile Interactivity: Users can now tap cards, links, tables, and buttons freely across the entire mobile screen without triggering backdrops.',
                ],
            ],
            [
                'version' => '2.8.8',
                'date' => '2026-10-28',
                'title' => 'Human-Readable Entity Resolution for Action Log Foreign Key Values',
                'type' => 'micro',
                'badge' => 'Audit & UX',
                'badge_color' => 'success',
                'description' => 'Resolved raw foreign key IDs in Action Log diffs (such as designation_id, department_id, employee_id, user_id) to their human-readable names with subtle ID labels.',
                'highlights' => [
                    'Entity Resolution Service: Created ActionLogValueResolver to map foreign keys (designation_id, department_id, employee_id, user_id, etc.) to human-readable names with per-request memoized lookups.',
                    'Clear Before & After Presentation: Action log diff tables now display real names (e.g. "GRA (#44) → Driver (#33)") rather than ambiguous raw integer IDs.',
                    'Clean Field Column Labels: Database column suffixes like "_id" are cleaned into clean labels (e.g. "Designation", "Department") while preserving the technical column identifier below.',
                    'Action Description Summary Enhancement: Action logs created during model updates now summarize meaningful entity changes (e.g. "Updated: Designation (GRA → Driver)") instead of raw column strings.',
                ],
            ],
            [
                'version' => '2.8.7',
                'date' => '2026-10-28',
                'title' => 'Mobile Sidebar Swipe Gestures & Tactile Edge Pull Tab Overhaul',
                'type' => 'micro',
                'badge' => 'Mobile UX',
                'badge_color' => 'primary',
                'description' => 'Overhauled mobile screen gestures to trigger effortlessly: added a tactile edge pull tab, expanded swipe zone to 80px, unified with Pointer Events, and applied real-time live translation.',
                'highlights' => [
                    'Tactile Edge Pull Tab: Added a floating orange edge pull tab on the left screen margin that opens the sidebar with a single tap or smooth drag gesture.',
                    'Expanded Edge Zone & Reduced Friction: Broadened edge detection threshold to 80px (or 25% of viewport), reduced trigger distance to 5px, and removed restrictive element filters so swipes over tables and cards initiate immediately.',
                    'Real-Time Live Drag Tracking: Fixed CSS stylesheet overrides by utilizing setProperty("translate", ...) with !important during active drag, ensuring the sidebar glides synchronously with the finger with zero lag.',
                    'Unified Pointer Events: Migrated gesture engine to the modern Pointer Events API for seamless compatibility across touchscreens, stylus, and mobile viewports.',
                ],
            ],
            [
                'version' => '2.8.6',
                'date' => '2026-10-28',
                'title' => 'Fix Action Log Attribute Modifications Old & New Values Display',
                'type' => 'micro',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'description' => 'Resolved a display issue where Action Log field modifications showed "null" for both old and new values due to TextEntry array splitting, and added in-form field diff tables to record views.',
                'highlights' => [
                    'Fixed Field Modification Diff Display: Replaced TextEntry with ViewEntry and created a dedicated action-log-changes-table Blade component, properly displaying real field names with before and after values (including boolean true/false badges, nulls, numbers, and dates).',
                    'In-Form Record View Diff Visibility: Added the changes table to the ActionLogSection repeater across all resource forms, allowing users to view field modifications directly inside individual record edit and view pages.',
                    'Data Normalization in HasActionLogs: Improved updating hook in HasActionLogs trait to normalize DateTimeInterface instances, scalar types, and boolean comparisons.',
                ],
            ],
            [
                'version' => '2.8.5',
                'date' => '2026-10-28',
                'title' => 'Resilient Chat Page Navigation Stub for Remote Component Caches',
                'type' => 'micro',
                'badge' => 'Stability & Cache Fix',
                'badge_color' => 'danger',
                'description' => 'Restored a lightweight App\Filament\Pages\Chat stub with navigation registration disabled to prevent fatal 500 errors when deployed environments have stale cached panel components.',
                'highlights' => [
                    'Navigation Crash Guard: Provided an App\Filament\Pages\Chat stub that keeps shouldRegisterNavigation() as false and redirects to /kamkaj, eliminating Class "App\Filament\Pages\Chat" not found exceptions caused by stale filament:cache-components.',
                    'Shield Permission Exclusion: Registered Chat::class in config/filament-shield.php exclude array to prevent unnecessary permission generation.',
                ],
            ],
            [
                'version' => '2.8.4',
                'date' => '2026-10-28',
                'title' => 'Sidebar Close Slide Animation, Modal Slide & Click-Outside, and Chat Close Fix',
                'type' => 'micro',
                'badge' => 'UI & Animations',
                'badge_color' => 'success',
                'description' => 'Added smooth sliding exit animations when closing the sidebar and modals, enabled instant click-outside-to-close behavior across all modals, and resolved a race condition with the chat drawer close button.',
                'highlights' => [
                    'Smooth Sidebar Closing Slide Animation: Enhanced `.fi-sidebar` CSS with both `translate` and `transform` transitions matching Tailwind CSS v4 output, ensuring the sidebar slides out to the left gracefully rather than snapping shut.',
                    'Modal Sliding Animations: Added cubic-bezier slide transitions to all modal dialogs (sliding up smoothly into place and sliding down on close, with slide-overs entering and exiting from the right edge).',
                    'Modal Click-Outside Close: Restored container pointer events on `.fi-modal-window-ctn` and added delegated backdrop click handlers so clicking anywhere outside an active modal dialog immediately closes it.',
                    'Chat Drawer Close Button Fix: Implemented an explicit `closeWidget()` action on FloatingChatWidget to eliminate the Livewire @entangle vs wire:click toggle race condition, making the close button and overlay 100% reliable.',
                ],
            ],
            [
                'version' => '2.8.3',
                'date' => '2026-10-28',
                'title' => 'Retire Dedicated Chat Page in Favor of Global Chat Drawer',
                'type' => 'micro',
                'badge' => 'Cleanup & UX',
                'badge_color' => 'info',
                'description' => 'Removed the legacy full-page chat resource (/kamkaj/chat) from sidebar navigation and routing, unifying all messaging workflows into the responsive slide-over chat drawer.',
                'highlights' => [
                    'Removed redundant full-page Chat resource (Chat.php and chat.blade.php), eliminating dead weight and decluttering the sidebar navigation.',
                    'Retired /kamkaj/chat route and updated database and web push notification handlers to link directly to the portal.',
                    'Removed full-view transition buttons from the quick chat drawer header and conversation list footer.',
                    'Updated unit test suite to align with active Filament pages.',
                ],
            ],
            [
                'version' => '2.8.2',
                'date' => '2026-10-28',
                'title' => 'Sidebar Slide Animations, Mobile Touch Gestures & Topbar Chat Drawer',
                'type' => 'micro',
                'badge' => 'UI & Navigation',
                'badge_color' => 'primary',
                'description' => 'Added smooth cubic-bezier slide animations for sidebar navigation, mobile edge swipe gestures, transformed chat launcher into a topbar slide-over drawer, and fixed chat action icons.',
                'highlights' => [
                    'Smooth Sidebar Slide Animations: Added cubic-bezier transform and width transitions to the sidebar and backdrop overlay on both desktop collapsible states and mobile views.',
                    'Mobile Touch Screen Gestures: Implemented native-feeling swipe gestures (edge swipe right to open, swipe left to close) with real-time translation tracking and subtle edge indicator.',
                    'Topbar Chat Drawer: Relocated persistent floating chat bubble to an unobtrusive top navigation bar button with live unread badge, opening into a smooth slide-over drawer overlay.',
                    'Chat Reply & Forward Icons: Replaced distorted custom SVG paths with clean, modern Heroicons across the chat widget.',
                ],
            ],
            [
                'version' => '2.8.1',
                'date' => '2026-10-28',
                'title' => 'Employee SSID/SSF Directory Dark Theme & Dropdown UI Overhaul',
                'type' => 'micro',
                'badge' => 'UI Enhancement',
                'badge_color' => 'warning',
                'description' => 'Fixed dark mode text contrast issues in search inputs and redesigned select dropdowns with custom chevron arrows and interactive copy notifications.',
                'highlights' => [
                    'Resolved input color mismatch in dark theme where typed text blended into dark background.',
                    'Fixed padding collision where search icon overlapped placeholder and query text.',
                    'Overhauled department and status dropdowns with custom chevron indicators and styled option popups.',
                    'Enhanced export modal inputs and scrollable employee selector.',
                    'Added floating copy toast confirmation when copying employee SSIDs.',
                ],
            ],
            [
                'version' => '2.8.0',
                'date' => '2026-10-28',
                'title' => 'Universal Action Logs & Central Audit Trail Resource',
                'type' => 'minor',
                'badge' => 'Audit & Security',
                'badge_color' => 'success',
                'highlights' => 'Introduced a polymorphic action logging system recording record creation, status updates, and field diffs across all admin panel resources, complete with an in-form Action Log History section and a dedicated central Action Logs resource.',
                'changes' => [
                    'added' => [
                        'Universal HasActionLogs Trait: Automatically records create, update (with JSON field-level before/after diffs), status transitions, and deletion events across all models.',
                        'Central Action Logs Resource: Dedicated full-featured audit trail viewer under System Settings with filtering by resource type, action, date range, and modal inspection.',
                        'In-Form Action Log History Section: Reusable collapsible section embedded across all resources (Biometric Allotments, Candidates, Employees, Leavers, Promotions, Transfers, Suspensions, Salary Increments, Tips, Nametags, Settings, etc.).',
                        'Unified Action Logs Migration: Created action_logs table with composite indexing and backfilled existing history from id_card_request_logs and unofficial_nametag_request_logs.',
                    ],
                ],
            ],
            [
                'version' => '2.7.1',
                'date' => '2026-10-27',
                'title' => 'Fix Adjustments Amount Column QueryException on Finance Widget',
                'type' => 'patch',
                'badge' => 'Dashboard',
                'badge_color' => 'primary',
                'highlights' => 'Resolved SQLSTATE[42S22] unknown column amount error in FinanceOverviewWidget by adapting adjustments metric calculations to the actual database schema.',
                'changes' => [
                    'fixed' => [
                        'Schema Harmonization: Replaced sum(amount) calculation with accurate total and approved adjustment status counts matching the adjustments table schema.',
                    ],
                ],
            ],
            [
                'version' => '2.7.0',
                'date' => '2026-10-27',
                'title' => 'Category Resource Operations Suite (Finance, IT, Purchase & Store) & Shield Permissions',
                'type' => 'minor',
                'badge' => 'Dashboard',
                'badge_color' => 'primary',
                'highlights' => 'Built and deployed 3 dedicated category resource widgets covering Finance, IT & Systems, and Purchase & Store with real-time operational queues and registered Filament Shield permissions.',
                'changes' => [
                    'added' => [
                        'Finance Operations Widget: Central hub tracking pending adjustments (count & total NPR amount), active salary/tips holds, monthly leavers, terminations, and pending HRMS sync.',
                        'IT & Systems Hub Widget: Dedicated operations widget monitoring ID card requests across lifecycles (pending, designed, sent for print, done), biometric allotments/enrollments, print batches, and user mappings.',
                        'Purchase & Store Hub Widget: Real-time inventory and supply operations tracking nametag distributions this month, pending unofficial nametag requests, nametag fines amount/count, and unacknowledged fines.',
                        'Filament Shield Permissions: Registered View:FinanceOverviewWidget, View:ItOverviewWidget, and View:PurchaseStoreOverviewWidget permissions with HasWidgetShield integration for role-based category visibility.',
                    ],
                ],
            ],
            [
                'version' => '2.6.0',
                'date' => '2026-10-27',
                'title' => 'Dashboard Operations Suite & Filament Shield Widget Permissions',
                'type' => 'minor',
                'badge' => 'Dashboard',
                'badge_color' => 'primary',
                'highlights' => 'Introduced 4 new high-fidelity dashboard widgets built with Waldo\'s Zen Soft Tech dark aesthetic and generated granular Filament Shield permissions for role-based widget visibility.',
                'changes' => [
                    'added' => [
                        'Workforce Overview Widget: Real-time workforce metrics tracking active, suspended, resigning, and tips-on-hold staff with interactive gender diversity distribution bars and quick navigation.',
                        'Pending Operations Widget: Centralized operational queue tracking pending financial adjustments, active suspensions, and pending salary increment requests with pulsing status alerts.',
                        'Offboarding & Leavers Tracker Widget: Dedicated offboarding overview displaying this month\'s leavers, salary/tips holds, and quick leaver timeline cards.',
                        'Promotions & Growth Widget: Staff advancement tracker showing monthly promotions, acknowledgement rates, HRMS sync statuses, and recent promotion history.',
                        'Filament Shield Widget Permissions: Generated and registered View:WorkforceOverviewWidget, View:PendingOperationsWidget, View:LeaversTrackerWidget, and View:PromotionTransferActivityWidget permissions for granular role access.',
                    ],
                ],
            ],
            [
                'version' => '2.5.13',
                'date' => '2026-10-27',
                'title' => 'Global Elimination of Nested Widget Corner Outlines',
                'type' => 'patch',
                'badge' => 'Dashboard',
                'badge_color' => 'primary',
                'highlights' => 'Removed .fi-wi-widget from global elevated card styling in theme.css, ensuring widget wrappers remain purely transparent layout elements and completely eliminating the duplicate background layer and corner outlines across all dashboard widgets.',
                'changes' => [
                    'fixed' => [
                        'Global Widget Wrapper Transparency: Uncoupled .fi-wi-widget from .fi-section/.fi-card elevated borders in theme.css and set it to strictly transparent globally with zero borders and zero shadows.',
                        'Clean Corner Profiles: Eliminated the secondary outline previously visible behind rounded-3xl corners on Birthdays & Cards, Date Converter, and Department Overview widgets.',
                    ],
                ],
            ],
            [
                'version' => '2.5.12',
                'date' => '2026-10-27',
                'title' => 'Custom Calendar Picker & Clean Widget Box Alignment',
                'type' => 'patch',
                'badge' => 'Dashboard',
                'badge_color' => 'primary',
                'highlights' => 'Replaced native date input with a custom Sunday-first interactive calendar popover, repositioned calendar icon to the end of the input, and eliminated nested widget card backgrounds.',
                'changes' => [
                    'fixed' => [
                        'Eliminated Double Widget Box: Added .fi-wi-clean and [data-widget-card] zero-border rules in theme.css to prevent outer .fi-wi-widget from rendering an awkward empty box below custom dashboard widgets.',
                        'Clean Height Alignment: Set full-height flex column stretching so Date Converter aligns its top and bottom borders cleanly with BirthdayListWidget.',
                    ],
                    'improved' => [
                        'Custom Calendar Popover: Built an interactive, Sunday-first Alpine.js calendar dropdown featuring month/year quick selects, previous/next navigation, active date glow, and jump-to-today action.',
                        'Right-Aligned Date Trigger Icon: Repositioned the calendar icon to the trailing end of the date input inside a dedicated accent pill with hover glow.',
                        'Rich Date Typography: Displayed bold localized date text with formatted English and subtle ISO helper previews.',
                    ],
                ],
            ],
            [
                'version' => '2.5.11',
                'date' => '2026-10-27',
                'title' => 'Date Converter Widget Premium UI Redesign & Quick Actions',
                'type' => 'patch',
                'badge' => 'Dashboard',
                'badge_color' => 'primary',
                'highlights' => 'Redesigned the Date Converter dashboard widget with sleek segmented tabs, modern input styling, quick action tools (Today & Swap), and a glowing bilingual result card.',
                'changes' => [
                    'improved' => [
                        'Segmented Mode Switcher: Replaced the harsh red gradient pill with a refined segmented control featuring active dot indicators and smooth light/dark surface transitions.',
                        'Quick Action Controls: Added a "Today" instant reset button with sparkle icon and a bidirectional "Swap" button (⇄) that transfers converted dates between AD and BS modes.',
                        'Native Dark Picker Support: Fixed Chromium input rendering via [color-scheme:light] dark:[color-scheme:dark] styling and added a calendar prefix icon.',
                        'Bilingual Nepali Month Selector: Enhanced BS to AD dropdowns with Nepali month script labels (e.g. Baishakh / बैशाख) and custom chevrons.',
                        'Ambient Glowing Result Card: Overhauled conversion results with pulsing live indicator, dual-format click-to-copy badges, and bilingual weekday details.',
                    ],
                ],
            ],
            [
                'version' => '2.5.10',
                'date' => '2026-10-27',
                'title' => 'Bulk Role Assignment & Amendment for Users',
                'type' => 'patch',
                'badge' => 'User Management',
                'badge_color' => 'primary',
                'highlights' => 'Added a bulk action to the Users resource enabling administrators to assign roles to multiple selected users with flexible Append or Replace amendment modes.',
                'changes' => [
                    'added' => [
                        'Bulk Role Assignment Action: Introduced an "Assign Roles" toolbar bulk action in UsersTable with searchable multi-role selection and automatic selection deselection upon completion.',
                        'Append vs Replace Mode: Built an assignment mode toggle allowing admins to either append selected roles (preserving current user roles) or replace existing roles entirely.',
                        'Granular Permission & Security Safeguard: Registered AssignRoles custom shield permission and safeguarded super_admin role assignment against non-super-admin escalation.',
                    ],
                ],
            ],
            [
                'version' => '2.5.9',
                'date' => '2026-10-27',
                'title' => 'Restore Dedicated Role Creation Routing & Fix Action Modal Override',
                'type' => 'patch',
                'badge' => 'Filament Shield',
                'badge_color' => 'primary',
                'highlights' => 'Resolved "Array to string conversion" QueryException when creating or editing roles by uncoupling global modal(true) overrides and explicitly directing Create and Edit role actions to dedicated pages.',
                'changes' => [
                    'fixed' => [
                        'Removed Global modal(true) Override: Removed forced modal(true) from CreateAction, EditAction, and ViewAction in AppServiceProvider so resources with dedicated create/edit page lifecycles are not prematurely hijacked into modal forms.',
                        'Explicit Dedicated Page Routing on Roles: Bound CreateAction on ListRoles and EditAction on RoleResource to their respective full-page URLs (/kamkaj/shield/roles/create and /kamkaj/shield/roles/{record}/edit).',
                        'Guard Name Fallback: Added automatic fallback to default auth guard in CreateRole afterCreate and EditRole afterSave.',
                    ],
                ],
            ],
            [
                'version' => '2.5.8',
                'date' => '2026-10-27',
                'title' => 'Vite & Tailwind Build Pipeline Performance Optimization',
                'type' => 'patch',
                'badge' => 'Build & Tooling',
                'badge_color' => 'success',
                'highlights' => 'Accelerated production build pipeline from ~32s down to ~5.8s (>5x speedup) by removing single-thread Rust Rayon restriction, disabling redundant gzip calculations, and cleaning source scanning paths.',
                'changes' => [
                    'changed' => [
                        'Removed Single-Thread Rayon Limitation: Removed RAYON_NUM_THREADS=1 from package.json build script, allowing the Rust-powered Tailwind v4 engine to leverage all CPU cores in parallel and fixing execution on Windows Command Prompt/PowerShell.',
                        'Optimized Vite Build Options: Configured reportCompressedSize: false in vite.config.js to bypass slow chunk-by-chunk gzip calculations during asset bundling.',
                        'Pruned Redundant Source Scanning: Removed duplicate scanning of compiled storage views from app.css.',
                    ],
                ],
            ],
            [
                'version' => '2.5.7',
                'date' => '2026-10-27',
                'title' => 'Role Creation & Permissions Livewire Memory Optimization',
                'type' => 'patch',
                'badge' => 'Filament Shield',
                'badge_color' => 'primary',
                'highlights' => 'Resolved 500 error when creating or updating roles caused by memory exhaustion during Livewire state dehydration with large permission schemas, and sanitized role permissions filtering.',
                'changes' => [
                    'changed' => [
                        'Raised Request Memory Limit: Elevated runtime PHP memory limit to 512M across application bootstrap (public/index.php, AppServiceProvider) and role resource pages to accommodate large Livewire permission snapshots without hitting 128M memory limits.',
                        'Sanitized Permission Extraction: Filtered role form permissions on CreateRole and EditRole to ensure only valid non-empty string permission identifiers are passed to firstOrCreate and syncPermissions.',
                    ],
                ],
            ],
            [
                'version' => '2.5.6',
                'date' => '2026-10-27',
                'title' => 'Designations Default Tips Import & Pre-filled Sample Template Downloads',
                'type' => 'patch',
                'badge' => 'Imports & HR',
                'badge_color' => 'primary',
                'highlights' => 'Added a dedicated "Import Tips" action to Designations supporting CSV and Excel file uploads, along with dynamic pre-filled sample templates containing all existing designations and their current default tips.',
                'changes' => [
                    'added' => [
                        'Import Default Tips Action: Introduced an Import Tips toolbar action on Designations supporting both CSV and XLSX formats with overwrite toggles and detailed import statistics.',
                        'Pre-filled Sample Template Generator: Added sample download capabilities (in both CSV and Excel .xlsx) that automatically populate all existing designations, department associations, and existing default tips amounts.',
                    ],
                    'changed' => [
                        'Smart Header & Delimiter Detection: Enhanced designation import parsing to flexibly detect headers (ID, title, department, tips) with fallback support for headerless spreadsheets and multi-format currency sanitization.',
                    ],
                ],
            ],
            [
                'version' => '2.5.5',
                'date' => '2026-10-27',
                'title' => 'Designation Default Tips Amount & Dynamic Employee Tips Rendering',
                'type' => 'patch',
                'badge' => 'HR & Payroll',
                'badge_color' => 'success',
                'highlights' => 'Added a default tips amount field to Designations, and enabled dynamic expected tips calculation during employee creation and editing without syncing uncalculated values to Google Sheets.',
                'changes' => [
                    'added' => [
                        'Designation Default Tips Amount: Added default_tips_amount field to designations table, model, form schema, table columns, and export service.',
                        'Automatic Tips Amount Assignment: Added booted model saving hook on Employee to automatically assign default tips amount when creating or saving with a designation if tips amount is null.',
                    ],
                    'changed' => [
                        'Dynamic Employee Form Tips Rendering: Configured designation_id and tips_adj to reactively calculate and render the expected tips amount during employee creation and editing.',
                        'Local DB Tips Persistence: Dehydrated tips_amount in employee form so the expected amount is saved in the database while keeping Google Sheets sync isolated from non-calculated values.',
                    ],
                ],
            ],
            [
                'version' => '2.5.4',
                'date' => '2026-10-27',
                'title' => 'Department Overview Print Report Scope Modal & Filtering',
                'type' => 'patch',
                'badge' => 'Reports & UI',
                'badge_color' => 'warning',
                'highlights' => 'Added an interactive modal popup to the Print Report action in the Department Overview widget, enabling users to print either all departments or specific selected departments.',
                'changes' => [
                    'added' => [
                        'Print Scope Selection Modal: Introduced a sleek modal with radio options to choose between "All Departments" and "Select Departments".',
                        'Multi-Department Dropdown Selector: Integrated a searchable multi-select department dropdown with quick Select All / Clear actions and live selection counts.',
                    ],
                    'changed' => [
                        'Multi-Department Controller Filtering: Updated ReportController@departments to accept and filter reports by department_ids array or comma-separated list alongside single department_id.',
                    ],
                ],
            ],
            [
                'version' => '2.5.3',
                'date' => '2026-10-27',
                'title' => 'Global Slide-Over Modals for All Screen Sizes & Mobile Drawer Support',
                'type' => 'patch',
                'badge' => 'Mobile & Modal UX',
                'badge_color' => 'success',
                'highlights' => 'Configured all actions and modals to slide over from the screen edge globally across all device sizes, replacing center popup dialogs with full-height edge drawers on mobile.',
                'changes' => [
                    'changed' => [
                        'Global Action Slide-Over: Globally configured base Action and ViewAction (alongside CreateAction and EditAction) with ->slideOver() in AppServiceProvider so every action modal opens as a slide-over drawer.',
                        'Edge-Anchored Slide-Over CSS: Restructured slide-over modal CSS in theme.css with full 100dvh flex positioning, sticky header and footer, and fluid horizontal slide transitions.',
                        'Mobile Full-Width Drawer: On screens below 640px, all modals render as full-width (100vw) edge-to-edge sliding sheets, eliminating center popup modals in mobile view.',
                    ],
                ],
            ],
            [
                'version' => '2.5.2',
                'date' => '2026-10-27',
                'title' => 'Global Sunday-First Week Start for Datepickers',
                'type' => 'patch',
                'badge' => 'UI / Localization',
                'badge_color' => 'success',
                'highlights' => 'Configured all datepicker and datetimepicker fields across the application to start the calendar week on Sunday by default.',
                'changes' => [
                    'changed' => [
                        'Global Datepicker Configuration: Globally configured DatePicker and DateTimePicker components in AppServiceProvider to use Sunday as the first day of the week (firstDayOfWeek 7).',
                        'Removed Redundant Local Configs: Streamlined form schemas across Candidates, Employees, and Biometric Allotments to inherit the global Sunday-first calendar default.',
                        'Flatpickr Sunday Default: Explicitly set Flatpickr default firstDayOfWeek to 0 (Sunday) in the document studio layout.',
                    ],
                ],
            ],
            [
                'version' => '2.5.1',
                'date' => '2026-10-26',
                'title' => 'Mobile Record Card Action Trigger & Selection Checkbox Fix',
                'type' => 'patch',
                'badge' => 'Mobile UX',
                'badge_color' => 'info',
                'highlights' => 'Enabled card tapping on mobile stacked table views to automatically trigger primary actions (view, edit, or custom record URL) and resolved the squeezed bulk selection checkbox by removing unnecessary labels.',
                'changes' => [
                    'fixed' => [
                        'Mobile Selection Checkbox: Removed the "Select Record" pseudo-element label and desktop width constraints in mobile stacked view, preserving fixed checkbox dimensions so bulk selection checkboxes are never squeezed.',
                        'Card Tap Action Trigger: Configured dynamic recordAction defaults (prioritizing view_checklist, view, edit) and added a touch-friendly mobile click delegator to activate record actions directly when tapping table cards.',
                    ],
                ],
            ],
            [
                'version' => '2.5.0',
                'date' => '2026-10-26',
                'title' => 'Global 7XL Slide-Over Modals & Checklist Row Click Routing',
                'type' => 'minor',
                'badge' => 'Global UI/UX',
                'badge_color' => 'primary',
                'highlights' => 'Configured CreateAction and EditAction globally across all resources to slide over with a 7xl modal width instead of redirecting to separate pages, and updated row clicks on Monthly Manual Attendance and Roster list views to open the interactive checklist directly.',
                'changes' => [
                    'added' => [
                        'Global Slide-Over Modals: Configured CreateAction and EditAction globally in AppServiceProvider to modal(true), slideOver(), and modalWidth(Width::SevenExtraLarge), ensuring future actions seamlessly open wide slide-over drawers by default.',
                    ],
                    'changed' => [
                        'Checklist Row Navigation: Clicking table rows in Monthly Manual Attendance and Monthly Manual Roster lists now navigates directly to the interactive checklist view (recordUrl & recordAction) instead of opening the edit form.',
                    ],
                ],
            ],
            [
                'version' => '2.4.1',
                'date' => '2026-10-26',
                'title' => 'Center Alignment for Manual Attendance & Roster Checkboxes',
                'type' => 'patch',
                'badge' => 'UI Adjustment',
                'badge_color' => 'info',
                'highlights' => 'Center aligned the checkbox columns for both Monthly Manual Attendance and Monthly Manual Roster checklist views, enhancing readability, consistency, and table layout aesthetics.',
                'changes' => [
                    'fixed' => [
                        'CheckboxColumn Alignment: Corrected alignment declaration to alignCenter() on is_attendance_updated and is_roster_updated in ViewMonthlyManualAttendance and ViewMonthlyManualRoster.',
                        'Table Cell & Checkbox Flex Alignment: Enhanced theme.css to properly center table body cells, .fi-ta-col containers, and .fi-ta-checkbox elements across both light and dark modes.',
                    ],
                ],
            ],
            [
                'version' => '2.4.0',
                'date' => '2026-10-25',
                'title' => 'Unofficial NameTag Requests & Role Navigation Fix',
                'type' => 'minor',
                'badge' => 'Purchase & Store',
                'badge_color' => 'success',
                'highlights' => 'Introduced the Unofficial NameTag Requests module under Purchase & Store to record and track nametag printing for both existing employees and external/custom persons, featuring real-time list view status transitions, automated action logs, and resolved a role navigation method conflict in RoleResource.',
                'changes' => [
                    'added' => [
                        'Unofficial NameTag Requests Resource: Standalone module under Purchase & Store supporting both database employee search/autofill and manual non-employee entries.',
                        'Custom Name on Tag & Remarks: Explicit field to specify exact text to be printed on tags, alongside request reasons and instructions.',
                        'Real-Time List Status Updates: Interactive SelectColumn in the table to update status instantly (Pending, In Progress, Ready, Distributed, Cancelled).',
                        'Automated Action Logs: Full audit trail tracking user changes and timestamps for every status transition.',
                    ],
                    'fixed' => [
                        'RoleResource Navigation Compatibility: Removed legacy Utils navigation calls in RoleResource and properly delegated to FilamentShieldPlugin, eliminating 500 internal server errors on panel load.',
                    ],
                ],
            ],
            [
                'version' => '2.3.0',
                'date' => '2026-10-25',
                'title' => 'Filament Shield Enhanced & Granular Role Permissions Categorization',
                'type' => 'minor',
                'badge' => 'Access Control',
                'badge_color' => 'primary',
                'highlights' => 'Integrated agroezinger/filament-shield-enhanced to categorize role permissions into intuitive sub-tabs matching panel navigation groups. Introduced fine-grained permission control for pages, custom resource actions, and standalone custom application capabilities.',
                'changes' => [
                    'added' => [
                        'Shield Enhanced Architecture: Permission management clustered by panel navigation groups (HR & Admin, Roster Management, IT, Finance, Tips, Purchase & Store, Filament Shield, System Settings).',
                        'Granular Resource Permissions: Converted hardcoded role checks into fine-grained permissions for Employee onboarding/suspension/termination/manager toggles, promotion and transfer HRMS syncing, salary increment acknowledgements, nametag fine acknowledgements, tips report validation/unlocking, user activation/impersonation, and Discord notifications.',
                        'Enhanced Function Pages: Declared fine-grained permissions for SSF Upload Generator (view, download, generate), Chat (view, group chats), and System Changelog.',
                        'Custom Permissions: Added explicit permissions for external tools including Letters & Templates, Employee SSF IDs, Salary Calculator, and Department Reports.',
                    ],
                ],
            ],
            [
                'version' => '2.2.0',
                'date' => '2026-10-25',
                'title' => 'Manual Attendance Staff & Monthly Manual Attendance Management',
                'type' => 'minor',
                'badge' => 'New Module',
                'badge_color' => 'success',
                'highlights' => 'Introduced dedicated Manual Attendance Staff selector and Monthly Manual Attendance checklist resources under Roster Management. Features month-end attendance tracking, live KPI progress statistics, department filtering, batch HRMS update actions, and single/bulk staff synchronization.',
                'changes' => [
                    'added' => [
                        'Manual Attendance Staff Resource: Select and designate active employees whose monthly attendance is tracked manually instead of biometric scans, with department previews and status toggles.',
                        'Monthly Manual Attendance Resource: Period-based monthly checklist tracking attendance entry into HRMS with automated active staff initialization, interactive completion checkboxes, and live statistical metrics.',
                        'Checklist Refinement & Controls: Includes departmental filter tabs, status views (All, Pending, Updated), bulk and individual "Mark as Done" actions, and missing staff synchronization.',
                    ],
                ],
            ],
            [
                'version' => '2.1.2',
                'date' => '2026-10-23',
                'title' => 'Biometric Allotment Header Actions & Responsive Mobile Layout',
                'type' => 'patch',
                'badge' => 'Enhancement',
                'badge_color' => 'success',
                'highlights' => 'Added "Mark as Left" and "Mark as Not Required" header actions to the Biometric Allotment form section for mapped users, with confirmation modals, set_by attribution, and responsive icon-collapsing on mobile screens to prevent layout clustering.',
                'changes' => [
                    'added' => [
                        'Mark as Left Action: Allows mapped users to mark allotments as "Left Job" with optional remarks and set_by tracking.',
                        'Mark as Not Required Action: Allows mapped users to mark allotments as "Bio Not Required" with optional remarks and set_by tracking.',
                    ],
                    'changed' => [
                        'Mobile Layout Optimization: Applied compact sizing and responsive labeledFrom("md") breakpoints across all section header actions (Mark as Done, Mark as Left, Mark as Not Required, Call) with tooltips to prevent clustering on mobile displays.',
                    ],
                ],
            ],
            [
                'version' => '2.1.1',
                'date' => '2026-10-22',
                'title' => 'Fix Reverb WebSocket Startup and Watchdog Resilience',
                'type' => 'patch',
                'badge' => 'Bug Fix',
                'badge_color' => 'success',
                'highlights' => 'Restored required hostname key in reverb server configuration resolving Undefined array key "hostname" runtime crash. Expanded watchdog candidate PHP paths to include CloudLinux alt-php and system binaries, and reinforced websocket health check socket probing with stream socket fallbacks.',
                'changes' => [
                    'fixed' => [
                        'Reverb Server Config: Restored missing "hostname" configuration array key required by Laravel Reverb start command.',
                        'Watchdog Script: Added /opt/alt/php84/usr/bin/php, /opt/alt/php83/usr/bin/php, and /usr/local/bin/php candidate binaries to run_reverb.sh.',
                        'Socket Probing: Added stream_socket_client fallback and extended probe timeout in WebsocketStatusIndicator component.',
                    ],
                ],
            ],
            [
                'version' => '2.1.0',
                'date' => '2026-10-16',
                'title' => 'Excel & CSV Import for Promotions and Transfers',
                'type' => 'minor',
                'badge' => 'New Feature',
                'badge_color' => 'success',
                'highlights' => 'Introduced seamless Excel (.xlsx) and CSV record import capability for both Employee Promotions and Employee Transfers. Formatted to perfectly mirror the downloaded HRMS change-type Excel export for bidirectional sync between production and local environments. Imports operate purely at the database model level without triggering external Google Sheet sync jobs.',
                'changes' => [
                    'added' => [
                        'Promotions Excel Import: Toolbar modal allowing upload of downloaded promotions Excel/CSV files with optional updating of existing records by employee code and effective date.',
                        'Transfers Excel Import: Toolbar modal allowing upload of downloaded transfers Excel/CSV files with optional updating of existing records by employee code and effective date.',
                        'Silent Database Ingestion: Imports execute with Eloquent withoutEvents() and saveQuietly() ensuring zero Google Sheets updates or background queue job dispatches.',
                        'Intelligent Designation & Division Mapping: Built-in normalization recognizing HRMS slug abbreviations and department cross-references.',
                    ],
                ],
            ],
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
