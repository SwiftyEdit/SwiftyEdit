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
 * get the theme, layout and stylesheet a page is rendered with
 * used by smarty.php (page matched by routing) and error.php (the error page)
 *
 * A page without an existing theme of its own (use_standard, empty, no page at
 * all or a theme that was removed) uses the site-wide theme and layout.
 *
 * @param array $page_contents from se_get_content()
 * @param array $se_settings
 * @param string $themes_path
 * @return array{template: string, layout: string, stylesheet: string}
 */
function se_resolve_page_theme(array $page_contents, array $se_settings, string $themes_path): array {

	// default template
	$se_template = $se_settings['template'] ?: 'default';
	$se_template_layout = $se_settings['template_layout'] ?: 'layout_default.tpl';
	$se_template_stylesheet = $se_settings['template_stylesheet'] ?? '';

	/* page has its own theme/template */
	if(is_dir($themes_path.'/'.$page_contents['page_template'].'/templates/')) {
		$se_template = $page_contents['page_template'];

		// without a layout of its own the page keeps the site-wide layout,
		// otherwise Smarty would look for a file named "use_standard"
		if(!in_array($page_contents['page_template_layout'], ['', 'use_standard'], true)) {
			$se_template_layout = $page_contents['page_template_layout'];
		}

		// Only override the site-wide stylesheet when the page actually pins its
		// own (there's no page-editor UI for this yet - see docs/v2/*/09-01-00-themes.md
		// - so this is normally empty). Otherwise a page that merely pins its own
		// theme/layout would always lose the site-wide stylesheet selection.
		if($page_contents['page_template_stylesheet'] != '') {
			$se_template_stylesheet = $page_contents['page_template_stylesheet'];
		}

		if($se_settings['usertemplate'] == 'overwrite') {
			/* the user theme has the same tpl file, so we can overwrite */
			if(($_SESSION['prefs_template'] ?? '') != '' && is_file($themes_path.'/'.$_SESSION['prefs_template'].'/templates/'.$page_contents['page_template_layout'])) {
				$se_template = $_SESSION['prefs_template'];
				$se_template_layout = $page_contents['page_template_layout'];
			}
		}
	}

	return [
		'template' => basename($se_template),
		'layout' => basename($se_template_layout),
		'stylesheet' => basename($se_template_stylesheet)
	];
}

/**
 * cache busting for theme assets: newest mtime of the built files in dist/,
 * so every theme build reaches clients immediately (used as ?v= in head.tpl)
 *
 * @param string $themes_path
 * @param string $template theme directory name
 * @return int
 */
function se_get_theme_assets_version(string $themes_path, string $template): int {

	$assets_version = 0;
	$theme_dist_files = array_merge(
		glob($themes_path.'/'.$template.'/dist/*.css') ?: [],
		glob($themes_path.'/'.$template.'/dist/*.js') ?: [],
		glob($themes_path.'/'.$template.'/dist/skins/*.css') ?: []
	);
	foreach($theme_dist_files as $dist_file) {
		$assets_version = max($assets_version, (int) filemtime($dist_file));
	}

	return $assets_version;
}

/**
 * get the language of the page behind a (frontend) url
 * used by xhr requests, which don't belong to a page themselves
 * the path is shortened segment by segment until it matches a permalink,
 * so detail urls like /en/blog/my-post/ resolve to the page /en/blog/
 *
 * @param string $url e.g. the HX-Current-URL or Referer header
 * @return string|null the page language or null if no page matches
 */
function se_get_page_language_by_url(string $url): ?string {

	global $db_content, $se_settings;

	$path = (string) parse_url($url, PHP_URL_PATH);
	$path = strtolower(urldecode($path));

	// strip the installation base (e.g. /subfolder/)
	$cms_base = trim($se_settings['cms_base'] ?? '', '/');
	$path = trim($path, '/');
	if($cms_base != '' && str_starts_with($path, $cms_base)) {
		$path = trim(substr($path, strlen($cms_base)), '/');
	}

	$segments = ($path == '') ? [] : explode('/', $path);

	while(count($segments) > 0) {
		$page_language = $db_content->get("se_pages", "page_language", [
			"page_permalink" => implode('/', $segments).'/'
		]);
		if(!empty($page_language)) {
			return $page_language;
		}
		array_pop($segments);
	}

	return null;
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