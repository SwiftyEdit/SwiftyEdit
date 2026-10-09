<?php

/**
 * Two-factor authentication (2FA) for the backend login.
 *
 * Disabled by default; $se_2fa_required = true in SE_CONTENT/config.php
 * requires it for every backend user (see config.php). Frontend accounts
 * don't use 2FA.
 *
 * Flow (see acp/login.php): the password is checked by se_user_login(),
 * which then only stores a "pending login" in the session - no user data,
 * no rights. The session is started by se_2fa_complete_login() after the
 * second factor (or the setup) succeeded.
 *
 * Methods: code by e-mail. Recovery codes work instead of any method.
 */

/** seconds a pending login stays valid */
const SE_2FA_PENDING_LIFETIME = 600;
/** seconds a mail code stays valid */
const SE_2FA_MAIL_CODE_LIFETIME = 600;
/** minimum seconds between two mail codes */
const SE_2FA_MAIL_RESEND_DELAY = 60;
/** wrong codes per pending login, then back to the password */
const SE_2FA_MAX_ATTEMPTS = 5;
/** number of recovery codes */
const SE_2FA_RECOVERY_COUNT = 10;

/**
 * Whether this user has to pass 2FA when logging in to the backend
 */
function se_2fa_required_for(array $user): bool {

    global $se_2fa_required;

    if (($se_2fa_required ?? false) !== true) {
        return false;
    }
    // only backend users - the backend login of other accounts doesn't grant any rights
    if (($user['user_class'] ?? '') !== 'administrator') {
        return false;
    }
    if (se_2fa_bypass_active((string) $user['user_nick'])) {
        record_log($user['user_nick'], '2FA skipped by $se_2fa_bypass in config.php', 5);
        return false;
    }
    return true;
}

/**
 * Emergency switch $se_2fa_bypass in SE_CONTENT/config.php - only for the
 * named user and only until the given date
 */
function se_2fa_bypass_active(string $user_nick): bool {

    global $se_2fa_bypass;

    if (!is_array($se_2fa_bypass) || ($se_2fa_bypass['user'] ?? '') === '' || $user_nick === '') {
        return false;
    }
    $until = strtotime((string) ($se_2fa_bypass['until'] ?? ''));

    return $se_2fa_bypass['user'] === $user_nick && $until !== false && $until > time();
}

/**
 * Password was correct, the second factor is still missing
 */
function se_2fa_start_pending(array $user, bool $remember): void {

    // new session id - the pending login must not reuse an id known before
    session_regenerate_id(true);

    $_SESSION['2fa_pending'] = [
        'user_id' => (int) $user['user_id'],
        'since' => time(),
        'remember' => $remember,
        'attempts' => 0
    ];
}

/**
 * The pending login, or null if there is none or it has expired
 */
function se_2fa_get_pending(): ?array {

    $pending = $_SESSION['2fa_pending'] ?? null;
    if (!is_array($pending)) {
        return null;
    }
    if (time() - (int) $pending['since'] > SE_2FA_PENDING_LIFETIME) {
        unset($_SESSION['2fa_pending']);
        return null;
    }
    return $pending;
}

function se_2fa_cancel_pending(): void {
    unset($_SESSION['2fa_pending']);
}

/**
 * The user of the pending login (fresh from the database)
 */
function se_2fa_pending_user(): ?array {

    global $db_user;

    $pending = se_2fa_get_pending();
    if ($pending === null) {
        return null;
    }
    $user = $db_user->get("se_user", "*", [
        "AND" => [
            "user_id" => $pending['user_id'],
            "user_verified" => "verified"
        ]
    ]);

    return is_array($user) ? $user : null;
}

/**
 * Count a wrong code. After SE_2FA_MAX_ATTEMPTS the pending login ends.
 *
 * @return bool true if the pending login is still active
 */
function se_2fa_count_failed_attempt(): bool {

    se_rate_limit_add('2fa', 900);

    if (!isset($_SESSION['2fa_pending'])) {
        return false;
    }
    $_SESSION['2fa_pending']['attempts']++;
    if ($_SESSION['2fa_pending']['attempts'] >= SE_2FA_MAX_ATTEMPTS) {
        se_2fa_cancel_pending();
        return false;
    }
    return true;
}

/**
 * Too many wrong codes from this client (all accounts together)
 */
function se_2fa_throttled(): bool {
    return se_rate_limit_exceeded('2fa', 20, 900);
}

/**
 * Keyed hash of a mail code - a 6-digit code alone would be trivial to
 * find from a plain hash
 */
function se_2fa_hash_code(string $code): string {
    return hash_hmac('sha256', $code, se_get_site_secret());
}

/**
 * Send a new code by e-mail
 *
 * @return string 'sent', 'wait' (last code was sent less than a minute ago) or 'failed'
 */
