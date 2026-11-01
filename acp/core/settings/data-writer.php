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


// save a section of a settings page, only the fields of the section are stored
// (see se_settings_sections() in acp/core/settings/functions.php)
if (isset($_POST['save_settings'])) {
    require_once __DIR__ . '/functions.php';

    $data = se_settings_section_values((string) $_POST['save_settings'], $_POST);
    if ($data !== null) {
        se_write_option($data, 'se');
        show_toast($lang['msg_success_db_changed'], 'success');
    }
    exit;
}

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
// save a delivery area (send_delivery_country = save or the id of the area)
if (isset($_POST['send_delivery_country'])) {

    // the code has to be an ISO 3166 country, status and tax are 1 (yes) or 2 (no)
    $country_code = (string) ($_POST['delivery_country'] ?? '');
    $country_name = '';
    foreach (se_get_countries() as $country) {
        if ($country['alpha2'] === $country_code) {
            $country_name = $country['name'];
        }
    }
    if ($country_name === '') {
        show_toast($lang['label_please_select'], 'danger');
        exit;
    }

    $values = [
        "code" => $country_code,
        "name" => $country_name,
        "status" => ($_POST['delivery_country_status'] ?? '') == '2' ? '2' : '1',
        "tax" => ($_POST['delivery_country_tax'] ?? '') == '1' ? '1' : '2'
    ];

    // a country is listed once, adding it again updates it
    $area_id = (int) $_POST['send_delivery_country'];
    if ($area_id < 1) {
        $area_id = (int) $db_content->get("se_delivery_areas", "id", ["code" => $country_code]);
    }

    if ($area_id > 0) {
        $db_content->update("se_delivery_areas", $values, ["id" => $area_id]);
    } else {
        $db_content->insert("se_delivery_areas", $values);
    }

    show_toast($lang['msg_success_db_changed'], 'success');
    header("HX-Trigger: update_deliveryCountries_list");
    exit;
}

