<?php

/**
 * get contents of the current page (default by (int) $p)
 * get contents for navigation
 *
 * @return array
 */

function se_get_content($page, $mode = 'p') {

	global $db_content;
	global $languagePack;

	if($mode == 'permalink') {
		
		$page_contents = $db_content->get("se_pages", "*", [
			"page_permalink" => $page
		]);
		
	} elseif ($mode == 'type_of_use') {
			
		$page_contents = $db_content->get("se_pages", "*", [
				"AND" => [
				"page_type_of_use" => $page,
				"page_language" => "$languagePack"
				]
		]);	
	
	
	} elseif ($mode == 'page_sort') {
			
		$page_contents = $db_content->get("se_pages", "*", [
				"AND" => [
				"page_sort" => "$page",
				"page_language" => "$languagePack"
				]
		]);		
	
	
	} elseif ($mode == 'preview') {
	
		$page_contents = $db_content->get("se_pages_cache", "*", [
				"AND" => [
				"page_id_original" => "$page",
				"page_language" => "$languagePack"
			],
				"ORDER" => ["page_id" => "DESC"]
			]);			
	
	} else {
		
	
		$page_contents = $db_content->get("se_pages", "*", [
			"page_id" => $page
		]);
	
	}

	// Medoo's get() returns null when no row matches (e.g. unknown permalink/id);
	// normalize to an empty array so callers can rely on the documented @return array
	if (!is_array($page_contents)) {
		$page_contents = [];
	}

	// callers throughout the frontend compare this against known type_of_use
	// values (e.g. 'display_product', '404') without checking it's set first
	$page_contents['page_type_of_use'] ??= '';

	// same story for the posts/products/events type filter ('p', 'e', 'm', ...)
	$page_contents['page_posts_types'] ??= '';

	// posts-list.php/products-list.php/events.php explode() this on ',' without
	// checking it's set - an unset key would pass null to explode() (deprecated)
	$page_contents['page_posts_categories'] ??= '';

	// smarty.php/error.php pick the theme/template based on these; also read
	// by posts-list.php/products-list.php for per-page theme overrides
	$page_contents['page_template'] ??= '';
	$page_contents['page_template_layout'] ??= '';
	$page_contents['page_template_stylesheet'] ??= '';

	// template-setup.php reads these two directly off the array (not via the
	// $$k convenience vars) to detect the homepage and the categories display mode
	$page_contents['page_sort'] ??= '';
	$page_contents['page_categories_mode'] ??= '';

	if(($page_contents['page_language'] ?? '') == '') {
		$page_contents['page_language'] = $languagePack;
	} else {
		$languagePack = $page_contents['page_language'];
	}

	if(!isset($_SESSION['user_class']) OR $_SESSION['user_class'] != 'administrator') {

		// cached navigation for regular visitors, built by se_build_navigation_cache()
		$se_nav = se_get_navigation_from_cache($languagePack);

		if ($se_nav === null) {

			$se_nav = $db_content->select("se_pages", ['page_id', 'page_parent_id', 'position', 'page_classes', 'page_hash', 'page_language', 'page_linkname', 'page_permalink', 'page_target', 'page_title', 'page_sort', 'page_status'], [
					"AND" => [
						"OR" => [
							"page_status[!]" => ["draft","ghost"]
					],
					"page_language" => $languagePack
				],
					"ORDER" => ["page_sort" => "DESC"]
				]);

			$se_nav = se_array_multisort($se_nav, 'page_language', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);
		}

	} else {

		// administrators always see a live query, including draft/ghost pages
		$se_nav = $db_content->select("se_pages", ['page_id', 'page_parent_id', 'position', 'page_classes', 'page_hash', 'page_language', 'page_linkname', 'page_permalink', 'page_target', 'page_title', 'page_sort', 'page_status'], [
				"page_language" => $languagePack
			],[
				"ORDER" => ["page_sort" => "DESC"]
			]);

		$se_nav = se_array_multisort($se_nav, 'page_language', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);
	}

	$contents = array($page_contents,$se_nav);
	
	return $contents;
}



/**
 * check if given url is a shortlink
 * if applicable, immediately redirect to page permalink
 */

function se_check_shortlinks($shortlink) {

	global $db_content;
	
	$page = $db_content->get("se_pages", ["page_permalink", "page_permalink_short_cnt"], [
		"page_permalink_short" => $shortlink
	]);

	// no matching shortlink found
	if (!is_array($page)) {
		return;
	}

	/* increase page_permalink_short_cnt
		 redirect to page_permalink	*/

	if($page['page_permalink'] != '') {
				
		$page_permalink_short_cnt = (int) $page['page_permalink_short_cnt'] +1;
		
		$db_content->update("se_pages", [
			"page_permalink_short_cnt" => $page_permalink_short_cnt
		], [
			"page_permalink_short" => $shortlink
		]);
		
				
		$redirect = '/'.$page['page_permalink'];
		header("location: $redirect",TRUE,301);	
		exit;
	}	
}

/**
 * check if given url is a funnel uri
 * if applicable, immediately redirect to page permalink
 */

function se_check_funnel_uri($uri) {
		
	global $db_content;

	$pages = $db_content->select("se_pages", ["page_permalink", "page_funnel_uri"], [
		"page_funnel_uri[~]" => "%$uri%"
	]);
	
	foreach($pages as $page) {
		$page_funnel_uri = explode(',', $page['page_funnel_uri']);
		foreach($page_funnel_uri as $u) {

			if($u == $uri) {
				$redirect = '/'.$page['page_permalink'];
				header("location: $redirect",TRUE,301);
				exit;
			}
			
		}
	}	
}

/**
 * @param $type
 * @return array empty array if no page of this type_of_use is configured
 */
function se_get_type_of_use_pages($type) {

	global $db_content;
	global $languagePack;

	$page = $db_content->get("se_pages", ["page_permalink", "page_funnel_uri"], [
		"AND" => [
			"page_type_of_use" => "$type",
			"page_language" => "$languagePack"
		]
	]);

	// no page configured for this type_of_use (e.g. no "orders" page set up yet)
	if (!is_array($page)) {
		$page = [];
	}

	return $page;
}


?>