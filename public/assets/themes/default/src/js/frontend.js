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

document.addEventListener('DOMContentLoaded', function(event) {
    const lightbox = GLightbox({
        selector: '.lightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });

    registerElements()

    document.querySelectorAll('.slider-container').forEach(container => {
        var slider = container.querySelector('.slider');
        var minInput = container.querySelector('.minValue');
        var maxInput = container.querySelector('.maxValue');
        var rangeDisplay = container.querySelector('.rangeDisplay');
        var rawValues = container.querySelector('.slider-values').value.split(',');

        // clean up the values: Only numbers + optional sign
        function cleanValue(value) {
            let match = value.match(/^[+-]?\d+/); // Erlaubt nur Zahlen mit optionalem +/-
            return match ? parseInt(match[0], 10) : null;
        }

        noUiSlider.cssClasses.target += ' range-slider';

        // Save adjusted values as numbers
        var values = rawValues.map(cleanValue).filter(v => v !== null); // Entfernt ungültige Werte

        var min = Math.min(...values);
        var max = Math.max(...values);
        var step = values.length > 1 ? Math.abs(values[1] - values[0]) : 1;

        noUiSlider.create(slider, {
            start: [parseInt(minInput.value), parseInt(maxInput.value)],
            connect: true,
            range: {
                'min': min,
                'max': max
            },
            step: step,
            tooltips: true,
            format: {
                to: function(value) {
                    let rounded = Math.round(value);
                    return rounded >= 0 ? `+${rounded}` : rounded.toString(); // Optional “+” for positive values
                },
                from: function(value) {
                    return Number(value.replace('+', '')); // Remove “+” when converting back
                }
            }
        });

        slider.noUiSlider.on('update', function(values) {
            minInput.value = values[0].replace('+', ''); // Save without “+”
            maxInput.value = values[1].replace('+', ''); // Save without “+”
            rangeDisplay.textContent = values[0] + ' - ' + values[1]; // With optional “+”
        });
    });

});