<?php

/**
 * global variables
 * @var array $lang
 */


// write event settings
if (isset($_POST['update_events'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write blog settings
if (isset($_POST['update_posts'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write shop settings
if (isset($_POST['update_shop_settings'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write payment plugins
if (isset($_POST['update_payment_plugins'])) {
    $data['prefs_payment_addons'] = '';
    if(isset($_POST['payment_addons'])) {
        $pm_addon_str = json_encode($_POST['payment_addons'],JSON_FORCE_OBJECT);
        $data['prefs_payment_addons'] = $pm_addon_str;
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write shipping plugins
if (isset($_POST['update_shipping_plugins'])) {
    $data['prefs_delivery_addons'] = '';
    if(isset($_POST['delivery_addons'])) {
        $sh_addon_str = json_encode($_POST['delivery_addons'],JSON_FORCE_OBJECT);
        $data['prefs_delivery_addons'] = $sh_addon_str;
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

if(isset($_POST['update_language'])) {
    $data['prefs_default_language'] = htmlentities($_POST['prefs_default_language']);
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

if(isset($_POST['update_hide_languages'])) {
    $data['prefs_deactivated_languages'] = json_encode($_POST['hide_langs']);
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// save delivery area
if (isset($_POST['send_delivery_country'])) {

    $country = sanitizeUserInputs($_POST['delivery_country']);
    $status = (int) $_POST['delivery_country_status'];
    $tax = (int) $_POST['delivery_country_tax'];

    if($_POST['send_delivery_country'] == 'save') {
        $db_content->insert("se_delivery_areas", [
            "name" => $country,
            "status" => $status,
            "tax" => $tax
        ]);
    } else {
        $db_content->update("se_delivery_areas", [
            "name" => $country,
            "status" => $status,
            "tax" => $tax
        ],[
            "id" => (int) $_POST['send_delivery_country']
        ]);
    }


    header( "HX-Trigger: update_deliveryCountries_list");
}

// delete delivery country
if (isset($_POST['delete_delivery_country'])) {
    $delete_id = (int) $_POST['delete_delivery_country'];

    $db_content->delete("se_delivery_areas", [
        "id" => $delete_id
    ]);
    header( "HX-Trigger: update_deliveryCountries_list");
}

if (isset($_POST['update_email'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write general settings
if (isset($_POST['update_general'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write domain and server settings
if (isset($_POST['update_general_system'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write date and time settings
if (isset($_POST['update_datetime'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write themes settings
if (isset($_POST['update_themes'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    $data['prefs_smarty_compile_check'] = 0;
    if(isset($_POST['prefs_smarty_compile_check'])) {
        $data['prefs_smarty_compile_check'] = 1;
    }

    $data['prefs_smarty_cache'] = 0;
    if(isset($_POST['prefs_smarty_cache'])) {
        $data['prefs_smarty_cache'] = 1;
    }

    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write mail settings
if (isset($_POST['update_email'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write user settings
if (isset($_POST['update_user'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    $data['prefs_showloginform'] = 'no';
    if(isset($_POST['prefs_showloginform'])) {
        $data['prefs_showloginform'] = 'yes';
    }

    $data['prefs_user_unlock_by_admin'] = 'no';
    if(isset($_POST['prefs_user_unlock_by_admin'])) {
        $data['prefs_user_unlock_by_admin'] = 'yes';
    }

    $data['prefs_userregistration'] = 'no';
    if(isset($_POST['prefs_userregistration'])) {
        $data['prefs_userregistration'] = 'yes';
    }

    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write comments and reactions settings
if (isset($_POST['update_reactions'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    //print_r($_POST);

    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// labels
if (isset($_POST['post_label'])) {

    $label_color = sanitizeUserInputs($_POST['label_color']);
    $label_title = sanitizeUserInputs($_POST['label_title']);
    $label_description = sanitizeUserInputs($_POST['label_description']);

    $label_custom_id = clean_filename($label_title);

    $data = $db_content->insert("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ]);

    show_toast($lang['msg_success_db_changed'],'success');
    record_log($_SESSION['user_nick'],"create new label","1");
    header( "HX-Trigger: updated_labels");
}

if(isset($_POST['update_label'])) {

    $label_color = sanitizeUserInputs($_POST['label_color']);
    $label_title = sanitizeUserInputs($_POST['label_title']);
    $label_description = sanitizeUserInputs($_POST['label_description']);

    $data = $db_content->update("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ],[
        "label_id" => (int) $_POST['label_id']
    ]);

    show_toast("Label updated successfully","success");
    header( "HX-Trigger: updated_labels");
}

if(isset($_POST['delete_label'])) {

    $label_id = (int) $_POST['label_id'];

    $data = $db_content->delete("se_labels", [
        "label_id" => $label_id
    ]);
    show_toast($lang['msg_success_db_changed'],'success');
    record_log($_SESSION['user_nick'],"deleted label","5");
}

if(isset($_POST['sendmail_test'])) {
    $subject = 'SwiftyEdit Mail Test';
    $message = 'SwiftyEdit Test (via '.$se_settings['mailer_type'].')';

    if($se_settings['notify_mail'] != '') {
        $recipient = array('name' => $se_settings['mailer_name'], 'mail' => $se_settings['notify_mail']);
    } else {
        $recipient = array('name' => $se_settings['mailer_name'], 'mail' => $se_settings['mailer_adr']);
    }

    $testmail = se_send_mail($recipient,$subject,$message);
    if($testmail == 1) {
        echo '<p class="alert alert-success mt-3">'.$icon['check'].' '.$lang['msg_success_mailer_sent_test'].'</p>';
    } else {
        echo '<div class="alert alert-danger mt-3">'.print_r($testmail).'</div>';;
    }
}

