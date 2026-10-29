/*
 * Global live search in the ACP header (acp/templates/layout/nav-top.twig).
 * The results dropdown is filled by htmx; it closes on a click outside of
 * the search and on Escape (which also leaves the input).
 */

document.addEventListener("click", function (e) {
    const wrapper = document.getElementById("globalSearchWrapper");
    const results = document.getElementById("globalSearchResults");
    if (!wrapper || !results || wrapper.contains(e.target)) { return; }
    results.classList.remove("show");
});

document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") { return; }
    const input = document.getElementById("globalSearchInput");
    const results = document.getElementById("globalSearchResults");
    if (results) { results.classList.remove("show"); }
    if (input && document.activeElement === input) { input.blur(); }
});
