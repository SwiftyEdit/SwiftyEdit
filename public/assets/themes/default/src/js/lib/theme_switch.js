/*!
 * Light/dark mode toggler for SwiftyEdit's default theme, adapted from
 * Bootstrap's docs color-mode example (https://getbootstrap.com/). Unlike
 * the original this has no "auto" option a user can pick - most visitors
 * don't know what that means, and a single click is simpler than a
 * dropdown. The system's color-scheme preference is only used once, to
 * pick light or dark on a visitor's first visit; from then on it's just
 * whatever they last toggled to.
 */

(() => {
    'use strict'

    const getStoredTheme = () => localStorage.getItem('theme')
    const setStoredTheme = theme => localStorage.setItem('theme', theme)

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme()
        if (storedTheme === 'light' || storedTheme === 'dark') {
            return storedTheme
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = theme => {
        document.documentElement.setAttribute('data-bs-theme', theme)
    }

    // Applied immediately (not just after DOMContentLoaded) to avoid a
    // flash of the wrong theme on load. If nothing valid was stored yet -
    // either a first visit, or a leftover "auto" from before this theme
    // had only light/dark - this is the one-time pick from the system
    // preference, remembered so the site stays on that theme instead of
    // silently following further system preference changes.
    const initialTheme = getPreferredTheme()
    setTheme(initialTheme)
    const storedTheme = getStoredTheme()
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
        setStoredTheme(initialTheme)
    }

    const showActiveTheme = theme => {
        const activeThemeIcon = document.querySelector('.theme-icon-active')
        if (activeThemeIcon) {
            activeThemeIcon.setAttribute('class', theme === 'dark' ? 'bi theme-icon-active bi-moon-stars-fill' : 'bi theme-icon-active bi-sun-fill')
        }

        const toggleBtn = document.getElementById('themeToggle')
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false')
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        showActiveTheme(getPreferredTheme())

        const toggleBtn = document.getElementById('themeToggle')
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const nextTheme = getPreferredTheme() === 'dark' ? 'light' : 'dark'
                setStoredTheme(nextTheme)
                setTheme(nextTheme)
                showActiveTheme(nextTheme)
            })
        }
    })
})()
