<?php

/**
 * show number of up- and downvotes
 */

// Read-only w.r.t. $_SESSION - safe to release the session lock immediately.
// This GET fires alongside other hx-trigger="load" widgets on the same
// page, all sharing one PHPSESSID; without an early close here, the
// default file session handler would force them to queue up and run one
// at a time instead of concurrently. Do not add $_SESSION writes below
// without removing this first.
session_write_close();

$allowed_section = ['p', 'b', 'e', 's'];
$section = '';
if(in_array($_GET['section'], $allowed_section)) {
    $section = $_GET['section'];
}

if(isset($_REQUEST['upv'])) {
    $id = (int) $_REQUEST['upv'];
    $votes = se_get_votes('upv',$id,$section);
}
if(isset($_REQUEST['dnv'])) {
    $id = (int) $_REQUEST['dnv'];
    $votes = se_get_votes('dnv',$id,$section);
}
echo $votes;