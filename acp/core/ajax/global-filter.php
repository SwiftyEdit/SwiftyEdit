<?php

require '_include.php';

$set_lang = $_SESSION['lang'];
if(isset($_REQUEST['set_lang'])) {
    $set_lang = $_REQUEST['set_lang'];
}



if(isset($_POST)) {

    if(isset($_POST['set_label'])) {
        $_SESSION['global_filter_label'] = json_encode($_POST['set_label']);
    } else {
        $_SESSION['global_filter_label'] = '';
    }

    if(isset($_POST['set_lang'])) {
        $_SESSION['global_filter_languages'] = json_encode($_POST['set_lang']);
    } else {
        $_SESSION['global_filter_languages'] = '';
    }

    if(isset($_POST['set_status'])) {
        $_SESSION['global_filter_status'] = json_encode($_POST['set_status']);
    } else {
        $_SESSION['global_filter_status'] = '';
    }

    echo '<div class="alert alert-info fade show alert-auto-close">';
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}