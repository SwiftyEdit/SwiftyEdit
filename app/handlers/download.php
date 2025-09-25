<?php

/**
 * SwiftyEdit frontend
 * start download from se_media
 *
 * @var object $db_content medoo database object
 */

$download_file = basename($_POST['file']);
$get_target_file = '../files/'.$download_file;

/* get file data from database se_media */
$target_file = $db_content->get("se_media", "*", [
    "media_file" => $get_target_file
]);

/* update counter */
$counter = ((int) $target_file['media_file_hits'])+1;
$update_file = $db_content->update("se_media", [
    "media_file_hits" => $counter
],[
    "media_file" => $get_target_file
]);

/* we take the filepath from the database, so we have no trouble if someone trying to inject evil filepath */
$download_file = SE_PUBLIC.'/assets'.str_replace('../files/','/files/',$target_file['media_file']);

if(is_file($download_file)) {

    header('Content-Description: File Transfer');
    header('Content-Type: ' . mime_content_type($download_file));
    header('Content-Disposition: attachment; filename="'.basename($download_file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($download_file));
    readfile($download_file);

    exit;
}