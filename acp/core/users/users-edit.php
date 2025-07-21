<?php

$writer_uri = '/admin/xhr/users/write/';

if(isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
    $get_user_id = (int) $_POST['user_id'];
    $form_mode = $get_user_id;
    $btn_submit_text = $lang['update'];
}

if(is_int($get_user_id)) {

    $get_user = $db_user->get("se_user","*",[
        "user_id" => "$get_user_id"
    ]);

    foreach($get_user as $k => $v) {
        if($v == '') {
            continue;
        }
        $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
    }

} else {
    $btn_submit_text = $lang['save'];
    $form_mode = 'new';
}

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['users'].' '.$lang['nav_btn_user'];
echo '<span class="ms-auto d-inline-block">'.$form_mode.'</span>';
echo '</div>';

echo '<div id="formResponse"></div>';

$input_username = [
    "input_name" => "user_nick",
    "input_value" => $user_nick,
    "label" => $lang['label_username'],
    "type" => "text"
];

$input_firstname = [
    "input_name" => "user_firstname",
    "input_value" => $user_firstname,
    "label" => $lang['label_firstname'],
    "type" => "text"
];

$input_ba_firstname = [
    "input_name" => "ba_firstname",
    "input_value" => $ba_firstname,
    "label" => $lang['label_firstname'],
    "type" => "text"
];

$input_sa_firstname = [
    "input_name" => "sa_firstname",
    "input_value" => $sa_firstname,
    "label" => $lang['label_firstname'],
    "type" => "text"
];

$input_lastname = [
    "input_name" => "user_lastname",
    "input_value" => $user_lastname,
    "label" => $lang['label_lastname'],
    "type" => "text"
];

$input_ba_lastname = [
    "input_name" => "ba_lastname",
    "input_value" => $ba_lastname,
    "label" => $lang['label_lastname'],
    "type" => "text"
];

$input_sa_lastname = [
    "input_name" => "sa_lastname",
    "input_value" => $sa_lastname,
    "label" => $lang['label_lastname'],
    "type" => "text"
];

$input_mail = [
    "input_name" => "user_mail",
    "input_value" => $user_mail,
    "label" => $lang['label_mail'],
    "type" => "text"
];

$input_ba_mail = [
    "input_name" => "ba_mail",
    "input_value" => $ba_mail,
    "label" => $lang['label_mail'],
    "type" => "text"
];

$input_sa_mail = [
    "input_name" => "sa_mail",
    "input_value" => $sa_mail,
    "label" => $lang['label_mail'],
    "type" => "text"
];

$input_company = [
    "input_name" => "user_company",
    "input_value" => $user_company,
    "label" => $lang['label_company'],
    "type" => "text"
];

$input_ba_company = [
    "input_name" => "ba_company",
    "input_value" => $ba_company,
    "label" => $lang['label_company'],
    "type" => "text"
];

$input_sa_company = [
    "input_name" => "sa_company",
    "input_value" => $sa_company,
    "label" => $lang['label_company'],
    "type" => "text"
];

$input_street = [
    "input_name" => "user_street",
    "input_value" => $user_street,
    "label" => $lang['label_street'],
    "type" => "text"
];

$input_ba_street = [
    "input_name" => "ba_street",
    "input_value" => $ba_street,
    "label" => $lang['label_street'],
    "type" => "text"
];

$input_sa_street = [
    "input_name" => "sa_street",
    "input_value" => $sa_street,
    "label" => $lang['label_street'],
    "type" => "text"
];

$input_street_nbr = [
    "input_name" => "user_street_nbr",
    "input_value" => $user_street_nbr,
    "label" => $lang['label_nr'],
    "type" => "text"
];

$input_ba_street_nbr = [
    "input_name" => "ba_street_nbr",
    "input_value" => $ba_street_nbr,
    "label" => $lang['label_nr'],
    "type" => "text"
];

$input_sa_street_nbr = [
    "input_name" => "sa_street_nbr",
    "input_value" => $sa_street_nbr,
    "label" => $lang['label_nr'],
    "type" => "text"
];

$input_zip = [
    "input_name" => "user_zip",
    "input_value" => $user_zip,
    "label" => $lang['label_zip'],
    "type" => "text"
];

$input_ba_zip = [
    "input_name" => "ba_zip",
    "input_value" => $ba_zip,
    "label" => $lang['label_zip'],
    "type" => "text"
];

