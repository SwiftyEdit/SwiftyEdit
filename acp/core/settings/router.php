<?php

$subinc = match (true) {
    str_starts_with($query, 'settings/labels/') => 'labels',
    str_starts_with($query, 'settings/posts/') => 'posts',
    str_starts_with($query, 'settings/events/') => 'events',
    str_starts_with($query, 'settings/shop/') => 'shop',
    str_starts_with($query, 'settings/database/') => 'database',
    default => 'general'
};


if($_SESSION['acp_system'] != "allowed"){
	$subinc = "no_access";
}

include $subinc.'.php';