"use strict";

import '../scss/backend.scss';
import $ from 'jquery';
window.jQuery = $;
window.$ = $;

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';
import './components/color-mode.js';
import ClipboardJS from "clipboard";

import ace from "ace-builds/src-noconflict/ace";
import 'ace-builds/src-noconflict/mode-html';
import 'ace-builds/src-noconflict/theme-twilight'
import 'ace-builds/src-noconflict/theme-chrome'

import Sortable from 'sortablejs';
window.Sortable = Sortable;

import './components/products';
import './components/count_chars';
import 'htmx.org';

import '@selectize/selectize/dist/js/selectize.min';

import Uppy from '@uppy/core'
import Dashboard from '@uppy/dashboard'
import XHRUpload from '@uppy/xhr-upload'
import Form from '@uppy/form'

import '../../node_modules/@uppy/core/dist/style.css'
import '../../node_modules/@uppy/dashboard/dist/style.css'

import 'print-js/dist/print'
import 'print-js/dist/print.css'

import Prism from 'prismjs'
import 'prismjs/components/prism-markup'
import 'prismjs/components/prism-markup-templating'
import 'prismjs/components/prism-php'
import 'prismjs/components/prism-javascript'
import 'prismjs/components/prism-css'

import './components/tooltips';

function registerElements() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && event.target.classList.contains('no-enter')) {
        event.preventDefault();
    }
});


document.addEventListener('htmx:afterRequest', function(evt) {

    $(function() {
        setTimeout(function() {
            $(".alert-auto-close").slideUp('slow');
        }, 2000);
    });

    registerElements();

});

import htmx from "htmx.org/dist/htmx.esm";
window.htmx = htmx;

htmx.onLoad(function(content) {

    var sortables_src = content.querySelectorAll(".sortable_source");
    if (sortables_src.length > 0) {
        for (var i = 0; i < sortables_src.length; i++) {
            var sortable_source = sortables_src[i];
            var sortableInstance = new Sortable(sortable_source, {
                group: {
                    name: 'shared',
                    pull: 'clone'
                },
                animation: 150,
                ghostClass: 'bg-info-subtle',
                filter: ".htmx-indicator",
                draggable: ".draggable",
                onMove: function (evt) {
                    return evt.related.className.indexOf('htmx-indicator') === -1;
                }
            });
        }
    }

    var sortables_target = content.querySelectorAll(".sortable_target");
    if (sortables_target.length > 0) {
        for (var i = 0; i < sortables_target.length; i++) {
            var sortable_target = sortables_target[i];
            var sortableInstanceTarget = new Sortable(sortable_target, {
                group: {
                    name: 'shared'
                },
                animation: 150,
                ghostClass: 'bg-info-subtle',
                filter: ".htmx-indicator",
                draggable: ".draggable",
                removeOnSpill: true
            });
        }
    }
})

// image picker - sortablejs
function observeContainersForDraggableDivs(parentSelector) {
    const parentDivs = document.querySelectorAll(parentSelector);

    if (parentDivs.length === 0) {
        // no container found
        return;
    }

    parentDivs.forEach((parentDiv,parentIndex) => {
        function assignHiddenInputsToDivs() {
            const childDivs = parentDiv.querySelectorAll('div.draggable');

            childDivs.forEach((div, index) => {
                if (!div.querySelector('input[type="hidden"]')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `picker_${parentIndex}[]`;

                    const dataId = div.getAttribute('data-id');
                    hiddenInput.value = dataId ? dataId : `value_${index}`; // Fallback-Wert falls keine data-id vorhanden ist

                    const deleteButton = document.createElement('button');
                    deleteButton.innerHTML = '<i class="bi bi-trash"></i>';
                    deleteButton.className = 'btn btn-danger btn-sm d-flex ms-auto';
                    deleteButton.onclick = function() {
                        this.parentElement.remove();
                    };

                    div.appendChild(hiddenInput);
                    div.appendChild(deleteButton);
                }
            });
        }

        // init MutationObserver
        const observer = new MutationObserver(() => {
            assignHiddenInputsToDivs();
        });

        // start observer
        observer.observe(parentDiv, {
            childList: true,
            subtree: true,
        });
        assignHiddenInputsToDivs();
    });
}

document.addEventListener('DOMContentLoaded', () => {

    setPrismTheme();
    observeContainersForDraggableDivs('.sortable_target');

    // handle toggle functionality
    document.querySelectorAll('.btn-toggle').forEach(button => {

        button.addEventListener('click', (event) => {
            event.preventDefault(); // Verhindert das Absenden des Formulars

            const targetClass = button.getAttribute('data-target');
            const targetContainers = document.querySelectorAll(`.toggle-item.${targetClass}`);

            targetContainers.forEach(container => {
                container.classList.toggle('d-none');
            });
        });
    });

    const toggleAllButton = document.getElementById('toggle-all');

    if (toggleAllButton) {
        toggleAllButton.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent form submission

            document.querySelectorAll('.toggle-item').forEach(container => {
                container.classList.toggle('d-none'); // Toggle each container independently
            });
        });
    }

    // uploads
    const uppy = new Uppy({
        debug: false,
        autoProceed: false,
    })

    uppy.use(Form, {
        target: '.dropper-form',
    })

    uppy.use(Dashboard, {
        inline: true,
        target: '.dropper-form',
    })
    uppy.use(XHRUpload, {
        endpoint: '/admin-xhr/widgets/upload/',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })

    uppy.on('complete', (result) => {
        htmx.trigger("body", "update_uploads_list");
    });

    document.getElementById('sidebarToggleDesktop')
        .addEventListener('click', function () {
            document.getElementById('page-content')
                .classList.toggle('sidebar-collapsed');
        });

});


