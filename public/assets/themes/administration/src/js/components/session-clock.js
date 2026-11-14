/**
 * Session clock in the top navigation (#sessionClock, acp/templates/layout/nav-top.twig)
 *
 * Shows the remaining backend idle time: the clock turns from green to red,
 * a popover shows the time left. The server counts every backend request as
 * activity (se_acp_session_expired()), so the timer restarts after every
 * successful htmx request - a click on the clock sends a keepalive request.
 *
 * The last activity is shared between tabs via localStorage, a tab the admin
 * is working in keeps the other tabs logged in. When the time is up, the
 * backend logs out per POST with the CSRF token (see app/routing.php).
 */

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';

const STORAGE_KEY = 'se_acp_last_activity';

let lastActivity = Date.now();

function readSharedActivity() {
    try {
        const stored = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
        if (stored > lastActivity) {
            lastActivity = stored;
        }
    } catch (e) {
        // no storage (private mode) - every tab counts on its own
    }
}

function touch() {
    lastActivity = Date.now();
    try {
        localStorage.setItem(STORAGE_KEY, String(lastActivity));
    } catch (e) {
        // see readSharedActivity()
    }
}

function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    const mm = String(m).padStart(h > 0 ? 2 : 1, '0');
    const ss = String(s).padStart(2, '0');
    return h > 0 ? `${h}:${mm}:${ss}` : `${mm}:${ss}`;
}

function logout() {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = '/logout';
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = 'csrf_token';
    token.value = window.seBackend?.csrfToken ?? '';
    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}

function initSessionClock() {

    const clock = document.getElementById('sessionClock');
    if (!clock) {
        return;
    }

    const lifetime = parseInt(clock.dataset.lifetime || '0', 10);
    if (lifetime <= 0) {
        return;
    }
    const text = clock.dataset.text || '%s';
    let interval = null;

    const popover = new bootstrap.Popover(clock, {
        trigger: 'hover focus',
        placement: 'bottom',
        content: () => text.replace('%s', formatTime(remaining()))
    });
    let popoverShown = false;
    clock.addEventListener('shown.bs.popover', () => { popoverShown = true; });
    clock.addEventListener('hidden.bs.popover', () => { popoverShown = false; });

    function remaining() {
        return Math.max(0, lifetime - Math.floor((Date.now() - lastActivity) / 1000));
    }

    function update() {
        readSharedActivity();
        const left = remaining();

        if (left <= 0) {
            clearInterval(interval);
            logout();
            return;
        }

        // hue 120 (green) with the full time left down to 0 (red)
        const hue = Math.round(120 * left / lifetime);
        clock.style.color = `hsl(${hue}, 75%, 42%)`;
        if (popoverShown) {
            // change only the text - popover.setContent() would rebuild the popover every second (flicker)
            const tip = document.getElementById(clock.getAttribute('aria-describedby') || '');
            const body = tip ? tip.querySelector('.popover-body') : null;
            if (body) {
                body.textContent = text.replace('%s', formatTime(left));
            }
        }
    }

    // every successful backend request (including the keepalive click) counts as activity
    document.addEventListener('htmx:afterRequest', (evt) => {
        if (evt.detail.successful) {
            touch();
            update();
        }
    });

    touch();
    update();
    interval = setInterval(update, 1000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSessionClock);
} else {
    initSessionClock();
}
