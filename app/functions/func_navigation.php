<?php


/**
 * Build the Mainmenu
 * get all pages where page_sort is integer
 *
 * @return	array
 */

function show_mainmenu() {

	global $se_nav;
	global $current_page_sort;
	global $se_defs;
	
	$count_result = count($se_nav);
	
	for($i=0;$i<$count_result;$i++) {
		
		/* push portal links to the and of the array */
		if($se_nav[$i]['page_sort'] == 'portal') {
			$menu[$count_result+1]['homepage_linkname'] = $se_nav[$i]['page_linkname'];
			$menu[$count_result+1]['homepage_title'] = $se_nav[$i]['page_title'];
			$menu[$count_result+1]['homepage_permalink'] = $se_nav[$i]['page_permalink'];
		}
	
		if($se_nav[$i]['page_sort'] == "" || $se_nav[$i]['page_permalink'] == "" || $se_nav[$i]['page_sort'] == 'portal') {
			continue; //no page_sort or portal -> no menu item
		}
		
		$sort = $se_nav[$i]['page_sort'];
		$points_of_item = substr_count($sort, '.');
		
		if($points_of_item < 1) {
			$menu[$i]['page_id'] = $se_nav[$i]['page_id'];
			$menu[$i]['page_sort'] = $se_nav[$i]['page_sort'];
			$menu[$i]['page_linkname'] = stripslashes($se_nav[$i]['page_linkname']);
			$menu[$i]['page_title'] = stripslashes($se_nav[$i]['page_title']);
			$menu[$i]['page_permalink'] = $se_nav[$i]['page_permalink'];
			$menu[$i]['page_target'] = $se_nav[$i]['page_target'];
			$menu[$i]['page_hash'] = $se_nav[$i]['page_hash'];
			$menu[$i]['page_classes'] = $se_nav[$i]['page_classes'];
			$menu[$i]['link_status'] = $se_defs['main_nav_class'];
		
			if(left_string($current_page_sort) == left_string($menu[$i]['page_sort']) ) {
				$menu[$i]['link_status'] = $se_defs['main_nav_class_active'];
				define('se_MAIN_CAT', clean_filename($se_nav[$i]['page_linkname']));
				define('se_TOC_HEADER', $menu[$i]['page_linkname']);
			}
		
			/* generate the main menu */
			$menu[$i]['link'] = SE_INCLUDE_PATH . "/" . $se_nav[$i]['page_permalink'];
		}
	}
	
	return $menu;

} // eol func show_menu




/**
 * Build the Submenu
 * get all pages where page_sort begins with the given number (also a page_sort)
 *
 * @param mixed $num (page_sort of parent page)
 * @return array
 */

function show_menu($num){
	
	global $se_nav;
	global $current_page_sort;
	
	
	if($num == "") { return; }
	$items = array();
	$m = array();
	$num_split = explode('.',$num);
	$current_page_sort_split = explode('.',$current_page_sort);
	$current_level = count($num_split);
	$cnt_all_navs = count($se_nav); // number of all nav entries
	
	$current_match_elements = array_slice($num_split, 0, $current_level);
		
	for($i=0;$i<$cnt_all_navs;$i++) {
		
		$nav_sort = $se_nav[$i]['page_sort'];
		$nav_split = explode('.',$nav_sort);
		$nav_level = count($nav_split); // level
		$nav_match_elements = array_slice($nav_split, 0, $current_level);
		
		if($nav_level <= 1) {
			continue;
		}
		
		if($nav_level > ($current_level+1)) {
			continue;
		}
		
		if($nav_level > $current_level) {
			
			if($current_match_elements !== $nav_match_elements) {
				continue;
			}

		}
		
		if($nav_level <= $current_level) {
			
			$l = array_slice($num_split, 0, ($nav_level-1));
			$r = array_slice($nav_split, 0, ($nav_level-1));

			if($l !== $r) {
				continue;
			}
			
		}
				
		
		if(count(array_intersect_assoc($num_split, $nav_split)) < 1) {
			continue;
		}
		
		$items = build_submenu($i,$nav_level);
		
		foreach($items as $value) {
			$m[] = $value;
		}


	}

	return $m;
}



function build_submenu($index,$level=1) {

	global $se_nav;
	global $current_page_sort;
	global $se_defs;
	
	$sort = $se_nav[$index]['page_sort'];
	
	$submenu[$index]['page_id'] = $se_nav[$index]['page_id'];
	$submenu[$index]['page_sort'] = $se_nav[$index]['page_sort'];
	$submenu[$index]['page_permalink'] = $se_nav[$index]['page_permalink'];
	$submenu[$index]['page_target'] = $se_nav[$index]['page_target'];
	$submenu[$index]['page_hash'] = $se_nav[$index]['page_hash'];
	$submenu[$index]['page_classes'] = $se_nav[$index]['page_classes'];
	$submenu[$index]['page_linkname'] = stripslashes($se_nav[$index]['page_linkname']);
	$submenu[$index]['page_title'] = stripslashes($se_nav[$index]['page_title']);
	

	if($sort === $current_page_sort) {
		$submenu[$index]['link_status'] = $se_defs['sub_nav_prefix_class_active'].$level;
	} else {
		$submenu[$index]['link_status'] = 'sub_link'.$level;
	}
	
	$submenu[$index]['sublink'] = SE_INCLUDE_PATH . "/" . $se_nav[$index]['page_permalink'];
	
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
        $path .= '/' . $part;
        $label = urldecode($part);
        $title = $label;
        $check_permalink .= $part.'/';

        $get_page_info = $db_content->get("se_pages",["page_linkname", "page_title", "page_meta_description"],[
            "page_permalink" => $check_permalink,
            "page_status" => ['public','ghost']
        ]);

        if($get_page_info['page_linkname'] != '') {
            $label = $get_page_info['page_linkname'];
        }

        if($get_page_info['page_title'] != '') {
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

function left_string($string) {
    if($string == '') {
        return;
    }
  $string = explode(".", $string);
  return $string[0];
}

?>