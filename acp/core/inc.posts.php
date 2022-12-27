<?php
//prohibit unauthorized access
require __DIR__.'/access.php';


switch ($sub) {

	case "blog-list":
		$subinc = "posts.list";
		break;

    case "edit":
	case "blog-edit":
		$subinc = "posts.edit";
		break;

		
	default:
		$subinc = "posts.list";
		break;
}

include $subinc.'.php';