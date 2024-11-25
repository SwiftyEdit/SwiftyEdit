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
import './components/jquery-sortable';
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





document.addEventListener('htmx:afterRequest', function(evt) {

    $(function() {
        setTimeout(function() {
            $(".alert-auto-close").slideUp('slow');
        }, 2000);
    });
});

window.htmx = require('htmx.org');


$(function() {

    const uppy = new Uppy({
        debug: true,
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

    /**
     * image picker for choosing thumbnails
     * we use this f.e. for pages thumbnails
     */

    $(".image-checkbox").each(function () {
        if ($(this).find('input[type="checkbox"]').first().attr("checked")) {
            $(this).addClass('image-checkbox-checked');
        } else {
            $(this).removeClass('image-checkbox-checked');
        }
    });

    // sync the state to the input
    $(".image-checkbox").on("click", function (e) {
        $(this).toggleClass('image-checkbox-checked');
        var $checkbox = $(this).find('input[type="checkbox"]');
        $checkbox.prop("checked", !$checkbox.prop("checked"))

        e.preventDefault();
    });

    $('.filter-images').keyup(function() {
        var value = $(this).val();
        var exp = new RegExp('^' + value, 'i');

        $('.image-checkbox').not('.image-checkbox-checked').each(function() {
            var isMatch = exp.test($('.card-footer', this).text());
            $(this).toggle(isMatch);
        });
    });


    // editors
    ace.config.set("basePath", "/assets/themes/administartion/dist/ace");
    ace.config.set('modePath', '/assets/themes/administartion/dist/ace');
    ace.config.set('themePath', '/assets/themes/administartion/dist/ace');

    /* css and html editor for page header */
    if($('#CSSeditor').length != 0) {
        var CSSeditor = ace.edit("CSSeditor");
        var CSStextarea = $('textarea[class*=aceEditor_css]').hide();
        CSSeditor.$blockScrolling = Infinity;
        CSSeditor.getSession().setValue(CSStextarea.val());
        CSSeditor.setTheme("ace/theme/" + ace_theme);
        CSSeditor.getSession().setMode("ace/mode/css");
        CSSeditor.getSession().setUseWorker(false);
        CSSeditor.setShowPrintMargin(false);
        CSSeditor.getSession().on('change', function(){
            CSStextarea.val(CSSeditor.getSession().getValue());
        });
    }

    if($('#HTMLeditor').length != 0) {
        var HTMLeditor = ace.edit("HTMLeditor");
        var HTMLtextarea = $('textarea[class*=aceEditor_html]').hide();
        HTMLeditor.$blockScrolling = Infinity;
        HTMLeditor.getSession().setValue(HTMLtextarea.val());
        HTMLeditor.setTheme('ace/theme/'+ace_theme);
        HTMLeditor.getSession().setMode({ path:'ace/mode/html', inline:true });
        HTMLeditor.getSession().setUseWorker(false);
        HTMLeditor.setShowPrintMargin(false);
        HTMLeditor.getSession().on('change', function(){
            HTMLtextarea.val(HTMLeditor.getSession().getValue());
        });
    }

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




    $(".filter-table-input").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        var group = $(this).closest('.filter-group');
        $(group).find(".table-filter tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });



    $('.page-info-btn').click(function(){

        var pageid = $(this).data('id');
        var csrf_token = $(this).data('token');

        // AJAX request
        $.ajax({
            url: './core/ajax/show-page-info.php',
            type: 'post',
            data: {pageid: pageid, csrf_token: csrf_token},
            success: function(response){
                // Add response in Modal body
                $('#infoModal .modal-body').html(response);
                $('#infoModal .modal-header .modal-title').html('Page ID #' + pageid);
            }
        });
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


    $('.sortableListGroup').sortable({
        handle: '.bi-arrows-move',
        invertSwap: true
    });



});

