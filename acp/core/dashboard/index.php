<?php

/**
 * @var array $lang
 * @var array $icon
 */

$reader_uri = '/admin-xhr/dashboard/read/';

$tpl_file = file_get_contents('../acp/templates/dashboard_top.tpl');
$tpl_file = str_replace('{reader_uri}', $reader_uri, $tpl_file);
$tpl_file = str_replace('{tab_pages}', $lang['nav_btn_pages'], $tpl_file);
$tpl_file = str_replace('{tab_snippets}', $lang['nav_btn_snippets'], $tpl_file);
$tpl_file = str_replace('{tab_blog}', $lang['nav_btn_blog'], $tpl_file);
$tpl_file = str_replace('{tab_products}', $lang['nav_btn_products'], $tpl_file);
$tpl_file = str_replace('{tab_events}', $lang['nav_btn_events'], $tpl_file);
$tpl_file = str_replace('{tab_comments}', $lang['nav_btn_comments'], $tpl_file);
$tpl_file = str_replace('{tab_user}', $lang['nav_btn_user'], $tpl_file);
$tpl_file = str_replace('{tab_cache}', $lang['nav_btn_cache'], $tpl_file);
$tpl_file = str_replace('{tab_addons}', $lang['nav_btn_addons'], $tpl_file);
$tpl_file = str_replace('{tab_orders}', $lang['nav_btn_orders'], $tpl_file);

$tpl_file = str_replace('{icon_pages}', $icon['files'], $tpl_file);
$tpl_file = str_replace('{icon_snippets}', $icon['card_heading'], $tpl_file);
$tpl_file = str_replace('{icon_blog}', $icon['file_earmark_post'], $tpl_file);
$tpl_file = str_replace('{icon_products}', $icon['store'], $tpl_file);
$tpl_file = str_replace('{icon_events}', $icon['calendar_event'], $tpl_file);
$tpl_file = str_replace('{icon_user}', $icon['people'], $tpl_file);
$tpl_file = str_replace('{icon_cache}', $icon['database'], $tpl_file);
$tpl_file = str_replace('{icon_addons}', $icon['plugin'], $tpl_file);
$tpl_file = str_replace('{icon_orders}', $icon['cart'], $tpl_file);
$tpl_file = str_replace('{icon_activity}', $icon['activity'], $tpl_file);
$tpl_file = str_replace('{icon_alerts}', $icon['exclamation_triangle'], $tpl_file);
$tpl_file = str_replace('{icon_settings}', $icon['gear'], $tpl_file);

$tpl_file = str_replace('{label_content}', $lang['dashboard_group_content'], $tpl_file);
$tpl_file = str_replace('{label_system}', $lang['dashboard_group_system'], $tpl_file);
$tpl_file = str_replace('{label_activity}', $lang['dashboard_label_activity'], $tpl_file);
$tpl_file = str_replace('{label_alerts}', $lang['dashboard_label_alerts'], $tpl_file);
$tpl_file = str_replace('{label_settings}', $lang['dashboard_label_settings'], $tpl_file);

$btn_addons_overview = '<a href="/admin/addons/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$tpl_file = str_replace('{btn_addons_overview}', $btn_addons_overview, $tpl_file);

$btn_settings_overview = '<a href="/admin/settings/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$tpl_file = str_replace('{btn_settings_overview}', $btn_settings_overview, $tpl_file);

// Icon-only manual refresh for the two cards with no other footer action -
// reloads just that card's own list, same target/indicator convention the
// rest of the dashboard's hx-get widgets already use.
$btn_reload_activity = '<button class="btn btn-default btn-sm" title="'.$lang['dashboard_btn_reload'].'" '
    .'hx-get="'.$reader_uri.'?action=list_logfile" hx-target="#getLogfile" hx-swap="innerHTML" hx-indicator=".htmx-indicator">'
    .$icon['arrow_clockwise'].'</button>';
$tpl_file = str_replace('{btn_reload_activity}', $btn_reload_activity, $tpl_file);

