<?php

use Twig\Environment;

/**
 * dashboard data reader
 *
 * @var object $db_content
 * @var object $db_user
 * @var object $db_posts
 * @var string $db_type
 * @var array $se_page_types
 * @var array $se_settings
 * @var array $lang
 * @var array $lang_codes
 * @var array $icon
 * @var bool $se_backend_updates
 * @var Environment $twig
 */

require_once __DIR__.'/functions.php';

// This endpoint only reads from $_SESSION (header.php's own writes are
// already done by this point) and never writes to it. The dashboard fires
// several of these hx-trigger="load" requests at once, all sharing one
// PHPSESSID - the default file session handler holds an exclusive lock on
// the session file for the whole request, so without closing it early here
// those requests queue up and run one at a time instead of concurrently.
// Do not add $_SESSION writes below without removing this first.
session_write_close();

$reader_uri = '/admin-xhr/dashboard/read/';


// activity grid and history

if ($_REQUEST['action'] === 'activity') {

    se_cleanup_logs();

    $tab = ($_GET['tab'] ?? '') === 'history' ? 'history' : 'overview';
    $day = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['day'] ?? '')) ? (string) $_GET['day'] : '';
    $type = (string) ($_GET['type'] ?? 'all');

    $html = $twig->render('dashboard/activity.twig', ['reader_uri' => $reader_uri] + se_dashboard_activity($tab, $day, $type));

    se_html_response($html);
}


// plugins

if ($_REQUEST['action'] === 'list_addons') {

    $filter = in_array($_GET['filter'] ?? '', ['all', 'favorites'], true) ? $_GET['filter'] : 'usable';
    $activated = array_column(se_get_addons('plugin'), 'addon_dir');
    $favorites = se_get_my_favorite_plugins();
    $count_favorites = 0;

    $addons = [];
    $count_usable = 0;
    foreach (se_get_all_addons() as $dir => $info) {

        $is_activated = in_array($dir, $activated, true);
        $needs_activation = se_plugin_needs_activation($dir, $info);
        $is_inactive = !$is_activated && $needs_activation;

        if (!$is_inactive) {
            $count_usable++;
        }

        $is_favorite = in_array($dir, $favorites, true);
        if ($is_favorite) {
            $count_favorites++;
        }

        // same categories as the filter buttons on the addons page
        // "Plugin" and "Editor" are technical terms, not translated
        $category = 'Plugin';
        if (($info['addon']['type'] ?? '') === 'editor') {
            $category = 'Editor';
        } elseif (str_ends_with($dir, '-pay')) {
            $category = $lang['label_payment'];
        }

        $name = (string) ($info['addon']['name'] ?? $dir);
        $words = preg_split('/[\s_-]+/', $name) ?: [];
        $initials = mb_substr($words[0] ?? $name, 0, 1).mb_substr($words[1] ?? '', 0, 1);

        // the plugin's own pages only respond once it is activated, see se_is_plugin_activated()
        $nav_file = $info['navigation'][0]['file'] ?? null;

        $addons[] = [
            'dir' => $dir,
            'name' => $name,
            'initials' => $initials,
            'version' => $info['addon']['version'] ?? '',
            'category' => $category,
            'state' => $is_activated ? 'active' : ($is_inactive ? 'inactive' : 'none'),
            'favorite' => $is_favorite,
            'link' => ($nav_file && !$is_inactive) ? '/admin/addons/plugin/'.$dir.'/'.$nav_file.'/' : '/admin/addons/'
        ];
    }

    $count_all = count($addons);
    if ($filter === 'usable') {
        $addons = array_values(array_filter($addons, static fn($addon) => $addon['state'] !== 'inactive'));
    } elseif ($filter === 'favorites') {
        $addons = array_values(array_filter($addons, static fn($addon) => $addon['favorite']));
    }
    usort($addons, static fn($a, $b) => strcasecmp($a['name'], $b['name']));

    $html = $twig->render('dashboard/table-addons.twig', [
        'addons' => $addons,
        'filter' => $filter,
        'count_favorites' => $count_favorites,
        'count_usable' => $count_usable,
        'count_all' => $count_all,
        'reader_uri' => $reader_uri
    ]);

    se_html_response($html);
}


// new users

if ($_REQUEST['action'] === 'list_user') {
    $get_user = $db_user->select("se_user", [
        "user_id", "user_nick", "user_firstname", "user_lastname", "user_mail", "user_registerdate"
    ], [
        "user_class[!]" => "deleted",
        "ORDER" => ["user_id" => "DESC"],
        "LIMIT" => 5
    ]);

    if (count($get_user) < 1) {
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
    }

    foreach ($get_user as &$user) {
        $user['user_registerdate_formatted'] = se_format_datetime($user['user_registerdate']);
        $user['display_name'] = trim($user['user_firstname'].' '.$user['user_lastname']);
        $source = $user['display_name'] !== '' ? $user['display_name'] : $user['user_nick'];
        $words = preg_split('/\s+/', $source) ?: [];
        $user['initials'] = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
    }
    unset($user);

    $html = $twig->render('dashboard/table-users.twig', [
        'get_user' => $get_user
    ]);

    se_html_response($html);
}


/**
 * central cache management
 * lists every cache the application writes, with its current size and
 * either a single "rebuild" action or (products, smarty) their existing
 * dedicated actions
 */

