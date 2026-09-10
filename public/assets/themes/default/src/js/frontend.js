"use strict";

import '../scss/core.scss';

import $ from 'jquery';
window.jQuery = $; window.$ = $;

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';
import './lib/theme_switch.js';

import GLightbox from 'glightbox';
window.glightbox = GLightbox;

import htmx from "htmx.org/dist/htmx.esm";
window.htmx = htmx;

function registerElements() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // Instant-search modal (see navigation.tpl): focus its input as soon as
    // the modal has finished opening, and reset query + results on close so
    // the next open always starts from a clean state instead of showing the
    // previous search.
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
        const searchModalInput = searchModal.querySelector('#searchModalInput');

        searchModal.addEventListener('shown.bs.modal', function() {
            searchModalInput.focus();
        });

        searchModal.addEventListener('hidden.bs.modal', function() {
            searchModalInput.value = '';
            const suggestions = searchModal.querySelector('.search-suggestions');
            if (suggestions) {
                suggestions.classList.remove('show');
                suggestions.innerHTML = '';
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function(event) {
    const lightbox = GLightbox({
        selector: '.lightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });

    registerElements()

    // when a product was added to the cart: briefly bump the shopping cart
    // icon and open the mini cart sidebar, so customers immediately see
    // that it worked and how to get to checkout
    document.body.addEventListener('cart_item_added', function() {
        const cartIcon = document.querySelector('.shopping-cart-container');
        if (cartIcon) {
            cartIcon.classList.remove('cart-bump');
            void cartIcon.offsetWidth; // restart the animation on repeated triggers
            cartIcon.classList.add('cart-bump');
        }

        const miniCart = document.getElementById('miniCartOffcanvas');
        if (miniCart) {
            bootstrap.Offcanvas.getOrCreateInstance(miniCart).show();
        }
    });

});