<?php

/**
 * SwiftyEdit /admin-xhr/settings/read/
 * delivery areas of the shop settings (list and form), the API keys and the labels
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 * @var array $se_settings
 * @var object $twig
 */

$action = $_REQUEST['action'] ?? '';
$settings_reader_uri = '/admin-xhr/settings/read/';
$settings_writer_uri = '/admin-xhr/settings/general/write/';

// delivery areas, status and tax: 1 = yes, 2 = no
if($action == 'deliveryCountries') {

    $countries = [];
    foreach($db_content->select("se_delivery_areas", "*", ["ORDER" => ["name" => "ASC"]]) as $country) {
        $countries[] = [
            'id' => (int) $country['id'],
            'code' => $country['code'],
            'name' => $country['name'],
            'public' => ($country['status'] == '1'),
            'tax' => ($country['tax'] == '1')
        ];
    }
    echo $twig->render('settings/delivery-areas.twig', [
        'countries' => $countries,
        'reader_uri' => $settings_reader_uri,
        'writer_uri' => $settings_writer_uri
    ]);
    exit;
}

// form of a new or an edited delivery area
if(($_REQUEST['show'] ?? '') == 'deliveryCountriesForm' || isset($_REQUEST['edit_delivery_country'])) {

    $edit_id = (int) ($_REQUEST['edit_delivery_country'] ?? 0);
    $get_country = [];
    if($edit_id > 0) {
        $get_country = $db_content->get("se_delivery_areas", "*", ["id" => $edit_id]) ?: [];
    }

    $country_options = [
        $lang['label_please_select'] => ''
    ];
    foreach(se_get_countries() as $country) {
        $country_options[$country['name']] = $country['alpha2'];
    }

    echo $twig->render('settings/delivery-area-form.twig', [
        'edit_id' => $get_country ? $edit_id : 0,
        'reader_uri' => $settings_reader_uri,
        'writer_uri' => $settings_writer_uri,
        'inputs' => [
            'country' => se_print_form_input([
                "input_name" => "delivery_country",
                "input_value" => $get_country['code'] ?? '',
                "label" => $lang['label_shop_add_delivery_area'],
                "options" => $country_options,
                "type" => "select"
            ]),
            'status' => se_print_form_input([
                "input_name" => "delivery_country_status",
                "input_value" => $get_country['status'] ?? '1',
                "label" => $lang['label_status'],
                "options" => [
                    $lang['status_public'] => '1',
                    $lang['status_draft'] => '2'
                ],
                "type" => "select"
            ]),
            'tax' => se_print_form_input([
                "input_name" => "delivery_country_tax",
                "input_value" => $get_country['tax'] ?? '2',
                "label" => $lang['label_tax'],
                "options" => [
                    $lang['yes'] => '1',
                    $lang['no'] => '2'
                ],
                "type" => "select"
            ])
        ]
    ]);
    exit;
}

// API keys (Settings > API keys)
if(isset($_GET['load_api_keys'])) {

    if(!se_hasPermission('drm_acp_system')) {
        http_response_code(403);
        exit;
    }

    $keys = [];
    foreach(se_api_get_keys() as $api_key) {
        $keys[] = [
            'id' => (int) $api_key['id'],
            // stored html-encoded (sanitizeUserInputs), Twig escapes on output
            'label' => html_entity_decode((string) $api_key['label'], ENT_QUOTES, 'UTF-8'),
            'prefix' => (string) $api_key['key_prefix'],
            'scopes' => array_values(array_filter(explode(',', (string) $api_key['scopes']))),
            'created' => se_format_datetime($api_key['created_at']),
            'last_used' => ((int) $api_key['last_used_at'] > 0) ? se_format_datetime($api_key['last_used_at']) : '-',
            'requests' => (int) $api_key['request_count'],
            'active' => ((int) $api_key['is_active'] === 1)
        ];
    }
    echo $twig->render('settings/api-keys-list.twig', [
        'keys' => $keys,
        'writer_uri' => '/admin-xhr/settings/api-keys/write/'
    ]);
    exit;
}

// labels, one form per label (settings/labels-list.twig)
if(isset($_GET['load_labels'])) {

    // stored html-encoded, Twig escapes on output
    $decode = static fn($value): string => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

    $labels = [];
    foreach(se_get_labels() as $label) {
        $labels[] = [
            'id' => (int) $label['label_id'],
            'color' => $label['label_color'],
            'title' => $decode($label['label_title']),
            'description' => $decode($label['label_description'])
        ];
    }
    echo $twig->render('settings/labels-list.twig', [
        'labels' => $labels,
        'writer_uri' => '/admin-xhr/settings/labels/write/'
    ]);
    exit;
}