if ($_REQUEST['action'] === 'list_cache') {

    $caches = [
        ['target' => 'smarty', 'label' => $lang['cache_label_smarty'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/templates_c/'), 'clear_only' => true],
        ['target' => 'twig', 'label' => $lang['cache_label_twig'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/twig/'), 'clear_only' => true],
        ['target' => 'navigation', 'label' => $lang['cache_label_navigation'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/navigation/')],
        ['target' => 'urls', 'label' => $lang['cache_label_urls'], 'bytes' => is_file(SE_CONTENT.'/cache/active_urls.json') ? filesize(SE_CONTENT.'/cache/active_urls.json') : 0],
        ['target' => 'categories', 'label' => $lang['label_categories'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/categories/')],
        ['target' => 'tags', 'label' => $lang['label_keywords'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/tags/')],
        ['target' => 'snippets', 'label' => $lang['cache_label_snippets'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/snippets/')],
        ['target' => 'preferences', 'label' => $lang['cache_label_preferences'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/preferences/')],
        ['target' => 'products', 'label' => $lang['label_products'], 'bytes' => se_dir_size(SE_CONTENT.'/cache/products/'), 'clear_and_rebuild' => true]
    ];

    // the bars show the size in relation to the largest cache
    $largest = max(array_column($caches, 'bytes')) ?: 1;
    foreach ($caches as &$cache) {
        $cache['size'] = readable_filesize($cache['bytes']);
        $cache['percent'] = (int) round(100 * $cache['bytes'] / $largest);
    }
    unset($cache);

    $html = $twig->render('dashboard/table-cache.twig', [
        'caches' => $caches
    ]);

    se_html_response($html);
}


/**
 * OPcache status ("OPcache" card, see dashboard/cards.twig / index.php - the
 * whole card is only wired in for admins, see se_hasPermission() there, but
 * the read is harmless on its own so no extra check is repeated here).
 */

if ($_REQUEST['action'] === 'list_opcache') {
    $html = $twig->render('dashboard/table-opcache.twig', [
        'opcache' => se_get_opcache_status()
    ]);

    se_html_response($html);
}


/**
 * "To review": things somebody should look at
 */

if($_REQUEST['action'] === 'list_alerts') {
    $se_check_messages = [];

    $writable_items = array(
        SE_PUBLIC.'/sitemap.xml',
        SE_PUBLIC.'/',
        SE_PUBLIC.'/assets/avatars/',
        SE_ROOT.'/data/cache/',
        SE_PUBLIC.'/assets/files/',
        SE_PUBLIC.'/assets/images/',
        SE_ROOT.'/data/database/content.sqlite3',
        SE_ROOT.'/data/database/user.sqlite3'
    );

    foreach($writable_items as $f) {

        if($db_type !== 'sqlite') {
            if($f == SE_ROOT.'/data/database/content.sqlite3') {
                continue;
            }
            if($f == SE_ROOT.'/data/database/user.sqlite3') {
                continue;
            }
        }

        if(!is_writable($f)) {
            $se_check_messages[] = basename($f);
        }
    }

    // Smarty creates its compile dir on the first frontend request,
    // so a missing directory is fine - only flag an existing, non-writable one
    $smarty_compile_dir = SE_ROOT.'/data/cache/templates_c/';
    if(is_dir($smarty_compile_dir) && !is_writable($smarty_compile_dir)) {
        $se_check_messages[] = basename($smarty_compile_dir);
    }

    // se_get_site_secret() creates the file on first use - if it still doesn't
    // exist, every request gets a new secret (2FA, mail codes, IP hashes break)
    se_get_site_secret();
    if(!is_file(SE_CONTENT.'/site_secret.php')) {
        $se_check_messages[] = 'site_secret.php';
    }

    $missing_types = [];
    foreach($se_page_types as $pt) {

        if($pt == 'normal') {
            continue;
        }

        $find_target_page = $db_content->select("se_pages", ["page_permalink","page_type_of_use"], [
            "page_type_of_use" => "$pt"
        ]);

        if(count($find_target_page) < 1) {
            $missing_types[] = $pt;
        }
    }

    // security entries are only for those who may open the log
    $can_view_log = se_hasPermission('drm_acp_sensitive_files');

    $html = $twig->render('dashboard/review.twig', [
        'not_writable' => $se_check_messages,
        'missing_types' => $missing_types,
        'security' => $can_view_log ? se_dashboard_security_summary() : null
    ]);

    se_html_response($html);
}


// system information

if ($_REQUEST['action'] === 'list_infos') {
    $html = $twig->render('dashboard/table-infos.twig', [
        'database' => $db_type,
        'php_version' => phpversion(),
        'languages' => mb_strtoupper(implode(', ', $lang_codes))
    ]);

    se_html_response($html);
}


// installed version and available update, loaded on its own because it may ask swiftyedit.net

if ($_REQUEST['action'] === 'version_state') {

    $version = json_decode((string) file_get_contents(SE_ROOT.'version.json'), true);
    $installed_build = (string) ($version['build'] ?? '');

    // the update itself needs this right, the others don't get a button they can't use
    $can_update = $se_backend_updates !== false && se_hasPermission('drm_acp_sensitive_files');

    $html = $twig->render('dashboard/version-state.twig', [
        'installed_build' => $installed_build,
        'can_update' => $can_update,
        'available_build' => $can_update ? se_dashboard_available_update($installed_build) : ''
    ]);

    se_html_response($html);
}


// the log page

if ($_REQUEST['action'] === 'log') {

    if (!se_hasPermission('drm_acp_sensitive_files')) {
        se_html_response('<div class="alert alert-info">'.$lang['rm_no_access'].'</div>', 403);
    }

    $filter = in_array($_GET['filter'] ?? '', ['content', 'security'], true) ? $_GET['filter'] : 'all';
    $page = max(0, (int) ($_GET['p'] ?? 0));

    $html = $twig->render('dashboard/log-list.twig', [
        'reader_uri' => $reader_uri,
        'filter' => $filter,
        'page' => $page
    ] + se_dashboard_log_entries($filter, $page, 50));

    se_html_response($html);
}
