<?php

/**
 * page form in /admin/pages/edit/, /new/ and /duplicate/ - and older versions (restore_id),
 * markup in acp/templates/pages/edit-form.twig
 *
 * global variables
 * @var array $lang
 * @var array $icon
 * @var object $db_content
 * @var array $se_settings
 * @var array $se_page_types
 * @var array $lang_codes
 * @var array $active_lang
 * @var string $languagePack
 * @var string $se_base_url
 * @var string $query
 * @var object $twig
 */

require_once __DIR__ . '/functions.php';

$writer_uri = '/admin-xhr/pages/write/';

// the page to edit: last part of the url, the page_id of the list or the page to duplicate
$get_page_id = null;
$form_mode = 'new';
$btn_submit_text = $lang['save'];

$path = (string) parse_url($query, PHP_URL_PATH);
$segments = explode('/', rtrim($path, '/'));
$last_segment = end($segments);
if(is_numeric($last_segment)) {
    $get_page_id = (int) $last_segment;
    $form_mode = $get_page_id;
    $btn_submit_text = $lang['update'];
}

if(isset($_POST['page_id']) && is_numeric($_POST['page_id'])) {
    $get_page_id = (int) $_POST['page_id'];
    $form_mode = $get_page_id;
    $btn_submit_text = $lang['update'];
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_page_id = (int) $_POST['duplicate_id'];
    $form_mode = 'new';
    $btn_submit_text = $lang['duplicate'];
}

/*
 * $get_page is the stored (html-encoded) row, page-values.php of the themes
 * reads it as global (see docs/v2/en/09-01-00-themes.md)
 */
$get_page = [];
if($get_page_id !== null) {
    $get_page = $db_content->get("se_pages", "*", [
        "page_id" => $get_page_id
    ]) ?: [];
}

// an older version from se_pages_cache, saved as the original page
if(isset($_POST['restore_id']) && is_numeric($_POST['restore_id'])) {
    $get_page = $db_content->get("se_pages_cache", "*", [
        "page_id" => (int) $_POST['restore_id']
    ]) ?: [];
    $get_page_id = (int) ($get_page['page_id_original'] ?? 0);
    $form_mode = $get_page_id;
    $btn_submit_text = $lang['update'];
}

$record_data = $get_page;

// the page that is saved, null for new pages and duplicates
$current_page_id = is_int($form_mode) ? $form_mode : null;

// stored value of a column, the form helpers print it as it is (html-encoded)
$stored = static fn(string $column): string => (string) ($get_page[$column] ?? '');
// decoded value of a column, Twig escapes on output
$decode = static fn($value): string => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$page_language = $stored('page_language') ?: $languagePack;

/* info tab */

$page_canonical_url = $stored('page_canonical_url');
if($page_canonical_url == '' && $stored('page_permalink') != '') {
    $page_canonical_url = rtrim($se_base_url, '/') . '/' . ltrim($stored('page_permalink'), '/');
}

$type_options = [];
foreach($se_page_types as $page_type) {
    $type_options[$lang['type_of_use_'.$page_type]] = $page_type;
}

