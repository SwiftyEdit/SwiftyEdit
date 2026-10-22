<?php
/**
 * list all images from assets/images/
 * used in tinyMCE's filebrowser
 */
// production: don't show errors, but log serious ones (fatal, parse, exceptions)
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');
require_once __DIR__.'/../../../../../app/session.php';
se_session_start();

if(($_SESSION['user_class'] ?? '') !== 'administrator'){
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