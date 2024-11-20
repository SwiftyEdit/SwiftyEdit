<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['files'].' '.$lang['nav_btn_blog'];
echo '<a href="/admin/blog/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin/blog/read/';
$writer_uri = '/admin/blog/write/';

echo '<div id="getPosts" class="" hx-post="'.$reader_uri.'?action=list_posts" hx-trigger="load, update_posts_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';