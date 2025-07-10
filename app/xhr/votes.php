<?php

/**
 * show number of up- and downvotes
 */

$allowed_section = ['p', 'b', 'e'];
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