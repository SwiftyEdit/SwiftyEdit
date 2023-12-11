/**
 * Prepend to this file:
 * - jquery
 * - popper.js
 * - bootstrap.js
 * - accounting
 * - tempus dominus
 * - tempus dominus moment-parse.js
 * - tempus dominus jQuery Provider
 * - tags-input
 * - clipboard
 * - dropzone
 * - image-picker
 * - dirtyforms
 * - textcounter
 * - moment
 *
 * Append to this File
 * - tinymce
 * - tinymce.jquery
 */

tempusDominus.extend(tempusDominus.plugins.moment_parse, 'YYYY-MM-DD HH:mm');

$(function() {
	

	/* dirty forms */
	$('form').dirtyForms();
	$.DirtyForms.dialog = false;
			
				
	$("#toggleExpand").click(function() {
		$('.info-collapse').toggleClass('info-hide');
	});
				
					
	setTimeout(function() {
		$(".alert-auto-close").slideUp('slow');
	}, 2000);
			
	$('#showVersions').collapse('hide');
			
	$('[data-bs-toggle="popover"]').popover();
	$('[data-bs-toggle="tooltip"]').tooltip();
				
	var clipboard = new ClipboardJS('.copy-btn');

	/* time picker */

	$('.dp').tempusDominus({
		display: {
			icons: {
				time: 'bi bi-clock',
				date: 'bi bi-calendar',
				up: 'bi bi-arrow-up',
				down: 'bi bi-arrow-down',
				previous: 'bi bi-chevron-left',
				next: 'bi bi-chevron-right',
				today: 'bi bi-calendar-check',
				clear: 'bi bi-trash',
				close: 'bi bi-x',
			},
			components: {
				seconds: false
			}
		},localization: {
			hourCycle: 'h24'
		}
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

	
	Dropzone.options.myDropzone = {
		init: function() {
			this.on("success", function(file, responseText) {
				file.previewTemplate.appendChild(document.createTextNode(responseText));
			});
		}
	};
	
	Dropzone.options.dropAddons = {
		init: function() {
			this.on("success", function(file, responseText) {
				window.location.href = "acp.php?tn=moduls&sub=u";
			});
		}
	};
	

	/**
	 * count chars and words
	 * we use this f.e. in meta descriptions
	 */
	 
	$('.cntWords').textcounter({   
		type: "word",
		stopInputAtMaximum: false,
		counterText: '%d'
	});
	$('.cntChars').textcounter({   
		type: "character",
		stopInputAtMaximum: false,
		counterText: '%d'
	});

	ace.config.set("basePath", "/acp/theme/js/ace");
	ace.config.set('modePath', '/acp/theme/js/ace');
	ace.config.set('themePath', '/acp/theme/js/ace');

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

	
	
	//SIDEBAR
	
	var sidebarState = sessionStorage.getItem('sidebarState');
	var sidebarHelpState = sessionStorage.getItem('sidebarHelpState');

	windowWidth = $(window).width();

	$(window).resize(function() {
		windowWidth = $(window).width();

		if( windowWidth < 992 ){ //992 is the value of $screen-md-min in boostrap variables.scss
			$('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
			$('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
			$('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
			
		} else {
	    
		   if(sidebarState){
				$('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
				$('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
				$('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
		   } else {
			 	$('#page-sidebar-inner').addClass('sidebar-expanded').removeClass('sidebar-collapsed');
			 	$('#page-content').addClass('sb-expanded').removeClass('sb-collapsed');
			 	$('#page-sidebar').addClass('sb-expanded').removeClass('sb-collapsed');
		   }
  		}  
	});

	function setSidebarState(item,value){
   	sessionStorage.setItem(item, value);
	}

	function clearSidebarState(item){
   	sessionStorage.removeItem(item);
	}

	function collapseSidebar(){
	    $('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
	    $('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
	    $('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
	    $('.caret_left').addClass('d-none');
	    $('.caret_right').removeClass('d-none');
	}
	
	function expandSidebar(){
	    $('#page-sidebar-inner').addClass('sidebar-expanded').removeClass('sidebar-collapsed');
	    $('#page-content').addClass('sb-expanded').removeClass('sb-collapsed');
	    $('#page-sidebar').addClass('sb-expanded').removeClass('sb-collapsed');
	    $('.caret_right').addClass('d-none');
	    $('.caret_left').removeClass('d-none');
	}
	
	function SupportCol_hide(){
	    $('#collapseSupport').addClass('d-none').removeClass('col-3');
	    setSidebarState('sidebarHelpState','hidden');
	}
	
	function SupportCol_show(){
	    $('#collapseSupport').addClass('col-3').removeClass('d-none');;
	    setSidebarState('sidebarHelpState','expanded');
	}


    /** check sessionStorage to expand/collapse sidebar onload **/
    if (sidebarState == "collapsed") {
    	collapseSidebar();
    } else {

    	if( windowWidth < 992 ) {
				$('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
				$('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
				$('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
      } else {
      
      	if(sidebarState){
					$('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
					$('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
					$('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
        } else {
					$('#page-sidebar-inner').addClass('sidebar-expanded').removeClass('sidebar-collapsed');
					$('#page-content').addClass('sb-expanded').removeClass('sb-collapsed');
					$('#page-sidebar').addClass('sb-expanded').removeClass('sb-collapsed');
				  $('.caret_right').addClass('d-none');
					$('.caret_left').removeClass('d-none');
				}
      }  
    }
 
	  if(sidebarHelpState === "hidden" || typeof sidebarHelpState==='undefined' || sidebarHelpState===null){
		  SupportCol_hide();
	  } else {
		  SupportCol_show();
	  }


    /** collapse the sidebar navigation **/    
    $('#toggleNav').click(function(){
        if(!($('#page-sidebar-inner').hasClass('sidebar-collapsed'))) { // if sidebar is not yet collapsed
          collapseSidebar();
          setSidebarState('sidebarState','collapsed');
        } else {
        	expandSidebar();
          clearSidebarState('sidebarState');
        }
        return false;
    })
    
    /** toggle the sidebar for help **/    
    $('#toggleSupport').click(function(){
        if(($('#collapseSupport').hasClass('d-none'))) {
			SupportCol_show();
        } else {
			SupportCol_hide();
        }
        return false;
    })


	// globalFilterForm
	$("#globalFilterForm").submit(function(e){
		e.preventDefault();
		submitFilterForm();
		return false;
	});


	function submitFilterForm(){
		$.ajax({
			type: "POST",
			url: "core/ajax/global-filter.php",
			data: $('form#globalFilterForm').serialize(),
			success: function(response){
				$("#response").html(response)
			}
		});
	}

	$('#globalFilter').on('hide.bs.offcanvas', function () {
		location.reload();
	});

	$('.page-info-btn').click(function(){
				   
	   var pageid = $(this).data('id');
	   var csrf_token = $(this).data('token');

	   // AJAX request
		$.ajax({
			url: 'core/ajax/show-page-info.php',
			type: 'post',
			data: {pageid: pageid, csrf_token: csrf_token},
			success: function(response){ 
				 // Add response in Modal body
				$('#infoModal .modal-body').html(response);
				$('#infoModal .modal-header .modal-title').html('Page ID #' + pageid);
			}
		});
	});


	$('.show-doc').click(function(){
		var docfile = $(this).data('file');
		var csrf_token = $(this).data('token');
		var contents = load_modal_content(docfile,csrf_token);
		$('#infoModal .modal-header .modal-title').html(contents.header.title);
		$('#infoModal .modal-body').html(contents.content);
	});

	$('#infoModal').on('shown.bs.modal', function () {
		$('#infoModal .jump-doc').click(function(){
			var docfile = $(this).data('file');
			var csrf_token = $(this).data('token');
			var contents = load_modal_content(docfile,csrf_token);
			$('#infoModal .modal-header .modal-title').html(contents.header.title);
			$('#infoModal .modal-body').html(contents.content);
		});
	});

	function load_modal_content(file,token) {
		response = null;
		$.ajax({
			url: 'core/ajax/show-docs.php',
			type: 'post',
			async: false,
			data: {file: file, csrf_token: token},
			success: function(content){
				response = $.parseJSON(content);

			}
		});
		return response;
	}

				 
  $(window).resize(function () {
  	stretchAppContainer();
		$( "div.scroll-box" ).each(function() {
			var divTop = $(this).offset().top;
		  var newHeight = $('div.app-container').innerHeight() - divTop +40;
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
			

		 

				 
	



  function addTax(price,tax) {
		tax = parseInt(tax);
		price = price*(tax+100)/100;
			return price;
		}
		  	
	function removeTax(price,tax) {
		tax = parseInt(tax);
		price = price*100/(tax+100);
		return price;
	}

	if($("#price").val()) {
					
		get_price_net = $("#price").val();
		var e = document.getElementById("tax");
		var get_tax = e.options[e.selectedIndex].text;
		get_tax = parseInt(get_tax);
		get_net_calc = get_price_net.replace(/\./g, '');
		get_net_calc = get_net_calc.replace(",",".");
		current_gross = addTax(get_net_calc,get_tax);
		current_gross = accounting.formatNumber(current_gross,8,".",",");
		$('#price_total').val(current_gross);
		
		calculated_net = addTax(get_net_calc,0);
		calculated_net = accounting.formatNumber(calculated_net,8,".",",");
		$('#calculated_net').html(calculated_net);
		
		$('.show_price_tax').html(get_tax);

		$('#price').keyup(function(){
			get_price_net = $('#price').val();
			get_net_calc = get_price_net.replace(/\./g, '');
			get_net_calc = get_net_calc.replace(",",".");
			current_gross = addTax(get_net_calc,get_tax);
			current_gross = accounting.formatNumber(current_gross,8,".",",");
			$('#price_total').val(current_gross);
			
			calculated_net = addTax(get_net_calc,0);
			calculated_net = accounting.formatNumber(calculated_net,8,".",",");
			$('#calculated_net').html(calculated_net);
			
		});
					
		$('#price_total').keyup(function(){
			get_brutto = $('#price_total').val();
			get_gross_calc = get_brutto.replace(/\./g, '');
			get_gross_calc = get_gross_calc.replace(",",".");
			current_net = removeTax(get_gross_calc,get_tax);
			current_net = accounting.formatNumber(current_net,8,".",",");
			$('#price').val(current_net);
			$('#calculated_net').html(current_net);
		});
		
		$('#price_addition').keyup(function(){
			get_price_net = $('#price').val();
			
			get_net_calc = get_price_net.replace(/\./g, '');
			get_net_calc = get_net_calc.replace(",",".");
			current_gross = addTax(get_net_calc,get_tax);
			current_gross = accounting.formatNumber(current_gross,8,".",",");
			$('#price_total').val(current_gross);
		});
		
		$('#tax').bind("change keyup", function(){
			
			var e = document.getElementById("tax");
			var get_tax = e.options[e.selectedIndex].text;
			get_tax = parseInt(get_tax);

			get_price_net = $('#price').val();
			get_net_calc = get_price_net.replace(",",".");

			current_gross = addTax(get_net_calc,get_tax);
			current_gross = accounting.formatNumber(current_gross,8,".",",");

			$('#price_total').val(current_gross);
		});
	}

});

/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2023 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */

(() => {
	'use strict'

	const storedTheme = localStorage.getItem('backend-theme')

	const getPreferredTheme = () => {
		if (storedTheme) {
			return storedTheme
		}

		return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
	}

	const setTheme = function (theme) {
		if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			document.documentElement.setAttribute('data-bs-theme', 'dark')
		} else {
			document.documentElement.setAttribute('data-bs-theme', theme)
		}
	}

	setTheme(getPreferredTheme())

	const showActiveTheme = theme => {
		const activeThemeIcon = document.querySelector('.theme-icon-active')
		const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
		const iconOfActiveBtn = btnToActive.querySelector('i').getAttribute('class')

		document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
			element.classList.remove('active')
		})

		btnToActive.classList.add('active')
		activeThemeIcon.setAttribute('class', iconOfActiveBtn)
	}

	window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
		if (storedTheme !== 'light' || storedTheme !== 'dark') {
			setTheme(getPreferredTheme())
		}
	})

	window.addEventListener('DOMContentLoaded', () => {
		showActiveTheme(getPreferredTheme())

		document.querySelectorAll('[data-bs-theme-value]')
			.forEach(toggle => {
				toggle.addEventListener('click', () => {
					const theme = toggle.getAttribute('data-bs-theme-value')
					localStorage.setItem('backend-theme', theme)
					setTheme(theme)
					showActiveTheme(theme)
				})
			})
	})
})()