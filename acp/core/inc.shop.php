<?php
//prohibit unauthorized access
require 'core/access.php';


switch ($sub) {

    case "shop-list":
        $subinc = "shop.list";
        break;

    case "edit":
    case "shop-edit":
        $subinc = "shop.edit";
        break;

    case "shop-features":
        $subinc = "shop.features";
        break;

    case "shop-filter":
        $subinc = "shop.filter";
        break;

    case "shop-orders":
        $subinc = "shop.orders";
        break;

    default:
        $subinc = "shop.list";
        break;

}


include $subinc.'.php';
