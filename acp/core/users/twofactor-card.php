<?php

/**
 * Two-factor authentication card in the personal settings (/admin/users/settings/).
 * Rendered as a whole and replaced after every action (see settings-writer.php).
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
    $is_set_up = $user['user_2fa_method'] !== '';

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

    } elseif (!$is_set_up) {

        $code_sent = (int) $user['user_2fa_mail_expires'] > time();
        $html .= '<p><strong>'.$lang['account_2fa_not_set_up'].'</strong> ';
        $html .= str_replace('{MAIL}', '<strong>'.$mail.'</strong>', htmlspecialchars($lang['account_2fa_setup_intro'], ENT_QUOTES)).'</p>';

        if ($code_sent) {
            $html .= '<div class="input-group mb-2">';
            $html .= '<input type="text" class="form-control" name="twofa_code" placeholder="'.htmlspecialchars($lang['login_2fa_label_code'], ENT_QUOTES).'" inputmode="numeric" autocomplete="one-time-code">';
            $html .= '<button class="btn btn-primary" name="twofa_setup_verify" value="1" '.$hx.'>'.$lang['login_2fa_btn_verify'].'</button>';
            $html .= '</div>';
        }
        $html .= '<button class="btn btn-'.($code_sent ? 'default' : 'primary').'" name="twofa_setup_send" value="1" '.$hx.'>';
        $html .= $code_sent ? $lang['login_2fa_btn_resend'] : $lang['login_2fa_btn_send'];
        $html .= '</button>';

    } else {

        $status = str_replace(
            ['{MAIL}', '{DATE}', '{COUNT}'],
            [$mail, htmlspecialchars(se_format_datetime($user['user_2fa_since']), ENT_QUOTES), (string) se_2fa_recovery_left($user)],
            htmlspecialchars($lang['account_2fa_status'], ENT_QUOTES)
        );
        $html .= '<p>'.$status.'</p>';
        $html .= '<hr>';
        $html .= '<p class="text-muted">'.$lang['account_2fa_confirm_intro'].'</p>';

        $html .= '<div class="row g-2 mb-2">';
        $html .= '<div class="col-md-6"><input type="password" class="form-control" name="twofa_psw" placeholder="'.htmlspecialchars($lang['account_2fa_label_password'], ENT_QUOTES).'" autocomplete="current-password"></div>';
        $html .= '<div class="col-md-6"><div class="input-group">';
        $html .= '<input type="text" class="form-control" name="twofa_code" placeholder="'.htmlspecialchars($lang['login_2fa_label_code'], ENT_QUOTES).'" autocomplete="one-time-code">';
        $html .= '<button class="btn btn-default" name="twofa_send" value="1" '.$hx.'>'.$lang['login_2fa_btn_send'].'</button>';
        $html .= '</div></div>';
        $html .= '</div>';

        $html .= '<div class="d-flex gap-2">';
        $html .= '<button class="btn btn-default" name="twofa_new_recovery" value="1" '.$hx.'>'.$lang['account_2fa_btn_new_recovery'].'</button>';
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
