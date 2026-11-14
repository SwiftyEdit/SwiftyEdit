/*
 * Copy to clipboard for every .copy-btn with a data-clipboard-text attribute,
 * e.g. the snippet names in the snippets overview (copy_text macro in
 * acp/templates/components/list/macros.twig). ClipboardJS listens on the
 * document, so buttons loaded later by htmx work as well.
 * After copying, the icon turns into a check for a moment and the popover
 * (if the button has one) shows data-copied-title above the copied text.
 *
 * backend.js is loaded in <head>: ClipboardJS listens on document.body, so it
 * is set up once the document is parsed - otherwise it throws and stops the
 * whole bundle.
 */

import ClipboardJS from "clipboard";
import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';

document.addEventListener('DOMContentLoaded', function () {
    const clipboard = new ClipboardJS('.copy-btn');
    clipboard.on('success', showCopied);
});

function showCopied(e) {
    const button = e.trigger;
    const icon = button.querySelector('.bi');
    const popover = bootstrap.Popover.getInstance(button);

    button.classList.add('text-success');
    icon?.classList.replace('bi-clipboard', 'bi-clipboard-check');
    // setContent() replaces header and body, so both are passed
    if (popover && button.dataset.copiedTitle) {
        popover.setContent({'.popover-header': button.dataset.copiedTitle, '.popover-body': button.dataset.bsContent});
    }

    setTimeout(function () {
        button.classList.remove('text-success');
        icon?.classList.replace('bi-clipboard-check', 'bi-clipboard');
        if (popover) {
            popover.setContent({'.popover-header': button.dataset.bsTitle, '.popover-body': button.dataset.bsContent});
        }
    }, 1500);

    e.clearSelection();
}
