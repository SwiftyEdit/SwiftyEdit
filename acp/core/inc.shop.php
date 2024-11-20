<?php

$subinc = match (true) {
    str_starts_with($query, 'shop/orders/') => 'orders',
    str_starts_with($query, 'shop/filter/') => 'filters',
    str_starts_with($query, 'shop/features/') => 'features',
    str_starts_with($query, 'shop/prices/') => 'prices',
    str_starts_with($query, 'shop') => 'products-list',
    default => 'products-list'
};


if($_SESSION['acp_system'] != "allowed"){
    $subinc = "no_access";
}

include 'shop/'.$subinc.'.php';