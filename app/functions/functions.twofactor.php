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
 * Methods: code by e-mail, or an authenticator app (TOTP, RFC 6238).
 * Recovery codes work instead of any method.
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
    if ($user['user_2fa_method'] === 'totp' && se_2fa_check_totp($user, $input)) {
        return true;
    }
    return se_2fa_use_recovery_code($user, $input);
}

/* ---------------------------------------------------------------------------
 * Authenticator app (TOTP, RFC 6238): 30-second steps, 6 digits, HMAC-SHA1 -
 * the defaults every authenticator app supports.
 * ------------------------------------------------------------------------- */

/**
 * The app method needs to store the key encrypted - sodium (bundled with PHP)
 * or OpenSSL with AES-256-GCM
 */
function se_2fa_app_available(): bool {
    return function_exists('sodium_crypto_secretbox')
        || (function_exists('openssl_encrypt') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true));
}

/**
 * New random key for an authenticator app, Base32 encoded (160 bit)
 */
function se_2fa_new_totp_secret(): string {
    return se_2fa_base32_encode(random_bytes(20));
}

function se_2fa_base32_encode(string $data): string {

    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($data) as $char) {
        $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 5) as $chunk) {
        $out .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
    }
    return $out;
}

function se_2fa_base32_decode(string $base32): string {

    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $base32));
    $bits = '';
    foreach (str_split($base32) as $char) {
        $bits .= str_pad(decbin(strpos($alphabet, $char)), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 8) as $byte) {
        if (strlen($byte) === 8) {
            $out .= chr(bindec($byte));
        }
    }
    return $out;
}

/**
 * The 6-digit code of a time step (RFC 4226 / 6238)
 */
function se_2fa_totp_code(string $key, int $step): string {

    $hash = hash_hmac('sha1', pack('J', $step), $key, true);
    $offset = ord($hash[19]) & 0x0f;
    $number = ((ord($hash[$offset]) & 0x7f) << 24)
        | (ord($hash[$offset + 1]) << 16)
        | (ord($hash[$offset + 2]) << 8)
        | ord($hash[$offset + 3]);

    return str_pad((string) ($number % 1000000), 6, '0', STR_PAD_LEFT);
}

/**
 * Check a code against a Base32 key - one step before/after is accepted
 * (clock drift). Steps up to $last_step were already used and are refused,
 * so an intercepted code can't be used a second time.
 *
 * @return int|false the matching step, false if the code is wrong
 */
function se_2fa_totp_match(string $secret_b32, string $code, int $last_step = 0): int|false {

    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6) {
        return false;
    }
    $key = se_2fa_base32_decode($secret_b32);
    $now = intdiv(time(), 30);

    for ($step = $now - 1; $step <= $now + 1; $step++) {
        if ($step > $last_step && hash_equals(se_2fa_totp_code($key, $step), $code)) {
            return $step;
        }
    }
    return false;
}

/**
 * Check an app code of a user with the app method set up
 */
function se_2fa_check_totp(array $user, string $code): bool {

    global $db_user;

    $secret = se_2fa_decrypt((string) $user['user_2fa_secret']);
    if ($secret === null) {
        return false;
    }
    $step = se_2fa_totp_match($secret, $code, (int) $user['user_2fa_last_step']);
    if ($step === false) {
        return false;
    }

    $db_user->update("se_user", [
        "user_2fa_last_step" => $step
    ], [
        "user_id" => (int) $user['user_id']
    ]);

    return true;
}

/**
 * otpauth:// address for the QR code of the app setup
 */
function se_2fa_otpauth_uri(string $secret_b32, string $account): string {

    global $se_settings;

    $issuer = trim((string) ($se_settings['pagetitle'] ?? ''));
    if ($issuer === '') {
        $issuer = $_SERVER['HTTP_HOST'] ?? 'SwiftyEdit';
    }
    $issuer = str_replace(':', '', $issuer);

    return 'otpauth://totp/'.rawurlencode($issuer.':'.$account)
        .'?secret='.$secret_b32
        .'&issuer='.rawurlencode($issuer)
        .'&algorithm=SHA1&digits=6&period=30';
}

