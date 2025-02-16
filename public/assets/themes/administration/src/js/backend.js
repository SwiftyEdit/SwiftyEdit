"use strict";

import '../scss/backend.scss';
import 'jquery';
window.jQuery = $; window.$ = $;

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';
import './components/color-mode.js';
import ClipboardJS from "clipboard";
import './components/sidebar.js';

import ace from "ace-builds/src-noconflict/ace";
import 'ace-builds/src-noconflict/mode-html';
import 'ace-builds/src-noconflict/theme-twilight'
import 'ace-builds/src-noconflict/theme-chrome'

import Sortable from 'sortablejs';
window.Sortable = Sortable;

import './components/products';
import 'htmx.org';

import '@selectize/selectize/dist/js/selectize.min';

import Uppy from '@uppy/core'
import Dashboard from '@uppy/dashboard'
import XHRUpload from '@uppy/xhr-upload'
import Form from '@uppy/form'

import '@uppy/core/dist/style.css'
import '@uppy/dashboard/dist/style.css'

import 'print-js/dist/print'
import 'print-js/dist/print.css'



document.addEventListener('htmx:afterRequest', function(evt) {

    $(function() {
        setTimeout(function() {
            $(".alert-auto-close").slideUp('slow');
        }, 2000);
    });

});

import htmx from "htmx.org/dist/htmx.esm";
window.htmx = htmx;

htmx.onLoad(function(content) {
    var sortables_src = content.querySelectorAll(".sortable_source");
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

    var sortables_target = content.querySelectorAll(".sortable_target");
    for (var i = 0; i < sortables_target.length; i++) {
        var sortable_target = sortables_target[i];
        var sortableInstanceTarget = new Sortable(sortable_target, {
            group: {
                name: 'shared'
            },
            animation: 150,
            ghostClass: 'bg-info-subtle',
            filter: ".htmx-indicator",
            draggable: ".draggable"
        });
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

                    div.appendChild(hiddenInput);
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

    const noEnterFields = document.querySelectorAll('.no-enter');

    noEnterFields.forEach(field => {
        field.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    });

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

});




$(function() {

    // observe "sortable_target" (image picker)
    observeContainersForDraggableDivs('.sortable_target');

    const uppy = new Uppy({
        debug: false,
        autoProceed: false,
    })

    uppy.use(Form, {
        target: '#dropper',
    })

    uppy.use(Dashboard, {
        inline: true,
        target: '#dropper',
    })
    uppy.use(XHRUpload, {
        endpoint: '/admin/upload/',
    })


    $('[data-bs-toggle="popover"]').popover();
    $('[data-bs-toggle="tooltip"]').tooltip();

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