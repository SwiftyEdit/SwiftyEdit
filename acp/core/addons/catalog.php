<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 * @var bool $se_upload_addons from config.php
 */

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' <a href="/admin/addons/">'.$lang['nav_btn_addons'].'</a>';
echo '<span class="ms-1">'.$lang['catalog_title'].'</span>';
echo '</div>';

if(!$se_upload_addons) {
    echo '<div class="card p-3">';
    echo $lang['catalog_disabled'];
    echo '</div>';
    return;
}

echo '<div class="app-container">';
echo '<div class="max-height-container">';

// Client-side only - the grid is already fully loaded, no need for a
// server round-trip per filter click. Categories come from each entry's
// registry "tags" (see CONTRIBUTING.md), not from slug naming conventions
// (e.g. a "-editor"/"-pay" suffix) - tags are explicit and reviewed at
// submission time, a naming convention could misfire on an unrelated slug.
// "theme" is a tag like any other here - the install pipeline
// (data-writer.php) already handles a "theme" addon_type end to end, so
// entries the registry (se_get_catalog_entries(), https://swiftyedit.net/api/plugins)
// tags as "theme" install correctly today; this button just surfaces them.
echo '<div class="btn-group mb-3" data-catalog-filter>';
echo '<button type="button" class="btn btn-sm btn-default active" data-filter-tag="">'.$lang['catalog_filter_all'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-tag="__plain__">'.$lang['catalog_filter_plugins'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-tag="theme">'.$lang['catalog_filter_themes'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-tag="editor">'.$lang['catalog_filter_editors'].'</button>';
echo '<button type="button" class="btn btn-sm btn-default" data-filter-tag="payment">'.$lang['catalog_filter_payment'].'</button>';
echo '</div>';

echo '<div id="catalogGrid" hx-get="/admin-xhr/addons/read/?action=list_catalog" hx-trigger="load, refresh_catalog from:body"></div>';
echo '</div>';
echo '</div>';

// Shared install modal for the whole grid (one at a time can ever be open) -
// swapping the confirm/install response into a modal instead of inline into
// a card avoids growing that card and desyncing the grid row's height
// against its siblings. Kept outside #catalogGrid so a "refresh_catalog"
// re-render (see above) can't tear this down while it's open. Starts empty;
// data-writer.php's get_addon_info_from_url/install_addon_from_url steps
// fill #catalogInstallModalBody with the modal-header/-body/-footer markup
// (same shell-plus-fetched-content pattern as #comment-modal in
// acp/core/inbox/data-reader.php).
echo '<div class="modal fade" id="catalogInstallModal" tabindex="-1" aria-hidden="true">';
echo '<div class="modal-dialog modal-dialog-centered">';
echo '<div class="modal-content" id="catalogInstallModalBody"></div>';
echo '</div>';
echo '</div>';

echo '<script>
(function () {
    var group = document.querySelector("[data-catalog-filter]");
    if (!group) { return; }

    group.addEventListener("click", function (e) {
        var btn = e.target.closest("[data-filter-tag]");
        if (!btn) { return; }

        group.querySelectorAll("[data-filter-tag]").forEach(function (b) {
            b.classList.toggle("active", b === btn);
        });

        var tag = btn.getAttribute("data-filter-tag");
        document.querySelectorAll("#catalogGrid [data-catalog-tags]").forEach(function (card) {
            var tags = card.getAttribute("data-catalog-tags").split(",").filter(Boolean);
            var show = tag === ""
                ? true
                : (tag === "__plain__" ? (tags.indexOf("editor") === -1 && tags.indexOf("payment") === -1 && tags.indexOf("theme") === -1) : tags.indexOf(tag) !== -1);
            card.style.display = show ? "" : "none";
        });
    });
})();
</script>';
