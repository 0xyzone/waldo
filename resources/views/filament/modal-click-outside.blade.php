{{-- Global click-outside handler for Filament Modals --}}
<script>
    (function () {
        document.addEventListener('click', function (e) {
            // Check if click target is a modal overlay or modal container outside the actual window
            const overlay = e.target.closest('.fi-modal-close-overlay');
            const isWindowCtn = e.target.classList && e.target.classList.contains('fi-modal-window-ctn');

            if (overlay || isWindowCtn) {
                const modal = (overlay ? overlay.closest('.fi-modal') : e.target.closest('.fi-modal'))
                    || document.querySelector('.fi-modal.fi-modal-open');

                if (modal) {
                    const alpineData = (window.Alpine && typeof window.Alpine.$data === 'function')
                        ? window.Alpine.$data(modal)
                        : null;

                    if (alpineData && typeof alpineData.close === 'function') {
                        alpineData.close();
                    } else if (alpineData && 'isOpen' in alpineData) {
                        alpineData.isOpen = false;
                    } else {
                        const closeBtn = modal.querySelector('.fi-modal-close-btn') || modal.querySelector('[data-action="close"]');
                        if (closeBtn) {
                            closeBtn.click();
                        }
                    }
                }
            }
        });
    })();
</script>
