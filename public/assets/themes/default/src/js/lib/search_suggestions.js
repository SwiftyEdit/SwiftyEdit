/**
 * Instant-search suggestions (search_suggestions.tpl), used by the nav search
 * modal (navigation.tpl) and the standalone search page (search.tpl).
 *
 * - Arrow keys move between the field and the result links, Enter follows
 *   the focused link (native link behaviour), Escape goes back to the field.
 * - The dropdown on the search page closes on a click outside of it and
 *   opens again when the field gets the focus back.
 *
 * HTMX replaces the whole suggestions container on every request
 * (hx-swap="outerHTML"), so everything here uses delegated listeners on the
 * document instead of binding to the container itself.
 */

const FIELD_SELECTOR = 'input[name="s"][hx-target]';

function getContainer(field) {
    const target = field.getAttribute('hx-target');
    return target ? document.querySelector(target) : null;
}

function getField(container) {
    return container.id ? document.querySelector(`${FIELD_SELECTOR}[hx-target="#${container.id}"]`) : null;
}

function getLinks(container) {
    return [...container.querySelectorAll('a.dropdown-item')];
}

// the modal variant is always visible, the dropdown only with ".show"
function isVisible(container) {
    return container.classList.contains('search-suggestions-modal') || container.classList.contains('show');
}

function isDropdown(container) {
    return !container.classList.contains('search-suggestions-modal');
}

document.addEventListener('keydown', function(event) {

    // in the search field: arrow down jumps to the first result,
    // Escape closes the dropdown on the search page
    if (event.target.matches(FIELD_SELECTOR)) {
        const container = getContainer(event.target);
        if (!container) {
            return;
        }

        if (event.key === 'ArrowDown' && isVisible(container)) {
            const links = getLinks(container);
            if (links.length > 0) {
                event.preventDefault();
                links[0].focus();
            }
        } else if (event.key === 'Escape' && isDropdown(container)) {
            container.classList.remove('show');
        }
        return;
    }

    // on a result link: move up/down, back to the field from the first link
    const link = event.target.closest('.search-suggestions a.dropdown-item');
    if (!link) {
        return;
    }

    const container = link.closest('.search-suggestions');
    const links = getLinks(container);
    const index = links.indexOf(link);
    const field = getField(container);

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (index < links.length - 1) {
            links[index + 1].focus();
        }
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (index > 0) {
            links[index - 1].focus();
        } else if (field) {
            field.focus();
        }
    } else if (event.key === 'Escape' && field && isDropdown(container)) {
        // in the modal, Escape is left to Bootstrap and closes the modal
        event.preventDefault();
        field.focus();
        container.classList.remove('show');
    }
});

// search page: close the dropdown on a click outside of field and dropdown
document.addEventListener('click', function(event) {
    document.querySelectorAll('.search-suggestions.show:not(.search-suggestions-modal)').forEach(function(container) {
        const field = getField(container);
        if (container.contains(event.target) || (field && field.contains(event.target))) {
            return;
        }
        container.classList.remove('show');
    });
});

// search page: show the last results again when the field gets the focus back
document.addEventListener('focusin', function(event) {
    if (!event.target.matches(FIELD_SELECTOR) || event.target.value === '') {
        return;
    }
    const container = getContainer(event.target);
    if (container && isDropdown(container) && container.children.length > 0) {
        container.classList.add('show');
    }
});
