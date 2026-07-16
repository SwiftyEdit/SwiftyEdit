<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 */

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['arrow_clockwise'].' '.$lang['update'].'';
echo '<div class="ms-auto">helpers</div>';
echo '</div>';

$hx_vals = [
    "csrf_token"=> $_SESSION['token']
];

echo '<div id="response"></div>';

echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Serch & Replace contents</h5></div>';
echo '<div class="card-body">';

echo '<p>If you have imported a database from SwiftyEdit 1.x, all paths to your images and downloads must be adjusted.<br>';
echo 'This tool searches the entire database for the character string <code>../content/images/</code> and replaces it with <code>/images/</code> etc.</p>';

echo '</div>';

echo '<div class="card-footer">';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_content">Update page, snippets and categories</button>';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_posts">Update posts, products and events</button>';
echo '</div>';
echo '</div>';

echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Delivery Areas / Migrating country fields to ISO 3166-1 alpha-2 codes</h5></div>';
echo '<div class="card-body">';

echo '<p>In earlier versions of SwiftyEdit, the country was stored as text. 
However, we now use ISO 3166-1 alpha-2 codes. Here, all Delivery Areas can be converted at once.</p>';

echo '</div>';

echo '<div class="card-footer">';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_delivery_areas_country">Update Delivery Areas</button>';
echo '</div>';
echo '</div>';


echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>User accounts / Migrating country fields to ISO 3166-1 alpha-2 codes</h5></div>';
echo '<div class="card-body">';

echo '<p>In earlier versions of SwiftyEdit, the country was stored as text. 
However, we now use ISO 3166-1 alpha-2 codes. Here, all user accounts can be converted at once.</p>';

echo '</div>';

echo '<div class="card-footer">';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_users_country">Update Users</button>';
echo '</div>';
echo '</div>';


echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Add UUIDs if not exists</h5></div>';
echo '<div class="card-body">';

echo '<p>In earlier versions of SwiftyEdit, UUIDs weren\'t always available. You can add them here. Any existing entries will be skipped.</p>';

echo '</div>';

echo '<div class="card-footer">';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_users_uuid">Update Users</button>';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_orders_uuid">Update Orders</button>';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_products_uuid">Update Products</button>';
echo '</div>';
echo '</div>';


echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Content-format editors / page_content_source column</h5></div>';
echo '<div class="card-body">';

echo '<p>Adds the <code>page_content_source</code> column to <code>se_pages</code> and <code>se_pages_cache</code>.
Content-format editor plugins (e.g. a Markdown or block-builder editor) store their raw content there;
<code>page_content</code> keeps holding the plugin\'s rendered HTML, computed once when the page is saved.
Safe to run more than once - already-existing columns are skipped.</p>';

echo '</div>';

echo '<div class="card-footer">';
echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="page_content_source_column">Add page_content_source column</button>';
echo '</div>';
echo '</div>';