$inputs = [];
$inputs['linkname'] = se_print_form_input([
    "input_name" => "page_linkname",
    "input_value" => $stored('page_linkname'),
    "label" => $lang['label_pages_link_name'],
    "type" => "text"
]);
$inputs['classes'] = se_print_form_input([
    "input_name" => "page_classes",
    "input_value" => $stored('page_classes'),
    "label" => $lang['label_pages_classes'],
    "type" => "text"
]);
$inputs['target'] = se_print_form_input([
    "input_name" => "page_target",
    "input_value" => $stored('page_target'),
    "label" => "target",
    "options" => ["_self" => "_self", "_blank" => "_blank", "_parent" => "_parent", "_top" => "_top"],
    "type" => "select"
]);
$inputs['hash'] = se_print_form_input([
    "input_name" => "page_hash",
    "input_value" => $stored('page_hash'),
    "label" => $lang['label_pages_hash'],
    "type" => "text"
]);
$inputs['permalink'] = se_print_form_input([
    "input_name" => "page_permalink",
    "input_value" => $stored('page_permalink'),
    "label" => $lang['label_pages_permalink'],
    "input_group_start_text" => $se_base_url,
    "type" => "text"
]);
$inputs['canonical_url'] = se_print_form_input([
    "input_name" => "page_canonical_url",
    "input_value" => $page_canonical_url,
    "label" => 'Canonical URL',
    "type" => "text"
]);
$inputs['content_tags'] = se_print_form_input([
    "input_name" => "content_tags",
    "input_value" => se_tags_csv_for_content('page', (int) $current_page_id),
    "label" => $lang['label_tags'],
    "input_classes" => "form-control tags",
    "type" => "text"
]);
$inputs['custom_row'] = se_print_row([
    se_print_form_input([
        "input_name" => "page_custom_id",
        "input_value" => $stored('page_custom_id'),
        "label" => $lang['label_pages_custom_id'],
        "type" => "text"
    ]),
    se_print_form_input([
        "input_name" => "page_custom_classes",
        "input_value" => $stored('page_custom_classes'),
        "label" => $lang['label_pages_custom_classes'],
        "type" => "text"
    ]),
    se_print_form_input([
        "input_name" => "page_priority",
        "input_value" => $stored('page_priority'),
        "label" => $lang['label_priority'],
        "type" => "text"
    ])
]);
$inputs['type_of_use'] = se_print_form_input([
    "input_name" => "page_type_of_use",
    "input_value" => $stored('page_type_of_use'),
    "label" => $lang['label_pages_type_of_use'] . ' '. se_print_docs_link("02-00-pages.md#page-usage"),
    "options" => $type_options,
    "type" => "select"
]);
$inputs['shortlink'] = se_print_form_input([
    "input_name" => "page_permalink_short",
    "input_value" => $stored('page_permalink_short'),
    "label" => $lang['label_pages_permalink_short'],
    "input_group_end_text" => (string) (int) $stored('page_permalink_short_cnt'),
    "type" => "text"
]);
$inputs['funnel_urls'] = se_print_form_input([
    "input_name" => "page_funnel_uri",
    "input_value" => $stored('page_funnel_uri'),
    "label" => $lang['label_pages_funnel_url'],
    "type" => "textarea"
]);
$redirect_codes = ['301', '302', '303', '304', '305', '306', '307', '308', '309'];
$inputs['redirect_row'] = se_print_row([
    se_print_form_input([
        "input_name" => "page_redirect_code",
        "input_value" => $stored('page_redirect_code'),
        "label" => $lang['label_redirect'],
        "options" => array_combine($redirect_codes, $redirect_codes),
        "type" => "select"
    ]),
    se_print_form_input([
        "input_name" => "page_redirect",
        "input_value" => $stored('page_redirect'),
        "label" => null,
        "input_group_start_text" => '<i class="bi bi-arrow-right-short"></i>',
        "type" => "text"
    ])
], ['col-md-3', 'col-md-9']);

// translation urls, json of language => url
$translation_urls = json_decode($decode($stored('page_translation_urls')), true);
$translations = [];
foreach($active_lang as $active) {
    $translations[] = [
        'sign' => $active['sign'],
        'url' => (string) ($translation_urls[$active['sign']] ?? '')
    ];
}

/* content tab, the field is swapped by the data-reader when the format changes */

$inputs['content'] = se_print_form_input(se_build_page_content_field($get_page, null));

/* metas tab */

$inputs['title'] = se_print_form_input([
    "input_name" => "page_title",
    "input_value" => $stored('page_title'),
    "label" => $lang['label_title'],
    "type" => "text"
]);
$inputs['meta_description'] = se_print_form_input([
    "input_name" => "page_meta_description",
    "input_value" => $stored('page_meta_description'),
    "input_classes" => "form-control count-chars",
    "label" => $lang['label_description'],
    "type" => "textarea"
]);
$inputs['keywords'] = se_print_form_input([
    "input_name" => "page_meta_keywords",
    "input_value" => $stored('page_meta_keywords'),
    "input_classes" => "form-control tags",
    "label" => $lang['label_keywords'],
    "type" => "text"
]);
$inputs['author'] = se_print_form_input([
    "input_name" => "page_meta_author",
    "input_value" => $stored('page_meta_author'),
    "label" => $lang['label_author'],
    "type" => "text"
]);

$page_meta_robots = $stored('page_meta_robots') ?: 'all';
$robots = [];
foreach(["all", "noindex", "nofollow", "none", "noarchive", "nosnippet", "noodp", "notranslate", "noimageindex"] as $robot) {
    $robots[] = [
        'value' => $robot,
        'checked' => str_contains($page_meta_robots, $robot)
    ];
}

