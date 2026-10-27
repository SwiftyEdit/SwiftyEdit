<?php

/**
 * Security headers sent by PHP, so they also work without Apache's
 * mod_headers (e.g. nginx, Caddy).
 *
 * Stand-alone file without dependencies, loaded by the entry points next to
 * app/session.php. A full Content-Security-Policy (with nonces) is a separate
 * topic - only frame-ancestors is set here, for the backend.
 */

if(!function_exists('se_send_security_headers')) {

    /**
     * @param bool $backend true for the backend: may only be framed by itself
     */
    function se_send_security_headers(bool $backend = false): void {

        if(headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        if($backend) {
            header("Content-Security-Policy: frame-ancestors 'self'");
            header('X-Frame-Options: SAMEORIGIN');
        }
    }
}