$input_sa_zip = [
    "input_name" => "sa_zip",
    "input_value" => $sa_zip,
    "label" => $lang['label_zip'],
    "type" => "text"
];

$input_city = [
    "input_name" => "user_city",
    "input_value" => $user_city,
    "label" => $lang['label_town'],
    "type" => "text"
];

$input_ba_city = [
    "input_name" => "ba_city",
    "input_value" => $ba_city,
    "label" => $lang['label_town'],
    "type" => "text"
];

$input_sa_city = [
    "input_name" => "sa_city",
    "input_value" => $sa_city,
    "label" => $lang['label_town'],
    "type" => "text"
];

$input_country = [
    "input_name" => "user_country",
    "input_value" => $user_country,
    "label" => $lang['label_country'],
    "type" => "text"
];

$input_ba_country = [
    "input_name" => "ba_country",
    "input_value" => $ba_country,
    "label" => $lang['label_country'],
    "type" => "text"
];

$input_sa_country = [
    "input_name" => "sa_country",
    "input_value" => $sa_country,
    "label" => $lang['label_country'],
    "type" => "text"
];

$input_ba_tax_id = [
    "input_name" => "ba_tax_id_number",
    "input_value" => $ba_tax_id_number,
    "label" => $lang['label_tax_id_number'],
    "type" => "text"
];

$input_sa_tax_id = [
    "input_name" => "sa_tax_id_number",
    "input_value" => $sa_tax_id_number,
    "label" => $lang['label_tax_id_number'],
    "type" => "text"
];

$input_ba_tax_number = [
    "input_name" => "ba_tax_number",
    "input_value" => $ba_tax_number,
    "label" => $lang['label_tax_number'],
    "type" => "text"
];

$input_sa_tax_number = [
    "input_name" => "sa_tax_number",
    "input_value" => $sa_tax_number,
    "label" => $lang['label_tax_number'],
    "type" => "text"
];


$status_options = [
    $lang['status_user_verified'] => 'verified',
    $lang['status_user_waiting'] => 'waiting',
    $lang['status_user_paused'] => 'paused'
];

$input_select_user_status = [
    "input_name" => "user_verified",
    "input_value" => $user_verified,
    "label" => $lang['label_status'],
    "radios" => $status_options,
    "type" => "radios"
];

$input_check_unlocked_by_admin = [
    "input_name" => "user_unlocked_by_admin",
    "input_value" => 'yes',
    "label" => 'User unlocked by admin',
    "status" => str_contains($user_verified_by_admin,"yes") ? 'checked' : '',
    "type" => "checkbox"
];

$input_password_new = [
    "input_name" => "user_psw_new",
    "input_value" => '',
    "label" => $lang['label_psw'],
    "type" => "password"
];

$input_password_repeat = [
    "input_name" => "user_psw_reconfirmation",
    "input_value" => '',
    "label" => $lang['label_psw_repeat'],
    "type" => "password"
];

