<?php

/**
 * forms of the product filters, markup in acp/templates/shop/filter-group-form.twig
 * and acp/templates/shop/filter-value-form.twig
 *
 * /admin/shop/filters/edit/group/<id>/   edit a group
 * /admin/shop/filters/new/group/         new group
 * /admin/shop/filters/edit/value/<id>/   edit a value
 * /admin/shop/filters/new/value/<id>/    new value in the group <id> (optional)
 * the older POST parameters edit_group, edit_value (id or "new") and parent_id still work
 *
 * groups: filter_type 1, filter_parent_id NULL (the frontend loads the groups by that)
 * values: filter_type 2, filter_parent_id = id of the group, products reference them by filter_id
 *
 * global variables
 * @var array $lang
 * @var array $lang_codes
 * @var object $db_content
 * @var string $query
 * @var object $twig
 */

$writer_uri = '/admin-xhr/shop/write/';

// shop/filters/<edit|new>/<group|value>/<id>/
$segments = array_values(array_filter(explode('/', (string) parse_url($query, PHP_URL_PATH))));
$form = $segments[3] ?? '';
$id = (isset($segments[4]) && is_numeric($segments[4])) ? (int) $segments[4] : null;
$is_new = (($segments[2] ?? '') == 'new');

if(isset($_POST['edit_group'])) {
    $form = 'group';
    $is_new = !is_numeric($_POST['edit_group']);
    $id = $is_new ? null : (int) $_POST['edit_group'];
}
if(isset($_POST['edit_value'])) {
    $form = 'value';
    $is_new = !is_numeric($_POST['edit_value']);
    $id = $is_new ? (isset($_POST['parent_id']) ? (int) $_POST['parent_id'] : null) : (int) $_POST['edit_value'];
}

// the stored entry (empty for new ones)
$filter_data = [];
if(!$is_new && $id !== null) {
    $filter_data = $db_content->get("se_filter", "*", [
        "AND" => [
            "filter_id" => $id,
            "filter_type" => ($form == 'group') ? 1 : 2
        ]
    ]) ?? [];
}

/*
 * the form helpers print the values as they are: the title is stored
 * html-encoded (se_return_clean_value) and is passed unchanged,
 * the description is stored as html and is escaped for the textarea
 */
$inputs = [
    'title' => se_print_form_input([
        "input_name" => "filter_title",
        "input_value" => $filter_data['filter_title'] ?? '',
        "label" => $lang['label_title'],
        "type" => "text"
    ]),
    'description' => se_print_form_input([
        "input_name" => "filter_description",
        "input_value" => htmlspecialchars((string) ($filter_data['filter_description'] ?? ''), ENT_QUOTES, 'UTF-8'),
        "label" => $lang['label_description'],
        "type" => "textarea",
        "mode" => "wysiwyg"
    ]),
    'priority' => se_print_form_input([
        "input_name" => "filter_priority",
        "input_value" => $filter_data['filter_priority'] ?? '',
        "label" => $lang['label_priority'],
        "type" => "text"
    ])
];

if($form == 'group') {

    $lang_options = [];
    foreach(get_all_languages() as $langs) {
        if(!in_array($langs['lang_folder'],$lang_codes)) {
            continue;
        }
        $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
    }

    $inputs['language'] = se_print_form_input([
        "input_name" => "filter_lang",
        "input_value" => $filter_data['filter_lang'] ?? '',
        "label" => $lang['label_language'],
        "options" => $lang_options,
        "type" => "select"
    ]);

    $inputs['input_type'] = se_print_form_input([
        "input_name" => "filter_input_type",
        "input_value" => $filter_data['filter_input_type'] ?? '',
        "label" => $lang['label_type'],
        "options" => [
            'Radio' => '1',
            'Checkbox' => '2',
            'Range' => '3'
        ],
        "type" => "select"
    ]);

    // the categories of the group: "all" and/or category hashes, comma separated
    $group_categories = array_filter(explode(",", (string) ($filter_data['filter_categories'] ?? '')));
    $categories = [];
    foreach(se_get_categories() as $cat) {
        $categories[] = [
            'hash' => $cat['cat_hash'],
            'name' => html_entity_decode((string) $cat['cat_name'], ENT_QUOTES, 'UTF-8'),
            'lang' => $cat['cat_lang'],
            'checked' => in_array($cat['cat_hash'], $group_categories)
        ];
    }

    echo $twig->render('shop/filter-group-form.twig', [
        'group_id' => isset($filter_data['filter_id']) ? (int) $filter_data['filter_id'] : null,
        'writer_uri' => $writer_uri,
        'inputs' => $inputs,
        'all_categories' => in_array('all', $group_categories),
        'categories' => $categories
    ]);
}

if($form == 'value') {

    // a new value: $id is the group it is created in (from the list), if any
    $parent_id = $filter_data['filter_parent_id'] ?? ($is_new ? $id : null);

    // select for the group
    $group_options = [];
    foreach(se_get_product_filter_groups('all') as $group) {
        $group_options['#'.$group['filter_id'].' | '.$group['filter_title']] = $group['filter_id'];
    }

    $inputs['group'] = se_print_form_input([
        "input_name" => "filter_parent_id",
        "input_value" => $parent_id,
        "label" => $lang['label_group'],
        "options" => $group_options,
        "type" => "select"
    ]);

    // values from older versions may have no hash yet
    $filter_hash = (string) ($filter_data['filter_hash'] ?? '');
    if($filter_hash == '') {
        $filter_hash = uniqid();
    }

    echo $twig->render('shop/filter-value-form.twig', [
        'value_id' => isset($filter_data['filter_id']) ? (int) $filter_data['filter_id'] : null,
        'writer_uri' => $writer_uri,
        'inputs' => $inputs,
        'filter_hash' => $filter_hash
    ]);
}
