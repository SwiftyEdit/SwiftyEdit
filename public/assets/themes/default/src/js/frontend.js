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

});