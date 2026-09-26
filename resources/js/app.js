// Prevent mouse wheel from accidentally altering number inputs on scroll
document.addEventListener('wheel', function (event) {
    if (document.activeElement && document.activeElement.tagName === 'INPUT' && document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
}, { passive: true });

// Prevent ArrowUp and ArrowDown keys from changing number input values
document.addEventListener('keydown', function (event) {
    if (event.target && event.target.tagName === 'INPUT' && event.target.type === 'number') {
        if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
            event.preventDefault();
        }
    }
});
