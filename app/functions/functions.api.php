<?php

/**
 * functions for the public/headless API (see app/handlers/api-routes.php)
 * used by the API itself and by the ACP (Settings > API keys)
 */

/**
 * Get the scope catalog from app/api/scopes.php
 *
 * @return array scope => ['label' => language key]
 */
function se_api_get_scopes(): array {
    static $scopes = null;
    if ($scopes === null) {
        $scopes = include SE_ROOT . 'app/api/scopes.php';
    }
    return $scopes;
}

/**
 * Reduce a list of scopes to the ones that exist in the catalog
 *
 * @param array $scopes
 * @return array
 */
function se_api_filter_scopes(array $scopes): array {
    $known = array_keys(se_api_get_scopes());
    return array_values(array_intersect($known, $scopes));
}

/**
 * Generate a new random API key
 * format: "se_" + 48 hex characters (24 random bytes)
 *
 * @return string plaintext key - only ever shown once, store the hash
 */
function se_api_generate_key(): string {
    return 'se_' . bin2hex(random_bytes(24));
}

/**
 * Hash an API key for storage and lookup
 * keys are random and high-entropy, so a fast hash is sufficient here
 * (unlike passwords) and allows looking a key up by its hash directly
 *
 * @param string $key
 * @return string
 */
function se_api_hash_key(string $key): string {
    return hash('sha256', $key);
}

/**
 * The part of a key that is stored in plaintext and shown in the ACP,
 * so an admin can tell keys apart without ever seeing them again
 *
 * @param string $key
 * @return string
 */
function se_api_key_prefix(string $key): string {
    return substr($key, 0, 10);
}

/**
 * Create a new API key
 *
 * @param string $label
 * @param array $scopes
 * @param int $user_id creator
 * @return string the plaintext key
 */
function se_api_create_key(string $label, array $scopes, int $user_id): string {

    global $db_user;

    $key = se_api_generate_key();

    // every column is written explicitly, the SQLite schema has no defaults
    $db_user->insert('se_api_keys', [
        'label' => $label,
        'key_hash' => se_api_hash_key($key),
        'key_prefix' => se_api_key_prefix($key),
        'scopes' => implode(',', se_api_filter_scopes($scopes)),
        'is_active' => 1,
        'created_by' => $user_id,
        'created_at' => time(),
        'last_used_at' => 0,
        'request_count' => 0
    ]);

    return $key;
}

/**
 * Get all API keys, newest first (without the hash)
 *
 * @return array
 */
function se_api_get_keys(): array {

    global $db_user;

    return $db_user->select('se_api_keys', [
        'id', 'label', 'key_prefix', 'scopes', 'is_active',
        'created_by', 'created_at', 'last_used_at', 'request_count'
    ], [
        'ORDER' => ['id' => 'DESC']
    ]);
}
