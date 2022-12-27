<?php

//prohibit unauthorized access
require __DIR__."/access.php";

/**
 * including vars
 * tn -> mainscripts
 * sub -> subscripts
 */

if(!isset($_GET['tn'])){
	$tn = "dashboard";
} else {
	$tn = clean_vars($_GET['tn']);
}

if(!isset($_GET['sub'])){
	$sub = "";
} else {
	$sub = clean_vars($_GET['sub']);
}

if(!isset($_GET['a'])){
    $a = "";
} else {
    $a = clean_vars($_GET['a']);
}


switch ($tn) {

    case "pages":
		$maininc = "inc.pages";
		break;

    case "moduls":
    case "addons":
		$maininc = "inc.addons";
		break;

    case "filebrowser":
		$maininc = "inc.filebrowser";
		$headinc = "head.filebrowser.dat";
		break;
		
	case "user":
		$maininc = "inc.user";
		break;
		
	case "system":
		$maininc = "inc.system";
		break;

	case "events":
		$maininc = "inc.events";
		break;

	case "posts":
		$maininc = "inc.posts";
		break;

    case "shop":
        $maininc = "inc.shop";
        break;

	case "inbox":
		$maininc = "inc.inbox";
		break;

    case "dashboard":
    default:
		$maininc = "inc.dashboard";
		break;
}