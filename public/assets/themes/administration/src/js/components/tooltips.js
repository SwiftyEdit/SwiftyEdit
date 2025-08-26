/**
 * Tooltip System for SwiftyEdit Backend
 *
 * Uses Floating UI for smart positioning with automatic flipping and shifting.
 * Supports hover/focus interactions, delays, and arrow elements.
 * Auto-initializes based on data-help and data-help-template attributes.
 */

import { computePosition, offset, flip, shift, autoUpdate, arrow } from '@floating-ui/dom';

function attachTooltip(triggerEl, contentHtml, {
    placement = 'bottom',  // default placement
    openDelay = 0,
    closeDelay = 150,
    gap = 8,
} = {}) {

    // tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'se-tooltip';
    tooltip.innerHTML = contentHtml;
    document.body.appendChild(tooltip);

    // optional arrow
    const arrowEl = tooltip.querySelector('[data-arrow]');

    let hideTimeout = null;
    let openTimeout = null;
    let cleanupAuto = null;

    // position
    const update = () =>
        computePosition(triggerEl, tooltip, {
            placement,
            middleware: [
                offset(gap),
                flip(),
                shift(),
                ...(arrowEl ? [arrow({ element: arrowEl, padding: 6 })] : []), // nur wenn Arrow vorhanden
            ],
        }).then(({ x, y, middlewareData, placement: finalPlacement }) => {
            Object.assign(tooltip.style, { left: `${x}px`, top: `${y}px` });

            if (arrowEl && middlewareData.arrow) {
                const { x: arrowX, y: arrowY } = middlewareData.arrow;

                // position arrow
                Object.assign(arrowEl.style, {
                    left: arrowX != null ? `${arrowX}px` : '',
                    top: arrowY != null ? `${arrowY}px` : '',
                    position: 'absolute'
                });

                // arrow direction
                arrowEl.className = `tooltip-arrow arrow-${finalPlacement.split('-')[0]}`;
            }
        });

    // start AutoUpdate
    const startAutoUpdate = () => {
        if (cleanupAuto) return;
        if (typeof autoUpdate === 'function') {
            cleanupAuto = autoUpdate(triggerEl, tooltip, update);
        } else {
            const onScrollResize = () => update();
            window.addEventListener('scroll', onScrollResize);
            window.addEventListener('resize', onScrollResize);
            cleanupAuto = () => {
                window.removeEventListener('scroll', onScrollResize);
                window.removeEventListener('resize', onScrollResize);
            };
        }
    };

    const show = () => {
        clearTimeout(hideTimeout);
        clearTimeout(openTimeout);
        openTimeout = setTimeout(async () => {
            tooltip.setAttribute('data-open', 'true');
            await update();
            startAutoUpdate();
        }, openDelay);
    };

    const scheduleHide = () => {
        clearTimeout(openTimeout);
        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
            tooltip.removeAttribute('data-open');
            if (cleanupAuto) {
                cleanupAuto();
                cleanupAuto = null;
            }
        }, closeDelay);
    };

    // events for trigger
    triggerEl.addEventListener('pointerenter', show);
    triggerEl.addEventListener('pointerleave', (e) => {
        if (!tooltip.contains(e.relatedTarget)) scheduleHide();
    });

    // events for tooltip
    tooltip.addEventListener('pointerenter', () => clearTimeout(hideTimeout));
    tooltip.addEventListener('pointerleave', (e) => {
        if (!triggerEl.contains(e.relatedTarget)) scheduleHide();
    });

    // focus events
    triggerEl.addEventListener('focus', show);
    triggerEl.addEventListener('blur', scheduleHide);

    // cleanup
    return () => {
        clearTimeout(openTimeout);
        clearTimeout(hideTimeout);
        if (cleanupAuto) cleanupAuto();
        tooltip.remove();
    };
}

// init, check for data-help & data-help-template
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-help], [data-help-template]').forEach(el => {
        let contentHtml = '';

        if (el.hasAttribute('data-help')) {
            contentHtml = el.getAttribute('data-help');
        } else if (el.hasAttribute('data-help-template')) {
            const tpl = document.getElementById(el.getAttribute('data-help-template'));
            if (tpl) contentHtml = tpl.innerHTML;
        }

        if (contentHtml) {
            attachTooltip(el, contentHtml, {
                placement: el.getAttribute('data-help-placement') || 'bottom',
                closeDelay: 150,
                gap: 8,
            });
        }
    });
});