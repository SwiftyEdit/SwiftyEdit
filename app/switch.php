<?php

/**
 * SwiftyEdit frontend
 * - show shopping cart
 * - send order
 *
 * global variables
 * @var array $page_contents if there is a page to show, it is an array
 * @var array $se_prefs
 * @var object $smarty
 */

if (is_array($page_contents)) {

    foreach ($page_contents as $k => $v) {
        if($v != '') {
            $$k = stripslashes($v);
        }

        /* if we have custom fields, assign the values to smarty */
        if (preg_match("/custom_/i", $k)) {
            $v = text_parser($v);
            if($v != '') {
                $smarty->assign("$k", stripslashes($v));
            }
        }
    }
} else {
    $show_404 = "true";
}

$current_page_sort = $page_sort;

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
 * gereate mainmenu, submenu, breadcrumps and sitemap
 */

$mainmenu = array();
$submenu = array();

$mainmenu = show_mainmenu();
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
	$smarty->assign('legend_toc', text_parser(se_TOC_HEADER));
}

if($page_contents['page_sort'] == 'portal' OR $p == '') {
	$smarty->assign('homelink_status', "$se_defs[main_nav_class_active]");
} else {
	$smarty->assign('homelink_status', "$se_defs[main_nav_class]");
}

$smarty->assign('body_template', $se_template_layout);


$snippet_footer = se_get_textlib('footer_text',"$languagePack",'all');
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

/* show checkout */
if($p == "checkout") {
	include 'checkout.php';
}

/* show checkout */
if($p == "orders") {
	include 'orders.php';
}

/* list or display products */
if($p == "products") {
	include 'products.php';
}

/* list or display events */
if($p == "events") {
	include 'events.php';
}

/* list or display posts */
if($p == "posts") {
	include 'posts.php';
}

/* start download from /content/files/ */
if(isset($_POST['download'])) {
    include 'download.php';
}


/* comments */
$post_comments = 0;
if(isset($post_data) AND $post_data['post_comments'] == 1) {
	$post_comments = 1;
}

if(($page_comments == 1 OR $post_comments == 1) && $se_prefs['prefs_comments_mode'] != 3) {
    $smarty->assign('show_page_comments', 'true', true);
}



/* register */

if($p == "register") {

	if($page_contents['page_permalink'] != '') {
		$smarty->assign("form_url", '/'.$page_contents['page_permalink']);
	} else {
		$form_url = SE_INCLUDE_PATH . "/register/";
		$smarty->assign("form_url","$form_url");
	}

	if($se_prefs['prefs_userregistration'] != "yes") {

		$smarty->assign("msg_title",$lang['legend_register']);
		$smarty->assign("msg_text",$lang['msg_register_intro_disabled']);
		$output = $smarty->fetch("status_message.tpl",$cache_id);
		$smarty->assign('page_content', $output, true);

	} else {

		// INCLUDE/SHOW AGREEMENT TEXT
		$agreement_txt = se_get_textlib("agreement_text", $languagePack,'content');
		$smarty->assign("agreement_text",$agreement_txt);

		if($_POST['send_registerform']) {
			include 'user_register.php';
		}

		$output = $smarty->fetch("registerform.tpl",$cache_id);
		$smarty->assign('page_content', $output, true);

	}
}


/* confirm new account */
if($p == "account") {
	
	$user = se_return_clean_value($_GET['user']);
	$al = se_return_clean_value($_GET['al']);
	
	$verify = $db_user->update("se_user", [
		"user_verified" => 'verified'
		], [
			"AND" => [
			"user_nick" => $user,
			"user_activationkey" => $al
		]
	]);
	
	$cnt_changes = $verify->rowCount();
	
	
	if($cnt_changes > 0){
		$account_msg = se_get_textlib("account_confirm", $languagePack,'content');
		$account_msg = str_replace("{USERNAME}","$user",$account_msg);
		record_log("switch","user activated via mail - $user","5");
	} else {
		$account_msg = "";
	}
	
	$smarty->assign('page_content', $account_msg, true);
}


/* edit profile */
if(($p == "profile" OR $page_contents['page_type_of_use'] == 'profile') AND ($goto != "logout")) {
	include 'user_updateprofile.php';
}


/* include search */
if($p == 'search' OR $page_contents['page_permalink'] == 'suche/' OR $page_contents['page_permalink'] == 'search/' OR $page_contents['page_type_of_use'] == 'search') {
	include 'search.php';
}

/* forgotten password */
if($p == "password" OR $page_contents['page_type_of_use'] == 'password') {
	include 'password.php';
}

if($p == "unlock" OR $page_contents['page_type_of_use'] == 'unlock_account') {
    include 'unlock_account.php';
}


if($p == "404") {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	
	if($page_contents['page_permalink'] == '') {
	
		$smarty->assign('page_title', "404 Page Not Found");
		$output = $smarty->fetch("404.tpl");
		$smarty->assign('page_content', $output);
	}
	
	$show_404 = "false";
}



/**
 * no page, no content
 * assign the 404 template
 */

if((in_array("$p", $a_allowed_p)) OR ($p == "")) {
	$show_404 = "false";
}

if(isset($show_404) AND $show_404 == "true") {
	$output = $smarty->fetch("404.tpl");
	$smarty->assign('page_content', $output);
}

