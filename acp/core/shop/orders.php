<?php

$reader_uri = '/admin/shop/read/';
$writer_uri = '/admin/shop/write/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'].' / '.$lang['nav_btn_orders'];
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getOrders" class="" hx-post="/admin/shop/read/?action=list_orders" hx-trigger="load, update_orders_list from:body" hx-include="[name=\'csrf_token\']">';
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
echo '<input class="form-control" type="text" name="orders_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches_orders" hx-trigger="load, changed, update_orders_list from:body"></div>';

echo '</div>';
echo '</div>';
echo '</div>';