<?php
$reader_uri = '/admin/dashboard/read/';

$tpl_file = file_get_contents('../acp/templates/dashboard_top.tpl');
$tpl_file = str_replace('{reader_uri}', $reader_uri, $tpl_file);
$tpl_file = str_replace('{tab_pages}', $lang['nav_btn_pages'], $tpl_file);
$tpl_file = str_replace('{tab_snippets}', $lang['nav_btn_snippets'], $tpl_file);
$tpl_file = str_replace('{tab_blog}', $lang['nav_btn_blog'], $tpl_file);
$tpl_file = str_replace('{tab_products}', $lang['nav_btn_products'], $tpl_file);
$tpl_file = str_replace('{tab_events}', $lang['nav_btn_events'], $tpl_file);
$tpl_file = str_replace('{tab_comments}', $lang['nav_btn_comments'], $tpl_file);
$tpl_file = str_replace('{tab_user}', $lang['nav_btn_user'], $tpl_file);

$btn_page_overview = '<a href="/admin/pages/" class="btn btn-default">'.$lang['overview'].'</a>';
$tpl_file = str_replace('{btn_page_overview}', $btn_page_overview, $tpl_file);
$btn_page_new = '<a href="/admin/pages/new/" class="btn btn-default">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_new_page}', $btn_page_new, $tpl_file);
$tpl_file = str_replace('{label_btn_delete_cache}', $lang['btn_delete_cache'], $tpl_file);

$btn_snippets_overview = '<a href="/admin/snippets/" class="btn btn-default">'.$lang['overview'].'</a>';
$btn_snippets_new = '<a href="/admin/snippets/new/" class="btn btn-default">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_snippets_overview}', $btn_snippets_overview, $tpl_file);
$tpl_file = str_replace('{btn_snippets_new}', $btn_snippets_new, $tpl_file);

$btn_blog_overview = '<a href="/admin/blog/" class="btn btn-default">'.$lang['overview'].'</a>';
$btn_blog_new = '<a href="/admin/blog/new/" class="btn btn-default">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_blog_overview}', $btn_blog_overview, $tpl_file);
$tpl_file = str_replace('{btn_blog_new}', $btn_blog_new, $tpl_file);

$btn_products_overview = '<a href="/admin/shop/" class="btn btn-default">'.$lang['overview'].'</a>';
$btn_products_new = '<a href="/admin/shop/new/" class="btn btn-default">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_products_overview}', $btn_products_overview, $tpl_file);
$tpl_file = str_replace('{btn_products_new}', $btn_products_new, $tpl_file);

$btn_events_overview = '<a href="/admin/events/" class="btn btn-default">'.$lang['overview'].'</a>';
$btn_events_new = '<a href="/admin/events/new/" class="btn btn-default">'.$icon['plus'].$lang['btn_new'].'</a>';
$tpl_file = str_replace('{btn_events_overview}', $btn_events_overview, $tpl_file);
$tpl_file = str_replace('{btn_events_new}', $btn_events_new, $tpl_file);

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['speedometer'].' '.$lang['nav_btn_dashboard'];
echo '<span class="ms-auto">';
echo se_print_docs_link('dashboard.md');
echo '</span>';
echo '</div>';

echo $tpl_file;