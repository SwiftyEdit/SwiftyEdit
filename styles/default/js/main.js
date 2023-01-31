/* exported vote */
/* vote() is used in posts */

function vote($val){

	var data = $val.split("-");
	var post_id = data[data.length - 1];

	$.ajax({
  	type: 'POST',
    url: '/core/ajax.votings.php',
    data: { 
    	val: $val
    },
    success: function(response) { 
	    
			var votes = JSON.parse(response);
			
			var upvote_element_id = 'vote-up-nbr-'+post_id;
			var dnvote_element_id = 'vote-dn-nbr-'+post_id;
			
			var cnt_upv = document.getElementById(upvote_element_id);
			cnt_upv.innerHTML = votes.upv;
			var cnt_dnv = document.getElementById(dnvote_element_id);
			cnt_dnv.innerHTML = votes.dnv;
			
    }
  });
}

/* exported sign_guestlist */
/* sign_guestlist() is used in posts type events */

function sign_guestlist($val){

	$.ajax({
  	type: 'POST',
    url: '/core/ajax.guestlist.php',
    data: { 
    	val: $val
    },
    success: function(response) { 
	    		
			var commiters = JSON.parse(response);
			var cnt_commit = document.getElementById('nbr-commitments');
			cnt_commit.innerHTML = commiters.evc;
		
    }
  });
}


/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2022 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */

(() => {
	'use strict'

	const storedTheme = localStorage.getItem('theme')

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
					localStorage.setItem('theme', theme)
					setTheme(theme)
					showActiveTheme(theme)
				})
			})
	})
})()