// thumbnails, stored html-encoded and separated by <->
$images = [];
foreach(array_filter(explode('<->', $decode($stored('page_thumbnail')))) as $image) {
    $image_src = str_replace('../content/', '/', $image); // old path from SwiftyEdit 1.x
    $image_src = str_replace('../images/', '/images/', $image_src);
    $images[] = [
        'path' => $image,
        'src' => $image_src,
        'name' => basename($image)
    ];
}

/* position tab */

// page_parent_id / position drive the tree (see
// app/functions/functions.pages.php's se_index_pages_by_parent() /
// se_flatten_page_tree()) - page_sort is only still read here for the
// 'portal' marker, which has no equivalent in the new fields.
$current_page_parent_id = $stored('page_parent_id') !== '' ? (int) $stored('page_parent_id') : null;
$current_position = (int) $stored('position');

if ($stored('page_sort') === 'portal') {
    $page_role = 'portal';
} else if ($current_page_parent_id === null) {
    $page_role = 'single';
} else {
    $page_role = 'tree';
}

$pages_in_language = $db_content->select("se_pages",
    ["page_id", "page_parent_id", "position", "page_linkname", "page_title", "page_permalink", "page_sort"],
    ["page_language" => $page_language]
);

$portal_id = null;
foreach ($pages_in_language as &$lp) {
    $lp['page_id'] = (int) $lp['page_id'];
    $lp['page_parent_id'] = ($lp['page_parent_id'] ?? '') !== '' ? (int) $lp['page_parent_id'] : null;
    $lp['position'] = (int) $lp['position'];
    if ($lp['page_sort'] === 'portal') {
        $portal_id = $lp['page_id'];
    }
}
unset($lp);

// the parent dropdown's "top level" option (an empty <select> value) always
// means "parent = the portal page", never a literal NULL - a page with no
// parent_id recorded yet (new page, or currently role=single/portal) still
// needs to show *some* siblings if the dropdown defaults to "top level"
$effective_parent_id = $current_page_parent_id ?? $portal_id;

// current siblings under the page's existing parent, and which one it's
// currently placed right after - so the initial render already reflects
// where the page actually is (the HTMX swap recomputes this fresh
// whenever the parent dropdown changes, always defaulting to "at the end")
$siblings = array_values(array_filter($pages_in_language, function ($p) use ($effective_parent_id, $current_page_id) {
    return $p['page_parent_id'] === $effective_parent_id && $p['page_id'] !== $current_page_id;
}));
usort($siblings, function ($a, $b) { return $a['position'] <=> $b['position']; });

$selected_after_page_id = null;
foreach ($siblings as $sibling) {
    if ($sibling['position'] < $current_position) {
        $selected_after_page_id = $sibling['page_id'];
    }
}

/* theme tab, page-values.php of the theme adds its fields */

$page_theme = $stored('page_template');
if($page_theme == '' || $page_theme == 'use_standard') {
    $page_theme = $se_settings['template'];
}
$theme_values = null;
foreach(['page-values.php', 'page_values.php'] as $file) {
    $injector = SE_PUBLIC.'/assets/themes/'.basename($page_theme).'/php/'.$file;
    if(is_file($injector)) {
        // own scope, the file only sees its globals (global $get_page)
        $include_injector = static function(string $file): string {
            ob_start();
            include $file;
            return (string) ob_get_clean();
        };
        $theme_values = $include_injector($injector);
        break;
    }
}

/* posts tab */

$post_types = [];
$checked_post_types = explode(',', $stored('page_posts_types'));
foreach([
    "m" => $lang['post_type_message'],
    "i" => $lang['post_type_image'],
    "g" => $lang['post_type_gallery'],
    "v" => $lang['post_type_video'],
    "f" => $lang['post_type_file'],
    "l" => $lang['post_type_link'],
    "e" => $lang['post_type_event'],
    "p" => $lang['post_type_product']
] as $type => $label) {
    $post_types[] = [
        'value' => $type,
        'label' => $label,
        'checked' => in_array($type, $checked_post_types)
    ];
}

$inputs['categories_mode'] = se_print_form_input([
    "input_name" => "page_categories_mode",
    "input_value" => $stored('page_categories_mode'),
    "label" => "",
    "options" => [$lang['label_categories_show'] => "1", $lang['label_categories_hide'] => "2"],
    "type" => "select"
]);

$checked_categories = explode(',', $stored('page_posts_categories'));
$categories = [];
foreach(se_get_categories() as $category) {
    $categories[] = [
        'hash' => $category['cat_hash'],
        'name' => $decode($category['cat_name']),
        'lang' => $category['cat_lang'],
        'checked' => in_array($category['cat_hash'], $checked_categories)
    ];
}

