{{-- Mobile Screen Gestures for Filament Sidebar Navigation --}}
<div id="mobile-sidebar-gesture-root" class="fixed inset-0 z-40 overflow-hidden pointer-events-none lg:hidden">
    {{-- Tactile edge pull tab: gives an immediate visual affordance and easy touch target --}}
    <button id="mobile-edge-handle"
            type="button"
            aria-label="Open navigation sidebar"
            class="pointer-events-auto fixed left-0 top-1/2 -translate-y-1/2 w-4 h-16 rounded-r-xl bg-gradient-to-b from-orange-400/85 via-orange-500/95 to-amber-500/85 text-white shadow-md shadow-orange-500/30 flex items-center justify-center transition-all duration-200 active:w-5 active:scale-105 z-40 opacity-75 hover:opacity-100 touch-none">
        <svg class="w-2.5 h-2.5 text-white -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </button>
</div>

<script>
    (function () {
        // Natural mobile edge zone: up to 50px or 15% of viewport width
        function getEdgeThreshold() {
            return Math.max(45, Math.min(60, Math.floor(window.innerWidth * 0.15)));
        }

        const SWIPE_MIN_TRIGGER = 10;     // Min horizontal distance (px) before locking into drag
        const VELOCITY_THRESHOLD = 0.18;  // Flick velocity threshold (px/ms)
        const OPEN_DISTANCE_MIN = 35;     // Min px drag needed to trigger open/close

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
        let edgeHandle = null;
        let sidebarWidth = 280;

        function getSidebarStore() {
            return (window.Alpine && typeof window.Alpine.store === 'function')
                ? window.Alpine.store('sidebar')
                : null;
        }

        function isModalActive() {
            const openModal = document.querySelector('.fi-modal:not([style*="display: none"]) .fi-modal-window');
            if (openModal && openModal.offsetParent !== null) return true;

            const chatDrawer = document.querySelector('[x-ref="chatDrawer"]');
            if (chatDrawer && chatDrawer.offsetParent !== null) return true;

            return false;
        }

        function isFormInput(target) {
            if (!target) return false;
            // Only exclude form text inputs so typing or selecting text near the edge is uninterrupted
            return !!target.closest('input, textarea, select, [contenteditable="true"], .table-editor-cell, [data-no-gesture]');
        }

        function initElements() {
            sidebarEl = document.querySelector('.fi-sidebar');
            overlayEl = document.querySelector('.fi-sidebar-close-overlay');
            edgeHandle = document.getElementById('mobile-edge-handle');
            if (sidebarEl) {
                sidebarWidth = sidebarEl.offsetWidth || 280;
            }
        }

        function syncOverlayState() {
            if (!overlayEl) {
                overlayEl = document.querySelector('.fi-sidebar-close-overlay');
            }
            if (!overlayEl || window.innerWidth >= 1024) return;

            const store = getSidebarStore();
            const isOpen = store ? !!store.isOpen : (sidebarEl && sidebarEl.classList.contains('fi-sidebar-open'));

            if (!isOpen) {
                overlayEl.style.setProperty('display', 'none', 'important');
                overlayEl.style.setProperty('opacity', '0', 'important');
                overlayEl.style.setProperty('pointer-events', 'none', 'important');
                overlayEl.style.setProperty('visibility', 'hidden', 'important');
            } else {
                overlayEl.style.setProperty('display', 'block', 'important');
                overlayEl.style.setProperty('opacity', '0.6', 'important');
                overlayEl.style.setProperty('pointer-events', 'auto', 'important');
                overlayEl.style.removeProperty('visibility');
            }
        }

        function cleanupStyles() {
            if (sidebarEl) {
                sidebarEl.classList.remove('fi-sidebar-dragging');
                sidebarEl.style.removeProperty('translate');
                sidebarEl.style.removeProperty('transform');
                sidebarEl.style.removeProperty('transition');
                sidebarEl.style.removeProperty('pointer-events');
            }
            if (edgeHandle) {
                edgeHandle.style.transform = 'translateY(-50%)';
            }
            isDragging = false;
            isVerticalScroll = false;
            gestureDirection = null;

            syncOverlayState();
        }

        function getCoords(e) {
            if (e.touches && e.touches.length > 0) {
                return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            }
            if (e.clientX !== undefined) {
                return { x: e.clientX, y: e.clientY };
            }
            return { x: 0, y: 0 };
        }

        function handlePointerStart(e) {
            if (window.innerWidth >= 1024) return;
            if (e.touches && e.touches.length > 1) return;
            if (e.pointerType === 'mouse' && e.buttons !== 1) return;
            if (isModalActive()) return;

            initElements();
            if (!sidebarEl) return;

            const coords = getCoords(e);
            startX = coords.x;
            startY = coords.y;
            currentX = startX;
            currentY = startY;
            startTime = Date.now();
            isDragging = false;
            isVerticalScroll = false;
            gestureDirection = null;

            const store = getSidebarStore();
            const isOpen = store ? store.isOpen : sidebarEl.classList.contains('fi-sidebar-open');
            const isEdgeHandleHit = edgeHandle && edgeHandle.contains(e.target);

            if (!isOpen) {
                // Open gesture: starts either on edge handle or within edge threshold
                if (isEdgeHandleHit || startX <= getEdgeThreshold()) {
                    if (!isEdgeHandleHit && isFormInput(e.target)) return;
                    gestureDirection = 'open';
                }
            } else {
                // Close gesture: when open, user can swipe left from anywhere
                gestureDirection = 'close';
            }
        }

        function handlePointerMove(e) {
            if (window.innerWidth >= 1024) return;
            if (!gestureDirection || isVerticalScroll) return;

            const coords = getCoords(e);
            currentX = coords.x;
            currentY = coords.y;

            const deltaX = currentX - startX;
            const deltaY = currentY - startY;
            const absX = Math.abs(deltaX);
            const absY = Math.abs(deltaY);

            if (!isDragging) {
                // Cancel if vertical scrolling dominates
                if (absY > 12 && absY > absX * 1.4) {
                    isVerticalScroll = true;
                    cleanupStyles();
                    return;
                }

                // Lock into horizontal drag once threshold is exceeded and horizontal exceeds vertical
                if (absX >= SWIPE_MIN_TRIGGER && absX >= absY * 1.1) {
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
                if (e.cancelable) {
                    e.preventDefault();
                }

                sidebarEl.classList.add('fi-sidebar-dragging');
                sidebarEl.style.setProperty('transition', 'none', 'important');
                if (overlayEl) overlayEl.style.setProperty('transition', 'none', 'important');

                if (gestureDirection === 'open') {
                    const clampedDelta = Math.max(0, Math.min(deltaX, sidebarWidth));
                    const translateX = -sidebarWidth + clampedDelta;

                    sidebarEl.style.setProperty('translate', translateX + 'px 0', 'important');
                    sidebarEl.style.setProperty('transform', 'none', 'important');
                    sidebarEl.style.setProperty('pointer-events', 'none', 'important');

                    if (overlayEl && clampedDelta > 8) {
                        const progress = clampedDelta / sidebarWidth;
                        overlayEl.style.setProperty('display', 'block', 'important');
                        overlayEl.style.setProperty('opacity', (progress * 0.6).toFixed(3), 'important');
                        overlayEl.style.setProperty('pointer-events', 'auto', 'important');
                        overlayEl.style.removeProperty('visibility');
                    }

                    if (edgeHandle) {
                        edgeHandle.style.transform = 'translate(' + Math.min(clampedDelta, 40) + 'px, -50%)';
                    }
                } else if (gestureDirection === 'close') {
                    const clampedDelta = Math.max(-sidebarWidth, Math.min(0, deltaX));

                    sidebarEl.style.setProperty('translate', clampedDelta + 'px 0', 'important');
                    sidebarEl.style.setProperty('transform', 'none', 'important');

                    if (overlayEl) {
                        const progress = 1 - (Math.abs(clampedDelta) / sidebarWidth);
                        overlayEl.style.setProperty('opacity', Math.max(0, progress * 0.6).toFixed(3), 'important');
                    }
                }
            }
        }

        function handlePointerEnd(e) {
            if (window.innerWidth >= 1024) return;

            const deltaX = currentX - startX;
            const absX = Math.abs(deltaX);
            const absY = Math.abs(currentY - startY);
            const isEdgeHandleHit = edgeHandle && edgeHandle.contains(e.target);

            // Handle direct tap on edge handle
            if (!isDragging && isEdgeHandleHit && absX < 12 && absY < 12) {
                const store = getSidebarStore();
                if (store) {
                    store.open();
                }
                cleanupStyles();
                return;
            }

            if (!isDragging) {
                cleanupStyles();
                return;
            }

            const elapsed = Math.max(1, Date.now() - startTime);
            const velocity = deltaX / elapsed; // px / ms
            const store = getSidebarStore();

            if (gestureDirection === 'open') {
                const shouldOpen = deltaX > OPEN_DISTANCE_MIN || (velocity > VELOCITY_THRESHOLD && deltaX > 15);

                sidebarEl.classList.remove('fi-sidebar-dragging');
                sidebarEl.style.setProperty('transition', 'translate 280ms cubic-bezier(0.16, 1, 0.3, 1), transform 280ms cubic-bezier(0.16, 1, 0.3, 1)', 'important');
                if (overlayEl) overlayEl.style.setProperty('transition', 'opacity 280ms ease', 'important');

                if (shouldOpen) {
                    sidebarEl.style.setProperty('translate', '0 0', 'important');
                    sidebarEl.style.setProperty('transform', 'none', 'important');
                    if (overlayEl) {
                        overlayEl.style.setProperty('opacity', '0.6', 'important');
                        overlayEl.style.setProperty('pointer-events', 'auto', 'important');
                        overlayEl.style.removeProperty('visibility');
                    }

                    setTimeout(function () {
                        if (store) store.open();
                        cleanupStyles();
                    }, 280);
                } else {
                    sidebarEl.style.setProperty('translate', '-100% 0', 'important');
                    sidebarEl.style.setProperty('transform', 'none', 'important');
                    if (overlayEl) {
                        overlayEl.style.setProperty('opacity', '0', 'important');
                        overlayEl.style.setProperty('pointer-events', 'none', 'important');
                    }

                    setTimeout(function () {
                        if (store) store.close();
                        cleanupStyles();
                    }, 280);
                }
            } else if (gestureDirection === 'close') {
                const shouldClose = deltaX < -OPEN_DISTANCE_MIN || (velocity < -VELOCITY_THRESHOLD && deltaX < -15);

                sidebarEl.classList.remove('fi-sidebar-dragging');
                sidebarEl.style.setProperty('transition', 'translate 280ms cubic-bezier(0.16, 1, 0.3, 1), transform 280ms cubic-bezier(0.16, 1, 0.3, 1)', 'important');
                if (overlayEl) overlayEl.style.setProperty('transition', 'opacity 280ms ease', 'important');

                if (shouldClose) {
                    sidebarEl.style.setProperty('translate', '-100% 0', 'important');
                    sidebarEl.style.setProperty('transform', 'none', 'important');
                    if (overlayEl) {
                        overlayEl.style.setProperty('opacity', '0', 'important');
                        overlayEl.style.setProperty('pointer-events', 'none', 'important');
                    }

                    setTimeout(function () {
                        if (store) store.close();
                        cleanupStyles();
                    }, 280);
                } else {
                    sidebarEl.style.setProperty('translate', '0 0', 'important');
                    sidebarEl.style.setProperty('transform', 'none', 'important');
                    if (overlayEl) {
                        overlayEl.style.setProperty('opacity', '0.6', 'important');
                        overlayEl.style.setProperty('pointer-events', 'auto', 'important');
                        overlayEl.style.removeProperty('visibility');
                    }

                    setTimeout(function () {
                        if (store) store.open();
                        cleanupStyles();
                    }, 280);
                }
            } else {
                cleanupStyles();
            }
        }

        // Register pointer events for modern touch/mouse handling
        if (window.PointerEvent) {
            document.addEventListener('pointerdown', handlePointerStart, { passive: true });
            document.addEventListener('pointermove', handlePointerMove, { passive: false });
            document.addEventListener('pointerup', handlePointerEnd, { passive: true });
            document.addEventListener('pointercancel', cleanupStyles, { passive: true });
        } else {
            document.addEventListener('touchstart', handlePointerStart, { passive: true });
            document.addEventListener('touchmove', handlePointerMove, { passive: false });
            document.addEventListener('touchend', handlePointerEnd, { passive: true });
            document.addEventListener('touchcancel', cleanupStyles, { passive: true });
        }

        // Click outside handler: clicking elsewhere from the sidebar when it is open closes it
        document.addEventListener('click', function (e) {
            if (window.innerWidth >= 1024) return;
            initElements();

            const store = getSidebarStore();
            const isOpen = store ? store.isOpen : (sidebarEl && sidebarEl.classList.contains('fi-sidebar-open'));
            if (!isOpen) return;

            // If click was inside the sidebar itself, allow interaction
            if (sidebarEl && sidebarEl.contains(e.target)) {
                return;
            }

            // If click was on sidebar toggle buttons or edge handle, let their own handlers work
            if (e.target.closest('.fi-topbar-sidebar-toggle-btn, .fi-layout-sidebar-toggle-btn, .fi-sidebar-open-sidebar-btn, #mobile-edge-handle')) {
                return;
            }

            // Clicked anywhere else on the screen outside the sidebar -> close immediately
            e.preventDefault();
            e.stopPropagation();

            if (store) {
                store.close();
            }
            cleanupStyles();
        }, true); // Capture phase ensures it catches clicks before they are swallowed

        // Keep overlay state strictly in sync with Alpine store
        function setupAlpineSync() {
            initElements();
            if (window.Alpine) {
                window.Alpine.effect(function () {
                    syncOverlayState();
                });
            } else {
                document.addEventListener('alpine:init', function () {
                    window.Alpine.effect(function () {
                        syncOverlayState();
                    });
                }, { once: true });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupAlpineSync);
        } else {
            setupAlpineSync();
        }

        document.addEventListener('livewire:navigated', function () {
            initElements();
            syncOverlayState();
        });

        window.addEventListener('resize', function () {
            syncOverlayState();
        });
    })();
</script>
