<?php
/**
 * Template Setup and Smarty Configuration
 * SwiftyEdit CMS
 */

$structuredDataContext = [
    'type' => 'WebPage',
    'data' => $page_contents
];

/**
 * assign all translations to smarty
 */
foreach($lang as $key => $val) {
    $smarty->assign("lang_$key", $val);
}

foreach($se_settings as $key => $val) {
    $smarty->assign("prefs_$key", $val);
}

/**
 * check if we have 'page_posts_type' then display posts
 * check if we have 'page_type_of_use'
 */
if($page_contents['page_posts_types'] != '' OR $page_contents['page_type_of_use'] != 'normal') {
    $show_posts = true;

    foreach($se_page_types as $type) {
        if($page_contents['page_type_of_use'] == $type) {
            $show_posts = false;
        }
    }

    $restricted_pages = ['password', 'profile', 'orders', 'account', 'register', 'unlock', 'tagged', 'checkout'];
    if (in_array($p, $restricted_pages)) {
        $show_posts = false;
    }

    if($page_contents['page_posts_types'] != '') {
        $show_posts = true;
    }

    if($page_contents['page_posts_types'] == 'p' OR $page_contents['page_type_of_use'] == 'display_product') {
        $p = 'products';
        $show_posts = false;
    }
    if($page_contents['page_posts_types'] == 'e' OR $page_contents['page_type_of_use'] == 'display_event') {
        $p = 'events';
        $show_posts = false;
    }

    if($page_contents['page_type_of_use'] == 'display_post') {
        $p = 'posts';
    }

    if($page_contents['page_type_of_use'] == 'checkout') {
        $p = 'checkout';
    }

    if($show_posts === true) {
        $p = 'posts';
    }
}

$tyo_search = se_get_type_of_use_pages('search');
$smarty->assign("search_uri", '/'.$tyo_search['page_permalink']);

/* legal pages */
$legal_pages = se_get_legal_pages();
$cnt_legal_pages = count($legal_pages);
if($cnt_legal_pages > 0) {
    $smarty->assign('legal_pages', $legal_pages);
}

$smarty->assign('languagePack', $languagePack);
$smarty->assign("page_id", $page_contents['page_id']);

if(isset($user_logout) && ($user_logout != '')) {
    $smarty->assign("msg_status","alert alert-success",true);
    $smarty->assign('msg_text', $lang['msg_logout'],true);
    $output = $smarty->fetch("status_message.tpl");
    $smarty->assign('msg_content', $output);
}

// Shopping Cart Setup
if($se_settings['posts_products_cart'] == 2 OR $se_settings['posts_products_cart'] == 3) {
    $smarty->assign('show_shopping_cart',true);

    // add product to the shopping cart
    if(isset($_POST['add_to_cart'])) {
        $se_cart = se_add_to_cart();
    }

    // get permalink for shopping cart
    $checkout_page = se_get_type_of_use_pages('checkout');
    if($checkout_page['page_permalink'] == '') {
        $sc_uri = '/checkout/';
    } else {
        $sc_uri = '/'.$checkout_page['page_permalink'];
    }

    $smarty->assign('shopping_cart_uri', $sc_uri);
}

// Process page contents
if (is_array($page_contents)) {
    foreach ($page_contents as $k => $v) {
        if($v != '') {
            $$k = stripslashes($v);
        }
    }
} else {
    $show_404 = "true";
}

$current_page_sort = $page_sort;

// Set default values
if(!isset($page_title) OR $page_title == "") {
    $page_title = $se_prefs['prefs_pagetitle'];
}

if(!isset($page_favicon) OR $page_favicon == "") {
    $page_favicon = $se_prefs['prefs_pagefavicon'];
}

if(!isset($page_meta_keywords)) {
    $page_meta_keywords = '';
}

if(!isset($page_meta_description)) {
    $page_meta_description = '';
}

/**
 * generate mainmenu, submenu, breadcrumps and sitemap
 */
$mainmenu = array();
$submenu = array();

$get_main_menu = show_mainmenu();
$mainmenu = $get_main_menu['menu'];
$submenu = show_menu($current_page_sort);
$bcmenu = breadcrumbs_menu();

