<?php

/**
 * Two-factor authentication card in the personal settings (/admin/users/settings/).
 * Rendered as a whole and replaced after every action (see settings-writer.php).
 * A setup in progress is kept in $_SESSION['2fa_setup'] (method, app key) -
 * nothing is stored in the database before the first code matches.
 */

/**
 * @param array $user row of se_user (the logged-in user)
 * @param array $alerts list of [type, text]
 * @param array $recovery_codes plain codes to show once (after setup/renewal)
 * @return string
 */
function se_acp_twofa_card(array $user, array $alerts = [], array $recovery_codes = []): string {

    global $lang, $icon, $se_2fa_required;

    $writer = '/admin-xhr/users/settings/write/';
    $hx = 'hx-post="'.$writer.'" hx-target="#twofaCard" hx-swap="outerHTML"';
    $mail = htmlspecialchars(se_2fa_mask_mail((string) $user['user_mail']), ENT_QUOTES);
    $required = ($se_2fa_required ?? false) === true;
    $method = $user['user_2fa_method'];
    $setup_method = $_SESSION['2fa_setup']['method'] ?? (se_2fa_app_available() ? '' : 'mail');
    $code_input = '<input type="text" class="form-control" name="twofa_code" placeholder="'.htmlspecialchars($lang['login_2fa_label_code'], ENT_QUOTES).'" inputmode="numeric" autocomplete="one-time-code">';
    $btn_back = se_2fa_app_available() ? '<button class="btn btn-default" name="twofa_back" value="1" '.$hx.'>'.$lang['login_2fa_btn_back'].'</button>' : '';

    // a form, so HTMX sends its fields and the clicked button; Enter must not submit it natively
    $html = '<form class="card mt-3" id="twofaCard" onsubmit="return false;">';
    $html .= '<div class="card-header">'.$icon['shield_lock'].' '.$lang['account_2fa_title'].'</div>';
    $html .= '<div class="card-body">';

    foreach ($alerts as [$type, $text]) {
        $html .= '<div class="alert alert-'.$type.'">'.htmlspecialchars($text, ENT_QUOTES).'</div>';
    }

    $html .= '<p class="text-muted">'.($required ? $lang['account_2fa_required'] : $lang['account_2fa_not_required']).'</p>';

    if ($recovery_codes !== []) {
        // shown once, right after the setup or a renewal
        $html .= '<p>'.$lang['account_2fa_recovery_intro'].'</p>';
        $html .= '<pre class="border rounded p-3 fs-5">'.htmlspecialchars(implode("\n", $recovery_codes), ENT_QUOTES).'</pre>';

    } elseif ($method === '' && $setup_method === '') {

        // not set up - choose the method
        $html .= '<p><strong>'.$lang['account_2fa_not_set_up'].'</strong> '.$lang['account_2fa_choose_method'].'</p>';
        $html .= '<div class="row g-2">';
        $html .= '<div class="col-md-6"><button class="btn btn-default w-100 h-100 text-start" name="twofa_choose" value="mail" '.$hx.'>';
        $html .= '<strong>'.$lang['login_2fa_method_mail'].'</strong><br><small class="text-muted">'.str_replace('{MAIL}', $mail, htmlspecialchars($lang['login_2fa_method_mail_help'], ENT_QUOTES)).'</small></button></div>';
        $html .= '<div class="col-md-6"><button class="btn btn-default w-100 h-100 text-start" name="twofa_choose" value="totp" '.$hx.'>';
        $html .= '<strong>'.$lang['login_2fa_method_app'].'</strong><br><small class="text-muted">'.$lang['login_2fa_method_app_help'].'</small></button></div>';
        $html .= '</div>';

    } elseif ($method === '' && $setup_method === 'mail') {

        $code_sent = (int) $user['user_2fa_mail_expires'] > time();
        $html .= '<p>'.str_replace('{MAIL}', '<strong>'.$mail.'</strong>', htmlspecialchars($lang['account_2fa_setup_intro'], ENT_QUOTES)).'</p>';
        if ($code_sent) {
            $html .= '<div class="input-group mb-2">'.$code_input;
            $html .= '<button class="btn btn-primary" name="twofa_setup_verify" value="1" '.$hx.'>'.$lang['login_2fa_btn_verify'].'</button>';
            $html .= '</div>';
        }
        $html .= '<div class="d-flex gap-2">';
        $html .= '<button class="btn btn-'.($code_sent ? 'default' : 'primary').'" name="twofa_setup_send" value="1" '.$hx.'>';
        $html .= ($code_sent ? $lang['login_2fa_btn_resend'] : $lang['login_2fa_btn_send']).'</button>';
        $html .= $btn_back;
        $html .= '</div>';

    } elseif ($method === '' && $setup_method === 'totp') {

        $secret = $_SESSION['2fa_setup']['secret'];
        $uri = se_2fa_otpauth_uri($secret, (string) $user['user_nick']);
        $html .= '<p>'.$lang['login_2fa_app_setup_intro'].'</p>';
        $html .= '<div class="bg-white p-2 rounded mb-2" style="max-width:220px" data-otpauth="'.htmlspecialchars($uri, ENT_QUOTES).'"></div>';
        $html .= '<p class="small">'.$lang['login_2fa_app_key'].': <code class="user-select-all">'.trim(chunk_split($secret, 4, ' ')).'</code></p>';
        $html .= '<div class="input-group mb-2" style="max-width:420px">'.$code_input;
        $html .= '<button class="btn btn-primary" name="twofa_setup_verify" value="1" '.$hx.'>'.$lang['login_2fa_btn_verify'].'</button>';
        $html .= '</div>';
        $html .= $btn_back;

    } else {

        $status_text = $method === 'totp' ? $lang['account_2fa_status_app'] : $lang['account_2fa_status'];
        $status = str_replace(
            ['{MAIL}', '{DATE}', '{COUNT}'],
            [$mail, htmlspecialchars(se_format_datetime($user['user_2fa_since']), ENT_QUOTES), (string) se_2fa_recovery_left($user)],
            htmlspecialchars($status_text, ENT_QUOTES)
        );
        $html .= '<p>'.$status.'</p>';
        $html .= '<hr>';
        $html .= '<p class="text-muted">'.($method === 'totp' ? $lang['account_2fa_confirm_intro_app'] : $lang['account_2fa_confirm_intro']).'</p>';

        $html .= '<div class="row g-2 mb-2">';
        $html .= '<div class="col-md-6"><input type="password" class="form-control" name="twofa_psw" placeholder="'.htmlspecialchars($lang['account_2fa_label_password'], ENT_QUOTES).'" autocomplete="current-password"></div>';
        $html .= '<div class="col-md-6"><div class="input-group">'.$code_input;
        if ($method === 'mail') {
            $html .= '<button class="btn btn-default" name="twofa_send" value="1" '.$hx.'>'.$lang['login_2fa_btn_send'].'</button>';
        }
        $html .= '</div></div>';
        $html .= '</div>';

        $html .= '<div class="d-flex flex-wrap gap-2">';
        $html .= '<button class="btn btn-default" name="twofa_new_recovery" value="1" '.$hx.'>'.$lang['account_2fa_btn_new_recovery'].'</button>';
        $html .= '<button class="btn btn-default" name="twofa_reconfigure" value="1" '.$hx.' hx-confirm="'.htmlspecialchars($lang['account_2fa_confirm_reconfigure'], ENT_QUOTES).'">'.$lang['account_2fa_btn_reconfigure'].'</button>';
        if (!$required) {
            $html .= '<button class="btn btn-default text-danger" name="twofa_disable" value="1" '.$hx.' hx-confirm="'.htmlspecialchars($lang['account_2fa_confirm_disable'], ENT_QUOTES).'">'.$lang['account_2fa_btn_disable'].'</button>';
        }
        $html .= '</div>';
    }

    $html .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
}
