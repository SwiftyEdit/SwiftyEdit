<?php

/**
 * @var array $lang
 * @var string $languagePack
 * @var array $se_settings
 * @var array $lang_codes
 * @var object $db_content
 * @var object $twig
 * @var string $se_base_url
 * @var string $query - the current url
 */

$writer_uri = '/admin-xhr/events/write/';
$reader_uri = '/admin-xhr/events/read/';

$post_data = [];
$mode = 'new';
$id = null;

// the id comes from the url (/admin/events/edit/5/, e.g. after saving a new event)
// or from the edit button in the list (POST)
$path = parse_url($query, PHP_URL_PATH);
$segments = explode('/', rtrim($path, '/'));
$lastSegment = end($segments);
if(is_numeric($lastSegment)) {
    $id = (int) $lastSegment;
}
if(is_numeric($_POST['id'] ?? null)) {
    $id = (int) $_POST['id'];
}

if(isset($id)) {
    $mode = 'update';
    $post_data = se_get_event_data($id) ?? [];
}

// text fields are stored html-encoded (events data-writer), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

// the form helpers get the stored (html-encoded) values, like before
$inputs = [];

foreach(['teaser' => 'teaser', 'text' => 'text', 'price_note' => 'event_price_note'] as $key => $field) {
    $inputs[$key] = se_print_form_input([
        "input_name" => $field,
        "input_value" => $post_data[$field] ?? null,
        "label" => ' ',
        "type" => "textarea",
        "mode" => "wysiwyg"
    ]);
}

// select language
$lang_options = [];
foreach(get_all_languages() as $langs) {
    if(!in_array($langs['lang_folder'],$lang_codes)) {
        continue;
    }
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$inputs['language'] = se_print_form_input([
    "input_name" => "event_lang",
    "input_value" => $post_data['event_lang'] ?? $languagePack,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
]);

// main category page: any page that accepts event content (page_posts_types
// contains "e"), same pattern as the blog's "Hauptkategorie-Seite" selector.
$all_event_pages = $db_content->select("se_pages", "page_permalink", [
    "page_posts_types[~]" => "e"
]);
array_unshift($all_event_pages, "default");

$main_category_options = [];
foreach($all_event_pages as $permalink) {
    $main_category_options[] = [
        'value' => $decode($permalink),
        'label' => ($permalink == 'default') ? $lang['label_use_default'] : $decode($permalink),
        'selected' => (($post_data['main_category_slug'] ?? '') == "$permalink")
    ];
}

$inputs['status'] = se_print_form_input([
    "input_name" => "status",
    "input_value" => $post_data['status'] ?? '2',
    "label" => $lang['label_status'],
    "options" => [
        $lang['status_public'] => '1',
        $lang['status_draft'] => '2'
    ],
    "type" => "select"
]);

$inputs['comments'] = se_print_form_input([
    "input_name" => "comments",
    "input_value" => $post_data['comments'] ?? '2',
    "label" => $lang['label_comments'],
    "options" => [
        $lang['yes'] => '1',
        $lang['no'] => '2'
    ],
    "type" => "select"
]);

$inputs['votings'] = se_print_form_input([
    "input_name" => "votings",
    "input_value" => $post_data['votings'] ?? $se_settings['posts_default_votings'],
    "label" => $lang['label_votings'],
    "options" => [
        $lang['label_votings_status_off'] => '1',
        $lang['label_votings_status_registered'] => '2',
        $lang['label_votings_status_global'] => '3'
    ],
    "type" => "select"
]);

$inputs['rss'] = se_print_form_input([
    "input_name" => "rss",
    "input_value" => $post_data['rss'] ?? 'off',
    "label" => $lang['label_rss'],
    "options" => [
        $lang['yes'] => 'on',
        $lang['no'] => 'off'
    ],
    "type" => "select"
]);

// guestlist mode
$guestlist = $post_data['event_guestlist'] ?? '';
if($guestlist == '') {
    $guestlist = $se_settings['posts_default_guestlist'];
}

$inputs['guestlist'] = se_print_form_input([
    "input_name" => "event_guestlist",
    "input_value" => $guestlist,
    "label" => '',
    "options" => [
        $lang['label_guestlist_status_off'] => '1',
        $lang['label_guestlist_status_registered'] => '2',
        $lang['label_guestlist_status_global'] => '3'
    ],
    "type" => "select"
]);

// event_guestlist_public_nbr -> 1 = hide, 2 = show
$guestlist_public_nbr = (int) ($post_data['event_guestlist_public_nbr'] ?? 0);
if($guestlist_public_nbr == 0) {
    $guestlist_public_nbr = 1;
}

// categories and labels
$checked_categories = [];
if(($post_data['categories'] ?? '') != '') {
    $checked_categories = explode("<->", $post_data['categories']);
}
$checked_labels = [];
if(($post_data['labels'] ?? '') != '') {
    $checked_labels = explode(",", $post_data['labels']);
}

/* image widget */
$images = [];
foreach(array_filter(explode("<->", $post_data['images'] ?? '')) as $image) {
    $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
    $image_src = str_replace('../images/','/images/',$image_src);
    $images[] = [
        'path' => $image,
        'src' => $image_src,
        'name' => basename($image)
    ];
}

// release date and event dates, now if not set
$format_date = static fn($timestamp) => date('Y-m-d H:i', ($timestamp > 0) ? $timestamp : time());

echo '<div id="formResponse"></div>';
echo $twig->render('events/event-form.twig', [
    'event' => array_map($decode, $post_data),
    'mode' => $mode,
    'inputs' => $inputs,
    'images' => $images,
    'categories' => se_get_category_checklist($checked_categories),
    'labels' => se_get_label_checklist($checked_labels),
    'content_tags' => $decode(se_tags_csv_for_content('event', (int) ($id ?? 0))),
    'releasedate' => $format_date($post_data['releasedate'] ?? 0),
    'event_start' => $format_date($post_data['event_startdate'] ?? 0),
    'event_end' => $format_date($post_data['event_enddate'] ?? 0),
    'guestlist_public_nbr' => $guestlist_public_nbr,
    'main_category_options' => $main_category_options,
    'se_base_url' => $se_base_url,
    'canonical_docs_link' => se_print_docs_link("06-00-events.md", null, "canonical-url")
]);