function se_2fa_send_mail_code(array $user): string {

    global $db_user, $lang, $se_settings;

    if (time() - (int) $user['user_2fa_mail_sent'] < SE_2FA_MAIL_RESEND_DELAY) {
        return 'wait';
    }
    if (se_rate_limit_exceeded('2fa-mail', 10, 900)) {
        return 'wait';
    }
    se_rate_limit_add('2fa-mail', 900);

    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $db_user->update("se_user", [
        "user_2fa_mail_code" => se_2fa_hash_code($code),
        "user_2fa_mail_expires" => time() + SE_2FA_MAIL_CODE_LIFETIME,
        "user_2fa_mail_sent" => time()
    ], [
        "user_id" => (int) $user['user_id']
    ]);

    $body = str_replace(
        ['{USERNAME}', '{CODE}', '{MINUTES}'],
        [htmlspecialchars($user['user_nick'], ENT_QUOTES), $code, (string) (SE_2FA_MAIL_CODE_LIFETIME / 60)],
        $lang['login_2fa_mail_body']
    );

    $mail_data = [
        'tpl' => 'mail.tpl',
        'subject' => $lang['login_2fa_mail_subject'].' / '.($se_settings['pagetitle'] ?? ''),
        'preheader' => $lang['login_2fa_mail_subject'],
        'title' => $lang['login_2fa_mail_subject'],
        'body' => $body
    ];
    $recipient = ['name' => $user['user_nick'], 'mail' => $user['user_mail']];

    $sent = se_send_mail($recipient, $mail_data['subject'], se_build_html_file($mail_data));

    return $sent === 1 ? 'sent' : 'failed';
}

/**
 * Check a mail code; a valid code can only be used once
 */
function se_2fa_check_mail_code(array $user, string $code): bool {

    global $db_user;

    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6 || $user['user_2fa_mail_code'] === '' || (int) $user['user_2fa_mail_expires'] < time()) {
        return false;
    }
    if (!hash_equals($user['user_2fa_mail_code'], se_2fa_hash_code($code))) {
        return false;
    }

    $db_user->update("se_user", [
        "user_2fa_mail_code" => "",
        "user_2fa_mail_expires" => 0
    ], [
        "user_id" => (int) $user['user_id']
    ]);

    return true;
}

/**
 * New recovery codes in the format XXXX-XXXX (no 0/O, 1/I/L)
 *
 * @return array plain codes - shown once, only their hashes are stored
 */
function se_2fa_generate_recovery_codes(): array {

    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $codes = [];

    for ($i = 0; $i < SE_2FA_RECOVERY_COUNT; $i++) {
        $code = '';
        for ($c = 0; $c < 8; $c++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $codes[] = substr($code, 0, 4).'-'.substr($code, 4);
    }

    return $codes;
}

/**
 * Use a recovery code instead of the second factor - each code works once
 */
function se_2fa_use_recovery_code(array $user, string $input): bool {

    global $db_user;

    $input = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $input));
    if (strlen($input) !== 8) {
        return false;
    }
    $input = substr($input, 0, 4).'-'.substr($input, 4);

    $hashes = json_decode((string) $user['user_2fa_recovery'], true);
    if (!is_array($hashes)) {
        return false;
    }

    foreach ($hashes as $key => $hash) {
        if (password_verify($input, $hash)) {
            unset($hashes[$key]);
            $db_user->update("se_user", [
                "user_2fa_recovery" => json_encode(array_values($hashes))
            ], [
                "user_id" => (int) $user['user_id']
            ]);
            record_log($user['user_nick'], '2FA: recovery code used, '.count($hashes).' left', 5);
            return true;
        }
    }

    return false;
}

/**
 * Check the input of the login step: a code of the user's method or a recovery code
 */
function se_2fa_check_input(array $user, string $input): bool {

    if ($user['user_2fa_method'] === 'mail' && se_2fa_check_mail_code($user, $input)) {
        return true;
    }
    return se_2fa_use_recovery_code($user, $input);
}

/**
 * Finish the setup: store the method and the hashes of new recovery codes
 *
 * @return array the plain recovery codes, to show them once
 */
function se_2fa_save_setup(array $user, string $method): array {

    global $db_user;

    $codes = se_2fa_generate_recovery_codes();
    $hashes = array_map(static fn($code) => password_hash($code, PASSWORD_DEFAULT), $codes);

    $db_user->update("se_user", [
        "user_2fa_method" => $method,
        "user_2fa_recovery" => json_encode($hashes),
        "user_2fa_since" => time()
    ], [
        "user_id" => (int) $user['user_id']
    ]);

    record_log($user['user_nick'], '2FA set up ('.$method.')', 5);

    return $codes;
}

/**
 * Second factor passed - start the session like a normal backend login
 */
function se_2fa_complete_login(array $user): void {

    $pending = se_2fa_get_pending();
    $remember = (bool) ($pending['remember'] ?? false);
    se_2fa_cancel_pending();

    se_finish_login($user, true, $remember);
}

/**
 * e-mail address for display, e.g. "p•••@example.com"
 */
function se_2fa_mask_mail(string $mail): string {

    $at = strpos($mail, '@');
    if ($at === false || $at < 1) {
        return '•••';
    }
    return substr($mail, 0, 1).'•••'.substr($mail, $at);
}
