<?php

/**
 * Settings > API keys
 * keys for the public/headless API (see app/handlers/api-routes.php)
 *
 * global variables
 * @var array $lang
 * @var array $icon
 */

echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['api_keys_title'].'</div>';

$writer_uri = '/admin-xhr/settings/api-keys/write/';
$reader_uri = '/admin-xhr/settings/api-keys/read/';

echo '<p class="text-muted">'.$lang['api_keys_intro'].'</p>';

// the plaintext key of a newly created key is shown here, exactly once
echo '<div id="apiKeyCreated"></div>';

echo '<div id="getApiKeys" class="card p-3" hx-get="'.$reader_uri.'" hx-trigger="load, updated_api_keys from:body" hx-vals=\'{"load_api_keys": "api_keys"}\'>';
echo '<span class="sr-only">Loading...</span>';
echo '</div>';

echo '<hr>';

echo '<div class="card">';
echo '<div class="card-header">'.$lang['new'].' ...</div>';
echo '<div class="card-body">';
echo '<form id="create_api_key" hx-post="'.$writer_uri.'" hx-target="#apiKeyCreated" hx-on:api-key-created="this.reset()">';

echo '<div class="mb-3">';
echo '<label class="form-label" for="api_key_label">'.$lang['api_keys_label'].'</label>';
echo '<input class="form-control" type="text" id="api_key_label" name="api_key_label" value="" maxlength="255" required>';
echo '</div>';

echo '<div class="mb-3">';
echo '<div class="form-label">'.$lang['api_keys_scopes'].'</div>';
foreach (se_api_get_scopes() as $scope => $scope_data) {
    $scope_id = 'scope_'.preg_replace('/[^a-z0-9]/', '_', $scope);
    $scope_label = $lang[str_replace('.', '_', $scope_data['label'])] ?? $scope;
    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="checkbox" name="api_key_scopes[]" value="'.htmlspecialchars($scope, ENT_QUOTES).'" id="'.$scope_id.'">';
    echo '<label class="form-check-label" for="'.$scope_id.'">'.htmlspecialchars($scope_label, ENT_QUOTES).' <code>'.htmlspecialchars($scope, ENT_QUOTES).'</code></label>';
    echo '</div>';
}
echo '</div>';

echo '<button type="submit" name="create_api_key" value="new" class="btn btn-default text-success">'.$lang['api_keys_btn_create'].'</button>';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';
echo '</div>';
echo '</div>';
