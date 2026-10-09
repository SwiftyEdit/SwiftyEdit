<?php

/**
 * Personal settings of the logged-in user (/admin-xhr/users/settings/write/).
 * Open to every backend user (see se_acp_xhr_permission()) - only actions
 * on the own account belong here, managing other users stays in
 * data-writer.php behind drm_acp_user.
 *
 * @var object $db_user
 * @var array $lang
 */

require_once __DIR__.'/twofactor-card.php';

$my_user_id = (int) $_SESSION['user_id'];

// presets (moved here from data-writer.php) - merged into the stored ones,
// which also hold flags like hide_2fa_notice (see below)
if(isset($_POST['save_my_settings'])) {

    $save_presets = se_get_my_presets() ?? [];
    $save_presets['status'] = sanitizeUserInputs($_POST['preset_status']);
    $save_presets['product_type'] = sanitizeUserInputs($_POST['preset_product_type']);

    $presets_json = json_encode($save_presets);

    $db_user->update("se_user",[
        "user_acp_settings" => $presets_json
    ],[
        "user_id" => $my_user_id
    ]);
    echo '<div class="alert alert-success">'.$lang['msg_success_db_changed'].'</div>';
    exit;
}

// hide the dashboard notice "two-factor authentication is not enabled"
if(isset($_POST['hide_2fa_notice'])) {

    $save_presets = se_get_my_presets() ?? [];
    $save_presets['hide_2fa_notice'] = true;

    $db_user->update("se_user",[
        "user_acp_settings" => json_encode($save_presets)
    ],[
        "user_id" => $my_user_id
    ]);
    exit;
}

/**
 * two-factor authentication - every action answers with the whole card
 */
$twofa_actions = ['twofa_setup_send', 'twofa_setup_verify', 'twofa_send', 'twofa_new_recovery', 'twofa_disable'];
if(array_intersect($twofa_actions, array_keys($_POST)) === []) {
    exit;
}

$my_user = $db_user->get("se_user", "*", ["user_id" => $my_user_id]);
$alerts = [];
$recovery_codes = [];
$code_input = is_string($_POST['twofa_code'] ?? null) ? $_POST['twofa_code'] : '';

// send a code - for the setup, or to confirm a change
if(isset($_POST['twofa_setup_send']) || isset($_POST['twofa_send'])) {
    $send_result = se_2fa_send_mail_code($my_user);
    if($send_result === 'sent') {
        $alerts[] = ['success', $lang['login_2fa_msg_sent']];
    } elseif($send_result === 'wait') {
        $alerts[] = ['info', $lang['login_2fa_msg_wait']];
    } else {
        $alerts[] = ['danger', $lang['login_2fa_msg_send_failed']];
    }
}

// finish the setup with the code from the mail
if(isset($_POST['twofa_setup_verify']) && $my_user['user_2fa_method'] === '') {
    if(se_2fa_throttled()) {
        $alerts[] = ['danger', $lang['login_2fa_msg_too_many']];
    } elseif(se_2fa_check_mail_code($my_user, $code_input)) {
        $recovery_codes = se_2fa_save_setup($my_user, 'mail');
        $alerts[] = ['success', $lang['account_2fa_msg_set_up']];
    } else {
        se_rate_limit_add('2fa', 900);
        $alerts[] = ['danger', $lang['login_2fa_msg_wrong_code']];
    }
}

// changes of an existing setup - password and a code (or recovery code) required
if((isset($_POST['twofa_new_recovery']) || isset($_POST['twofa_disable'])) && $my_user['user_2fa_method'] !== '') {

    $password = is_string($_POST['twofa_psw'] ?? null) ? $_POST['twofa_psw'] : '';

    if(se_2fa_throttled()) {
        $alerts[] = ['danger', $lang['login_2fa_msg_too_many']];
    } elseif(!password_verify($password, (string) $my_user['user_psw_hash'])) {
        se_rate_limit_add('2fa', 900);
        $alerts[] = ['danger', $lang['account_2fa_msg_wrong_password']];
    } elseif(!se_2fa_check_input($my_user, $code_input)) {
        se_rate_limit_add('2fa', 900);
        $alerts[] = ['danger', $lang['login_2fa_msg_wrong_code']];

    } elseif(isset($_POST['twofa_new_recovery'])) {
        $recovery_codes = se_2fa_renew_recovery_codes($my_user);
        $alerts[] = ['success', $lang['account_2fa_msg_new_recovery']];

    } elseif(($se_2fa_required ?? false) !== true) {
        // disabling is only possible while 2FA is not required for the site
        se_2fa_reset($my_user_id, $my_user['user_nick']);
        $alerts[] = ['success', $lang['account_2fa_msg_disabled']];
    }
}

$my_user = $db_user->get("se_user", "*", ["user_id" => $my_user_id]);
echo se_acp_twofa_card($my_user, $alerts, $recovery_codes);
