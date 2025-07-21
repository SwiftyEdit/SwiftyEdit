<?php

/**
 * global
 * @var array $icon
 */


if($_REQUEST['action'] == 'deliveryCountries') {

    $get_countries = $db_content->select("se_delivery_areas", "*");

    echo '<table class="table">';
    echo '<tr>';
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
        echo '<td>'.$country['name'].'</td>';
        echo '<td>'.$status.'</td>';
        echo '<td>'.$tax.'</td>';
        echo '<td>';
        echo '<button class="btn btn-sm btn-default text-success" hx-get="/admin/xhr/settings/read/?edit_delivery_country='.$country['id'].'" hx-target="#deliveryCountriesForm">'.$icon['edit'].'</button>';
        echo '<button class="btn btn-sm btn-default text-danger" hx-post="/admin/xhr/settings/general/write/" hx-swap="none" hx-include="[name=\'csrf_token\']" name="delete_delivery_country" value="'.$country['id'].'">'.$icon['trash'].'</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

if($_REQUEST['show'] == 'deliveryCountriesForm' OR $_REQUEST['edit_delivery_country']) {

    $submit_btn = '<button type="submit" class="btn btn-primary" name="send_delivery_country" value="save">'.$lang['btn_save'].'</button>';

    if(isset($_REQUEST['edit_delivery_country'])) {
        $edit_country = (int) $_REQUEST['edit_delivery_country'];
        $get_country = $db_content->get("se_delivery_areas", "*", ["id" => $edit_country]);
        $submit_btn = '<button type="submit" class="btn btn-primary" name="send_delivery_country" value="'.$edit_country.'">'.$lang['btn_update'].'</button>';
    }

    $input_delivery_country = [
        "input_name" => "delivery_country",
        "input_value" => $get_country['name'] ?? '',
        "label" => $lang['label_shop_add_delivery_area'],
        "type" => "text"
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

    echo '<form id="deliveryCountriesForm" hx-post="/admin/xhr/settings/shop/write/" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
    echo se_print_form_input($input_delivery_country);
    echo se_print_form_input($input_delivery_country_status);
    echo se_print_form_input($input_delivery_country_tax);
    echo $submit_btn;
    echo '</form>';
}

if(isset($_POST['load_labels'])) {


    $writer_uri = '/admin/settings/labels/write/';
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



}