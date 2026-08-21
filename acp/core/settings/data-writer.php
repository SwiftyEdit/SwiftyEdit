<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var object $db_user
 * @var object $db_posts
 * @var string $db_type
 * @var array $icon
 * @var array $se_settings
 * @var string $se_branding_path
 */


// remove a branding image (page logo / page thumbnail / favicon set)
if (isset($_POST['delete_branding'])) {
    require_once __DIR__ . '/branding-widget.php';

    $target = $_POST['delete_branding'];
    if (in_array($target, ['logo', 'thumbnail', 'favicon'], true)) {

        foreach (glob($se_branding_path . '/' . $target . '.*') as $old_file) {
            @unlink($old_file);
        }
        if ($target === 'favicon') {
            foreach (glob($se_branding_path . '/favicon-*.png') as $old_file) {
                @unlink($old_file);
            }
            @unlink($se_branding_path . '/favicon.ico');
            @unlink($se_branding_path . '/site.webmanifest');
        }

        se_write_option(['prefs_page' . $target => ''], 'se');

        // only the preview fragment - the persistent Uppy dropzone next to it is untouched
        echo se_render_branding_preview(
            $target,
            '',
            $se_branding_path,
            '/admin-xhr/settings/general/write/',
            $lang['btn_delete'],
            $lang['msg_confirm_delete_file']
        );
    }
}

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

    $data['prefs_posts_guest_order_enable'] = 0;
    if(isset($_POST['prefs_posts_guest_order_enable'])) {
        $data['prefs_posts_guest_order_enable'] = 1;
    }

    $data['prefs_wishlist_enabled'] = 0;
    if(isset($_POST['prefs_wishlist_enabled'])) {
        $data['prefs_wishlist_enabled'] = 1;
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

    if($_POST['delivery_country'] == '') {
        header( "HX-Trigger: update_deliveryCountries_list");
        exit;
    }

    $country_code = sanitizeUserInputs($_POST['delivery_country']);
    $status = (int) $_POST['delivery_country_status'];
    $tax = (int) $_POST['delivery_country_tax'];

    $countries = se_get_countries();
    foreach ($countries as $country) {
        if ($country['alpha2'] == $country_code) {
            $country_name = $country['name'];
        }
    }

    if($_POST['send_delivery_country'] == 'save') {
        $db_content->insert("se_delivery_areas", [
            "code" => $country_code,
            "name" => $country_name,
            "status" => $status,
            "tax" => $tax
        ]);
    } else {
        $db_content->update("se_delivery_areas", [
            "code" => $country_code,
            "name" => $country_name,
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

    $data['prefs_publisher_mode'] = '';
    if(isset($_POST['prefs_publisher_mode'])) {
        $data['prefs_publisher_mode'] = 'overwrite';
    }

    $data['prefs_uploads_remain_unchanged'] = '';
    if(isset($_POST['prefs_uploads_remain_unchanged'])) {
        $data['prefs_uploads_remain_unchanged'] = 'yes';
    }

    se_write_option($data,'se');
    show_toast($lang['msg_success_db_changed'],'success');
}

// write database (SQLite WAL mode) settings
if (isset($_POST['update_database']) && $db_type === 'sqlite') {

    $wal_enabled = isset($_POST['prefs_wal_mode']);
    $target_mode = $wal_enabled ? 'wal' : 'delete';
    $sqlite_error = '';

    // the journal mode is stored persistently in each database file, so this
    // only needs to run when the admin actually toggles it - not on every request.
    // PRAGMA journal_mode=... returns the resulting mode as a row, so it must
    // be run through query() - exec() silently fails to apply it.
    foreach ([$db_content, $db_user, $db_posts] as $db_conn) {
        try {
            $result_mode = strtolower((string) $db_conn->pdo->query('PRAGMA journal_mode = ' . $target_mode)->fetchColumn());
            if ($result_mode !== $target_mode) {
                $sqlite_error = 'unexpected journal_mode "'.$result_mode.'"';
            }
        } catch (\PDOException $e) {
            $sqlite_error = $e->getMessage();
        }
    }

    if ($sqlite_error === '') {
        $data['prefs_wal_mode'] = $wal_enabled ? 'yes' : '';
        se_write_option($data,'se');
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        show_toast($lang['msg_error_wal_mode'].' '.$sqlite_error,'danger');
    }
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
        if(is_string($val)) {
            $data[htmlentities($key)] = htmlentities($val);
        }
    }

    $data['prefs_required_fields_registration'] = json_encode($_POST['required_fields']);

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