/**
 * Key for encrypting the app keys, derived from the site secret - a copy
 * of the database alone is not enough to read them
 */
function se_2fa_encryption_key(): string {
    return hash('sha256', 'se-2fa-secret|'.se_get_site_secret(), true);
}

function se_2fa_encrypt(string $plain): string {

    $key = se_2fa_encryption_key();

    if (function_exists('sodium_crypto_secretbox')) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return 's1:'.base64_encode($nonce.sodium_crypto_secretbox($plain, $nonce, $key));
    }

    $iv = random_bytes(12);
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return 'o1:'.base64_encode($iv.$tag.$cipher);
}

/**
 * @return string|null null if the value can't be decrypted (e.g. the site
 *                     secret has changed)
 */
function se_2fa_decrypt(string $stored): ?string {

    $key = se_2fa_encryption_key();
    $data = base64_decode(substr($stored, 3), true);
    if ($data === false) {
        return null;
    }

    if (str_starts_with($stored, 's1:') && function_exists('sodium_crypto_secretbox_open')) {
        $nonce = substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open(substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), $nonce, $key);
        return $plain === false ? null : $plain;
    }
    if (str_starts_with($stored, 'o1:')) {
        $plain = openssl_decrypt(substr($data, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($data, 0, 12), substr($data, 12, 16));
        return $plain === false ? null : $plain;
    }
    return null;
}

/**
 * Finish the setup: store the method and the hashes of new recovery codes
 *
 * @param string $method 'mail' or 'totp'
 * @param string $totp_secret Base32 key of the app (method totp only)
 * @param int $totp_step step of the code used to confirm the app setup
 * @return array the plain recovery codes, to show them once
 */
function se_2fa_save_setup(array $user, string $method, string $totp_secret = '', int $totp_step = 0): array {

    global $db_user;

    $codes = se_2fa_generate_recovery_codes();
    $hashes = array_map(static fn($code) => password_hash($code, PASSWORD_DEFAULT), $codes);

    $db_user->update("se_user", [
        "user_2fa_method" => $method,
        "user_2fa_secret" => $method === 'totp' ? se_2fa_encrypt($totp_secret) : '',
        "user_2fa_last_step" => $method === 'totp' ? $totp_step : 0,
        "user_2fa_recovery" => json_encode($hashes),
        "user_2fa_since" => time()
    ], [
        "user_id" => (int) $user['user_id']
    ]);

    record_log($user['user_nick'], '2FA set up ('.$method.')', 5);

    return $codes;
}

/**
 * Remove the 2FA setup of a user (disable it for the own account, or reset
 * it for another user in the user management). If 2FA is required, the
 * setup starts again at the next login.
 */
function se_2fa_reset(int $user_id, string $log_trigger): void {

    global $db_user;

    $db_user->update("se_user", [
        "user_2fa_method" => "",
        "user_2fa_secret" => "",
        "user_2fa_last_step" => 0,
        "user_2fa_mail_code" => "",
        "user_2fa_mail_expires" => 0,
        "user_2fa_recovery" => "",
        "user_2fa_since" => 0
    ], [
        "user_id" => $user_id
    ]);

    record_log($log_trigger, '2FA reset for user #'.$user_id, 5);
}

/**
 * Store new recovery codes for an existing setup - the old ones stop working
 *
 * @return array the plain codes, to show them once
 */
function se_2fa_renew_recovery_codes(array $user): array {

    global $db_user;

    $codes = se_2fa_generate_recovery_codes();
    $db_user->update("se_user", [
        "user_2fa_recovery" => json_encode(array_map(static fn($code) => password_hash($code, PASSWORD_DEFAULT), $codes))
    ], [
        "user_id" => (int) $user['user_id']
    ]);

    record_log($user['user_nick'], '2FA: new recovery codes', 5);

    return $codes;
}

/**
 * Number of unused recovery codes
 */
function se_2fa_recovery_left(array $user): int {
    $hashes = json_decode((string) $user['user_2fa_recovery'], true);
    return is_array($hashes) ? count($hashes) : 0;
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