/* shortcodes will be replaced in text_parser */
$shortcodes = se_get_shortcodes();

foreach($mainmenu as $k => $v) {
    if(isset($mainmenu[$k]['page_linkname'])) {
        $mainmenu[$k]['page_linkname'] = text_parser($mainmenu[$k]['page_linkname']);
    }
}

if(is_array($submenu)) {
    foreach($submenu as $k => $v) {
        $submenu[$k]['page_linkname'] = text_parser($submenu[$k]['page_linkname']);
    }
}

if(is_array($bcmenu)) {
    foreach($bcmenu as $k => $v) {
        $bcmenu[$k]['page_linkname'] = text_parser($bcmenu[$k]['page_linkname']);
    }
}

if($page_contents['page_type_of_use'] == '404') {
    unset($bcmenu);
}

$arr_mainmenu = [];
$arr_subnmenu = [];
if(is_array($mainmenu)) {
    $arr_mainmenu = array_filter(array_values($mainmenu));
}
if(is_array($submenu)) {
    $arr_subnmenu = array_filter(array_values($submenu));
}

/* get the last key - it's the Home Link  */
$last_key = array_key_last($arr_mainmenu);

$smarty->assign('homepage_linkname', text_parser($arr_mainmenu[$last_key]['homepage_linkname']));
$smarty->assign('homepage_title', $arr_mainmenu[$last_key]['homepage_title']);
$smarty->assign('homepage_permalink', $arr_mainmenu[$last_key]['homepage_permalink']);

unset($arr_mainmenu[$last_key]['homepage_linkname'],$arr_mainmenu[$last_key]['homepage_title'],$arr_mainmenu[$last_key]['homepage_permalink'],$arr_mainmenu[$last_key]['page_linkname']);
$arr_mainmenu = array_filter(array_values($arr_mainmenu));

$smarty->assign('link_home', SE_INCLUDE_PATH . "/");
$smarty->assign('arr_menue', $arr_mainmenu);
$smarty->assign('arr_bcmenue', $bcmenu);

/* submenu only if $submenu != empty */
if(is_array($submenu) && count($submenu) >= 1) {
    $smarty->assign('arr_submenue', $arr_subnmenu);
    $smarty->assign('legend_toc', text_parser($get_main_menu['se_toc_header']));
}

if($page_contents['page_sort'] == 'portal' OR $p == '') {
    $smarty->assign('homelink_status', $se_defs['main_nav_class_active']);
} else {
    $smarty->assign('homelink_status', $se_defs['main_nav_class']);
}

$smarty->assign('body_template', $se_template_layout);

// Process snippets
$all_snippets = se_get_all_snippets();
$cnt_snippets = count($all_snippets);
$matched_snippets = array();

for($i=0;$i<$cnt_snippets;$i++) {
    $snippet_lang = $all_snippets[$i]['snippet_lang'];
    if($all_snippets[$i]['snippet_name'] == '') {
        continue;
    }
    $snippet_key = "se_snippet_" . str_replace("-","_",$all_snippets[$i]['snippet_name']);
    /* assign the correct snippet by $languagePack */
    if($snippet_lang == $languagePack) {
        $smarty->assign("$snippet_key", text_parser(stripslashes($all_snippets[$i]['snippet_content'])));
        $matched_snippets[] = $all_snippets[$i]['snippet_name'];
    }
    /* if we have no match by $languagePack - assign the last snippet with the same snippet_name */
    if(!in_array($all_snippets[$i]['snippet_name'], $matched_snippets)) {
        $smarty->assign("$snippet_key", text_parser(stripslashes($all_snippets[$i]['snippet_content'])));
    }
}

