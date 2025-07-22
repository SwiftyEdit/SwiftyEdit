<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['comments'].' '.$lang['nav_btn_comments'];
echo '</div>';


$reader_uri = '/admin-xhr/inbox/read/';
$writer_uri = '/admin-xhr/inbox/write/';

echo '<div id="inbox-response"></div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getComments" class="" hx-post="'.$reader_uri.'?action=list_comments" hx-trigger="load, update_comments_list from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['filter'].'</div>';
echo '<div class="card-body">';
echo '<form hx-post="'.$writer_uri.'" hx-swap="none" hx-on--after-request="this.reset()" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="comments_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches_comments" hx-trigger="load, changed, update_comments_list from:body"></div>';

echo '</div>';
echo '</div>';
echo '</div>';