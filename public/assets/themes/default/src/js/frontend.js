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

document.addEventListener('DOMContentLoaded', function(event) {
    const lightbox = GLightbox({
        selector: '.lightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });
});