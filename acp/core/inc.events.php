<?php
//prohibit unauthorized access
require 'core/access.php';

switch ($sub) {

    case "edit":
    case "events-edit":
        $subinc = "events.edit";
        break;

    case "bookings":
        $subinc = "events.bookings";
        break;

    case "list":
    case "events-list":
    default:
        $subinc = "events.list";
        break;
}

include $subinc.'.php';