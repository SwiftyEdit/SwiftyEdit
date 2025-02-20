"use strict";

import '../scss/default.scss';

import $ from 'jquery';
window.jQuery = $; window.$ = $;

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';
import './components/theme_switch.js';
const { vote } = require('./components/vote');
window.vote = vote;
const { sign_guestlist } = require('./components/guestlist');
window.sign_guestlist = sign_guestlist;

import htmx from "htmx.org/dist/htmx.esm";
window.htmx = htmx;