<?php

/**
 * ajax instant search - live suggestions dropdown for the nav search field
 * and the standalone search page (see search_suggestions.tpl).
 *
 * Loaded via route.php, which already includes bootstrap.php - do not
 * session_start()/define SE_SECTION/include config or database here again,
 * that duplicates what bootstrap.php already did and fatals with
 * "Cannot redeclare constant".
 *
 * @var object $db_content medoo database object (used inside se_search_pages())
 * @var object $db_posts   medoo database object (used inside se_search_products()/
 *                         se_search_posts()/se_search_events())
 * @var object $smarty
 * @var array $lang
 * @var string $languagePack
 */

// Read-only w.r.t. $_SESSION - safe to release the session lock immediately,
// same reasoning as comments.php: this fires on every keystroke (debounced),
// so it must not queue up behind other session-locked requests.
session_write_close();

// The template always re-renders the whole container (hx-swap "outerHTML"),
// so it needs to know which id and look to put back - the nav search modal
// and the standalone search page's inline dropdown share this one endpoint
// but need different markup (see search_suggestions.tpl): the page dropdown
// floats below the field, the modal's results should just grow as part of
// the modal body instead of looking like a second floating card inside it.
$container_id = preg_replace('/[^A-Za-z0-9\-_]/', '', $_GET['target'] ?? '') ?: 'search-suggestions';
$smarty->assign('container_id', $container_id, true);
$variant = ($_GET['variant'] ?? '') === 'modal' ? 'modal' : 'dropdown';
$smarty->assign('variant', $variant, true);

$s = sanitizeUserInputs($_GET['s'] ?? '');

// Nothing typed (e.g. the field was just cleared) - render the closed,
// empty container. Bootstrap's own ".dropdown-menu { display: none; }"
// (without "show") hides it - no extra CSS/caching to rely on here.
if ($s === '') {
    $smarty->assign('is_open', false, true);
    $smarty->display('search_suggestions.tpl');
    exit;
}

$smarty->assign('is_open', true, true);

if (strlen($s) < 3) {
    $smarty->assign('search_undersized_msg', $lang['msg_search_undersized'], true);
    $smarty->display('search_suggestions.tpl');
    exit;
}

$suggestion_limit = 5;
// Blog/events are a supplementary two more sections on top of pages/products
// - keep them shorter so the dropdown/modal doesn't grow unwieldy.
$suggestion_limit_small = 3;

$get_pages = se_search_pages($s, $languagePack, 1, $suggestion_limit);
$get_products = se_search_products($s, $languagePack, 1, $suggestion_limit);
$get_posts = se_search_posts($s, $languagePack, 1, $suggestion_limit_small);
$get_events = se_search_events($s, $languagePack, 1, $suggestion_limit_small);

$pages = [];
foreach ($get_pages['pages'] as $page) {
    $pages[] = [
        'title' => $page['page_title'],
        'href'  => '/' . $page['page_permalink'],
    ];
}

$products = [];
foreach ($get_products['products'] as $product) {
    $products[] = array_merge([
        'title' => $product['meta_title'] ?: $product['title'],
        'href'  => '/' . $product['main_catalog_slug'] . $product['slug'],
        'product_currency' => $product['product_currency'],
    ], se_get_product_price_tag($product));
}

// hrefs built the same way as app/handlers/posts-list.php / events-list.php:
// {display page permalink} + {slug}-{id}.html
$posts_target_page = se_get_type_of_use_pages('display_post');
$events_target_page = se_get_type_of_use_pages('display_event');

$posts = [];
foreach ($get_posts['posts'] as $post) {
    // prefer the stored canonical url (see app/handlers/search.php for why),
    // fall back to the old construction for rows saved before it existed.
    $posts[] = [
        'title' => $post['post_title'],
        'href'  => $post['post_canonical_url'] ?: ('/' . $posts_target_page['page_permalink'] . basename($post['post_slug']) . '-' . $post['post_id'] . '.html'),
    ];
}

$events = [];
foreach ($get_events['events'] as $event) {
    $events[] = [
        'title' => $event['title'],
        'href'  => $event['canonical_url'] ?: ('/' . $events_target_page['page_permalink'] . basename($event['slug']) . '-' . $event['id'] . '.html'),
    ];
}

// full search page URL, for the "show all results" link - same lookup
// template-setup.php uses for $search_uri, needed here since that file
// hasn't run yet on the xhr path (app.php loads xhr-routes.php before
// template-setup.php).
$tyo_search = se_get_type_of_use_pages('search');

$smarty->assign('search_string', $s, true);
$smarty->assign('search_uri', '/' . $tyo_search['page_permalink'], true);
$smarty->assign('pages', $pages, true);
$smarty->assign('products', $products, true);
$smarty->assign('posts', $posts, true);
$smarty->assign('events', $events, true);
$smarty->assign('pages_total', $get_pages['totalResults'], true);
$smarty->assign('products_total', $get_products['totalResults'], true);
$smarty->assign('posts_total', $get_posts['totalResults'], true);
$smarty->assign('events_total', $get_events['totalResults'], true);
$smarty->assign('lang_tagged_section_pages', $lang['tagged_section_pages'], true);
$smarty->assign('lang_tagged_section_products', $lang['tagged_section_products'], true);
$smarty->assign('lang_tagged_section_posts', $lang['tagged_section_posts'], true);
$smarty->assign('lang_tagged_section_events', $lang['tagged_section_events'], true);
$smarty->assign('lang_btn_show_all_results', $lang['btn_show_all_results'], true);
$smarty->assign('msg_no_search_results', $lang['msg_search_no_results'], true);

$smarty->display('search_suggestions.tpl');
