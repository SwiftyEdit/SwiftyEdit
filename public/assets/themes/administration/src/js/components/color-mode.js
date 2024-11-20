
/*
 * Color mode toggler based on Bootstrap's docs
 * https://getbootstrap.com/docs/5.3/customize/color-modes/
 * but we don't want a dropdown, we want a simple switch
 */

const getStoredTheme = () => localStorage.getItem("backendTheme");
const setStoredTheme = theme => localStorage.setItem("backendTheme", theme);


const getPreferredTheme = () => {
    const storedTheme = getStoredTheme()
    if (storedTheme) {
        return storedTheme
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

const setTheme = theme => {
    if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-bs-theme', 'dark')
    } else {
        document.documentElement.setAttribute('data-bs-theme', theme)
    }
}


setTheme(getPreferredTheme())

const container = document.documentElement;
if(localStorage.getItem("backendTheme")){
    container.setAttribute("data-bs-theme",getStoredTheme());
    toggleTheme(1)
}

function toggleTheme(r) {

    const activeTheme = getStoredTheme();
    let theme_switch;

    if (activeTheme === "light") {
        theme_switch = 1
    } else {
        theme_switch = 0
    }

    if (r) {
        theme_switch = !theme_switch
    } else {

    }
    if (theme_switch) {
        setTheme("dark");
        setStoredTheme("dark")
    } else {
        setTheme("light");
        setStoredTheme("light")
    }
}

window.toggleTheme = toggleTheme;