<?php

$reader_uri = '/admin-xhr/shop/read/';
$writer_uri = '/admin-xhr/shop/write/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_price_groups'];
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo '<div class="card p-3">';
echo '<div id="getPriceGroups" class="" hx-post="'.$reader_uri.'?action=list_price_groups" hx-trigger="load, update_price_groups from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-6">';
echo '<div class="card p-3">';


echo '<div id="PriceGroupForm" class="" hx-get="'.$reader_uri.'?action=show_price_groups_form" hx-trigger="load, show_price_groups_form from:body" hx-include="[name=\'csrf_token\']">';
echo '</div>';


echo '</div>';

echo '</div>';
echo '</div>';