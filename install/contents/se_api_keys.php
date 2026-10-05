<?php

/**
 * database for API keys (public/headless API, see app/handlers/api-routes.php)
 *
 * one row per key - the plaintext key is shown only once on creation,
 * only its SHA-256 hash is stored (keys are random and high-entropy,
 * so a fast hash is sufficient and allows direct lookup)
 * key_prefix: first characters of the key, for recognition in the ACP only
 * scopes: comma separated list, e.g. "products:read,orders:write"
 * is_active: revoke a key without deleting its history
 * last_used_at / request_count: written from day one, so rate limiting
 * can be added later without a schema change
 * note: the SQLite schema generator drops DEFAULT values, so always
 * write every column explicitly on insert
 */

$database = "user";
$table_name = "se_api_keys";

$cols = array(
    "id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "key_hash" => "VARCHAR(64) NOT NULL DEFAULT ''",
    "key_prefix" => "VARCHAR(16) NOT NULL DEFAULT ''",
    "scopes" => "VARCHAR(1000) NOT NULL DEFAULT ''",
    "is_active" => "BOOLEAN NOT NULL DEFAULT 1",
    "created_by" => 'INTEGER(12) NOT NULL DEFAULT 0',
    "created_at" => 'INTEGER(12) NOT NULL DEFAULT 0',
    "last_used_at" => 'INTEGER(12) NOT NULL DEFAULT 0',
    "request_count" => 'INTEGER(12) NOT NULL DEFAULT 0'
);
