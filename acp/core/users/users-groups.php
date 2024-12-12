<?php

$reader_uri = '/admin/users/read/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['users'].' '.$lang['nav_btn_user_groups'];
echo '<a href="/admin/users/groups/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-3">';

echo '<div class="card p-3">';
echo '<div id="getUserGroups" class="" hx-get="'.$reader_uri.'?action=list_usergroups" hx-trigger="load, update_groups_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-9">';

echo '<div class="card p-3">';
echo '<div id="groupForm" class="" hx-get="'.$reader_uri.'?action=show_groups_form" hx-trigger="load, show_groups_form from:body" hx-include="[name=\'csrf_token\']">';
echo '</div>';
echo'</div>';

echo '</div>';
echo '</div>';