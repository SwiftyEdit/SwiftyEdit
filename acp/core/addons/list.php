<?php

/**
 * @var array $lang
 * @var array $icon
 * @var string $se_upload_addons from config.php
 */

global $languagePack;

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' '.$lang['nav_btn_addons'];
if($se_upload_addons && se_hasPermission('drm_acp_sensitive_files')) {
    echo '<a href="/admin/addons/install/" class="btn btn-default text-success ms-auto">' . $icon['plus'] . ' ' . $lang['btn_install'] . '</a>';
    echo '<a href="/admin/addons/catalog/" class="btn btn-default text-primary ms-1">' . $icon['shop'] . ' ' . $lang['btn_browse_catalog'] . '</a>';
}
echo '</div>';

echo '<div class="app-container">';
echo '<div class="max-height-container">';

// placeholder shown until the htmx "load" request replaces the list content
$loading_placeholder = '<div class="d-flex align-items-center text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div>'.$lang['loading'].'</div>';

echo '<div class="row">';
echo '<div class="col-md-6">';
// list plugins
echo '<div class="card">';
echo '<div class="card-header d-flex align-items-center">';
echo 'Plugins';
// client-side only - the list is already fully loaded, a server round-trip
// per click would also re-fire every plugin's update check
echo '<div class="btn-group ms-auto" data-plugin-filter>';
echo '<button type="button" class="btn btn-sm btn-default active" data-filter-category="">'.$lang['catalog_filter_all'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-category="plugin">'.$lang['catalog_filter_plugins'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-category="payment">'.$lang['catalog_filter_payment'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-category="editor">'.$lang['catalog_filter_editors'].'</button>';
echo '</div>';
echo '</div>';
echo '<div class="card-body">';
echo '<div class="scroll-box">';
echo '<div id="listPlugins" hx-get="/admin-xhr/addons/read/?action=list_plugins" hx-trigger="load, update_plugins_list from:body">'.$loading_placeholder.'</div></div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-6">';
// list themes
echo '<div class="card">';
echo '<div class="card-header">Themes</div>';
echo '<div class="card-body">';
echo '<div class="scroll-box">';
// receives the toast of a refused theme deletion (see delete_theme in data-writer.php)
echo '<div id="theme-delete-response"></div>';
echo '<div id="listThemes" hx-get="/admin-xhr/addons/read/?action=list_themes" hx-trigger="load, update_themes_list from:body">'.$loading_placeholder.'</div></div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '<script>
(function () {
    var group = document.querySelector("[data-plugin-filter]");
    var list = document.getElementById("listPlugins");
    if (!group || !list) { return; }

    var current = "";

    function applyFilter() {
        list.querySelectorAll("[data-addon-category]").forEach(function (card) {
            var show = current === "" || card.getAttribute("data-addon-category") === current;
            card.style.display = show ? "" : "none";
        });
    }

    group.addEventListener("click", function (e) {
        var btn = e.target.closest("[data-filter-category]");
        if (!btn) { return; }

        group.querySelectorAll("[data-filter-category]").forEach(function (b) {
            b.classList.toggle("active", b === btn);
        });

        current = btn.getAttribute("data-filter-category");
        applyFilter();
    });

    // keep the selected filter when the list is re-rendered
    // (e.g. after activating or deleting a plugin, see update_plugins_list)
    list.addEventListener("htmx:afterSwap", function (e) {
        if (e.target === list) { applyFilter(); }
    });
})();
</script>';