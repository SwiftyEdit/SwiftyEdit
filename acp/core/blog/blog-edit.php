<?php

/**
 * post form in /admin/blog/edit/, /new/ and /duplicate/,
 * markup in acp/templates/blog/post-form.twig and blog/types/
 *
 * global variables
 * @var array $lang
 * @var string $languagePack
 * @var array $icon
 * @var array $se_settings
 * @var array $se_labels
 * @var array $se_upload_file_types
 * @var array $lang_codes
 * @var object $db_content
 * @var string $hidden_csrf_token
 * @var string $se_base_url
 * @var string $query
 * @var object $twig
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

// post types: label and the color class of the type (see _posts.scss), used by
// the type picker of new posts and the type badge in the header card
$post_types = [
    'm' => ['label' => $lang['post_type_message'], 'color' => 'color-message'],
    'i' => ['label' => $lang['post_type_image'], 'color' => 'color-image'],
    'g' => ['label' => $lang['post_type_gallery'], 'color' => 'color-gallery'],
    'v' => ['label' => $lang['post_type_video'], 'color' => 'color-video'],
    'l' => ['label' => $lang['post_type_link'], 'color' => 'color-link'],
    'f' => ['label' => $lang['post_type_file'], 'color' => 'color-file']
];

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

/*
 * header card above the form (components/edit-header.twig): title, status,
 * language, post type and the public url - the url follows the frontend search
 * (app/handlers/search.php): the stored canonical url, else detail page + slug + id.
 * New posts get the post type picker inside the card instead.
 */
$header_status = ['1' => 'public', '2' => 'draft'][(string) ($post_data['post_status'] ?? '')] ?? '';
$header_url = (string) ($post_data['post_canonical_url'] ?? '');
if($header_url == '' && $mode == 'update' && ($post_data['post_slug'] ?? '') != '') {
    $posts_target_page = se_get_type_of_use_pages('display_post');
    if(($posts_target_page['page_permalink'] ?? '') != '') {
        $header_url = rtrim($se_base_url, '/') . '/' . $posts_target_page['page_permalink'] . basename($post_data['post_slug']) . '-' . $post_id . '.html';
    }
}

$header = [
    'mode' => ($mode == 'update') ? 'edit' : 'new',
    'title' => $decode($post_data['post_title'] ?? ''),
    'status' => $header_status,
    'status_label' => $header_status != '' ? $lang['status_' . $header_status] : '',
    'badges' => [
        strtoupper(($post_data['post_lang'] ?? '') ?: $languagePack),
        $post_types[$post_type_form]['label'] ?? ''
    ],
    'url' => $header_url,
    'url_display' => preg_replace('#^https?://#', '', $header_url)
];

echo $twig->render($form_template, [
    'header' => $header,
    'post_types' => $post_types,
    'writer_uri' => $writer_uri,
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
    'canonical_docs_link' => se_print_docs_link("04-03-post-seo.md", null, "canonical-url"),
    'addon_module_forms' => se_render_record_addons('post', $record_data)
]);
