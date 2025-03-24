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
				

	if($page_contents['page_language'] == '') {
		$page_contents['page_language'] = $languagePack;
	} else {
		$languagePack = $page_contents['page_language'];
	}

	if(!isset($_SESSION['user_class']) OR $_SESSION['user_class'] != 'administrator') {

		$se_nav = $db_content->select("se_pages", ['page_id', 'page_classes', 'page_hash', 'page_language', 'page_linkname', 'page_permalink', 'page_target', 'page_title', 'page_sort', 'page_status'], [
				"AND" => [
					"OR" => [
						"page_status[!]" => ["draft","ghost"]
				],
				"page_language" => $languagePack
			],
				"ORDER" => ["page_sort" => "DESC"]
			]);
		
	} else {

		$se_nav = $db_content->select("se_pages", ['page_id', 'page_classes', 'page_hash', 'page_language', 'page_linkname', 'page_permalink', 'page_target', 'page_title', 'page_sort', 'page_status'], [
				"page_language" => $languagePack
			],[
				"ORDER" => ["page_sort" => "DESC"]
			]);
	}
	
	$se_nav = se_array_multisort($se_nav, 'page_language', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);
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
 * @return mixed array or NULL
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

	return $page;
}


?>