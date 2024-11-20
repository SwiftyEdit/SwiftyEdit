<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['calendar_event'].' '.$lang['nav_btn_events'];
echo '<a href="/admin/events/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin/events/read/';
$writer_uri = '/admin/events/write/';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getEvents" class="" hx-post="'.$reader_uri.'?action=list_events" hx-trigger="load, update_events_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card p-3">';

echo '<form hx-post="'.$writer_uri.'" hx-swap="none" hx-on--after-request="this.reset()" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="snippets_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div id="keyList" hx-post="'.$reader_uri.'?action=list_keywords" hx-trigger="load, update_snippet_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']"></div>';


echo '</div>';

echo '</div>';
echo '</div>';