<?php
//prohibit unauthorized access
require __DIR__ . '/access.php';


switch ($sub) {

    case "list":
    case "user-list":
        $subinc = "user.list";
        $sub_active[0] = "submenu_selected";
        break;

    case "edit":
        $subinc = "user.edit";
        $sub_active[1] = "submenu_selected";
        break;

    case "new":
        $subinc = "user.edit";
        $sub_active[2] = "submenu_selected";
        break;

    case "user-groups":
        $subinc = "user.groups";
        $sub_active[4] = "submenu_selected";
        break;

    default:
        $subinc = "user.list";
        $sub_active[0] = "submenu_selected";
        break;

}


if ($_SESSION['acp_user'] != "allowed" and $subinc == "user.edit") {
    $subinc = "no_access";
}

if ($_SESSION['acp_user'] != "allowed" and $subinc == "user.groups") {
    $subinc = "no_access";
}


include './core/' . $subinc . '.php';
