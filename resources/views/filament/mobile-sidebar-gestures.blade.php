{{-- Mobile Screen Gestures for Filament Sidebar Navigation --}}
<div id="mobile-sidebar-gesture-root" class="pointer-events-none fixed inset-0 z-40 overflow-hidden lg:hidden" aria-hidden="true">
    {{-- Subtle edge pull indicator --}}
    <div id="mobile-edge-indicator"
         class="pointer-events-none fixed left-0 top-1/2 -translate-y-1/2 w-1.5 h-16 rounded-r-full bg-gradient-to-b from-orange-400 via-orange-500 to-amber-500 opacity-0 shadow-lg shadow-orange-500/50 transition-opacity duration-200 z-50">
    </div>
</div>

<script>
    (function () {
        // Only run on touch-capable devices and screens under 1024px (Filament lg breakpoint)
        if (!('ontouchstart' in window) && navigator.maxTouchPoints <= 0) {
            // Still allow browser devtools touch simulation
        }

        const EDGE_THRESHOLD = 45;      // Max distance from left screen edge to start open swipe (px)
        const SWIPE_MIN_TRIGGER = 12;   // Min deltaX before locking into horizontal drag (px)
        const VELOCITY_THRESHOLD = 0.35; // px per ms for fast flicks
        const RATIO_LOCK = 1.25;        // deltaX must be 1.25x greater than deltaY to prevent hijacking vertical scroll

        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let currentY = 0;
        let startTime = 0;
        let isDragging = false;
        let isVerticalScroll = false;
        let gestureDirection = null; // 'open' | 'close' | null
        let sidebarEl = null;
        let overlayEl = null;
        let sidebarWidth = 280;
        let edgeIndicator = null;

        function getSidebarStore() {
            return (window.Alpine && typeof window.Alpine.store === 'function')
                ? window.Alpine.store('sidebar')
                : null;
        }

        function isModalActive() {
            // Check if any modal or drawer is actively displayed
            const openModal = document.querySelector('.fi-modal:not([style*="display: none"]) .fi-modal-window');
            if (openModal && openModal.offsetParent !== null) return true;

            const chatDrawer = document.querySelector('[x-ref="chatDrawer"]');
            if (chatDrawer && chatDrawer.offsetParent !== null) return true;

            return false;
        }

        function isInteractiveElement(target) {
            if (!target) return false;
            return !!target.closest(
                'input, textarea, select, button, a, [contenteditable="true"], [data-no-gesture], pre, code, table, .table-editor-cell'
            );
        }

        function initElements() {
            sidebarEl = document.querySelector('.fi-sidebar');
            overlayEl = document.querySelector('.fi-sidebar-close-overlay');
            edgeIndicator = document.getElementById('mobile-edge-indicator');
            if (sidebarEl) {
                sidebarWidth = sidebarEl.offsetWidth || 280;
            }
        }

        function cleanupStyles() {
            if (sidebarEl) {
                sidebarEl.style.translate = '';
                sidebarEl.style.transform = '';
                sidebarEl.style.transition = '';
                sidebarEl.style.pointerEvents = '';
            }
            if (overlayEl) {
                overlayEl.style.opacity = '';
                overlayEl.style.transition = '';
            }
            if (edgeIndicator) {
                edgeIndicator.style.opacity = '0';
                edgeIndicator.style.transform = 'translateY(-50%)';
            }
            isDragging = false;
            isVerticalScroll = false;
            gestureDirection = null;
        }

        document.addEventListener('touchstart', function (e) {
            if (window.innerWidth >= 1024) return;
            if (e.touches.length !== 1) return;
            if (isModalActive()) return;

            initElements();
            if (!sidebarEl) return;

            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
            currentX = startX;
            currentY = startY;
            startTime = Date.now();
            isDragging = false;
            isVerticalScroll = false;
            gestureDirection = null;

            const store = getSidebarStore();
            const isOpen = store ? store.isOpen : sidebarEl.classList.contains('fi-sidebar-open');

            if (!isOpen) {
                // Potential OPEN swipe from left edge
                if (startX <= EDGE_THRESHOLD) {
                    // Avoid triggering if touching an interactive control on the edge
                    if (isInteractiveElement(e.target)) return;
                    gestureDirection = 'open';
                    if (edgeIndicator) {
                        edgeIndicator.style.top = Math.max(40, Math.min(window.innerHeight - 40, startY)) + 'px';
                        edgeIndicator.style.opacity = '0.4';
                    }
                }
            } else {
                // Potential CLOSE swipe when open (swiping left anywhere on sidebar or overlay)
                gestureDirection = 'close';
            }
        }, { passive: true });

        document.addEventListener('touchmove', function (e) {
            if (window.innerWidth >= 1024) return;
            if (!gestureDirection || isVerticalScroll) return;
            if (e.touches.length !== 1) return;

            const touch = e.touches[0];
            currentX = touch.clientX;
            currentY = touch.clientY;

            const deltaX = currentX - startX;
            const deltaY = currentY - startY;
            const absX = Math.abs(deltaX);
            const absY = Math.abs(deltaY);

            // Determine intention early
            if (!isDragging) {
                if (absY > absX && absY > 8) {
                    // User is scrolling vertically - cancel gesture
                    isVerticalScroll = true;
                    cleanupStyles();
                    return;
                }

                if (absX >= SWIPE_MIN_TRIGGER && absX > absY * RATIO_LOCK) {
                    // Valid horizontal gesture detected
                    if (gestureDirection === 'open' && deltaX > 0) {
                        isDragging = true;
                    } else if (gestureDirection === 'close' && deltaX < 0) {
                        isDragging = true;
                    } else {
                        cleanupStyles();
                        return;
                    }
                }
            }

            if (isDragging) {
                // Prevent browser horizontal rubber-banding
                if (e.cancelable) {
                    e.preventDefault();
                }

                sidebarEl.style.transition = 'none';
                if (overlayEl) overlayEl.style.transition = 'none';

                if (gestureDirection === 'open') {
                    const clampedDelta = Math.max(0, Math.min(deltaX, sidebarWidth));
                    const translateX = -sidebarWidth + clampedDelta;
                    sidebarEl.style.transform = 'translateX(' + translateX + 'px)';
                    sidebarEl.style.pointerEvents = 'none';

                    if (overlayEl) {
                        const progress = clampedDelta / sidebarWidth;
                        overlayEl.style.display = 'block';
                        overlayEl.style.opacity = (progress * 0.6).toFixed(3);
                    }

                    if (edgeIndicator) {
                        const indicatorProgress = Math.min(1, clampedDelta / 80);
                        edgeIndicator.style.opacity = (0.4 + indicatorProgress * 0.6).toString();
                        edgeIndicator.style.transform = 'translateY(-50%) scale(' + (1 + indicatorProgress * 0.5) + ')';
                    }
                } else if (gestureDirection === 'close') {
                    const clampedDelta = Math.max(-sidebarWidth, Math.min(0, deltaX));
                    sidebarEl.style.transform = 'translateX(' + clampedDelta + 'px)';

                    if (overlayEl) {
                        const progress = 1 - (Math.abs(clampedDelta) / sidebarWidth);
                        overlayEl.style.opacity = Math.max(0, progress * 0.6).toFixed(3);
                    }
                }
            }
        }, { passive: false });

        document.addEventListener('touchend', function (e) {
            if (window.innerWidth >= 1024) return;
            if (!isDragging) {
                cleanupStyles();
                return;
            }

            const deltaX = currentX - startX;
            const elapsed = Math.max(1, Date.now() - startTime);
            const velocity = deltaX / elapsed; // px / ms
            const store = getSidebarStore();

            if (gestureDirection === 'open') {
                const shouldOpen = deltaX > (sidebarWidth * 0.3) || (velocity > VELOCITY_THRESHOLD && deltaX > 25);

                sidebarEl.style.transition = 'translate 300ms cubic-bezier(0.16, 1, 0.3, 1), transform 300ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 300ms ease';
                if (overlayEl) overlayEl.style.transition = 'opacity 300ms ease';

                if (shouldOpen) {
                    sidebarEl.style.translate = '0 0';
                    sidebarEl.style.transform = 'translateX(0)';
                    if (overlayEl) overlayEl.style.opacity = '0.6';

                    setTimeout(function () {
                        if (store) store.open();
                        cleanupStyles();
                    }, 300);
                } else {
                    sidebarEl.style.translate = '-100% 0';
                    sidebarEl.style.transform = 'translateX(-100%)';
                    if (overlayEl) overlayEl.style.opacity = '0';

                    setTimeout(function () {
                        if (store) store.close();
                        cleanupStyles();
                    }, 300);
                }
            } else if (gestureDirection === 'close') {
                const shouldClose = deltaX < -(sidebarWidth * 0.25) || (velocity < -VELOCITY_THRESHOLD && deltaX < -25);

                sidebarEl.style.transition = 'translate 280ms cubic-bezier(0.16, 1, 0.3, 1), transform 280ms cubic-bezier(0.16, 1, 0.3, 1)';
                if (overlayEl) overlayEl.style.transition = 'opacity 280ms ease';

                if (shouldClose) {
                    sidebarEl.style.translate = '-100% 0';
                    sidebarEl.style.transform = 'translateX(-100%)';
                    if (overlayEl) overlayEl.style.opacity = '0';

                    setTimeout(function () {
                        if (store) store.close();
                        cleanupStyles();
                    }, 280);
                } else {
                    sidebarEl.style.translate = '0 0';
                    sidebarEl.style.transform = 'translateX(0)';
                    if (overlayEl) overlayEl.style.opacity = '0.6';

                    setTimeout(function () {
                        if (store) store.open();
                        cleanupStyles();
                    }, 280);
                }
            } else {
                cleanupStyles();
            }
        }, { passive: true });

        document.addEventListener('touchcancel', function () {
            cleanupStyles();
        }, { passive: true });
    })();
</script>
