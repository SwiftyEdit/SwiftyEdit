"use strict";

import '../scss/default.scss';

import $ from 'jquery';
window.jQuery = $; window.$ = $;

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';
import './components/theme_switch.js';

const { sign_guestlist } = require('./components/guestlist');
window.sign_guestlist = sign_guestlist;

import GLightbox from 'glightbox';
window.glightbox = GLightbox;

import htmx from "htmx.org/dist/htmx.esm";
window.htmx = htmx;

import * as noUiSlider from 'nouislider';
window.noUiSlider = noUiSlider;

function registerElements() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

function adjustQuantity(step) {
    const input = document.getElementById('quantity');
    input.stepUp(step);
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}
window.adjustQuantity = adjustQuantity;

document.addEventListener('DOMContentLoaded', function(event) {
    const lightbox = GLightbox({
        selector: '.lightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });

    registerElements()

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