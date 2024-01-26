<?php

//prohibit unauthorized access
require 'core/access.php';


/* save user preferences */
if(isset($_POST['save_prefs_user'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    if(isset($_POST['prefs_userregistration'])) {
        $data['prefs_userregistration'] = 'yes';
    } else {
        $data['prefs_userregistration'] = 'no';
    }

    if(isset($_POST['prefs_showloginform'])) {
        $data['prefs_showloginform'] = 'yes';
    } else {
        $data['prefs_showloginform'] = 'no';
    }

    $data['prefs_acp_session_lifetime'] = (int) $_POST['prefs_acp_session_lifetime'];

    se_write_option($data,'se');
}

/* save comments mode */
if(isset($_POST['update_comments'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
}


if(!empty($_POST)) {
    /* read the preferences again */
    $se_get_preferences = se_get_preferences();

    foreach($se_get_preferences as $k => $v) {
        $key = $se_get_preferences[$k]['option_key'];
        $value = $se_get_preferences[$k]['option_value'];
        $se_prefs[$key] = $value;
    }

    foreach($se_prefs as $k => $v) {
        $$k = stripslashes($v);
    }
}




echo '<h5 class="heading-line">'.$lang['f_prefs_user'].'</h5>';
echo '<form action="?tn=system&sub=general&file=general-user" method="POST" class="form-horizontal">';


echo '<div class="form-group form-check mt-3">';
echo '<input type="checkbox" class="form-check-input" id="userregister" name="prefs_userregistration" '.($se_prefs['prefs_userregistration'] == "yes" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="userregister">'.$lang['f_prefs_registration'].'</label>';
echo '</div>';


echo '<div class="form-group form-check mt-3">';
echo '<input type="checkbox" class="form-check-input" id="loginform" name="prefs_showloginform" '.($se_prefs['prefs_showloginform'] == "yes" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="loginform">'.$lang['f_prefs_showloginform'].'</label>';
echo '</div>';

$input_acp_session_lifetime = [
    "input_name" => "prefs_acp_session_lifetime",
    "input_value" => $se_prefs['prefs_acp_session_lifetime'],
    "label" => $lang['acp_session_lifetime']
];

echo tpl_form_input_text($input_acp_session_lifetime);

echo '<input type="submit" class="btn btn-success" name="save_prefs_user" value="'.$lang['save'].'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';

echo '<h5 class="heading-line">'.$lang['tn_comments'].'</h5>';

echo '<form action="?tn=system&sub=general&file=general-user" method="POST">';

/* mode */

echo '<div class="row">';
echo '<div class="col-6">';

echo '<fieldset>';
echo '<legend>'.$lang['label_comment_mode'].'</legend>';

if($se_prefs['prefs_comments_mode'] == 1) {
    $select_mode_1 = "checked";
} else if($prefs_comments_mode == 2) {
    $select_mode_2 = "checked";
} else {
    $select_mode_3 = "checked";
}

echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_comments_mode" value="1" id="mode_1" '.$select_mode_1.'>
				<label for="mode_1">' . $lang['prefs_comments_mode_1'] . '</label>
	 		</div>';
echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_comments_mode" value="2" id="mode_2" '.$select_mode_2.'>
				<label for="mode_2">' . $lang['prefs_comments_mode_2'] . '</label>
	 		</div>';
echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_comments_mode" value="3" id="mode_3" '.$select_mode_3.'>
				<label for="mode_3">' . $lang['prefs_comments_mode_3'] . '</label>
	 		</div>';

echo '</fieldset>';

echo '</div>';
echo '<div class="col-6">';


/* prefs_comments_authorization */

echo '<fieldset>';
echo '<legend>'.$lang['label_comment_auth'].'</legend>';

if($se_prefs['prefs_comments_authorization'] == 1) {
    $select_auth_1 = "checked";
} else if($se_prefs['prefs_comments_authorization'] == 2) {
    $select_auth_2 = "checked";
} else {
    $select_auth_3 = "checked";
}

echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_comments_authorization" value="1" id="auth_1" '.$select_auth_1.'>
				<label for="auth_1">' . $lang['prefs_comments_auth_1'] . '</label>
	 		</div>';
echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_comments_authorization" value="2" id="auth_2" '.$select_auth_2.'>
				<label for="auth_2">' . $lang['prefs_comments_auth_2'] . '</label>
	 		</div>';
echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_comments_authorization" value="3" id="auth_3" '.$select_auth_3.'>
				<label for="auth_3">' . $lang['prefs_comments_auth_3'] . '</label>
	 		</div>';

echo '</fieldset>';

echo '</div>';
echo '</div>';


$input_comments_autoclose = [
    "input_name" => "prefs_comments_autoclose",
    "input_value" => $se_prefs['prefs_comments_autoclose'],
    "label" => $lang['prefs_comments_autoclose_time']
];

$input_comments_max_entries = [
    "input_name" => "prefs_comments_max_entries",
    "input_value" => $se_prefs['prefs_comments_max_entries'],
    "label" => $lang['label_comments_max_entries']
];

$input_comments_max_level = [
    "input_name" => "prefs_comments_max_level",
    "input_value" => $se_prefs['prefs_comments_max_level'],
    "label" => $lang['label_comments_max_level']
];

echo '<div class="row">';
echo '<div class="col-md-4">';
echo tpl_form_input_text($input_comments_autoclose);
echo '</div>';
echo '<div class="col-md-4">';
echo tpl_form_input_text($input_comments_max_entries);
echo '</div>';
echo '<div class="col-md-4">';
echo tpl_form_input_text($input_comments_max_level);
echo '</div>';
echo '</div>';


echo '<h5 class="heading-line">'.$lang['label_votings'].'</h5>';

/* votings */
$sel_votings1 = '';
$sel_votings2 = '';
$sel_votings3 = '';

if($prefs_posts_default_votings == 1 OR $prefs_posts_default_votings == '') {
    $sel_votings1 = 'selected';
} else if($prefs_posts_default_votings == 2) {
    $sel_votings2 = 'selected';
} else if($prefs_posts_default_votings == 3) {
    $sel_votings3 = 'selected';
}

echo '<div class="mb-3">';
echo '<select class="form-control custom-select" name="prefs_posts_default_votings">';
echo '<option value="1" '.$sel_votings1.'>'.$lang['label_votings_off'].'</option>';
echo '<option value="2" '.$sel_votings2.'>'.$lang['label_votings_on_registered'].'</option>';
echo '<option value="3" '.$sel_votings3.'>'.$lang['label_votings_on_global'].'</option>';
echo '</select>';
echo'</div>';

echo '<input type="submit" class="btn btn-success" name="update_comments" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';
