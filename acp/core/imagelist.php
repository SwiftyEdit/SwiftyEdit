<?php
/**
 * list all images from content/images
 * used in tinyMCE's filebrowser
 */
error_reporting(0);
session_start();

include '../../config.php';
include 'functions.php';

$path = "../../$img_path";
$abspath = '/' . $img_path;

$images = array();
$counter = 0;

$img = se_scandir_rec($path);
foreach ($img as $image) {
    $image = str_replace('../../content/images/', '', $image);

    $images[$counter]['title'] = $image;
    $images[$counter]['value'] = $abspath . "/" . $image;
    $counter++;
}

// Let PHP do the sorting an not the OS
ksort($images);


// Make output a real JavaScript file!
// browser will now recognize the file as a valid JS file
header('Content-type: text/javascript');
// prevent browser from caching
header('pragma: no-cache');
header('expires: 0'); // i.e. contents have already expired


echo json_encode($images);