<?php

$reader_uri = '/admin/categories/read/';
$writer_uri = '/admin/categories/write/';

$q = pathinfo($_REQUEST['query']);

echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>'.$lang['categories'].'</h3>';
echo '<a href="/admin/categories/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';



if($q['dirname'] == 'categories/edit' && is_numeric($q['filename'])) {
    $get_cat_id = (int) $q['filename'];
    include 'form.php';
}

if($q['filename'] == 'new') {
    include 'form.php';
}

// show existing labels

echo '<div id="getCategories" class="card p-3" hx-post="'.$reader_uri.'?action=list" hx-trigger="load, changed, updated_categories from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo'</div>';


echo '<hr>';