let pendingAnchor = null;

function extractAnchor(file) {
    const hashIndex = file.indexOf('#');
    if (hashIndex === -1) return null;
    return file.substring(hashIndex + 1);
}

function setPrismTheme() {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    let link = document.getElementById('prism-theme');
    if (!link) {
        link = document.createElement('link');
        link.rel = 'stylesheet';
        link.id = 'prism-theme';
        document.head.appendChild(link);
    }

    link.href = isDark
        ? '/themes/administration/dist/prismjs/prism-tomorrow.css'
        : '/themes/administration/dist/prismjs/prism.css';
}

document.addEventListener('htmx:afterOnLoad', function (e) {
    const target = e.detail.target;

    if (target?.id === 'showModalContent') {
        requestAnimationFrame(() => {
            Prism.highlightAllUnder(target);
        });
    }

    if (target?.id === 'helpModal') {
        const hxVals = e.detail.elt?.getAttribute('hx-vals');
        try {
            const file = JSON.parse(hxVals)?.file || '';
            pendingAnchor = extractAnchor(file);
        } catch {
            pendingAnchor = null;
        }
        return;
    }

    if (target?.id === 'showModalContent' && pendingAnchor) {
        const anchor = pendingAnchor;
        pendingAnchor = null;

        const scrollToSection = () => {
            const el = target.querySelector('#' + CSS.escape(anchor));
            if (!el) return;

            const scrollContainer = target.querySelector('#docsScrollContainer');
            if (scrollContainer) {
                scrollContainer.scrollTop = el.offsetTop-16;
            }
        };

        const modalEl = document.querySelector('#helpModal');
        if (!modalEl) return;

        if (modalEl.classList.contains('show')) {
            scrollToSection();
        } else {
            modalEl.addEventListener('shown.bs.modal', scrollToSection, { once: true });
        }
    }
});

document.addEventListener('click', function (e) {
    const container = document.querySelector('#showModalContent');
    if (!container || !container.contains(e.target)) return;

    const a = e.target.closest('a[href]');
    if (!a) return;

    const href = a.getAttribute('href') || '';
    if (/^https?:\/\//i.test(href)) return;

    e.preventDefault();

    const hashIndex = href.indexOf('#');
    const file = hashIndex !== -1 ? href.substring(0, hashIndex) : href;
    pendingAnchor = hashIndex !== -1 ? href.substring(hashIndex + 1) : null;

    htmx.ajax('GET', '/admin-xhr/docs/read/?show_file=' + encodeURIComponent(file), { target: '#showModalContent' });
});


$(function() {


    setTimeout(function() {
        $(".alert-auto-close").slideUp('slow');
    }, 2000);


    $(".tags").selectize({
        delimiter: ",",
        persist: false,
        create: function (input) {
            return {
                value: input,
                text: input,
            };
        },
    });



    // editors
    ace.config.set("basePath", "/assets/themes/administartion/dist/ace");
    ace.config.set('modePath', '/assets/themes/administartion/dist/ace');
    ace.config.set('themePath', '/assets/themes/administartion/dist/ace');



    /* ace editor instead of <pre>, readonly */
    $('textarea[data-editor]').each(function () {
        var textarea = $(this);
        var mode = textarea.data('editor');
        var editDiv = $('<div>', {
            position: 'absolute',
            width: '100%',
            height: '400px',
            'class': textarea.attr('class')
        }).insertBefore(textarea);
        textarea.css('display', 'none');
        var editor = ace.edit(editDiv[0]);
        editor.$blockScrolling = Infinity;
        editor.getSession().setValue(textarea.val());
        editor.setTheme("ace/theme/" + ace_theme);
        editor.getSession().setMode("ace/mode/" + mode);
        editor.getSession().setUseWorker(false);
        editor.setShowPrintMargin(false);
        editor.setReadOnly(true);
    });


    stretchAppContainer();

    $( "div.scroll-box" ).each(function() {
        var divTop = $(this).offset().top;
        var newHeight = $('div.app-container').innerHeight() - divTop +80;
        $(this).height(newHeight);
    });



    $(window).resize(function () {
        stretchAppContainer();
        $( "div.scroll-box" ).each(function() {
            var divTop = $(this).offset().top;
            var newHeight = $('div.app-container').innerHeight() - divTop +60;
            $(this).height(newHeight);
        });
    });


    function stretchAppContainer() {
        var appContainer = $('div.app-container');
        if(appContainer.length) {
            if(window.matchMedia('(max-width: 767px)').matches) {
                appContainer.height('auto');
            } else {
                var divTop = appContainer.offset().top;
                var winHeight = $(window).height();
                var divHeight = winHeight - divTop;
                appContainer.height(divHeight);
            }
        }
    }


});