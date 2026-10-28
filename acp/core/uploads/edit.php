<?php

$set_lang = $languagePack;
if(isset($_POST['set_lang'])) {
    $set_lang = htmlspecialchars($_POST['set_lang'],  ENT_QUOTES, 'UTF-8');
    unset($media_data);
}

$message = null;

if(isset($_POST['save'])) {

    $media_filename = se_filter_filepath($_POST['file']);

    if(str_starts_with($media_filename, "../files")) {
        $media_filename_abs = str_replace("../files/", '/files/', $media_filename);
    } else {
        $media_filename_abs = str_replace("../images/", '/images/', $media_filename);
    }

    $filesize = filesize('assets'.$media_filename_abs);

    $filedata = array(
        'filename' => $media_filename_abs,
        'title' => $_POST['title'],
        'notes' => $_POST['notes'],
        'keywords' => $_POST['keywords'],
        'text' => $_POST['text'],
        'url' => $_POST['url'],
        'alt' => $_POST['alt'],
        'lang' => $_POST['set_lang'],
        'credit' => $_POST['credit'],
        'priority' => $_POST['priority'],
        'license' => $_POST['license'],
        'lastedit' => time(),
        'filesize' => $filesize,
        'version' => $_POST['version'],
        'labels' => $_POST['media_labels']
    );

    $savedMedia = se_write_media_data($filedata);
    if($savedMedia == 'success') {
        $message = ['type' => 'success', 'text' => $lang['msg_success_db_changed']];
    } else {
        $message = ['type' => 'danger', 'text' => $lang['msg_error_db_changed'].$savedMedia];
    }
}


if(isset($_POST['file'])) {

    $media_filename = se_filter_filepath($_POST['file']);
    $media_data = $db_content->get("se_media","*",[
        "media_file" => "$media_filename",
        "media_lang" => "$set_lang"
    ]);

    echo '<div class="subHeader d-flex align-items-center">';
    echo '<a class="btn btn-default btn-sm" href="/admin/uploads/">'.$icon['arrow_left'].'</a> ';
    echo '<span class="ms-3">' . htmlspecialchars($media_filename,  ENT_QUOTES, 'UTF-8').'</span>';
    echo '</div>';


    if(str_starts_with($media_filename, "../files")) {
        $media_filename_abs = str_replace("../files/", '/files/', $media_filename);
        $is_image = false;
        $img_dimensions = '';
        $shortcode = 'file';
    } else {
        $media_filename_abs = str_replace("../images/", '/images/', $media_filename);
        $is_image = true;
        list($img_width, $img_height) = getimagesize("assets$media_filename_abs");
        $img_dimensions = $img_width.' x '.$img_height;
        $shortcode = 'image';
    }

    /*
     * se_write_media_data() stores the values html-encoded
     * (se_return_clean_value), decode them here because Twig escapes on output
     */
    $media_fields = ['title','alt','priority','keywords','url','text','license','credit','version','notes'];
    $media = [];
    foreach($media_fields as $field) {
        $media[$field] = html_entity_decode((string) ($media_data['media_'.$field] ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // labels
    $checked_labels = explode(",", (string) ($media_data['media_labels'] ?? ''));
    $labels = [];
    foreach($se_labels as $label) {
        $labels[] = [
            'id' => $label['label_id'],
            'title' => html_entity_decode((string) $label['label_title'], ENT_QUOTES, 'UTF-8'),
            'color' => $label['label_color'],
            'checked' => in_array((string) $label['label_id'], $checked_labels)
        ];
    }

    echo $twig->render('uploads/media-edit-form.twig', [
        'message' => $message,
        'media' => $media,
        'labels' => $labels,
        'lang_codes' => $lang_codes,
        'set_lang' => $set_lang,
        'file' => [
            'path' => $media_filename,
            'src' => $media_filename_abs,
            'basename' => basename($media_filename),
            'extension' => substr(strrchr($media_filename, "."), 1),
            'is_image' => $is_image,
            'dimensions' => $img_dimensions,
            'shortcode' => $shortcode,
            'filesize' => readable_filesize(filesize("assets$media_filename_abs")),
            'lastedit' => date('d.m.Y H:i', filemtime("assets$media_filename_abs"))
        ]
    ]);
}
