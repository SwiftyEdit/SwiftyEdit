"use strict";

/**
 * Shop component entry. Loaded only when the "shop" theme component is
 * enabled for a page (see php/page-values.php + templates/head.tpl).
 * Relies on jQuery/htmx already being loaded by core.js - the core
 * <script> tag must come before this one in head.tpl.
 */

import '../../scss/components/shop.scss';

import * as noUiSlider from 'nouislider';

import { initWishlistSortable, copyWishlistLink } from '../lib/wishlist.js';

const htmx = window.htmx;
if (htmx) {
    htmx.onLoad(function(content) {
        initWishlistSortable(content);
    });
}
window.copyWishlistLink = copyWishlistLink;

function adjustQuantity(step) {
    const input = document.getElementById('quantity');
    input.stepUp(step);
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}
window.adjustQuantity = adjustQuantity;

document.addEventListener('DOMContentLoaded', function() {

    const rangeSliders = document.querySelectorAll('.range-slider');

    rangeSliders.forEach(function(slider) {
        const filterSlug = slider.dataset.filterSlug;
        const min = parseFloat(slider.dataset.min);
        const max = parseFloat(slider.dataset.max);
        const currentMin = parseFloat(slider.dataset.currentMin);
        const currentMax = parseFloat(slider.dataset.currentMax);

        // Initialize noUiSlider
        noUiSlider.create(slider, {
            start: [currentMin, currentMax],
            connect: true,
            range: {
                'min': min,
                'max': max
            },
            step: (max - min) / 10, // Adjust step as needed
            format: {
                to: function (value) {
                    return Math.round(value);
                },
                from: function (value) {
                    return Number(value);
                }
            }
        });

        // Update display on change
        const display = document.getElementById('range-' + filterSlug + '-display');

        slider.noUiSlider.on('update', function (values) {
            display.textContent = values[0] + ' - ' + values[1];
        });

        // Update URL on slider release
        slider.noUiSlider.on('change', function (values) {
            const minValue = values[0];
            const maxValue = values[1];

            // Build new URL with range filter
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set(filterSlug, minValue + '-' + maxValue);
            urlParams.delete('page'); // Reset pagination

            // Redirect to new URL
            window.location.search = urlParams.toString();
        });
    });

});