/* addons tab */

$addon_options = [];
foreach(se_get_all_addons() as $addon_folder => $addon) {
    // payment and delivery plugins can't be the module of a page
    if(str_ends_with($addon_folder, '-pay') || str_ends_with($addon_folder, '-delivery')) {
        continue;
    }
    $addon_options[] = [
        'value' => $addon_folder,
        'label' => $addon['addon']['name'],
        'selected' => ($addon_folder == $stored('page_modul'))
    ];
}

// hooks of the plugins, selected ones run after saving (see data-writer)
$hooks = [];
foreach(se_get_backend_hook_meta('page.') as $hook_name => $entries) {
    foreach($entries as $entry_index => $meta) {
        if(!is_array($meta)) {
            continue;
        }
        $hooks[] = [
            'name' => 'hooks['.$hook_name.']['.(int) $entry_index.']',
            'plugin' => $meta['plugin'],
            'label' => $meta['label'] ?? ''
        ];
    }
}

/* sidebar */

$inputs['status'] = se_print_form_input([
    "input_name" => "page_status",
    "input_value" => $stored('page_status'),
    "label" => $lang['label_status'].' ' .se_print_docs_link('02-00-pages.md#page-status'),
    "options" => [
        $lang['status_public'] => 'public',
        $lang['status_private'] => 'private',
        $lang['status_draft'] => 'draft',
        $lang['status_ghost'] => 'ghost'
    ],
    "type" => "select"
]);

$lang_options = [];
foreach(get_all_languages() as $langs) {
    if(in_array($langs['lang_folder'], $lang_codes)) {
        $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
    }
}
$inputs['language'] = se_print_form_input([
    "input_name" => "page_language",
    "input_value" => $page_language,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
]);

// themes and their layout*.tpl files, the value is "theme<|-|>layout"
$templates = [];
foreach(get_all_templates() as $template) {
    if($template == 'administration') {
        continue;
    }
    $layouts = [];
    foreach(glob(SE_PUBLIC."/assets/themes/$template/templates/layout*.tpl") ?: [] as $layout_tpl) {
        $layout_tpl = basename($layout_tpl);
        $layouts[] = [
            'value' => $template.'<|-|>'.$layout_tpl,
            'label' => $template.' » '.$layout_tpl,
            'selected' => ($template == $stored('page_template') && $layout_tpl == $stored('page_template_layout'))
        ];
    }
    $templates[] = ['name' => $template, 'layouts' => $layouts];
}

// page_usergroup holds the (posted, decoded) names of the groups
$checked_groups = explode(",", $stored('page_usergroup'));
$usergroups = [];
foreach(get_all_groups() as $group) {
    $group_name = $decode($group['group_name']);
    $usergroups[] = [
        'id' => (int) $group['group_id'],
        'name' => $group_name,
        'checked' => in_array($group_name, $checked_groups)
    ];
}

echo $twig->render('pages/edit-form.twig', [
    'page' => array_map($decode, $get_page),
    'inputs' => $inputs,
    'writer_uri' => $writer_uri,
    'form_mode' => $form_mode,
    'btn_submit_text' => $btn_submit_text,
    'current_page_id' => $current_page_id,
    'page_language' => $page_language,
    'page_role' => $page_role,
    'parent_options' => se_build_parent_options($pages_in_language, $portal_id, $current_page_id, $current_page_parent_id),
    'sibling_picker' => se_build_sibling_picker($siblings, $selected_after_page_id),
    'translations' => $translations,
    'robots' => $robots,
    'images' => $images,
    'theme_values' => $theme_values,
    'post_types' => $post_types,
    'categories' => $categories,
    'categories_all' => in_array('all', $checked_categories),
    'addon_options' => $addon_options,
    'addon_module_forms' => se_render_record_addons('page', $record_data),
    'hooks' => $hooks,
    'templates' => $templates,
    'template_standard' => ($stored('page_template') == ''),
    'has_password' => ($stored('page_psw') != ''),
    'usergroups' => $usergroups,
    'labels' => se_get_label_checklist(explode(",", $stored('page_labels'))),
    'labels_docs_link' => se_print_docs_link('08-00-settings.md#labels', '', 'labels'),
    'docs_link_sorting' => se_print_docs_link('02-00-pages.md#sorting'),
    'docs_link_redirects' => se_print_docs_link("02-00-pages.md#redirects")
]);