/* include modul */
if(isset($page_modul) AND $page_modul != "") {
    $smarty->assign('modul_head_enhanced', $modul_head_enhanced, true);

    /* overwrite page's values by module */
    if($mod['page_title'] != "") {
        $page_title = $mod['page_title'];
    }
    if($mod['page_thumbnail'] != "") {
        $page_thumbnail = $mod['page_thumbnail'];
    }
    if($mod['page_favicon'] != "") {
        $page_favicon = $mod['page_favicon'];
    }
    if($mod['page_description'] != "") {
        $page_meta_description = $mod['page_description'];
    }
    if($mod['page_keywords'] != "") {
        $page_meta_keywords = $mod['page_keywords'];
    }
    if($mod['page_robots'] != "") {
        $page_meta_robots = $mod['page_robots'];
    }
}

/* parse [include] [script] [plugin] etc. */
if(!isset($modul_content)) {
    $modul_content = '';
}

if(!isset($page_content)) {
    $page_content = '';
}

$parsed_content = text_parser($page_content.$modul_content);

if($parsed_content != $page_content) {
    $smarty->assign('page_content', $parsed_content,true);
} else {
    $smarty->assign('page_content', $page_content);
}

/**
 * check if page is protected
 * if post psw, store md5 hash in session
 * unset session via ?reset_page_psw
 */
if(isset($_POST['page_psw']) && $_POST['page_psw'] != '') {
    if(md5($_POST['page_psw']) === $page_psw) {
        $_SESSION['page_psw'] = md5($_POST['page_psw']);
    }
}

if(isset($_GET['reset_page_psw'])) {
    unset($_SESSION['page_psw']);
}

if(isset($page_psw) && $page_psw !== '' && $_SESSION['page_psw'] !== $page_psw) {
    $formaction = SE_INCLUDE_PATH . '/'.$swifty_slug;
    $page_title = 'Password Protected Page';
    $page_meta_robots = 'noindex';

    $smarty->assign('formaction', $formaction);
    $smarty->assign('button_send', $lang['button_login']);
    $smarty->assign('label_psw_protected_page', $lang['label_psw_protected_page']);

    $output = $smarty->fetch("page_psw_input.tpl");
    $smarty->assign('page_content', $output);
}

/* page thumbnails */
if(!isset($page_thumbnail)) {
    $page_thumbnail = $se_prefs['prefs_pagethumbnail'];
} else {
    $page_thumbnail_array = explode("<->", $page_thumbnail);
    if(is_array($page_thumbnail_array)) {
        $page_thumbnail = $page_thumbnail_array[0];
        if(count($page_thumbnail_array) > 0) {
            $page_thumbnail = array_shift($page_thumbnail_array);
            $thumb = array();
            foreach($page_thumbnail_array as $t) {
                $t = str_replace('/content/', $se_base_url.'content/', $t);
                $thumb[] = $t;
            }
            $smarty->assign('page_thumbnails', $thumb);
        }
    }
}

/* page logo */
if(!isset($page_logo)) {
    $page_logo = $se_prefs['prefs_pagelogo'];
}

if(!isset($page_hash)) {
    $page_hash = '';
}

if($page_logo != 'null' && $page_logo != '') {
    $smarty->assign('page_logo', $page_logo);
}
if($page_thumbnail != 'null' && $page_thumbnail != '') {
    $smarty->assign('page_thumbnail', $page_thumbnail);
}
if($page_favicon != 'null' && $page_favicon != '') {
    $smarty->assign('page_favicon', $page_favicon);
}

$smarty->assign('page_title', html_entity_decode($page_title));
$smarty->assign('prefs_pagesglobalhead', $se_prefs['prefs_pagesglobalhead']);
$smarty->assign('page_meta_author', $page_meta_author);
$smarty->assign('page_meta_date', date('Y-m-d', $page_lastedit));
$smarty->assign('page_meta_keywords', html_entity_decode($page_meta_keywords));
$smarty->assign('page_meta_description', html_entity_decode($page_meta_description));
$smarty->assign('page_hash', $page_hash);

if($page_meta_robots == "") {
    $page_meta_robots = "all";
}

if(isset($_GET['s'])) {
    // do not index search results
    $page_meta_robots = 'noindex';
}

if($page_status == 'draft' || $page_status == 'private') {
    $page_meta_robots = 'noindex, nofollow';
}

$smarty->assign('page_meta_robots', $page_meta_robots);
$smarty->assign('page_canonical_url', $page_canonical_url);

