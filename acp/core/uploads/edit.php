<?php

$writer_uri = '/admin/uploads/write/';
$btn_submit_text = $lang['update'];

$set_lang = $languagePack;
if(isset($_POST['set_lang'])) {
    $set_lang = htmlspecialchars($_POST['set_lang'],  ENT_QUOTES, 'UTF-8');
    unset($media_data);
}

$form_tpl = file_get_contents('../acp/templates/media-edit-form.tpl');

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
        $message = '<div class="alert alert-success alert-auto-close">'.$lang['msg_success_db_changed'].'</div>';
    } else {
        $message = '<div class="alert alert-danger alert-auto-close">'.$lang['msg_error_db_changed'].$savedMedia.'</div>';
    }
    $form_tpl = str_replace('{message}', $message, $form_tpl);
} else {
    $form_tpl = str_replace('{message}', '', $form_tpl);
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
        $preview_src = '<p>Filetype: '.substr(strrchr($media_filename, "."), 1).'</p>';
        $realpath = $media_filename;
        $img_dimensions = '';
        $shortcode = 'file';
    } else {
        $media_filename_abs = str_replace("../images/", '/images/', $media_filename);
        $preview_src = '<img src="'. $media_filename_abs.'" class="img-fluid">';
        $realpath = $media_filename;
        list($img_width, $img_height) = getimagesize("assets$media_filename_abs");
        $img_dimensions = ' | '.$img_width.' x '.$img_height.' px';
        $shortcode = 'image';
    }

    $filesize = filesize("../$realpath");
    $rfilesize = readable_filesize(filesize("assets$media_filename_abs"));
    $lastedit = date('d.m.Y H:i',filemtime("assets$media_filename_abs"));

    // change language
    $langSwitch = '<div class="btn-group" role="group">';
    foreach($lang_codes as $langs) {
        $btn_status = '';
        if($langs == "$set_lang") { $btn_status = 'active'; }
        $langSwitch .= '<button type="submit" class="btn btn-default btn-sm '.$btn_status.'" name="set_lang" value="'.$langs.'">'.$langs.'</button>';
    }
    $langSwitch .= '</div>';
    $langSwitch .= '<input type="hidden" name="file" value="'.$media_filename.'">';


    // labels

    $cnt_labels = count($se_labels);
    $arr_checked_labels = explode(",", $media_data['media_labels']);

    for($i=0;$i<$cnt_labels;$i++) {
        $label_title = $se_labels[$i]['label_title'];
        $label_id = $se_labels[$i]['label_id'];
        $label_color = $se_labels[$i]['label_color'];

        if(in_array("$label_id", $arr_checked_labels)) {
            $checked_label = "checked";
        } else {
            $checked_label = "";
        }

        $checkbox_set_labels .= '<div class="form-check form-check-inline">';
        $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="media_labels[]" value="'.$label_id.'">';
        $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'" style="border-bottom: 1px solid '.$label_color.'">'.$label_title.'</label>';
        $checkbox_set_labels .= '</div>';
    }


    $form_tpl = str_replace('{media_labels}', $checkbox_set_labels, $form_tpl);

    $form_tpl = str_replace('{form_action}', "/admin/uploads/edit/", $form_tpl);
    $form_tpl = str_replace('{filename}', $media_filename, $form_tpl);
    $form_tpl = str_replace('{file}', $media_filename, $form_tpl);
    $form_tpl = str_replace('{basename}', basename($media_filename), $form_tpl);
    $form_tpl = str_replace('{realpath}', $realpath, $form_tpl);
    $form_tpl = str_replace('{rfilesize}', $rfilesize, $form_tpl);
    $form_tpl = str_replace('{image_dimensions}', $img_dimensions, $form_tpl);
    $form_tpl = str_replace('{edittime}', $lastedit, $form_tpl);
    $form_tpl = str_replace('{title}', $media_data['media_title'], $form_tpl);
    $form_tpl = str_replace('{description}', $media_data['media_description'], $form_tpl);
    $form_tpl = str_replace('{keywords}', $media_data['media_keywords'], $form_tpl);
    $form_tpl = str_replace('{text}', $media_data['media_text'], $form_tpl);
    $form_tpl = str_replace('{label_title}', $lang['label_title'], $form_tpl);
    $form_tpl = str_replace('{label_description}', $lang['label_description'], $form_tpl);
    $form_tpl = str_replace('{label_keywords}', $lang['label_keywords'], $form_tpl);
    $form_tpl = str_replace('{label_alt}', $lang['label_alt'], $form_tpl);
    $form_tpl = str_replace('{alt}', $media_data['media_alt'], $form_tpl);
    $form_tpl = str_replace('{label_url}', $lang['label_url'], $form_tpl);
    $form_tpl = str_replace('{url}', $media_data['media_url'], $form_tpl);
    $form_tpl = str_replace('{label_priority}', $lang['label_priority'], $form_tpl);
    $form_tpl = str_replace('{priority}', $media_data['media_priority'], $form_tpl);
    $form_tpl = str_replace('{label_license}', $lang['label_license'], $form_tpl);
    $form_tpl = str_replace('{license}', $media_data['media_license'], $form_tpl);
    $form_tpl = str_replace('{label_credits}', $lang['label_credits'], $form_tpl);
    $form_tpl = str_replace('{version}', $media_data['media_version'], $form_tpl);
    $form_tpl = str_replace('{label_version}', $lang['label_version'], $form_tpl);
    $form_tpl = str_replace('{credit}', $media_data['media_credit'], $form_tpl);
    $form_tpl = str_replace('{label_notes}', $lang['notes'], $form_tpl);
    $form_tpl = str_replace('{notes}', $media_data['notes'], $form_tpl);
    $form_tpl = str_replace('{label_text}', $lang['label_text'], $form_tpl);
    $form_tpl = str_replace('{preview}', $preview_src, $form_tpl);
    $form_tpl = str_replace('{save}', $lang['save'], $form_tpl);
    $form_tpl = str_replace('{set_lang}', $set_lang, $form_tpl);
    $form_tpl = str_replace('{filesize}', $filesize, $form_tpl);
    $form_tpl = str_replace('{lang_switch}', $langSwitch, $form_tpl);
    $form_tpl = str_replace('{shortcode}', $shortcode, $form_tpl);
    $form_tpl = str_replace('{token}',$_SESSION['token'],$form_tpl);



    echo $form_tpl;

    print_r($get_media);
}

