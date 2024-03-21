<?php
//error_reporting(E_ALL ^E_WARNING ^E_NOTICE);
//prohibit unauthorized access
require 'core/access.php';

/* save default language */
if(isset($_POST['save_prefs_language'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
}

/* save hidden languages */
if(isset($_POST['save_hide_language'])) {
    $data['prefs_deactivated_languages'] = json_encode($_POST['hide_langs']);
    se_write_option($data,'se');
}


/* save system settings */
if(isset($_POST['save_system'])) {

    if((substr("$prefs_cms_domain",0,7) !== 'http://')) {
        $data['prefs_cms_domain'] = $prefs_cms_domain = '';
    }
    if((substr("$prefs_cms_ssl_domain",0,8) !== 'https://')) {
        $data['prefs_cms_ssl_domain'] = '';
    }

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    se_write_option($data,'se');
}

/**
 * save date/time settings
 */

if(isset($_POST['save_datetime'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    se_write_option($data,'se');
}


/* save theme and template preferences */
if(isset($_POST['save_prefs_themes'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    if(isset($_POST['prefs_smarty_cache'])) {
        $data['prefs_smarty_cache'] = 1;
    } else {
        $data['prefs_smarty_cache'] = 0;
    }

    if(isset($_POST['prefs_smarty_compile_check'])) {
        $data['prefs_smarty_compile_check'] = 1;
    } else {
        $data['prefs_smarty_compile_check'] = 0;
    }

    $data['prefs_smarty_cache_lifetime'] = (int) $_POST['prefs_smarty_cache_lifetime'];

    se_write_option($data,'se');
}

if(isset($_POST['delete_smarty_cache'])) {
    se_delete_smarty_cache('all');
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




/**
 * Domain
 */

$prefs_cms_domain_input = '<input class="form-control" type="text" name="prefs_cms_domain" value="'.$se_prefs['prefs_cms_domain'].'">';
$prefs_cms_ssl_domain_input = '<input class="form-control" type="text" name="prefs_cms_ssl_domain" value="'.$se_prefs['prefs_cms_ssl_domain'].'">';
$prefs_cms_base_input = '<input class="form-control" type="text" name="prefs_cms_base" value="'.$se_prefs['prefs_cms_base'].'">';

echo '<form action="?tn=system&sub=general&file=general-system" method="POST" class="form-horizontal">';
echo tpl_form_control_group('',$lang['label_settings_cms_domain'],$prefs_cms_domain_input);
echo tpl_form_control_group('',$lang['label_settings_cms_domain_ssl'],$prefs_cms_ssl_domain_input);
echo tpl_form_control_group('',$lang['label_settings_cms_base'],$prefs_cms_base_input);
echo tpl_form_control_group('','',"<input type='submit' class='btn btn-success' name='save_system' value='$lang[save]'>");
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';


/**
 * Date and Time
 */

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

echo '<h5 class="heading-line">'.$lang['label_settings_datetime'].'</h5>';
echo '<form action="?tn=system&sub=general&file=general-system" method="POST">';

echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_timezone'].'</label>';
echo '<select class="form-control" name="prefs_timezone">';
$x=0;
foreach($timezones as $key => $value) {

    if(strpos($value,'/') !== false) {
        $region[$x] = substr($value,0,strpos($value,'/'));
        $location = substr($value, strpos($value,'/')+1);
    } else {
        $region[$x] = 'Other';
        $location = $value;
    }

    $s_optgroup = '';
    $e_optgroup = '';

    if(($region[$x] != $region[$x-1]) OR ($x==0)) {
        $s_optgroup = '<optgroup label="'.$region[$x].'">';
        $cnt_opt = $x;
    }

    if(($region[$x] != $region[$x-1]) AND ($x != $cnt_opt)) {
        $e_optgroup = '</optgroup>';
    }

    $selected = '';
    if($se_prefs['prefs_timezone'] == $value) {
        $selected = 'selected';
    }

    echo $s_optgroup;
    echo '<option value="'.$value.'" '.$selected.'>'.$location.'</option>';
    echo $e_optgroup;

    $x++;

}
echo '</select>';
echo '</div>';


$date_formats = array("Y-m-d","d.m.Y","d/m/Y","m/d/Y");

echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_date_format'].'</label>';
echo '<select class="form-control" name="prefs_dateformat">';

foreach($date_formats as $dates) {

    $selected = '';
    if($se_prefs['prefs_dateformat'] == $dates) {
        $selected = 'selected';
    }

    echo '<option value="'.$dates.'" '.$selected.'>'.date("$dates",time()).' ('.$dates.')</option>';
}


echo '</select>';
echo '</div>';

$time_formats = array("H:i","g:i a","g:i A");

echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_time_format'].'</label>';
echo '<select class="form-control" name="prefs_timeformat">';

foreach($time_formats as $times) {

    $selected = '';
    if($se_prefs['prefs_timeformat'] == $times) {
        $selected = 'selected';
    }

    echo '<option value="'.$times.'" '.$selected.'>'.date("$times",time()).' ('.$times.')</option>';
}

echo '</select>';
echo '</div>';

echo '<input type="submit" class="btn btn-success" name="save_datetime" value="'.$lang['save'].'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';


/**
 * Themes / Templates
 */


echo '<h5 class="heading-line">'.$lang['label_settings_themes'].'</h5>';
echo '<form action="?tn=system&sub=general&file=general-system" method="POST" class="form-horizontal">';

$prefs_maintenance_input = [
    "input_name" => "prefs_maintenance_code",
    "input_value" => $se_prefs['prefs_maintenance_code'],
    "label" => $lang['label_settings_maintenance_code']
];
echo tpl_form_input_text($prefs_maintenance_input);

if($se_prefs['prefs_usertemplate'] == '') {
    $se_prefs['prefs_usertemplate'] = 'off';
}
echo '<div class="form-check">';
echo '<input type="radio" class="form-check-input" id="usertpl_off" name="prefs_usertemplate" value="off" '.($se_prefs['prefs_usertemplate'] == "off" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="usertpl_off">'.$lang['label_settings_themes_userstyles_off'].'</label>';
echo '</div>';
echo '<div class="form-check">';
echo '<input type="radio" class="form-check-input" id="usertpl_on" name="prefs_usertemplate" value="on" '.($se_prefs['prefs_usertemplate'] == "on" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="usertpl_on">'.$lang['label_settings_themes_userstyles_on'].'</label>';
echo '</div>';
echo '<div class="form-check">';
echo '<input type="radio" class="form-check-input" id="usertpl_overwrite" name="prefs_usertemplate" value="overwrite" '.($se_prefs['prefs_usertemplate'] == "overwrite" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="usertpl_overwrite">'.$lang['label_settings_themes_userstyles_overwrite'].'</label>';
echo '</div>';

echo '<hr>';

echo '<div class="form-group form-check mt-3">';
echo '<input type="checkbox" class="form-check-input" id="cache" name="prefs_smarty_cache" '.($se_prefs['prefs_smarty_cache'] == "1" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="cache">Smarty Cache</label>';
echo '</div>';


echo '<div class="form-group form-check">';
echo '<input type="checkbox" class="form-check-input" id="compile" name="prefs_smarty_compile_check" '.($se_prefs['prefs_smarty_compile_check'] == "1" ? 'checked' :'').'>';
echo '<label class="form-check-label" for="compile">Smarty Compile check</label>';
echo '</div>';


$cache_size = se_dir_size(SE_CONTENT.'/cache/cache/');
$compile_size = se_dir_size(SE_CONTENT.'/cache/templates_c/');
$complete_size = readable_filesize($cache_size+$compile_size);

echo '<div class="input-group mb-3">';
echo '<label class="form-label" for="cache_lifetime">Smarty Cache lifetime</label>';
echo '<div class="input-group mb-3">';
echo '<input class="form-control" type="text" id="cache_lifetime" name="prefs_smarty_cache_lifetime" value="'.$se_prefs['prefs_smarty_cache_lifetime'].'">';
echo '<button class="btn btn-primary" type="submit" name="delete_smarty_cache">('.$complete_size.') '.$lang['btn_delete'].'</button>';
echo '</div>';
echo '</div>';

echo '<input type="submit" class="btn btn-success" name="save_prefs_themes" value="'.$lang['save'].'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';








/**
 *  languages
 */


echo '<h5 class="heading-line">'.$lang['label_language'].'</h5>';


echo '<div class="row">';
echo '<div class="col-md-6">';

echo '<fieldset>';
echo '<legend>'.$lang['label_settings_default_language'].'</legend>';
echo '<form action="?tn=system&sub=general&file=general-system" method="POST" class="form-horizontal">';

$get_all_languages = get_all_languages();

$select_default_language = '<select name="prefs_default_language" class="form-control custom-select">';
foreach($get_all_languages as $langs) {

    $selected = "";
    if($se_prefs['prefs_default_language'] == $langs['lang_folder']) {
        $selected = "selected";
    }

    $select_default_language .= '<option '.$selected.' value="'.$langs['lang_folder'].'">'.$langs['lang_desc'].'</option>';
}
$select_default_language .= '</select>';

echo '<div class="form-group">';
echo $select_default_language;
echo '</div>';

echo '<input type="submit" class="btn btn-success" name="save_prefs_language" value="'.$lang['save'].'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';
echo '</fieldset>';

echo '</div>';
echo '<div class="col-md-6">';

echo '<fieldset>';
echo '<legend>'.$lang['label_settings_hide_languages'].'</legend>';
echo '<form action="?tn=system&sub=general&file=general-system" method="POST" class="">';

echo '<table class="table table-sm table-hover">';

if($se_prefs['prefs_deactivated_languages'] != '') {
    $hidden_langs = json_decode($se_prefs['prefs_deactivated_languages']);
}

foreach($get_all_languages as $langs) {

    $check = '';
    if(is_array($hidden_langs)) {
        if (in_array($langs['lang_folder'], $hidden_langs)) {
            $check = 'checked';
        }
    }

    echo '<tr>';
    echo '<td>';
    echo '<input type="checkbox" id="'.$langs['lang_folder'].'" class="form-check-input" name="hide_langs[]" value="'.$langs['lang_folder'].'" '.$check.'>';
    echo '</td>';
    echo '<td><label for="'.$langs['lang_folder'].'" class="d-block">'.$langs['lang_sign'].'</label></td>';
    echo '<td><label for="'.$langs['lang_folder'].'" class="d-block">'.$langs['lang_desc'].'</label></td>';
    echo '<td><label for="'.$langs['lang_folder'].'" class="d-block">'.$langs['lang_folder'].'</label></td>';
    echo '</tr>';

}

echo '</table>';

echo '<input type="submit" class="btn btn-success" name="save_hide_language" value="'.$lang['save'].'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';
echo '</fieldset>';

echo '</div>';
echo '</div>';