<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['arrow_clockwise'].' '.$lang['update'].'';
echo '<div class="ms-auto">helpers</div>';
echo '</div>';

$hx_vals = [
    "csrf_token"=> $_SESSION['token']
];

echo '<div class="card">';
echo '<div class="card-header">Serch & Replace contents</div>';
echo '<div class="card-body">';
echo '<div class="alert alert-info">';
echo '<p>If you have imported a database from SwiftyEdit 1.x, all paths to your images and downloads must be adjusted.<br>';
echo 'This tool searches the entire database for the character string <code>../content/images/</code> and replaces it with <code>/images/</code> etc.</p>';
echo '</div>';


echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_content">Update page, snippets and categories</button>';

echo '<button class="btn btn-default text-danger m-1" hx-post="/admin-xhr/update/write/" hx-target="#response" hx-vals=\''.json_encode($hx_vals).'\' name="helper_update_table" value="se_posts">Update posts, products and events</button>';
echo '<div id="response" class="p-3"></div>';