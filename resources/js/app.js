/**
 * Progressive enhancements only: every form and link works without JavaScript.
 */

function initMobileNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.getElementById('mobile-nav');

    if (!toggle || !panel) {
        return;
    }

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        toggle.querySelector('[data-icon-open]')?.toggleAttribute('hidden', open);
        toggle.querySelector('[data-icon-close]')?.toggleAttribute('hidden', !open);
    };

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
}

function initFileInputs() {
    document.querySelectorAll('[data-file-input]').forEach((input) => {
        const output = document.getElementById(input.dataset.fileInput);

        if (!output) {
            return;
        }

        input.addEventListener('change', () => {
            const names = Array.from(input.files || []).map((file) => {
                const size = file.size >= 1048576 ? `${(file.size / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(file.size / 1024))} KB`;
                return `${file.name} (${size})`;
            });
            output.textContent = names.length ? names.join(', ') : 'No file selected';
        });
    });
}

function initCountdowns() {
    document.querySelectorAll('[data-countdown]').forEach((button) => {
        let remaining = Number.parseInt(button.dataset.countdown, 10);
        const label = button.querySelector('[data-countdown-label]');
        const original = button.dataset.label || button.textContent.trim();

        if (!Number.isFinite(remaining) || remaining <= 0) {
            return;
        }

        button.disabled = true;

        const tick = () => {
            if (remaining <= 0) {
                button.disabled = false;
                if (label) label.textContent = original;
                return;
            }
            if (label) label.textContent = `${original} (${remaining}s)`;
            remaining -= 1;
            window.setTimeout(tick, 1000);
        };

        tick();
    });
}

function initConfirmations() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}

function initSubmitOnce() {
    document.querySelectorAll('form[data-submit-once]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[type="submit"]');
            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                const label = button.querySelector('[data-busy-label]');
                if (label) label.textContent = label.dataset.busyLabel;
            }
        });
    });
}

function initSubjectFilter() {
    const input = document.querySelector('[data-subject-filter]');

    if (!input) {
        return;
    }

    const items = Array.from(document.querySelectorAll('[data-subject-item]'));
    const groups = Array.from(document.querySelectorAll('[data-subject-group]'));
    const status = document.getElementById('subject-filter-status');

    input.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();
        let visible = 0;

        items.forEach((item) => {
            const match = term === '' || item.dataset.subjectItem.includes(term);
            item.hidden = !match;
            if (match) visible += 1;
        });

        groups.forEach((group) => {
            group.hidden = group.querySelectorAll('[data-subject-item]:not([hidden])').length === 0;
        });

        if (status) {
            status.textContent = term === '' ? '' : `${visible} matching ${visible === 1 ? 'entry' : 'entries'}`;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initFileInputs();
    initCountdowns();
    initConfirmations();
    initSubmitOnce();
    initSubjectFilter();
});
