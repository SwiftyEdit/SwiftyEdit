<?php

/**
 * global
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 * @var array $se_settings
 */


if($_REQUEST['action'] == 'deliveryCountries') {

    $get_countries = $db_content->select("se_delivery_areas", "*");

    echo '<table class="table">';
    echo '<tr>';
    echo '<td>Code</td>';
    echo '<td>'.$lang['label_country'].'</td>';
    echo '<td>'.$lang['label_status'].'</td>';
    echo '<td>'.$lang['label_plus_tax'].'</td>';
    echo '<td></td>';
    echo '</tr>';
    foreach($get_countries as $country) {

        $status = '<span class="badge text-danger">'.$icon['circle'].'</span>';
        if($country['status'] == '1') {
            $status = '<span class="badge text-success">'.$icon['check_circle'].'</span>';
        }

        $tax = '<span class="badge text-danger">'.$icon['circle'].'</span>';
        if($country['tax'] == '1') {
            $tax = '<span class="badge text-success">'.$icon['check_circle'].'</span>';
        }

        echo '<tr>';
        echo '<td><code>'.$country['code'].'</code></td>';
        echo '<td>'.$country['name'].'</td>';
        echo '<td>'.$status.'</td>';
        echo '<td>'.$tax.'</td>';
        echo '<td>';
        echo '<button class="btn btn-sm btn-default text-success" hx-get="/admin-xhr/settings/read/?edit_delivery_country='.$country['id'].'" hx-target="#deliveryCountriesForm">'.$icon['edit'].'</button>';
        echo '<button class="btn btn-sm btn-default text-danger" hx-post="/admin-xhr/settings/general/write/" hx-swap="none" hx-include="[name=\'csrf_token\']" name="delete_delivery_country" value="'.$country['id'].'">'.$icon['trash'].'</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

if($_REQUEST['show'] == 'deliveryCountriesForm' OR $_REQUEST['edit_delivery_country']) {

    $all_countries = se_get_countries();

    $submit_btn = '<button type="submit" class="btn btn-primary" name="send_delivery_country" value="save">'.$lang['btn_save'].'</button>';

    if(isset($_REQUEST['edit_delivery_country'])) {
        $edit_country = (int) $_REQUEST['edit_delivery_country'];
        $get_country = $db_content->get("se_delivery_areas", "*", ["id" => $edit_country]);
        $submit_btn = '<button type="submit" class="btn btn-primary" name="send_delivery_country" value="'.$edit_country.'">'.$lang['btn_update'].'</button>';
    }

    $country_options = [
        $lang['label_please_select'] => ''
    ];

    foreach($all_countries as $country) {
        $country_options[$country['name']] = $country['alpha2'];
    }

    $input_delivery_country = [
        "input_name" => "delivery_country",
        "input_value" => $get_country['code'] ?? '',
        "label" => $lang['label_shop_add_delivery_area'],
        "options" => $country_options,
        "type" => "select"
    ];

    $input_delivery_country_status = [
        "input_name" => "delivery_country_status",
        "input_value" => $get_country['status'] ?? '1',
        "label" => $lang['label_status'],
        "options" => [
            $lang['status_public'] => 1,
            $lang['status_draft'] => 2
        ],
        "type" => "select"
    ];

    $input_delivery_country_tax = [
        "input_name" => "delivery_country_tax",
        "input_value" => $get_country['tax'] ?? '2',
        "label" => $lang['label_tax'],
        "options" => [
            $lang['yes'] => 1,
            $lang['no'] => 2
        ],
        "type" => "select"
    ];

    echo '<form id="deliveryCountriesForm" hx-post="/admin-xhr/settings/shop/write/" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
    echo se_print_form_input($input_delivery_country);
    echo se_print_form_input($input_delivery_country_status);
    echo se_print_form_input($input_delivery_country_tax);
    echo $submit_btn;
    echo '</form>';
}

// API keys (Settings > API keys)
if(isset($_GET['load_api_keys'])) {

    if(!se_hasPermission('drm_acp_system')) {
        http_response_code(403);
        exit;
    }

    $writer_uri = '/admin-xhr/settings/api-keys/write/';
    $api_keys = se_api_get_keys();

    if(count($api_keys) < 1) {
        echo '<p class="mb-0 text-muted">'.$lang['api_keys_none'].'</p>';
        exit;
    }

    echo '<table class="table table-sm align-middle mb-0">';
    echo '<thead><tr>';
    echo '<th>'.$lang['api_keys_label'].'</th>';
    echo '<th>'.$lang['api_keys_key'].'</th>';
    echo '<th>'.$lang['api_keys_scopes'].'</th>';
    echo '<th>'.$lang['api_keys_created'].'</th>';
    echo '<th>'.$lang['api_keys_last_used'].'</th>';
    echo '<th class="text-end">'.$lang['api_keys_requests'].'</th>';
    echo '<th>'.$lang['api_keys_status'].'</th>';
    echo '<th></th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach($api_keys as $api_key) {

        $key_id = (int) $api_key['id'];
        $is_active = ((int) $api_key['is_active'] === 1);
        $last_used = ((int) $api_key['last_used_at'] > 0) ? se_format_datetime($api_key['last_used_at']) : '-';
        $scopes = array_filter(explode(',', (string) $api_key['scopes']));

        echo '<tr class="'.($is_active ? '' : 'text-muted').'">';
        echo '<td>'.htmlspecialchars($api_key['label'], ENT_QUOTES).'</td>';
        echo '<td><code>'.htmlspecialchars($api_key['key_prefix'], ENT_QUOTES).'…</code></td>';
        echo '<td>';
        foreach($scopes as $scope) {
            echo '<code class="me-1">'.htmlspecialchars($scope, ENT_QUOTES).'</code>';
        }
        echo '</td>';
        echo '<td>'.se_format_datetime($api_key['created_at']).'</td>';
        echo '<td>'.$last_used.'</td>';
        echo '<td class="text-end">'.(int) $api_key['request_count'].'</td>';
        echo '<td>'.($is_active ? '<span class="badge text-bg-success">'.$lang['api_keys_active'].'</span>' : '<span class="badge text-bg-secondary">'.$lang['api_keys_revoked'].'</span>').'</td>';
        echo '<td class="text-end">';
        echo '<form class="d-inline">';
        echo '<input type="hidden" name="api_key_id" value="'.$key_id.'">';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        // revoking is final - a revoked key can only be deleted, not reactivated
        if($is_active) {
            echo '<button hx-post="'.$writer_uri.'" hx-confirm="'.htmlspecialchars($lang['api_keys_confirm_revoke'], ENT_QUOTES).'" hx-target="#apiKeyCreated" name="revoke_api_key" value="'.$key_id.'" class="btn btn-sm btn-default text-danger" title="'.$lang['api_keys_btn_revoke'].'">'.$icon['ban'].' '.$lang['api_keys_btn_revoke'].'</button>';
        } else {
            echo '<button hx-post="'.$writer_uri.'" hx-confirm="'.htmlspecialchars($lang['msg_confirm_delete'], ENT_QUOTES).'" hx-target="#apiKeyCreated" name="delete_api_key" value="'.$key_id.'" class="btn btn-sm btn-default text-danger" title="'.$lang['btn_delete'].'">'.$icon['trash_alt'].'</button>';
        }
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    exit;
}

if(isset($_GET['load_labels'])) {


    $writer_uri = '/admin-xhr/settings/labels/write/';
    $se_labels = se_get_labels();
    $cnt_labels = count($se_labels);

    for($i=0;$i<$cnt_labels;$i++) {
        echo '<form>';
        echo '<div class="row mb-1" id="row_'.$i.'">';
        echo '<div class="col-2">';
        echo '<div class="input-group">';
        echo '<span class="input-group-text" id="basic-addon1">#</span>';
        echo '<input class="form-control" type="text" name="label_id" value="'.$se_labels[$i]['label_id'].'" readonly>';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-2">';

        echo '<div class="input-group">';
        echo '<input type="color" class="form-control form-control-color" style="max-width:45px;" name="label_color" value="'.$se_labels[$i]['label_color'].'" title="Choose your color">';
        echo '<input class="form-control" type="text" name="label_title" value="'.$se_labels[$i]['label_title'].'">';
        echo '</div>';

        echo '</div>';
        echo '<div class="col">';
        echo '<input class="form-control" type="text" name="label_description" value="'.$se_labels[$i]['label_description'].'">';
        echo '<div class="update-response-'.$i.'"></div>';
        echo '</div>';
        echo '<div class="col-2">';
        echo '<input type="hidden" name="label_id" value="'.$se_labels[$i]['label_id'].'">';
        echo '<div class="btn-group d-flex" role="group">';
        echo '<button hx-post="'.$writer_uri.'" hx-target="#page-content" hx-swap="afterbegin" name="update_label" class="btn btn-default w-100 text-success">'.$icon['sync_alt'].'</button>';
        echo '<button hx-post="'.$writer_uri.'" hx-delete="'.$se_labels[$i]['label_id'].'" hx-target="#row_'.$i.'" hx-swap="outerHTML swap:0.1s" name="delete_label" class="btn btn-default w-100 text-danger">' .$icon['trash_alt'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</form>';

    }
    exit;
}