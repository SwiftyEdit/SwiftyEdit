<?php

$subinc = match (true) {
    str_starts_with($query, 'shop/orders/') => 'orders',
    str_starts_with($query, 'shop/filters/new/') => 'filters-edit',
    str_starts_with($query, 'shop/filters/edit/') => 'filters-edit',
    str_starts_with($query, 'shop/filters/') => 'filters',
    str_starts_with($query, 'shop/features/new/') => 'features-edit',
    str_starts_with($query, 'shop/features/edit/') => 'features-edit',
    str_starts_with($query, 'shop/features/') => 'features',
    str_starts_with($query, 'shop/options/new/') => 'options-edit',
    str_starts_with($query, 'shop/options/edit/') => 'options-edit',
    str_starts_with($query, 'shop/options/') => 'options',
    str_starts_with($query, 'shop/prices/') => 'prices',
    str_starts_with($query, 'shop/new/') => 'products-edit',
    str_starts_with($query, 'shop/duplicate/') => 'products-edit',
    str_starts_with($query, 'shop/edit/') => 'products-edit',
    str_starts_with($query, 'shop') => 'products-list',
    default => 'products-list'
};

if($_SESSION['drm_can_publish'] != "true"){
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include __DIR__.'/'.$subinc.'.php';
}

