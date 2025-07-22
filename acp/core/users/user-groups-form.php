<?php

$writer_uri = '/admin-xhr/users/write/';
include_once '../acp/core/templates.php';

echo '<div id="formResponse"></div>';


if(isset($_POST['open_user_group']) && is_numeric($_POST['open_user_group'])) {
    $get_group_id = (int) $_POST['open_user_group'];
    $form_mode = $get_group_id;
    $btn_submit_text = $lang['update'];
    $btn_reset = '<a href="/admin/users/groups/" class="btn btn-default ms-auto">'.$lang['reset'].'</a>';
    $btn_delete = '<button name="delete_user_group" value="'.$get_group_id.'" class="btn btn-default text-danger" 
                            hx-post="/admin-xhr/users/write/"
                            hx-trigger="click"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-swap="none"
                            >'.$icon['trash_alt'].'</button>';
}

if(is_int($get_group_id)) {

    $get_group = $db_user->get("se_groups","*",[
        "group_id" => "$get_group_id"
    ]);

    foreach($get_group as $k => $v) {
        if($v == '') {
            continue;
        }
        $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
    }

    $array_group_user = explode(" ",$get_group['group_user']);

} else {
    $btn_submit_text = $lang['save'];
    $form_mode = 'new';
}


$input_group_name = [
    "input_name" => "group_name",
    "input_value" => $group_name,
    "label" => $lang['label_name'],
    "type" => "text"
];

$input_group_description = [
    "input_name" => "group_description",
    "input_value" => $group_description,
    "label" => $lang['label_description'],
    "type" => "textarea"
];

$type_options = [
    $lang['label_groups_status_public'] => 'p',
    $lang['label_groups_status_hidden'] => 'h'
];

$select_form_type = [
    "input_name" => "group_type",
    "input_value" => $group_type,
    "label" => $lang['label_type'],
    "options" => $type_options,
    "type" => "select"
];

$form_tpl = '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_group'].'</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= '<form>';
$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-6">';
$form_tpl .= se_print_form_input($input_group_name);
$form_tpl .= se_print_form_input($select_form_type);
$form_tpl .= se_print_form_input($input_group_description);
$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-6">';
$form_tpl .= '<p>'.$lang['label_group_add_user'].'</p>';

$form_tpl .= '<div class="scroll-container">';
$users = $db_user->select("se_user","*");
$form_tpl .= '<table class="table table-sm">';
foreach ($users as $user) {

    $user_id = $user['user_id'];
    $user_nick = $user['user_nick'];
    $user_firstname = $user['user_firstname'];
    $user_lastname = $user['user_lastname'];

    $checked = in_array("$user_id", (array)$array_group_user) ? "checked" : "";

    $checkbox = [
        "input_name" => "incUser[]",
        "input_value" => $user_id,
        "status" => $checked,
        "label" => $user_nick,
        "type" => "checkbox"
    ];

    $form_tpl .= '<tr>';
    $form_tpl .= '<td>'.se_print_form_input($checkbox).'</td>';
    $form_tpl .= '<td>'.$user_firstname.' '.$user_lastname.'</td>';
    $form_tpl .= '</tr>';
}
$form_tpl .= '</table>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '<div class="d-flex">';
$form_tpl .= '<button class="btn btn-success" hx-post="'.$writer_uri.'" hx-trigger="click" hx-swap="innerHTML" hx-target="#formResponse" name="save_user_group" value="'.$form_mode.'">'.$btn_submit_text.'</button>';
$form_tpl .= $btn_reset;
$form_tpl .= $btn_delete;
$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
$form_tpl .= '</div>';

$form_tpl .= '</form>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';


echo $form_tpl;