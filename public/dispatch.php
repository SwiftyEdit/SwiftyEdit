<?php
/**
 * Generic bootstrap-free plugin endpoint dispatcher.
 *
 * Deliberately does NOT go through app/app.php (no DB connection, no
 * session, no Smarty/Twig setup) - only the SE_* path constants from
 * config.php are loaded. Any plugin that needs to answer requests as fast
 * as a static asset (e.g. the image-resizer's srcset variants, hit many
 * times per page load) can ship a plugins/<name>/endpoint.php and reach it
 * here as /dispatch.php?p=<name>&...
 *
 * WHICH plugins are reachable this way is decided entirely by
 * data/cache/bootstrap_endpoints.json, never by this request. That file is
 * written by mods_check_in() (acp/core/functions_addons.php) whenever a
 * plugin is activated/deactivated or a page is saved - the same trigger
 * that already maintains active_addons.json. A plugin only ends up in it if
 * it is (a) genuinely activated in the ACP (from se_addons, a real DB
 * check, just done once at write time instead of on every request) and (b)
 * ships an endpoint.php at all. An activated plugin already has full
 * code-execution trust on every normal request via
 * hooks-global/hooks-frontend/global/index.php - this dispatcher does not
 * grant anything beyond that, it just adds one more, bootstrap-free entry
 * point for plugins that opt in by shipping endpoint.php.
 *
 * The request only selects a key ("p") into that pre-approved registry, so
 * it can never make an inactive or endpoint-less plugin reachable, no
 * matter what name is passed.
 */

require __DIR__ . '/../config.php';

$plugin = basename($_GET['p'] ?? '');
$registryFile = SE_CONTENT . '/cache/bootstrap_endpoints.json';

$registry = [];
if (is_file($registryFile)) {
    $decoded = json_decode(file_get_contents($registryFile), true);
    if (is_array($decoded)) {
        $registry = $decoded;
    }
}

if ($plugin === '' || empty($registry[$plugin])) {
    http_response_code(404);
    exit;
}

$endpoint = SE_PLUGINS . '/' . $plugin . '/endpoint.php';

if (!is_file($endpoint)) {
    http_response_code(404);
    exit;
}

require $endpoint;
