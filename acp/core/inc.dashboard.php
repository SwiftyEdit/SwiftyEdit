<?php

/*
 * SwiftyEdit | dashboard
 */

//prohibit unauthorized access
require __DIR__.'/access.php';

if(isset($_POST['delete_cache'])) {
    se_delete_smarty_cache('all');
}

if(isset($_POST['update_index'])) {
    se_update_bulk_page_index();
}

include 'dashboard.checks.php';
include 'dashboard.top.php';
include 'dashboard.addons.php';
