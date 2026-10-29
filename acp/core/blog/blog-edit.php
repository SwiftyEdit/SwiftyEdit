<?php

/**
 * @var array $lang
 * @var string $languagePack
 * @var array $icon
 * @var array $se_settings
 * @var array $se_labels
 * @var array $se_upload_file_types
 * @var array $lang_codes
 * @var array $db_content
 * @var array $hidden_csrf_token
 * @var string $se_base_url
 * @var string $query
 */

$writer_uri = '/admin-xhr/blog/write/';
$reader_uri = '/admin-xhr/blog/read/';
$mode = 'new';

// check if last part of url is an id
$path = parse_url($query, PHP_URL_PATH);
$segments = explode('/', rtrim($path, '/'));
$lastSegment = end($segments);

if(is_numeric($lastSegment)) {
    $post_id = (int) $lastSegment;
    $mode = 'update';
    $post_data = se_get_post_data($post_id);
}

if(is_numeric($_POST['post_id'])) {
    $post_id = (int) $_POST['post_id'];
    $mode = 'update';
    $post_data = se_get_post_data($post_id);
}

$post_type_form = $_SESSION['post_type_form'] ?? 'm';
if(isset($post_data['post_type'])) {
    $post_type_form = $post_data['post_type'];
}

$record_data = $post_data ?? [];

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['files'].' '.$lang['nav_btn_blog'];
echo '<div class="d-flex ms-auto">'.$mode.' '.$post_type_form.'</div>';
echo '</div>';


if($mode == 'new') {
    echo '<form hx-post="' . $writer_uri . '">';
    echo '<div class="card mb-3">';
    echo '<div class="card-header">' . $lang['label_select_post_type'] . '</div>';
    echo '<div class="btn-group d-flex" role="group">';
    echo '<button name="set_post_type" value="m" class="btn btn-default w-100 '.($post_type_form == "m" ? 'active' :'').'"><span class="color-message">' . $icon['plus'] . '</span> ' . $lang['post_type_message'] . '</button>';
    echo '<button name="set_post_type" value="i" class="btn btn-default w-100 '.($post_type_form == "i" ? 'active' :'').'"><span class="color-image">' . $icon['plus'] . '</span> ' . $lang['post_type_image'] . '</button>';
    echo '<button name="set_post_type" value="g" class="btn btn-default w-100 '.($post_type_form == "g" ? 'active' :'').'"><span class="color-gallery">' . $icon['plus'] . '</span> ' . $lang['post_type_gallery'] . '</button>';
    echo '<button name="set_post_type" value="v" class="btn btn-default w-100 '.($post_type_form == "v" ? 'active' :'').'"><span class="color-video">' . $icon['plus'] . '</span> ' . $lang['post_type_video'] . '</button>';
    echo '<button name="set_post_type" value="l" class="btn btn-default w-100 '.($post_type_form == "l" ? 'active' :'').'"><span class="color-link">' . $icon['plus'] . '</span> ' . $lang['post_type_link'] . '</button>';
    echo '<button name="set_post_type" value="f" class="btn btn-default w-100 '.($post_type_form == "f" ? 'active' :'').'"><span class="color-file">' . $icon['plus'] . '</span> ' . $lang['post_type_file'] . '</button>';
    echo '</div>';
    echo '</div>';
    echo $hidden_csrf_token;
    echo '</form>';
}


// one template per post type, all of them extend blog/post-form.twig
$form_templates = [
    'm' => 'blog/types/message.twig',
    'i' => 'blog/types/image.twig',
    'g' => 'blog/types/gallery.twig',
    'v' => 'blog/types/video.twig',
    'l' => 'blog/types/link.twig',
    'f' => 'blog/types/file.twig'
];
$form_template = $form_templates[$post_type_form] ?? $form_templates['m'];

$post_data = $post_data ?? [];

// text fields are stored html-encoded (blog data-writer), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

// select main category page
$all_blog_pages = $db_content->select("se_pages","page_permalink",[
    "page_posts_types[~]" => ["m","i","g","v","l","f"]
]);
array_unshift($all_blog_pages, "default");

$main_category_options = [];
foreach($all_blog_pages as $permalink) {
    $main_category_options[] = [
        'value' => $decode($permalink),
        'label' => ($permalink == 'default') ? $lang['label_use_default'] : $decode($permalink),
        'selected' => (($post_data['main_category_slug'] ?? '') == "$permalink")
    ];
}

// the form helpers get the stored (html-encoded) values, like before
$inputs = [];

$inputs['teaser'] = se_print_form_input([
    "input_name" => "post_teaser",
    "input_value" => $post_data['post_teaser'] ?? null,
    "label" => $lang['label_description'],
    "type" => "textarea",
    "mode" => "wysiwyg"
]);

$inputs['text'] = se_print_form_input([
    "input_name" => "post_text",
    "input_value" => $post_data['post_text'] ?? null,
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
]);

