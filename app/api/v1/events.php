<?php
/**
 * API v1 - events (scope events:read)
 *
 * GET /api/v1/events/              list of public events
 *     ?period=upcoming             upcoming (default, like the frontend),
 *                                  past or all
 *     ?lang=de                     only events in this language
 *     ?category=concerts           only events in this category (slug)
 *     ?page=1&per_page=20          pagination, per_page max. 100
 * GET /api/v1/events/{id}/         a single event, also a past one
 *     ?render=1                    (both) resolve snippets/shortcodes in
 *                                  teaser and text, see se_api_render_text()
 *
 * Drafts (status 2) and events with a release date in the future are never
 * returned. An event counts as past once its end date is older than the
 * "posts_event_time_offset" setting - the same rule the frontend uses to
 * hide past events (se_get_event_entries()).
 *
 * variables
 * @var array $requestPathParts from routing.php
 * @var object $db_posts
 * @var string $db_type from app/database.php
 * @var array $se_settings
 */

$api_event_id = $requestPathParts[3] ?? '';

$api_format_options = [
    'render' => ($_GET['render'] ?? '') === '1'
];
$api_format = fn($event) => se_api_format_event($event, $api_format_options);

// public and released: no release date, or one in the past
$api_where = 'status = 1 AND (releasedate IS NULL OR releasedate <= :api_now)';
$api_map = [':api_now' => time()];

// --- single event ---

if ($api_event_id !== '') {

    if (!ctype_digit($api_event_id)) {
        se_api_error(404, 'Event not found');
    }

    $api_map[':api_event_id'] = (int) $api_event_id;
    $event = $db_posts->query("SELECT * FROM se_events WHERE id = :api_event_id AND $api_where", $api_map)->fetch(PDO::FETCH_ASSOC);

    if (!is_array($event)) {
        se_api_error(404, 'Event not found');
    }

    se_api_respond(['data' => $api_format($event)]);
}

// --- list ---

$api_page = max(1, (int) ($_GET['page'] ?? 1));
$api_per_page = min(100, max(1, (int) ($_GET['per_page'] ?? 20)));

$api_period = $_GET['period'] ?? 'upcoming';
if (!in_array($api_period, ['upcoming', 'past', 'all'], true)) {
    se_api_error(400, 'Invalid period parameter');
}

$api_past_border = time() - (int) $se_settings['posts_event_time_offset'];
if ($api_period === 'upcoming') {
    $api_where .= ' AND event_enddate >= :api_past_border';
    $api_map[':api_past_border'] = $api_past_border;
} else if ($api_period === 'past') {
    $api_where .= ' AND event_enddate < :api_past_border';
    $api_map[':api_past_border'] = $api_past_border;
}

if (isset($_GET['lang']) && $_GET['lang'] !== '') {
    if (!preg_match('/^[a-zA-Z-]{2,20}$/', $_GET['lang'])) {
        se_api_error(400, 'Invalid lang parameter');
    }
    $api_where .= ' AND event_lang = :api_lang';
    $api_map[':api_lang'] = $_GET['lang'];
}

if (isset($_GET['category']) && $_GET['category'] !== '') {
    $api_cat_hashes = se_api_category_hashes($_GET['category']);
    if (empty($api_cat_hashes)) {
        se_api_error(400, 'Unknown category');
    }
    $api_cat_conditions = [];
    foreach ($api_cat_hashes as $i => $api_cat_hash) {
        $api_cat_conditions[] = 'categories LIKE :api_cat' . $i;
        $api_map[':api_cat' . $i] = '%' . $api_cat_hash . '%';
    }
    $api_where .= ' AND (' . implode(' OR ', $api_cat_conditions) . ')';
}

$api_total = (int) $db_posts->query("SELECT COUNT(*) FROM se_events WHERE $api_where", $api_map)->fetchColumn();

// same order as the event listing (se_get_event_entries()): fixed events
// first, then by start day and priority - past events newest first
if ($db_type === 'sqlite') {
    $api_sortdate = "strftime('%Y-%m-%d', datetime(event_startdate, 'unixepoch'))";
} else {
    $api_sortdate = "FROM_UNIXTIME(event_startdate, '%Y-%m-%d')";
}
$api_direction = $api_period === 'past' ? 'DESC' : 'ASC';
$api_offset = ($api_page - 1) * $api_per_page;

$events = $db_posts->query(
    "SELECT * FROM se_events WHERE $api_where
     ORDER BY fixed ASC, $api_sortdate $api_direction, priority DESC, id $api_direction
     LIMIT $api_offset, $api_per_page",
    $api_map
)->fetchAll(PDO::FETCH_ASSOC);

se_api_respond([
    'data' => array_map($api_format, $events),
    'meta' => [
        'page' => $api_page,
        'per_page' => $api_per_page,
        'total' => $api_total,
        'total_pages' => (int) ceil($api_total / $api_per_page)
    ]
]);
