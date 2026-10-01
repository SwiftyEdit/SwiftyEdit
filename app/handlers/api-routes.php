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

include $api_endpoint;

// endpoints are expected to respond themselves - this is only a fallback
se_api_error(500, 'Endpoint returned no response');
