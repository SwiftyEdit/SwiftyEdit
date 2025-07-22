<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['files'].' '.$lang['nav_btn_blog'];
echo '<a href="/admin/blog/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin-xhr/blog/read/';
$writer_uri = '/admin-xhr/blog/write/';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getPosts" class="" hx-get="'.$reader_uri.'?action=list_posts" hx-trigger="load, update_posts_list from:body, updated_global_filter from:body">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['filter'].'</div>';
echo '<div class="card-body">';
echo '<form hx-post="'.$writer_uri.'" hx-swap="none" hx-on--after-request="this.reset()" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="blog_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches" hx-trigger="load, changed, update_posts_list from:body, updated_global_filter from:body"></div>';

echo '</div>';
echo '</div>';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['label_post_type'].'</div>';
echo '<div id="keyList" hx-get="'.$reader_uri.'?action=list_post_types" hx-trigger="load, update_posts_list from:body, updated_global_filter from:body"></div>';
echo '</div>';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['label_categories'].'</div>';
echo '<div class="scroll-container p-0">';
echo '<div id="keyList" hx-get="'.$reader_uri.'?action=list_categories" hx-trigger="load, update_posts_list from:body, updated_global_filter from:body"></div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';