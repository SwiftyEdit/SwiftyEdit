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


echo '<div class="subHeader d-flex align-items-center">';
echo $icon['speedometer'].' '.$lang['nav_btn_dashboard'];
echo '</div>';

echo $tpl_file;