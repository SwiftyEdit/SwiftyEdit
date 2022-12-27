<?php
//prohibit unauthorized access
require 'core/access.php';

switch ($sub) {

    case "votings":
		$subinc = "inbox.votings";
		break;

    case "mailbox":
        $subinc = "inbox.mail";
        break;

    case "comments":
    default:
		$subinc = "inbox.comments";
		break;
}

include $subinc.'.php';