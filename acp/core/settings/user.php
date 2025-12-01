<?php

/**
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 * @var string $hidden_csrf_token
 */

$writer_uri = '/admin-xhr/settings/general/write/';
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].' / '.$lang['nav_btn_user'].'</div>';


$input_check_userregistration = [
    "input_name" => "prefs_userregistration",
    "input_value" => $se_settings['userregistration'],
    "label" => $lang['label_settings_allow_registration'],
    "type" => "checkbox",
    "status" => $se_settings['userregistration'] == "yes" ? 'checked' :''
];

$input_check_login_form = [
    "input_name" => "prefs_showloginform",
    "input_value" => $se_settings['showloginform'],
    "label" => $lang['label_settings_show_login'],
    "type" => "checkbox",
    "status" => $se_settings['showloginform'] == "yes" ? 'checked' :''
];

$input_check_user_unlock_by_admin = [
    "input_name" => "prefs_user_unlock_by_admin",
    "input_value" => $se_settings['user_unlock_by_admin'],
    "label" => $lang['label_settings_new_user_unlock_by_admin'],
    "type" => "checkbox",
    "status" => $se_settings['user_unlock_by_admin'] == "yes" ? 'checked' :''
];

$input_session_lifetime = [
    "input_name" => "prefs_acp_session_lifetime",
    "input_value" => $se_settings['acp_session_lifetime'],
    "label" => $lang['label_settings_acp_session_lifetime'],
    "type" => "text"
];

$input_comments_mode = [
    "input_name" => "prefs_comments_mode",
    "input_value" => $se_settings['comments_mode'],
    "radios" => [
        "label_settings_comments_mode_1" => 1,
        "label_settings_comments_mode_2" => 2,
        "label_settings_comments_mode_3" => 3
    ],
    "type" => "radios"
];

$input_comments_auth = [
    "input_name" => "prefs_comments_authorization",
    "input_value" => $se_settings['comments_authorization'],
    "radios" => [
        "label_settings_comments_auth_1" => 1,
        "label_settings_comments_auth_2" => 2,
        "label_settings_comments_auth_3" => 3
    ],
    "type" => "radios"
];

$input_comments_autoclose = [
    "input_name" => "prefs_comments_autoclose",
    "input_value" => $se_settings['comments_autoclose'],
    "label" => $lang['label_settings_comments_autoclose_time'],
    "type" => "text"
];

$input_comments_max_entries = [
    "input_name" => "prefs_comments_max_entries",
    "input_value" => $se_settings['comments_max_entries'],
    "label" => $lang['label_settings_comments_max_entries'],
    "type" => "text"
];

$input_comments_max_level = [
    "input_name" => "prefs_comments_max_level",
    "input_value" => $se_settings['comments_max_level'],
    "label" => $lang['label_settings_comments_max_level'],
    "type" => "text"
];


$input_select_reactions = [
    "input_name" => "prefs_posts_default_votings",
    "input_value" => $se_settings['posts_default_votings'],
    "label" => $lang['label_votings'],
    "options" => [
        $lang['label_settings_votings_off'] => 1,
        $lang['label_settings_votings_on_registered'] => 2,
        $lang['label_settings_votings_on_global'] => 3
    ],
    "type" => "select"
];

$input_blacklist_usernames = [
    "input_name" => "prefs_blacklist_usernames",
    "input_value" => $se_settings['blacklist_usernames'],
    "label" => 'Blacklist (user names)',
    "type" => "textarea"
];

echo '<div class="card p-3">';

echo '<h5 class="heading-line">'.$lang['register'].'</h5>';

echo '<form hx-post="'.$writer_uri.'" hx-target="body" hx-swap="beforeend">';

echo '<div class="row">';
echo '<div class="col-md-6">';
echo se_print_form_input($input_check_userregistration);
echo se_print_form_input($input_check_login_form);
echo se_print_form_input($input_check_user_unlock_by_admin);
echo se_print_form_input($input_session_lifetime);
echo se_print_form_input($input_blacklist_usernames);
echo '</div>';
echo '<div class="col-md-6">';
echo '<div class="card">';
echo '<div class="card-header">'.$lang['legend_required_fields'].'</div>';
echo '<div class="card-body scroll-container">';

if($se_settings['required_fields_registration'] != '') {
    $required_fields_registration = json_decode($se_settings['required_fields_registration']);
}

if (!is_array($required_fields_registration)) {
    $required_fields_registration = [];
}

/**
 * get cols from installer file
 * @var array $cols
 */
include __DIR__.'/../../../install/contents/se_user.php';
$cols = array_keys($cols);
$excludedFields = [
    'user_nick','user_psw','user_mail','user_social_media',
    'user_id', 'user_class', 'user_psw_hash', 'user_failed_logins','user_unlock_code',
    'user_groups','user_avatar','user_registerdate','user_verified','user_verified_by_admin','user_drm',
    'user_acp_settings','user_activationkey','user_reset_psw'];
$availableFields = array_diff($cols, $excludedFields);

foreach($availableFields as $key) {

    $check = '';
    if (in_array($key, $required_fields_registration)) {
        $check = 'checked';
    }

    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="checkbox" name="required_fields[]" value="'.$key.'" id="'.$key.'" '.$check.'>';
    echo '<label class="form-check-label" for="'.$key.'">';
    echo $key;
    echo '</label>';
    echo '</div>';

}


echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo $hidden_csrf_token;
echo '<button type="submit" class="btn btn-primary" name="update_user" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '<h5 class="heading-line">'.$lang['label_comments'].'</h5>';
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo $lang['label_settings_comments_mode'];
echo se_print_form_input($input_comments_mode);

echo $lang['label_settings_comments_auth'];
echo se_print_form_input($input_comments_auth);

echo se_print_form_input($input_comments_autoclose);
echo se_print_form_input($input_comments_max_entries);
echo se_print_form_input($input_comments_max_level);

echo '<h5 class="heading-line">'.$lang['label_votings'].'</h5>';

echo se_print_form_input($input_select_reactions);


echo '<button type="submit" class="btn btn-primary" name="update_reactions" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '</div>';