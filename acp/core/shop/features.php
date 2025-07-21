<?php

$reader_uri = '/admin/xhr/shop/read/';
$writer_uri = '/admin/xhr/shop/write/';


echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '<a href="/admin/shop/features/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getFeatures" class="" hx-post="'.$reader_uri.'?action=list_features" hx-trigger="load, update_feature_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

// sidebar

echo '</div>';
echo '</div>';