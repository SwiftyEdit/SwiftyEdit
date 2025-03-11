<?php
/**
 * list all images from assets/images/
 * used in tinyMCE's filebrowser
 */
error_reporting(0);
session_start();

if(!isset($_SESSION['user_class']) && $_SESSION['user_class'] != 'administrator'){
    die();
}

require '../../../../../config.php';
require '../../../../../acp/core/functions.php';

$path = "../../../images";
$images = [];
$counter = 0;

$img = se_scandir_rec($path);
foreach ($img as $image) {
    $image = str_replace('../../../images/', '/images/', $image);
    $images[$counter]['title'] = $image;
    $images[$counter]['value'] = $image;
    $counter++;
}

ksort($images);

header('Content-type: text/javascript');
header('pragma: no-cache');
header('expires: 0'); // i.e. contents have already expired
echo json_encode($images);