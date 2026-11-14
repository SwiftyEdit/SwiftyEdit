<?php

/**
 * API keys in /admin/settings/api-keys/, markup in acp/templates/settings/api-keys.twig
 * keys for the public/headless API (see app/handlers/api-routes.php),
 * the list is loaded by the settings data-reader, keys are saved by the data-writer
 *
 * global variables
 * @var array $lang
 * @var array $icon
 * @var object $twig
 */

require_once __DIR__ . '/functions.php';

// permissions of a key, from the scope registry app/api/scopes.php
$scopes = [];
foreach (se_api_get_scopes() as $scope => $scope_data) {
    $scopes[] = [
        'value' => $scope,
        'id' => 'scope_'.preg_replace('/[^a-z0-9]/', '_', $scope),
        'label' => $lang[str_replace('.', '_', $scope_data['label'])] ?? $scope
    ];
}

echo $twig->render('settings/api-keys.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['api_keys_nav'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('api-keys'),
    'reader_uri' => '/admin-xhr/settings/api-keys/read/',
    'writer_uri' => '/admin-xhr/settings/api-keys/write/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', ['api-keys-list', 'api-keys-new']),
    'scopes' => $scopes
]);