// delete a delivery area
if (isset($_POST['delete_delivery_country'])) {
    $db_content->delete("se_delivery_areas", [
        "id" => (int) $_POST['delete_delivery_country']
    ]);
    show_toast($lang['msg_success_db_changed'], 'success');
    header("HX-Trigger: update_deliveryCountries_list");
    exit;
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

// compact the SQLite database files (VACUUM)
if (isset($_POST['vacuum_database']) && $db_type === 'sqlite') {

    $db_files = [
        CONTENT_DB => $db_content,
        USER_DB => $db_user,
        POSTS_DB => $db_posts
    ];

    $size_before = 0;
    $size_after = 0;
    $sqlite_error = '';

    foreach ($db_files as $db_path => $db_conn) {
        $size_before += filesize($db_path);
        try {
            // run through Medoo (not $db_conn->pdo), so the last statement Medoo
            // still holds is released first - SQLite refuses VACUUM while a
            // statement on the connection is unfinished
            $db_conn->query('VACUUM');

            // in WAL mode the rewritten pages land in the -wal file first,
            // a checkpoint moves them into the database file
            $db_conn->query('PRAGMA wal_checkpoint(TRUNCATE)');
        } catch (\PDOException $e) {
            $sqlite_error = $e->getMessage();
        }
        clearstatcache(true, $db_path);
        $size_after += filesize($db_path);
    }

    if ($sqlite_error === '') {
        $msg = str_replace(
            ['{before}', '{after}'],
            [readable_filesize($size_before), readable_filesize($size_after)],
            $lang['msg_success_vacuum']
        );
        show_toast($msg, 'success');
    } else {
        show_toast($lang['msg_error_vacuum'].' '.$sqlite_error, 'danger');
    }
}

// labels
// create (post_label) or update (update_label = id) a label
if (isset($_POST['post_label']) || isset($_POST['update_label'])) {

    $label_title = sanitizeUserInputs((string) ($_POST['label_title'] ?? ''));
    if ($label_title === '') {
        show_toast($lang['msg_error_no_title'], 'danger');
        exit;
    }

    // a color of the color input (#rrggbb), anything else becomes grey
    $label_color = (string) ($_POST['label_color'] ?? '');
    if (!preg_match('/^#[0-9a-f]{6}$/i', $label_color)) {
        $label_color = '#999999';
    }

    $values = [
        "label_custom_id" => clean_filename(html_entity_decode($label_title, ENT_QUOTES, 'UTF-8')),
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => sanitizeUserInputs((string) ($_POST['label_description'] ?? ''))
    ];

    if (isset($_POST['post_label'])) {
        $db_content->insert("se_labels", $values);
        record_log($_SESSION['user_nick'], "create new label", 1);
    } else {
        $db_content->update("se_labels", $values, [
            "label_id" => (int) $_POST['update_label']
        ]);
    }

    show_toast($lang['msg_success_db_changed'], 'success');
    header("HX-Trigger: updated_labels");
    exit;
}

if (isset($_POST['delete_label'])) {

    // entries keep the id, a missing label is just not shown anymore
    $db_content->delete("se_labels", [
        "label_id" => (int) $_POST['delete_label']
    ]);
    show_toast($lang['msg_success_db_changed'], 'success');
    record_log($_SESSION['user_nick'], "deleted label", 5);
    header("HX-Trigger: updated_labels");
    exit;
}

// API keys (Settings > API keys)
if(isset($_POST['create_api_key']) || isset($_POST['revoke_api_key']) || isset($_POST['delete_api_key'])) {
    if(!se_hasPermission('drm_acp_system')) {
        http_response_code(403);
        exit;
    }
}

if(isset($_POST['create_api_key'])) {

    $api_key_label = trim(sanitizeUserInputs($_POST['api_key_label']));
    $api_key_scopes = se_api_filter_scopes((array) ($_POST['api_key_scopes'] ?? []));

    if($api_key_label === '' || count($api_key_scopes) < 1) {
        show_toast($lang['api_keys_msg_missing_input'], 'danger');
        exit;
    }

    $new_api_key = se_api_create_key($api_key_label, $api_key_scopes, (int) $_SESSION['user_id']);

    record_log($_SESSION['user_nick'], "created API key: $api_key_label", "5");
    // api-key-created resets the form - only on success, so a failed
    // attempt keeps the entered name and scopes
    header("HX-Trigger: updated_api_keys, api-key-created");

    // the only time the plaintext key is ever shown
    echo '<div class="alert alert-warning">';
    echo '<p class="mb-2"><strong>'.$lang['api_keys_msg_created'].'</strong></p>';
    echo '<div class="input-group">';
    echo '<input type="text" class="form-control font-monospace" id="apiKeyPlain" value="'.htmlspecialchars($new_api_key, ENT_QUOTES).'" readonly onclick="this.select()">';
    echo '<button type="button" class="btn btn-default" onclick="navigator.clipboard.writeText(document.getElementById(\'apiKeyPlain\').value)" title="'.$lang['api_keys_btn_copy'].'">'.$icon['clipboard'].'</button>';
    echo '</div>';
    echo '</div>';
    exit;
}

if(isset($_POST['revoke_api_key'])) {

    $db_user->update("se_api_keys", [
        "is_active" => 0
    ], [
        "id" => (int) $_POST['revoke_api_key']
    ]);

    record_log($_SESSION['user_nick'], "revoked API key #".(int) $_POST['revoke_api_key'], "5");
    header("HX-Trigger: updated_api_keys");
    show_toast($lang['msg_success_db_changed'], 'success');
    exit;
}

if(isset($_POST['delete_api_key'])) {

    // only revoked keys can be deleted, an active key has to be revoked first
    $db_user->delete("se_api_keys", [
        "id" => (int) $_POST['delete_api_key'],
        "is_active" => 0
    ]);

    record_log($_SESSION['user_nick'], "deleted API key #".(int) $_POST['delete_api_key'], "5");
    header("HX-Trigger: updated_api_keys");
    show_toast($lang['msg_success_db_changed'], 'success');
    exit;
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
        echo '<div class="alert alert-danger mt-3">'.htmlspecialchars(print_r($testmail, true), ENT_QUOTES, 'UTF-8').'</div>';
    }
}

