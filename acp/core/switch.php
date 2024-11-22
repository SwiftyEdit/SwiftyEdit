<?php

$se_sections = [
    "pages","snippets","shortcodes",
    "addons","users","categories",
    "settings","shop","events",
    "blog","inbox","uploads", "dashboard"
];

$se_section = 'dashboard';
$maininc = "dashboard/router.php";

if(in_array($se_path[0], $se_sections)) {
    $se_section = $se_path[0];
    $maininc = $se_section."/router.php";
}