<?php

$writer_uri = '/admin/uploads/write/';
$btn_submit_text = $lang['update'];

if(is_int($get_media_id)) {

    $get_media = $db_content->get("se_media","*",[
        "media_id" => "$get_media_id"
    ]);

}

print_r($get_media);