<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 * @var string $hidden_csrf_token
 */

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['users'].' '.$lang['nav_btn_user'];
echo '<a href="/admin/users/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin-xhr/users/read/';
$writer_uri = '/admin-xhr/users/write/';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getUsers" class="" hx-get="'.$reader_uri.'?action=list_users" hx-trigger="load, update_users_list from:body, updated_global_filter from:body">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['filter'].'</div>';
echo '<div class="card-body">';
echo '<form hx-post="'.$writer_uri.'" hx-swap="none" hx-on--after-request="this.reset()" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="users_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches" hx-trigger="load, changed, update_users_list from:body, updated_global_filter from:body"></div>';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['label_status'].'</div>';
echo '<div class="list-group" hx-get="'.$reader_uri.'?action=list_user_status" hx-trigger="load, update_users_list from:body">';
echo '</div>';
echo '</div>';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['label_type'].'</div>';
echo '<div class="list-group" hx-get="'.$reader_uri.'?action=list_user_types" hx-trigger="load, update_users_list from:body">';
echo '</div>';
echo '</div>';


echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';