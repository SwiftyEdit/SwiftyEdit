<?php
/**
 * Public API Routes Handler
 * SwiftyEdit CMS
 *
 * Entry point for the headless REST API: /api/v{version}/{resource}/...
 * Unlike xhr-routes.php, this is meant for external clients - no session,
 * HTMX header or CSRF token is expected. Authentication will be done via
 * API keys (Authorization: Bearer <key>, table se_api_keys).
 *
 * Endpoint files live in app/api/v{version}/{resource}.php
 *
 * variables
 * @var array $requestPathParts from routing.php
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

/**
 * Send a JSON response and stop the request
 *
 * @param mixed $data
 * @param int $status HTTP status code
 * @return never
 */
function se_api_respond(mixed $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a JSON error response and stop the request
 *
 * @param int $status HTTP status code
 * @param string $message
 * @return never
 */
function se_api_error(int $status, string $message): never {
    se_api_respond(['error' => ['status' => $status, 'message' => $message]], $status);
}

$api_supported_versions = ['v1'];

$api_version = $requestPathParts[1] ?? '';
$api_resource = $requestPathParts[2] ?? '';

if (!in_array($api_version, $api_supported_versions, true)) {
    se_api_error(404, 'Unknown API version');
}

// resource names are restricted to lowercase letters, digits and dashes
if ($api_resource === '' || !preg_match('/^[a-z0-9-]+$/', $api_resource)) {
    se_api_error(404, 'Unknown resource');
}

$api_endpoint = __DIR__ . '/../api/' . $api_version . '/' . $api_resource . '.php';

if (!is_file($api_endpoint)) {
    se_api_error(404, 'Unknown resource');
}

// the required scope follows from resource + method:
// GET/HEAD need "{resource}:read", everything else "{resource}:write"
$api_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$api_access = in_array($api_method, ['GET', 'HEAD'], true) ? 'read' : 'write';
$api_required_scope = $api_resource . ':' . $api_access;

if (!array_key_exists($api_required_scope, se_api_get_scopes())) {
    $api_allowed_methods = [];
    if (array_key_exists($api_resource . ':read', se_api_get_scopes())) {
        $api_allowed_methods = ['GET', 'HEAD'];
    }
    if (array_key_exists($api_resource . ':write', se_api_get_scopes())) {
        $api_allowed_methods = array_merge($api_allowed_methods, ['POST', 'PUT', 'PATCH', 'DELETE']);
    }
    header('Allow: ' . implode(', ', $api_allowed_methods));
    se_api_error(405, 'Method not allowed for this resource');
}

// authentication: Authorization: Bearer <key>
$api_key = se_api_get_bearer_token();

if ($api_key === null) {
    header('WWW-Authenticate: Bearer');
    se_api_error(401, 'Missing API key');
}

$api_key_data = se_api_authenticate($api_key);

if ($api_key_data === null) {
    header('WWW-Authenticate: Bearer error="invalid_token"');
    se_api_error(401, 'Invalid or revoked API key');
}

if (!in_array($api_required_scope, $api_key_data['scopes'], true)) {
    header('WWW-Authenticate: Bearer error="insufficient_scope", scope="' . $api_required_scope . '"');
    se_api_error(403, 'API key lacks scope ' . $api_required_scope);
}

include $api_endpoint;

// endpoints are expected to respond themselves - this is only a fallback
se_api_error(500, 'Endpoint returned no response');