// select language
$lang_options = [];
foreach(get_all_languages() as $langs) {
    if(!in_array($langs['lang_folder'],$lang_codes)) {
        continue;
    }
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$inputs['language'] = se_print_form_input([
    "input_name" => "post_lang",
    "input_value" => $post_data['post_lang'] ?? $languagePack,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
]);

$inputs['status'] = se_print_form_input([
    "input_name" => "post_status",
    "input_value" => $post_data['post_status'] ?? '2',
    "label" => $lang['label_status'],
    "options" => [
        $lang['status_public'] => '1',
        $lang['status_draft'] => '2'
    ],
    "type" => "select"
]);

$post_hide_detail_page = $post_data['post_hide_detail_page'] ?? '0';
$inputs['hide_detail_page'] = se_print_form_input([
    "input_name" => "posts_hide_detail_page",
    "input_value" => $post_hide_detail_page,
    "label" => $lang['label_posts_hide_detail_page'],
    "type" => "checkbox",
    "status" => $post_hide_detail_page == "1" ? 'checked' :''
]);

$inputs['comments'] = se_print_form_input([
    "input_name" => "post_comments",
    "input_value" => $post_data['post_comments'] ?? '2',
    "label" => $lang['label_comments'],
    "options" => [
        $lang['yes'] => '1',
        $lang['no'] => '2'
    ],
    "type" => "select"
]);

$inputs['votings'] = se_print_form_input([
    "input_name" => "post_votings",
    "input_value" => $post_data['post_votings'] ?? $se_settings['posts_default_votings'],
    "label" => $lang['label_votings'],
    "options" => [
        $lang['label_votings_status_off'] => '1',
        $lang['label_votings_status_registered'] => '2',
        $lang['label_votings_status_global'] => '3'
    ],
    "type" => "select"
]);

$inputs['rss'] = se_print_form_input([
    "input_name" => "post_rss",
    "input_value" => $post_data['post_rss'] ?? 'off',
    "label" => $lang['label_rss'],
    "options" => [
        $lang['yes'] => 'on',
        $lang['no'] => 'off'
    ],
    "type" => "select"
]);

// categories and labels
$checked_categories = [];
if(($post_data['post_categories'] ?? '') != '') {
    $checked_categories = explode("<->", $post_data['post_categories']);
}
$checked_labels = [];
if(($post_data['post_labels'] ?? '') != '') {
    $checked_labels = explode(",", $post_data['post_labels']);
}

// release date
if(($post_data['post_releasedate'] ?? 0) > 0) {
    $post_releasedate = date('Y-m-d H:i', $post_data['post_releasedate']);
} else {
    $post_releasedate = date('Y-m-d H:i', time());
}

// select for files
$file_options = [
    ['value' => '', 'label' => '-- '.$lang['label_select_no_file'].' --', 'selected' => false]
];
$files_directory = SE_PUBLIC.'/assets/files';
foreach(se_scandir_rec($files_directory) as $file) {
    //$se_upload_file_types is set in config.php
    $file_info = pathinfo($file);
    if(in_array($file_info['extension'],$se_upload_file_types)) {
        $short_path = str_replace("$files_directory","",$file);
        $file_options[] = [
            'value' => $short_path,
            'label' => $short_path,
            'selected' => (($post_data['post_file_attachment'] ?? '') == $short_path)
        ];
    }
}

// the gallery upload needs an existing gallery
$gallery_upload = null;
if(($post_data['post_type'] ?? '') == 'g') {
    $gallery_upload = [
        'post_year' => date('Y', (int) $post_data['post_date']),
        'max_img_width' => $se_settings['maximagewidth'],
        'max_tmb_width' => $se_settings['maxtmbwidth'],
        'max_img_height' => $se_settings['maximageheight'],
        'max_tmb_height' => $se_settings['maxtmbheight']
    ];
}

/* image widget */
$images = [];
foreach(array_filter(explode("<->", $post_data['post_images'] ?? '')) as $image) {
    $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
    $image_src = str_replace('../images/','/images/',$image_src);
    $images[] = [
        'path' => $image,
        'src' => $image_src,
        'name' => basename($image)
    ];
}

echo '<div id="formResponse"></div>';
echo $twig->render($form_template, [
    'post' => array_map($decode, $post_data),
    'post_type' => $post_type_form,
    'mode' => $mode,
    'inputs' => $inputs,
    'images' => $images,
    'categories' => se_get_category_checklist($checked_categories),
    'labels' => se_get_label_checklist($checked_labels),
    'content_tags' => $decode(se_tags_csv_for_content('post', (int) ($post_data['post_id'] ?? 0))),
    'releasedate' => $post_releasedate,
    'main_category_options' => $main_category_options,
    'file_options' => $file_options,
    'gallery_upload' => $gallery_upload,
    'se_base_url' => $se_base_url,
    'canonical_docs_link' => se_print_docs_link("04-00-blog.md", null, "canonical-url"),
    'addon_module_forms' => se_render_record_addons('post', $record_data)
]);
