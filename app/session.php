<?php

/**
 * Start the PHP session with secure cookie settings.
 *
 * Stand-alone file without dependencies - it is loaded by every entry point
 * before config.php and the autoloader (frontend, backend, xhr, installer).
 *
 * - httponly: the session cookie can't be read by JavaScript
 * - secure: only sent over https, if the request came in over https
 * - SameSite=Lax: not sent with cross-site POST requests (forms, fetch),
 *   still sent when following a link to the site (e.g. back from a payment provider)
 * - strict mode: unknown session ids from the client are not accepted
 */

if(!function_exists('se_session_start')) {

    function se_session_start(): void {

        if(session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => se_request_is_https(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_start();
    }

    /**
     * true if the current request came in over https - directly or through
     * a proxy/load balancer that sets X-Forwarded-Proto
     */
    function se_request_is_https(): bool {

        if(!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if(($_SERVER['SERVER_PORT'] ?? '') == 443) {
            return true;
        }
        if(strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
            return true;
        }
        return false;
    }
}
