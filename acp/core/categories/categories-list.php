<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 */

$reader_uri = '/admin-xhr/categories/read/';
$writer_uri = '/admin-xhr/categories/write/';

$q = pathinfo($_REQUEST['query']);

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['bookmarks_fill'].' '.$lang['categories'].'  '.se_print_docs_link('01-02-basics.md#categories');
echo '<a href="/admin/categories/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

echo '<div class="card p-3">';
echo '<div id="getCategories" hx-get="'.$reader_uri.'?action=list" hx-trigger="load, changed, updated_categories from:body">';
echo'</div>';
echo'</div>';