$btn_reload_alerts = '<button class="btn btn-default btn-sm" title="'.$lang['dashboard_btn_reload'].'" '
    .'hx-get="'.$reader_uri.'?action=list_alerts" hx-target="#getAlerts" hx-swap="innerHTML" hx-indicator=".htmx-indicator">'
    .$icon['arrow_clockwise'].'</button>';
$tpl_file = str_replace('{btn_reload_alerts}', $btn_reload_alerts, $tpl_file);

$btn_orders_overview = '<a href="/admin/shop/orders/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$tpl_file = str_replace('{btn_orders_overview}', $btn_orders_overview, $tpl_file);

$btn_page_overview = '<a href="/admin/pages/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$tpl_file = str_replace('{btn_page_overview}', $btn_page_overview, $tpl_file);
$btn_page_new = '<a href="/admin/pages/new/" class="btn btn-default btn-sm btn-accent">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_new_page}', $btn_page_new, $tpl_file);

$btn_snippets_overview = '<a href="/admin/snippets/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$btn_snippets_new = '<a href="/admin/snippets/new/" class="btn btn-default btn-sm btn-accent">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_snippets_overview}', $btn_snippets_overview, $tpl_file);
$tpl_file = str_replace('{btn_snippets_new}', $btn_snippets_new, $tpl_file);

$btn_blog_overview = '<a href="/admin/blog/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$btn_blog_new = '<a href="/admin/blog/new/" class="btn btn-default btn-sm btn-accent">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_blog_overview}', $btn_blog_overview, $tpl_file);
$tpl_file = str_replace('{btn_blog_new}', $btn_blog_new, $tpl_file);

$btn_products_overview = '<a href="/admin/shop/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$btn_products_new = '<a href="/admin/shop/new/" class="btn btn-default btn-sm btn-accent">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_products_overview}', $btn_products_overview, $tpl_file);
$tpl_file = str_replace('{btn_products_new}', $btn_products_new, $tpl_file);

$btn_events_overview = '<a href="/admin/events/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$btn_events_new = '<a href="/admin/events/new/" class="btn btn-default btn-sm btn-accent">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_events_overview}', $btn_events_overview, $tpl_file);
$tpl_file = str_replace('{btn_events_new}', $btn_events_new, $tpl_file);

$btn_user_overview = '<a href="/admin/users/" class="btn btn-default btn-sm">'.$lang['overview'].'</a>';
$tpl_file = str_replace('{btn_user_overview}', $btn_user_overview, $tpl_file);
$btn_user_new = '<a href="/admin/users/new/" class="btn btn-default btn-sm btn-accent">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_new_user}', $btn_user_new, $tpl_file);

// Moved out of table-cache.twig into a static card-footer (like every other
// card's overview/new buttons) instead of living inside the htmx-swapped
// list, so it survives the "load, cache_rebuilt from:body" refresh instead
// of briefly disappearing while that reloads. Same $_SESSION['token'] the
// Twig side uses via the 'csrf_token' global (see header.php).
$btn_cache_rebuild_all = '<button hx-post="/admin-xhr/dashboard/write/" '
    .'hx-vals=\'{"cache_target":"all","csrf_token":"'.$_SESSION['token'].'"}\' '
    .'hx-target="#cacheStatus_all" hx-swap="innerHTML" hx-indicator=".htmx-indicator" '
    .'class="btn btn-default btn-sm">'.$icon['arrow_clockwise'].' '.$lang['cache_btn_rebuild_all'].'</button>'
    .'<span id="cacheStatus_all" class="align-self-center"></span>';
$tpl_file = str_replace('{btn_cache_rebuild_all}', $btn_cache_rebuild_all, $tpl_file);

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['speedometer'].' '.$lang['nav_btn_dashboard'];
echo '<span class="ms-auto">';
echo se_print_docs_link('01-02-basics.md#dashboard');
echo '</span>';
echo '</div>';

echo $tpl_file;