if(isset($page_head_styles) AND $page_head_styles != "") {
    $smarty->assign('page_head_styles', "<style> $page_head_styles </style>\n");
}

if(isset($page_head_enhanced) AND $page_head_enhanced != "") {
    $smarty->assign('page_head_enhanced', $page_head_enhanced);
}

$snippet_footer = text_parser($snippet_footer);
$smarty->assign("snippet_footer","$snippet_footer");

/* private pages, for admins only */
if(($page_status == "private") AND ($_SESSION['user_class'] != "administrator")) {
    $text = se_get_textlib("no_access", $languagePack,'content');
    $smarty->assign('page_content', $text);
}

/**
 * pages for usergroups
 * -> access if $_SESSION[user_id] is in selected usergroups
 * -> access for administrators
 */
if(isset($page_usergroup) AND $page_usergroup != "") {
    $arr_checked_groups = explode("<|-|>",$page_usergroup);

    for($i=0;$i<count($arr_checked_groups);$i++) {
        $is_user_in_group[] = is_user_in_group($_SESSION['user_id'],"$arr_checked_groups[$i]");
    }

    if((!in_array("true",$is_user_in_group)) AND ($_SESSION['user_class'] != "administrator")) {
        $text = se_get_textlib("no_access", $languagePack,'content');
        $smarty->assign('page_content', $text);
    }
}

/* draft pages for administrators only */
if(($page_status == "draft") AND ($_SESSION['user_class'] != "administrator")){
    $text = se_get_textlib("no_access", $languagePack,'content');
    $smarty->assign('page_content', $text);
}

/* show or hide categories */
$smarty->assign('page_categories_mode', $page_contents['page_categories_mode']);


/* comments */
$post_comments = 0;
if(isset($post_data) AND $post_data['post_comments'] == 1) {
    $post_comments = 1;
}

if(($page_comments == 1 OR $post_comments == 1) && $se_prefs['prefs_comments_mode'] != 3) {
    $smarty->assign('show_page_comments', 'true', true);
}

// Final template assignments
$smarty->assign("p","$p");
$smarty->assign("se_include_path", SE_INCLUDE_PATH);

$se_page_url = $se_base_url;
$se_base_href = $se_base_url;
if($swifty_slug != '' AND $swifty_slug != '/') {
    $se_page_url .= $swifty_slug;
}
if($mod_slug != '') {
    $se_page_url .= $mod_slug;
}
$smarty->assign('se_base_href', $se_base_href,true);
$smarty->assign('se_page_url', $se_page_url,true);

$se_end_time = microtime(true);
$se_pageload_time = round($se_end_time-$se_start_time,4);
$smarty->assign('se_start_time', $se_start_time,true);
$smarty->assign('se_end_time', $se_end_time,true);
$smarty->assign('se_pageload_time', $se_pageload_time,true);

if(is_array($page_json_ld)) {
    $json_ld = json_encode($page_json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $smarty->assign('json_ld', $json_ld,true);
}

$smarty->assign('prepend_head_code', $prepend_head_code);
$smarty->assign('append_head_code', $append_head_code);
$smarty->assign('prepend_body_code', $prepend_body_code);
$smarty->assign('append_body_code', $append_body_code);

// Admin helpers
$store = '';
if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == "administrator") {
    $store = $_SESSION['se_admin_helpers'];

    if(isset($store['snippet'])) {
        $smarty->assign('admin_helpers_snippets', $store['snippet']);
    }
    if(isset($store['plugin'])) {
        $store['plugin'] = array_unique($store['plugin']);
        $smarty->assign('admin_helpers_plugins', $store['plugin']);
    }
    if(isset($store['products'])) {
        $store['products'] = array_unique($store['products']);
        $smarty->assign('admin_helpers_products', $store['products']);
    }
    if(isset($store['images'])) {
        $store['images'] = array_unique($store['images']);
        $smarty->assign('admin_helpers_images', $store['images']);
    }
    if(isset($store['files'])) {
        $store['files'] = array_unique($store['files']);
        $smarty->assign('admin_helpers_files', $store['files']);
    }
}