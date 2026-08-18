<?php


/**
 * Build the Mainmenu = direct children of the portal page (i.e. the old
 * "page_sort has no dot" top-level pages), plus the portal/home link itself
 * appended last (template-setup.php pulls it off array_key_last()).
 *
 * @return	array
 */

function show_mainmenu(): array
{

	global $se_nav,$current_page_id,$se_defs;

	// only set inside the loop below, when the current page's top-level
	// category is found - stay '' otherwise (e.g. on the homepage)
	$se_main_cat = '';
	$se_toc_header = '';
	$menu = [];

	$portal = null;
	foreach ($se_nav as $page) {
		if ($page['page_sort'] === 'portal') {
			$portal = $page;
			break;
		}
	}

	if ($portal === null) {
		return ['menu' => $menu, 'se_main_cat' => $se_main_cat, 'se_toc_header' => $se_toc_header];
	}

	$index = se_index_pages_by_parent($se_nav);
	$pages_by_id = se_index_pages_by_id($se_nav);

	// which top-level item is the current page itself, or an ancestor of it
	$current_chain = se_get_page_ancestor_chain($pages_by_id, $current_page_id, $portal['page_id']);
	$active_top_level_id = $current_chain[0] ?? null;

	foreach (se_get_page_children($index, $portal['page_id']) as $page) {

		if ($page['page_permalink'] == "") {
			continue; // no permalink -> no menu item
		}

		$item = [
			'page_id' => $page['page_id'],
			'page_sort' => $page['page_sort'],
			'page_linkname' => stripslashes($page['page_linkname']),
			'page_title' => stripslashes($page['page_title']),
			'page_permalink' => $page['page_permalink'],
			'page_target' => $page['page_target'],
			'page_hash' => $page['page_hash'],
			'page_classes' => $page['page_classes'],
			'link_status' => $se_defs['main_nav_class'],
		];

		if ($active_top_level_id !== null && (int) $page['page_id'] === $active_top_level_id) {
			$item['link_status'] = $se_defs['main_nav_class_active'];
			$se_toc_header = $item['page_linkname'];
			$se_main_cat = clean_filename($page['page_linkname']);
		}

		$item['link'] = SE_INCLUDE_PATH . "/" . $page['page_permalink'];

		// direct children of this top-level page, rendered as a dropdown in the main nav
		$children = [];
		foreach (se_get_page_children($index, $page['page_id']) as $child) {

			if ($child['page_permalink'] == "") {
				continue; // no permalink -> no menu item
			}

			$children[] = [
				'page_id' => $child['page_id'],
				'page_linkname' => stripslashes($child['page_linkname']),
				'page_title' => stripslashes($child['page_title']),
				'page_permalink' => $child['page_permalink'],
				'page_target' => $child['page_target'],
				'page_hash' => $child['page_hash'],
				'page_classes' => $child['page_classes'],
				'link' => SE_INCLUDE_PATH . "/" . $child['page_permalink'],
				'link_status' => (int) $child['page_id'] === (int) $current_page_id ? $se_defs['main_nav_class_active'] : '',
			];
		}

		if (!empty($children)) {
			$item['children'] = $children;
		}

		$menu[] = $item;
	}

	// the portal/home link, appended last on purpose (see docblock)
	$menu[] = [
		'homepage_linkname' => $portal['page_linkname'],
		'homepage_title' => $portal['page_title'],
		'homepage_permalink' => $portal['page_permalink'],
	];

    return [
        'menu' => $menu,
        'se_main_cat' => $se_main_cat,
        'se_toc_header' => $se_toc_header
    ];

} // eol func show_menu




/**
 * Build the Submenu = the direct children of the current page.
 *
 * @param int|null $current_page_id
 * @return array
 */

function show_menu($current_page_id) {

	global $se_nav, $se_defs;

	if (!$current_page_id) {
		return;
	}

	$index = se_index_pages_by_parent($se_nav);
	$pages_by_id = se_index_pages_by_id($se_nav);

	$portal_id = null;
	foreach ($se_nav as $page) {
		if ($page['page_sort'] === 'portal') {
			$portal_id = $page['page_id'];
			break;
		}
	}

	// same numbering as before: a top-level page's direct children are
	// "level 2" (sub_link2), matching the old page_sort segment count
	$current_level = count(se_get_page_ancestor_chain($pages_by_id, $current_page_id, $portal_id));
	$child_level = $current_level + 1;

	$m = [];
	foreach (se_get_page_children($index, $current_page_id) as $page) {
		$m[] = build_submenu($page, $child_level, $current_page_id);
	}

	return $m;
}



function build_submenu($page, $level, $current_page_id) {

	global $se_defs;

	$submenu = [
		'page_id' => $page['page_id'],
		'page_sort' => $page['page_sort'],
		'page_permalink' => $page['page_permalink'],
		'page_target' => $page['page_target'],
		'page_hash' => $page['page_hash'],
		'page_classes' => $page['page_classes'],
		'page_linkname' => stripslashes($page['page_linkname']),
		'page_title' => stripslashes($page['page_title']),
	];

	if ((int) $page['page_id'] === (int) $current_page_id) {
		$submenu['link_status'] = $se_defs['sub_nav_prefix_class_active'].$level;
	} else {
		$submenu['link_status'] = 'sub_link'.$level;
	}

	$submenu['sublink'] = SE_INCLUDE_PATH . "/" . $page['page_permalink'];

	return $submenu;
}

/**
 * create breadcrumb menu from url
 * @return array
 */
function breadcrumbs_menu(): array {

    global $query, $db_content;

    $parts = explode('/', trim($query, '/'));
    $breadcrumbs = [];
    $path = '';
    $check_permalink = '';
    $lastIndex = count($parts) - 1;

    $x=0;
    foreach ($parts as $index => $part) {

        if($part == 'p') { continue; } // pagination
        if(is_numeric($part)) { continue; } // pagination

        $path .= '/' . $part;
        $label = urldecode($part);
        $title = $label;
        $check_permalink .= $part.'/';

        $get_page_info = $db_content->get("se_pages",["page_linkname", "page_title", "page_meta_description"],[
            "page_permalink" => $check_permalink,
            "page_status" => ['public','ghost']
        ]);

        // no page matches this breadcrumb segment (e.g. an intermediate,
        // non-page path part) - fall back to the values set above
        if (!is_array($get_page_info)) {
            $get_page_info = [];
        }

        if(($get_page_info['page_linkname'] ?? '') != '') {
            $label = $get_page_info['page_linkname'];
        }

        if(($get_page_info['page_title'] ?? '') != '') {
            $title = $get_page_info['page_title'];
        }

        if ($index === $lastIndex) {
            $breadcrumbs[$x]['page_linkname'] = $label;
            $breadcrumbs[$x]['link'] = '';
        } else {
            $breadcrumbs[$x]['page_linkname'] = $label;
            $breadcrumbs[$x]['page_title'] = $title;
            $breadcrumbs[$x]['link'] = $path.'/';
        }
        $x++;
    }

    return $breadcrumbs;
}

?>