/*
 * htmx containers with a data-keep-open attribute keep their opened
 * Bootstrap collapses open when htmx reloads their content (e.g. the
 * bookings and the tags list after deleting an entry).
 *
 * The ids of the open collapses are remembered before the swap and opened
 * again after htmx has settled - before that, htmx "settling" would set the
 * attributes of elements with the same id again and remove the class.
 * Collapses with data-keep-open-skip (e.g. a rename form) close as usual.
 */

document.addEventListener('htmx:beforeSwap', function (e) {
    const container = e.detail.target;
    if (!container || !container.hasAttribute('data-keep-open')) {
        return;
    }
    container.dataset.open = [...container.querySelectorAll('.collapse.show:not([data-keep-open-skip])')]
        .map(element => element.id)
        .filter(Boolean)
        .join(',');
});

document.addEventListener('htmx:afterSettle', function (e) {
    const container = e.detail.target;
    if (!container || !container.hasAttribute('data-keep-open')) {
        return;
    }
    (container.dataset.open || '').split(',').filter(Boolean).forEach(function (id) {
        document.getElementById(id)?.classList.add('show');
        container.querySelector('[data-bs-target="#' + id + '"]')?.setAttribute('aria-expanded', 'true');
    });
});
