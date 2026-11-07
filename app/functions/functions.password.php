<?php

/**
 * Rules for new passwords - the same ones wherever a password is set:
 * registration, password reset, profile, backend and installer.
 *
 * They are checked only when a password is set, never at login, so existing
 * accounts keep working with their current password.
 *
 * This file has no dependencies, because the installer includes it on its own.
 */

/**
 * bcrypt (PASSWORD_DEFAULT) silently ignores everything after 72 bytes
 */
const SE_PASSWORD_MAX_BYTES = 72;

/**
 * Common passwords, lower case. Entries shorter than the default minimum
 * length still matter if a site sets a lower $se_password_min_length.
 */
const SE_PASSWORD_BLOCKLIST = [
    '12345678', '123456789', '1234567890', '12345678910', '0123456789', '0987654321',
    '1q2w3e4r5t', '1qaz2wsx3edc', 'q1w2e3r4t5', 'a123456789', 'aa12345678',
    'abcd1234', 'abc1234567', 'abcdefgh', 'abcdefghij',
    'qwertyuiop', 'qwerty1234', 'qwerty123456', 'qwertzuiop', 'qwertz1234', 'qwertz123456',
    'asdfghjkl', 'asdfghjkl1', 'asdf123456',
    'password', 'password1', 'password12', 'password123', 'password1234', 'password12345',
    'passwort', 'passwort1', 'passwort12', 'passwort123', 'passwort1234', 'passwort12345',
    'administrator', 'administrator1', 'adminadmin', 'admin12345', 'admin123456',
    'changeme', 'changeme123', 'letmein123', 'login12345', 'master1234',
    'test123456', 'test1234', 'testtest', 'testtesttest',
    'welcome123', 'willkommen', 'willkommen1', 'willkommen123',
    'hallo123', 'hallo12345', 'hallohallo',
    'iloveyou', 'iloveyou123', 'ichliebedich', 'schatzi123', 'sonnenschein', 'sonnenschein1',
    'football123', 'baseball123', 'superman123', 'starwars123', 'dragon123', 'monkey1234',
    'swiftyedit', 'swiftyedit1', 'swiftyedit123'
];

/**
 * @return int minimum number of characters, set by $se_password_min_length in config.php
 */
function se_password_min_length(): int {
    global $se_password_min_length;
    return max(1, (int) ($se_password_min_length ?? 10));
}

/**
 * Check a new password against the rules
 *
 * @param string $psw the password as entered - not trimmed or sanitized in any way
 * @param array $personal values of the account the password must not equal (username, e-mail)
 * @return array error codes: too_short, too_long, common, personal - empty if the password is fine
 */
function se_password_check(string $psw, array $personal = []): array {

    // password_hash() throws an exception on a NUL byte. Only a forged request
    // can contain one, so it doesn't need a message of its own.
    if(str_contains($psw, "\0")) {
        return ['common'];
    }

    $errors = [];

    // spaces are allowed in a password, but don't count towards the length on the outside
    if(mb_strlen(trim($psw)) < se_password_min_length()) {
        $errors[] = 'too_short';
    }

    if(strlen($psw) > SE_PASSWORD_MAX_BYTES) {
        $errors[] = 'too_long';
    }

    $psw_lower = mb_strtolower($psw);

    // the fewer than 4 different characters catch e.g. "aaaaaaaaaa" or "abababababab"
    // only reported for passwords of sufficient length - a short one already has its message
    if(!in_array('too_short', $errors, true)) {
        if(in_array($psw_lower, SE_PASSWORD_BLOCKLIST, true) || count(array_unique(mb_str_split($psw_lower))) < 4) {
            $errors[] = 'common';
        }
    }

    foreach($personal as $value) {
        if(is_string($value) && $value !== '' && $psw_lower === mb_strtolower(trim($value))) {
            $errors[] = 'personal';
            break;
        }
    }

    return $errors;
}

/**
 * @param array $errors error codes from se_password_check()
 * @param array $lang translations
 * @return string the messages for these errors, separated by <br>
 */
function se_password_error_text(array $errors, array $lang): string {

    $messages = [];
    foreach($errors as $error) {
        $messages[] = str_replace('{min}', (string) se_password_min_length(), $lang['msg_psw_'.$error]);
    }

    return implode('<br>', $messages);
}

/**
 * @param array $lang translations
 * @return string the hint shown below the password fields
 */
function se_password_requirements_text(array $lang): string {
    return str_replace('{min}', (string) se_password_min_length(), $lang['msg_psw_requirements']);
}
