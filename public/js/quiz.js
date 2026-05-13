// QuizForge – client-side helpers
// All critical validation is already inline in each view.
// This file can be used for future progressive enhancements.

document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss flash messages after 4 seconds
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity    = '0';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    }
});