// rights management
$input_rm_user_is_admin = [
    "input_name" => "user_class",
    "input_value" => 'administrator',
    "label" => $lang['rm_is_admin'].'<br>'.$lang['rm_is_admin_txt'],
    "status" => ($user_class === 'administrator') ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_edit_user = [
    "input_name" => "drm_acp_user",
    "input_value" => 'drm_acp_user',
    "label" => $lang['rm_can_edit_user'].'<br>'.$lang['rm_can_edit_user_txt'],
    "status" => str_contains($user_drm,"drm_acp_user") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_upload_sensitive_files = [
    "input_name" => "drm_acp_sensitive_files",
    "input_value" => 'drm_acp_sensitive_files',
    "label" => $lang['rm_upload_sensitive_files'].'<br>'.$lang['rm_upload_sensitive_files_txt'],
    "status" => str_contains($user_drm,"drm_acp_sensitive_files") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_create_pages = [
    "input_name" => "drm_acp_pages",
    "input_value" => 'drm_acp_pages',
    "label" => $lang['rm_create_new_pages'],
    "status" => str_contains($user_drm,"drm_acp_pages") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_edit_pages = [
    "input_name" => "drm_acp_editpages",
    "input_value" => 'drm_acp_editpages',
    "label" => $lang['rm_edit_all_pages'],
    "status" => str_contains($user_drm,"drm_acp_editpages") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_edit_own_pages = [
    "input_name" => "drm_acp_editownpages",
    "input_value" => 'drm_acp_editownpages',
    "label" => $lang['rm_edit_own_pages'],
    "status" => str_contains($user_drm,"drm_acp_editownpages") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_publish = [
    "input_name" => "drm_can_publish",
    "input_value" => 'drm_can_publish',
    "label" => $lang['rm_can_publish'],
    "status" => str_contains($user_drm,"drm_can_publish") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_upload = [
    "input_name" => "drm_acp_files",
    "input_value" => 'drm_acp_files',
    "label" => $lang['rm_upload_files'],
    "status" => str_contains($user_drm,"drm_acp_files") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_edit_settings = [
    "input_name" => "drm_acp_system",
    "input_value" => 'drm_acp_system',
    "label" => $lang['rm_can_edit_settings'],
    "status" => str_contains($user_drm,"drm_acp_system") ? 'checked' : '',
    "type" => "checkbox"
];

$input_rm_user_can_moderate = [
    "input_name" => "drm_moderator",
    "input_value" => 'drm_moderator',
    "label" => $lang['rm_is_moderator'],
    "status" => str_contains($user_drm,"drm_moderator") ? 'checked' : '',
    "type" => "checkbox"
];

$form_tpl .= '<form hx-encoding="multipart/form-data">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-9">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">';
$form_tpl .= '<ul class="nav nav-tabs card-header-tabs">';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link active" id="info" data-bs-toggle="tab" data-bs-target="#info-tab">'.$lang['nav_btn_info'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link" id="contact" data-bs-toggle="tab" data-bs-target="#contact-tab">'.$lang['nav_btn_contact'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link" id="psw" data-bs-toggle="tab" data-bs-target="#psw-tab">'.$lang['nav_btn_psw'].'</a></li>';
$form_tpl .= '</ul>';
$form_tpl .= '</div>';
$form_tpl .= '<div class="card-body">';
$form_tpl .= '<div class="tab-content" id="myTabContent">';

$form_tpl .= '<div class="tab-pane fade show active" id="info-tab">';
$form_tpl .= se_print_form_input($input_username);

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_status'].'</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= se_print_form_input($input_select_user_status);
$form_tpl .= '<hr>';
$form_tpl .= se_print_form_input($input_check_unlocked_by_admin);
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '<div class="col">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_groups'].'</div>';
$form_tpl .= '<div class="card-body">';

$all_groups = get_all_groups();
if(is_array($all_groups)) {
    $nbr_of_groups = count($all_groups);

    foreach($all_groups as $group) {

        $get_group_id = $group['group_id'];
        $get_group_name = $group['group_name'];
        $get_group_user = $group['group_user'];

        $array_group_user = explode(" ", $get_group_user);

        $checked = "";
        if(in_array("$get_user_id", $array_group_user)) {
            $checked = "checked";
        }

        if($sub == "new") {
            $checked = "";
        }

        $input_check_this_group = [
            "input_name" => "user_groups[]",
            "input_value" => $get_group_id,
            "label" => $get_group_name,
            "status" => $checked,
            "type" => "checkbox"
        ];

        $form_tpl .= se_print_form_input($input_check_this_group);

    }

}

$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';



$form_tpl .= '</div>'; // tab-pane

$form_tpl .= '<div class="tab-pane fade" id="contact-tab">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-4">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['legend_adress_fields'].'</div>';
$form_tpl .= '<div class="card-body">';
$form_tpl .= se_print_form_input($input_firstname);
$form_tpl .= se_print_form_input($input_lastname);
$form_tpl .= se_print_form_input($input_mail);
$form_tpl .= se_print_form_input($input_company);
$form_tpl .= se_print_form_input($input_street);
$form_tpl .= se_print_form_input($input_street_nbr);
$form_tpl .= se_print_form_input($input_zip);
$form_tpl .= se_print_form_input($input_city);
$form_tpl .= se_print_form_input($input_country);
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-4">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_billing_address'].'</div>';
$form_tpl .= '<div class="card-body">';
$form_tpl .= se_print_form_input($input_ba_firstname);
$form_tpl .= se_print_form_input($input_ba_lastname);
$form_tpl .= se_print_form_input($input_ba_company);
$form_tpl .= se_print_form_input($input_ba_street);
$form_tpl .= se_print_form_input($input_ba_street_nbr);
$form_tpl .= se_print_form_input($input_ba_zip);
$form_tpl .= se_print_form_input($input_ba_city);
$form_tpl .= se_print_form_input($input_ba_country);
$form_tpl .= '<hr>';
$form_tpl .= se_print_form_input($input_ba_tax_id);
$form_tpl .= se_print_form_input($input_ba_tax_number);
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-4">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_delivery_address'].'</div>';
$form_tpl .= '<div class="card-body">';
$form_tpl .= se_print_form_input($input_sa_firstname);
$form_tpl .= se_print_form_input($input_sa_lastname);
$form_tpl .= se_print_form_input($input_sa_company);
$form_tpl .= se_print_form_input($input_sa_street);
$form_tpl .= se_print_form_input($input_sa_street_nbr);
$form_tpl .= se_print_form_input($input_sa_zip);
$form_tpl .= se_print_form_input($input_sa_city);
$form_tpl .= se_print_form_input($input_sa_country);
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // tab-pane

$form_tpl .= '<div class="tab-pane fade" id="psw-tab">';

$form_tpl .= '<p>'.$lang['msg_edit_psw'].'</p>';
$input_group = [
    se_print_form_input($input_password_new),
    se_print_form_input($input_password_repeat)
];

$form_tpl .= str_replace(['{col1}','{col2}'],$input_group,$bs_row_col2);
$form_tpl .= '<hr>';

$form_tpl .= '<div class="alert alert-danger mb-2">';
$form_tpl .= '<h4 class="text-danger">'.$lang['label_rm'].'</h4>';
$form_tpl .= '<p>'.$lang['rm_description'].'</p>';
$form_tpl .= se_print_form_input($input_rm_user_is_admin);
$form_tpl .= '<hr>';
$form_tpl .= '<div class="alert alert-danger mb-2">';
$form_tpl .= se_print_form_input($input_rm_user_can_edit_user);
$form_tpl .= '<hr>';
$form_tpl .= se_print_form_input($input_rm_user_can_upload_sensitive_files);
$form_tpl .= '</div>';
$form_tpl .= '<div class="alert alert-warning mb-2">';
$form_tpl .= se_print_form_input($input_rm_user_can_edit_settings);
$form_tpl .= se_print_form_input($input_rm_user_can_upload);
$form_tpl .= '</div>';
$form_tpl .= '<div class="alert alert-warning mb-2">';
$form_tpl .= se_print_form_input($input_rm_user_can_create_pages);
$form_tpl .= se_print_form_input($input_rm_user_can_edit_pages);
$form_tpl .= se_print_form_input($input_rm_user_can_edit_own_pages);
$form_tpl .= se_print_form_input($input_rm_user_can_publish);
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '<div class="alert alert-secondary mb-2">';
$form_tpl .= se_print_form_input($input_rm_user_can_moderate);
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // tab-pane

$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-3">';



$form_tpl .= '<div class="card mb-2">';
$form_tpl .= '<div class="card-header">Avatar</div>';
$form_tpl .= '<div class="card-body">';

$user_avatar_path = '/assets/avatars/' . md5($user_nick) . '.png';

if(is_file("../public$user_avatar_path")) {
    $form_tpl .= '<p class="text-center"><img src="'.$user_avatar_path.'" class="rounded-circle avatar"></p>';
    $form_tpl .= '<div class="form-check">';
    $form_tpl .= '<input type="checkbox" name="deleteAvatar" class="form-check-input" id="avatar"><label class="form-check-label" for="avatar">' . $lang['delete'] . '</label>';
    $form_tpl .= '</div>';
} else {
    $form_tpl .= '<p class="text-center"><img src="/themes/administration/images/avatar.png" class="rounded-circle avatar"></p>';
}

$form_tpl .= '<label>Upload</label>';
$form_tpl .= '<input name="avatar" class="form-control" type="file" size="50">';

$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '<div class="card p-2">';
$form_tpl .= '<button class="btn btn-primary" hx-post="'.$writer_uri.'" hx-trigger="click" hx-swap="innerHTML" hx-target="#formResponse" name="save_user" value="'.$form_mode.'">'.$btn_submit_text.'</button>';

$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

$form_tpl .= '</form>';

echo $form_tpl;