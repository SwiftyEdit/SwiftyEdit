<?php

$reader_uri = '/admin/categories/read/';
$writer_uri = '/admin/categories/write/';

$q = pathinfo($_REQUEST['query']);

echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>'.$lang['categories'].'</h3>';
echo '</div>';

echo '<div id="formResponse"></div>';

echo '<div class="row">';
echo '<div class="col-md-6">';
// show existing labels

echo '<div class="card p-3">';
echo '<div id="getCategories" hx-get="'.$reader_uri.'?action=list" hx-trigger="load, changed, updated_categories from:body">';
echo'</div>';
echo'</div>';

echo'</div>';
echo '<div class="col-md-6">';

echo '<div class="card p-3">';
echo '<div id="categoryForm" class="" hx-get="'.$reader_uri.'?action=show_category_form" hx-trigger="load, show_category_form from:body" hx-include="[name=\'csrf_token\']">';
echo '</div>';
echo'</div>';

echo'</div>';
echo'</div>';

echo '<hr>';