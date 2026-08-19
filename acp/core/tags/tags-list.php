<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 */

$reader_uri = '/admin-xhr/tags/read/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['tags'].' '.$lang['tags'];
echo '</div>';

echo '<div class="card p-3">';
echo '<div id="getTags" hx-get="'.$reader_uri.'?action=list" hx-trigger="load, changed, updated_tags from:body">';
echo '</div>';
echo '</div>';
