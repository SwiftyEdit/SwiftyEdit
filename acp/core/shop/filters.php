<?php

$reader_uri = '/admin/shop/read/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '<form action="/admin/shop/filters/edit/" method="post" class="d-inline ms-auto">';
echo '<button class="btn btn-default" name="edit_group" value="new">'.$icon['plus'].' '.$lang['btn_new_group'].'</button>';
echo '<button class="btn btn-default" name="edit_value" value="new">'.$icon['plus'].' '.$lang['btn_new_value'].'</button>';
echo  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo  '</form>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getFilter" class="" hx-post="'.$reader_uri.'?action=list_filters" hx-trigger="load, update_filter_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

// sidebar

echo '</div>';
echo '</div>';