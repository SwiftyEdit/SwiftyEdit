<?php

/**
 * edit the data of a file in /admin/uploads/edit/, markup in acp/templates/uploads/media-edit-form.twig
 * the list posts the file (and optionally a language) here, the form posts back to save
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var object $twig
 * @var string $languagePack
 * @var array $lang_codes
 * @var array $se_labels
 */

// media DB paths are stored as "../images/..." / "../files/...", the files are in public/assets/
$media_filename = se_filter_filepath((string) ($_POST['file'] ?? ''));
$media_file_src = false;
if(str_starts_with($media_filename, '../images/') || str_starts_with($media_filename, '../files/')) {
    $media_file_src = se_resolve_within(SE_PUBLIC.'/assets', substr($media_filename, 3));
}

if($media_file_src === false || !is_file($media_file_src)) {
    echo $twig->render('uploads/media-edit-form.twig', ['file' => null]);
    return;
}

// public url and the path se_write_media_data() expects, e.g. /images/example.jpg
$media_filename_abs = substr($media_filename, 2);

$set_lang = $_POST['set_lang'] ?? $languagePack;
if(!in_array($set_lang, $lang_codes)) {
    $set_lang = $languagePack;
}

$message = null;

if(isset($_POST['save'])) {

    $savedMedia = se_write_media_data([
        'filename' => $media_filename_abs,
        'title' => $_POST['title'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'keywords' => $_POST['keywords'] ?? '',
        'text' => $_POST['text'] ?? '',
        'url' => $_POST['url'] ?? '',
        'alt' => $_POST['alt'] ?? '',
        'lang' => $set_lang,
        'credit' => $_POST['credit'] ?? '',
        'priority' => $_POST['priority'] ?? 0,
        'license' => $_POST['license'] ?? '',
        'lastedit' => time(),
        'filesize' => filesize($media_file_src),
        'version' => $_POST['version'] ?? '',
        'labels' => $_POST['media_labels'] ?? ''
    ]);
    if($savedMedia == 'success') {
        $message = ['type' => 'success', 'text' => $lang['msg_success_db_changed']];
    } else {
        $message = ['type' => 'danger', 'text' => $lang['msg_error_db_changed'].' '.$savedMedia];
    }
}

$media_data = $db_content->get("se_media", "*", [
    "media_file" => $media_filename,
    "media_lang" => $set_lang
]);

$is_image = str_starts_with($media_filename, '../images/');
$img_dimensions = '';
if($is_image) {
    $image_size = getimagesize($media_file_src);
    if($image_size !== false) {
        $img_dimensions = $image_size[0].' x '.$image_size[1];
    }
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
        'extension' => pathinfo($media_filename, PATHINFO_EXTENSION),
        'is_image' => $is_image,
        'dimensions' => $img_dimensions,
        'shortcode' => $is_image ? 'image' : 'file',
        'filesize' => readable_filesize(filesize($media_file_src)),
        'lastedit' => se_format_datetime(filemtime($media_file_src))
    ]
]);
