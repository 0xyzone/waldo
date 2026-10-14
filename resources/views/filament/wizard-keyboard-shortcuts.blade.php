<script>
    (function () {
        document.addEventListener('keydown', function (event) {
            // Only trigger on Ctrl + Arrow or Cmd + Arrow (without Shift or Alt)
            if (!(event.ctrlKey || event.metaKey) || event.shiftKey || event.altKey) {
                return;
            }

            if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                return;
            }

            // Find open modal if any, otherwise active document
            var openModal = document.querySelector('.fi-modal.fi-modal-open');
            var container = openModal || document;
            var wizard = container.querySelector('.fi-sc-wizard');

            // If no wizard is visible, do nothing
            if (!wizard || (wizard.offsetWidth === 0 && wizard.offsetHeight === 0)) {
                return;
            }

            var alpineData = (window.Alpine && typeof window.Alpine.$data === 'function')
                ? window.Alpine.$data(wizard)
                : null;

            if (event.key === 'ArrowRight') {
                if (alpineData && typeof alpineData.isLastStep === 'function' && alpineData.isLastStep()) {
                    return;
                }

                var activeHeader = wizard.querySelector('.fi-sc-wizard-header-step.fi-active');
                var nextHeader = activeHeader ? activeHeader.nextElementSibling : null;
                var nextBtn = nextHeader ? nextHeader.querySelector('.fi-sc-wizard-header-step-btn') : null;

                if (alpineData && typeof alpineData.goToNextStep === 'function') {
                    event.preventDefault();
                    alpineData.goToNextStep();
                } else if (nextBtn && !nextBtn.disabled) {
                    event.preventDefault();
                    nextBtn.click();
                } else {
                    return;
                }

                focusActiveStepInput(wizard);
            } else if (event.key === 'ArrowLeft') {
                if (alpineData && typeof alpineData.isFirstStep === 'function' && alpineData.isFirstStep()) {
                    return;
                }

                var activeHeader = wizard.querySelector('.fi-sc-wizard-header-step.fi-active');
                var prevHeader = activeHeader ? activeHeader.previousElementSibling : null;
                var prevBtn = prevHeader ? prevHeader.querySelector('.fi-sc-wizard-header-step-btn') : null;

                if (alpineData && typeof alpineData.goToPreviousStep === 'function') {
                    event.preventDefault();
                    alpineData.goToPreviousStep();
                } else if (prevBtn && !prevBtn.disabled) {
                    event.preventDefault();
                    prevBtn.click();
                } else {
                    return;
                }

                focusActiveStepInput(wizard);
            }
        });

        function focusActiveStepInput(wizard) {
            setTimeout(function () {
                var activeStep = wizard.querySelector('.fi-sc-wizard-step.fi-active');
                if (activeStep) {
                    var input = activeStep.querySelector('input:not([type=hidden]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled])');
                    if (input) {
                        input.focus();
                    }
                }
            }, 60);
        }
    })();
</script>
