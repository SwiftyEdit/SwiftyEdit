<?php
	
/**
 * get entries from se_posts
 * @param integer $start
 * @param mixed $limit all or number
 * @param array $filter
 * @return array
 */

function se_get_post_entries($start,$limit,$filter): array {
	
	global $db_posts;
	global $db_type;
	global $time_string_start;
	global $time_string_end;
	global $time_string_now;
	global $se_prefs;
	global $se_labels;
	
	if(SE_SECTION == 'frontend') {
		global $se_prefs;
	}
	
	if(empty($start)) {
		$start = 0;
	}
	if(empty($limit)) {
		$limit = 10;
	}	
	
		
	$limit_str = 'LIMIT '. (int) $start;
	
	if($limit == 'all') {
		$limit_str = '';
	} else {
		$limit_str .= ', '. (int) $limit;
	}
	
	
	/**
	 * order and direction
	 * we ignore $order and $direction
	 */

	$order = "ORDER BY post_fixed ASC, sortdate DESC, post_priority DESC, post_id DESC";


	/* set filters */
	$sql_filter_start = 'WHERE post_id IS NOT NULL ';

    /* text search */
    if($filter['text'] != '') {
        $sql_text_filter = '';
        $all_filter = explode(" ",$filter['text']);
        // loop through keywords
        foreach($all_filter as $f) {
            if($f == "") { continue; }
            $sql_text_filter .= "(post_tags like '%$f%' OR post_title like '%$f%' OR post_teaser like '%$f%' OR post_text like '%$f%') AND";
        }
        $sql_text_filter = substr("$sql_text_filter", 0, -4); // cut the last ' AND'

    } else {
        $sql_text_filter = '';
    }

	/* language filter */
    if($filter['languages'] != '') {
        $sql_lang_filter = "post_lang IS NULL OR ";
        $lang = explode('-', $filter['languages']);
        foreach ($lang as $l) {
            if ($l != '') {
                $sql_lang_filter .= "(post_lang LIKE '%$l%') OR ";
            }
        }
        $sql_lang_filter = substr("$sql_lang_filter", 0, -3); // cut the last ' OR'
    } else {
        $sql_lang_filter = '';
    }
	
	/* type filter */
	$sql_types_filter = "post_type IS NULL OR ";
	$types = explode('-', $filter['types']);
	foreach($types as $t) {
		if($t != '') {
			$sql_types_filter .= "(post_type LIKE '%$t%') OR ";
		}		
	}
	$sql_types_filter = substr("$sql_types_filter", 0, -3); // cut the last ' OR'


	/* status filter */
    if($filter['status'] != '') {
        $sql_status_filter = "post_status IS NULL OR ";
        $status = explode('-', $filter['status']);
        foreach ($status as $s) {
            if ($s != '') {
                $sql_status_filter .= "(post_status LIKE '%$s%') OR ";
            }
        }
        $sql_status_filter = substr("$sql_status_filter", 0, -3); // cut the last ' OR'
    } else {
        $sql_status_filter = '';
    }
	
	/* category filter */
	if($filter['categories'] == 'all' OR $filter['categories'] == '') {
		$sql_cat_filter = '';
	} else {
		
		$cats = explode(',', $filter['categories']);
		foreach($cats as $c) {
			if($c != '') {
				$sql_cat_filter .= "(post_categories LIKE '%$c%') OR ";
			}		
		}
		$sql_cat_filter = substr("$sql_cat_filter", 0, -3); // cut the last ' OR'
	}
	
	/* label filter */
	if(!isset($filter['labels']) OR $filter['labels'] == 'all' OR $filter['labels'] == '') {
		$sql_label_filter = '';
	} else {

		$checked_labels_array = explode('-', $filter['labels']);
		
		for($i=0;$i<count($se_labels);$i++) {
			$label = $se_labels[$i]['label_id'];
			if(in_array($label, $checked_labels_array)) {
				$sql_label_filter .= "post_labels LIKE '%,$label,%' OR post_labels LIKE '%,$label' OR post_labels LIKE '$label,%' OR post_labels = '$label' OR ";
			}
		}		
		$sql_label_filter = substr("$sql_label_filter", 0, -3); // cut the last ' OR'
	}

	$sql_filter = $sql_filter_start;
	
	if($sql_lang_filter != "") {
		$sql_filter .= " AND ($sql_lang_filter) ";
	}
	if($sql_types_filter != "") {
		$sql_filter .= " AND ($sql_types_filter) ";
	}
	if($sql_status_filter != "") {
		$sql_filter .= " AND ($sql_status_filter) ";
	}
	if($sql_cat_filter != "") {
		$sql_filter .= " AND ($sql_cat_filter) ";
	}
	if($sql_label_filter != "") {
		$sql_filter .= " AND ($sql_label_filter) ";
	}
    if($sql_text_filter != "") {
        $sql_filter .= " AND ($sql_text_filter) ";
    }
	
	if(SE_SECTION == 'frontend') {
		$sql_filter .= "AND post_releasedate <= '$time_string_now' ";
	}

	if($time_string_start != '') {
		$sql_filter .= "AND post_releasedate >= '$time_string_start' AND post_releasedate <= '$time_string_end' AND post_releasedate < '$time_string_now' ";
	}
	
	if($db_type == 'sqlite') {
		$sql = "SELECT *, strftime('%Y-%m-%d',datetime(post_releasedate, 'unixepoch')) as 'sortdate' FROM se_posts $sql_filter $order $limit_str";
	} else {
		$sql = "SELECT *, FROM_UNIXTIME(post_releasedate,'%Y-%m-%d') as 'sortdate' FROM se_posts $sql_filter $order $limit_str";
	}

	$entries = $db_posts->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
	$sql_cnt = "SELECT count(*) AS 'A', (SELECT count(*) FROM se_posts $sql_filter) AS 'filter_posts',  (SELECT count(*) FROM se_posts WHERE post_type IN ('m','i','g','f','v','l') ) AS 'all_posts'";
	$stat = $db_posts->query("$sql_cnt")->fetch(PDO::FETCH_ASSOC);

	/* number of posts that match the filter */
	$entries[0]['cnt_posts'] = $stat['filter_posts'];
    $entries[0]['cnt_all_posts'] = $stat['all_posts'];
	return $entries;
	
}


function se_get_event_entries($start,$limit,$filter) {

    global $db_posts;
    global $db_type;
    global $time_string_start;
    global $time_string_end;
    global $time_string_now;
    global $se_prefs;
    global $se_labels;

    if(empty($start)) {
        $start = 0;
    }
    if(empty($limit)) {
        $limit = 10;
    }


    $limit_str = 'LIMIT '. (int) $start;

    if($limit == 'all') {
        $limit_str = '';
    } else {
        $limit_str .= ', '. (int) $limit;
    }

    if(!isset($filter['labels'])) {
        $filter['labels'] = '';
    }


    /**
     * order and direction
     * we ignore $order and $direction
     */

    $order = 'ORDER BY fixed ASC, sortdate_events ASC, priority DESC';

    /* set filters */
    $sql_filter_start = "WHERE id IS NOT NULL ";

    /* language filter */
    if($filter['languages'] != '') {
        $sql_lang_filter = "event_lang IS NULL OR ";
        $lang = explode('-', $filter['languages']);
        foreach ($lang as $l) {
            if ($l != '') {
                $sql_lang_filter .= "(event_lang LIKE '%$l%') OR ";
            }
        }
        $sql_lang_filter = substr("$sql_lang_filter", 0, -3); // cut the last ' OR'
    } else {
        $sql_lang_filter = '';
    }

    /* text search */
    if($filter['text_search'] != '') {
        $sql_text_filter = '';
        $all_filter = explode(" ",$filter['text_search']);
        // loop through keywords
        foreach($all_filter as $f) {
            if($f == "") { continue; }
            $sql_text_filter .= "(tags like '%$f%' OR title like '%$f%' OR teaser like '%$f%' OR text like '%$f%') AND";
        }
        $sql_text_filter = substr("$sql_text_filter", 0, -4); // cut the last ' AND'

    } else {
        $sql_text_filter = '';
    }

    /* status filter */
    if($filter['status'] != '') {
        $sql_status_filter = "status IS NULL OR ";
        $status = explode('-', $filter['status']);
        foreach ($status as $s) {
            if ($s != '') {
                $sql_status_filter .= "(status LIKE '%$s%') OR ";
            }
        }
        $sql_status_filter = substr("$sql_status_filter", 0, -3); // cut the last ' OR'
    } else {
        $sql_status_filter = '';
    }


    /* category filter */
    if($filter['categories'] == 'all' OR $filter['categories'] == '') {
        $sql_cat_filter = '';
    } else {

        $cats = explode(',', $filter['categories']);
        foreach($cats as $c) {
            if($c != '') {
                $sql_cat_filter .= "(categories LIKE '%$c%') OR ";
            }
        }
        $sql_cat_filter = substr("$sql_cat_filter", 0, -3); // cut the last ' OR'
    }

    /* label filter */
    if($filter['labels'] == 'all' OR $filter['labels'] == '') {
        $sql_label_filter = '';
    } else {

        $checked_labels_array = explode('-', $filter['labels']);

        for($i=0;$i<count($se_labels);$i++) {
            $label = $se_labels[$i]['label_id'];
            if(in_array($label, $checked_labels_array)) {
                $sql_label_filter .= "labels LIKE '%,$label,%' OR labels LIKE '%,$label' OR labels LIKE '$label,%' OR labels = '$label' OR ";
            }
        }
        $sql_label_filter = substr("$sql_label_filter", 0, -3); // cut the last ' OR'
    }

    $sql_filter = $sql_filter_start;

    if($sql_lang_filter != "") {
        $sql_filter .= " AND ($sql_lang_filter) ";
    }

    if($sql_status_filter != "") {
        $sql_filter .= " AND ($sql_status_filter) ";
    }
    if($sql_cat_filter != "") {
        $sql_filter .= " AND ($sql_cat_filter) ";
    }
    if($sql_label_filter != "") {
        $sql_filter .= " AND ($sql_label_filter) ";
    }

    if($sql_text_filter != "") {
        $sql_filter .= " AND ($sql_text_filter) ";
    }

    /* we hide past events in frontend */
    if(SE_SECTION == 'frontend') {
        $sql_filter .= "AND releasedate <= '$time_string_now' ";
        $time_hide_events = $time_string_now-$se_prefs['prefs_posts_event_time_offset'];
        $sql_filter .= "AND event_enddate >= '$time_hide_events' ";
    }

    /* hide past events in backend */
    if(SE_SECTION !== 'frontend' AND $_SESSION['show_past_events'] == 2) {
        $time_string_now = time();
        $time_hide_events = $time_string_now-$se_prefs['prefs_posts_event_time_offset'];
        $sql_filter .= "AND event_enddate >= '$time_hide_events' ";
    }

    if($time_string_start != '') {
        $sql_filter .= "AND releasedate >= '$time_string_start' AND releasedate <= '$time_string_end' AND releasedate < '$time_string_now' ";
    }

    if($db_type == 'sqlite') {
        $sql = "SELECT * , strftime('%Y-%m-%d',datetime(releasedate, 'unixepoch')) as 'sortdate', strftime('%Y-%m-%d',datetime(event_startdate, 'unixepoch')) as 'sortdate_events' FROM se_events $sql_filter $order $limit_str";
    } else {
        $sql = "SELECT * , FROM_UNIXTIME(releasedate,'%Y-%m-%d') as 'sortdate', FROM_UNIXTIME(event_startdate,'%Y-%m-%d') as 'sortdate_events' FROM se_events $sql_filter $order $limit_str";
    }

    $entries = $db_posts->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cnt = "SELECT count(*) AS 'A', (SELECT count(*) FROM se_events $sql_filter) AS 'filter_events', (SELECT count(*) FROM se_events) AS 'all_events'";
    $stat = $db_posts->query("$sql_cnt")->fetch(PDO::FETCH_ASSOC);

    /* number of posts that match the filter */
    $entries[0]['cnt_events'] = $stat['filter_events'];
    $entries[0]['cnt_all_events'] = $stat['all_events'];
    return $entries;

}


/**
 * count all entries
 */
 
function se_cnt_post_entries() {
	
	global $db_posts;
	
	$sql = "SELECT count(*) AS 'All',
		(SELECT count(*) FROM se_posts WHERE post_status LIKE '%1%' ) AS 'Public',
		(SELECT count(*) FROM se_posts WHERE post_status LIKE '%2%' ) AS 'Draft',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%m%' ) AS 'Message',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%l%' ) AS 'Link',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%v%' ) AS 'Video',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%i%' ) AS 'Image',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%e%' ) AS 'Event',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%p%' ) AS 'Product',
		(SELECT count(*) FROM se_posts WHERE post_type LIKE '%f%' ) AS 'File'
	FROM se_posts
	";
	
	$stats = $db_posts->query($sql)->fetch(PDO::FETCH_ASSOC);

	return $stats;
}


function se_get_post_data($id) {
	
	global $db_posts;
	
	$post_data = $db_posts->get("se_posts","*", [
		"post_id" => $id
	]);
	
	return $post_data;
}



function se_get_event_data($id) {

    global $db_posts;

    $data = $db_posts->get("se_events","*", [
        "id" => $id
    ]);

    return $data;
}


/**
 * print currency
 * aka 9,99
 *
 */
 
function se_post_print_currency($number) {

	$number = number_format($number, 2, ',', '.');
	
	$comma_pos = stripos($number, ",");
	$article_price_big = substr("$number", 0, $comma_pos);
	$article_price_small = substr("$number", -2);
	$article_price_string = "<span class='price-predecimal'>$article_price_big</span><span class='price-decimal'>,$article_price_small</span>";
		
	return $article_price_string;
}


/**
 * calculate product's price
 * return net and gross price, formatted and raw
 * @param float $price price net (stored in the database)
 * @param integer $tax
 * @param integer $amount
 * @return array gross, gross_single, gross_raw, net, net_single, net_raw
 */
function se_posts_calc_price($price,$tax,$amount=1) {
	
	if(empty($price)) {
		$price = 0;
	}
	
	$price = str_replace('.', '', $price);
	$price = str_replace(',', '.', $price);
    $price = number_format($price, 8, '.', '');

    $price_single_net = round($price,8);
    $price_sum_net = $price_single_net*$amount;

    if($tax != '0') {
        $price_single_gross = $price_single_net * ($tax + 100) / 100;
        $price_single_gross = round($price_single_gross, 2);
        $price_sum_gross = $price_single_gross * $amount;
    } else {
        $price_single_gross = $price_single_net;
        $price_sum_gross = $price_sum_net;
    }

	$prices['gross'] = se_post_print_currency($price_sum_gross);
	$prices['net'] = se_post_print_currency($price_sum_net);
    $prices['net_single'] = se_post_print_currency($price_single_net);
    $prices['gross_single'] = se_post_print_currency($price_single_gross);
	$prices['gross_raw'] = $price_sum_gross;
	$prices['net_raw'] = $price_sum_net;
	
	return $prices;
}





function se_set_pagination_query($display_mode,$start) {
	
	global $swifty_slug;
	global $pb_posts_filter;
	global $pub_preferences;
	global $array_mod_slug;
	
	if($display_mode == 'list_posts_category' OR $display_mode == 'list_products_category') {
		$pagination_link = "/$swifty_slug".$array_mod_slug[0].'/p/'."$start/";
	} else if($display_mode == 'list_archive_year') {
		$pagination_link = "/$swifty_slug".$array_mod_slug[0].'/p/'."$start/";
	} else if($display_mode == 'list_archive_month') {
		$pagination_link = "/$swifty_slug".$array_mod_slug[0].'/'.$array_mod_slug[1].'/p/'."$start/";
	} else if($display_mode == 'list_archive_day') {
		$pagination_link = "/$swifty_slug".$array_mod_slug[0].'/'.$array_mod_slug[1].'/'.$array_mod_slug[2].'/p/'."$start/";
	} else {
		$pagination_link = "/$swifty_slug".'p/'."$start/";
	}

	
	return $pagination_link;
}




/**
 * increase the hits counter
 */
 
function se_increase_posts_hits($post_id) {
	
	global $db_posts;
	
	$post_data_hits = $db_posts->get("se_posts","post_hits", [
		"post_id" => $post_id
	]);
	
	$post_data_hits = ((int) $post_data_hits)+1;

	$update = $db_posts->update("se_posts", [
		"post_hits" => $post_data_hits
	],[
		"post_id" => $post_id
	]);
		
}

/**
 * get voting data for posts or comments
 * return array $count['upv'] = x / $count['dnv'] = x, $count['all']
 */

function se_get_voting_data($type,$id) {
	
	global $db_content;
	$id = (int) $id;
	
	if($type == 'post') {
		
		$sql_cnt = "SELECT count(*) AS 'all_comments',
									(SELECT count(*) FROM se_comments WHERE (comment_type = 'upv' OR comment_type = 'dnv') AND comment_relation_id = $id) AS 'all',
									(SELECT count(*) FROM se_comments WHERE comment_type = 'upv' AND comment_relation_id = $id) AS 'upv',
									(SELECT count(*) FROM se_comments WHERE comment_type = 'dnv' AND comment_relation_id = $id) AS 'dnv'
									FROM se_comments";
		
		$count = $db_content->query("$sql_cnt")->fetch(PDO::FETCH_ASSOC);

		return $count;
	}
}

function se_get_votes($type,$id,$section) {
    global $db_content;
    $id = (int) $id;
    $count = 0;
    if($type == 'upv') {
        $comment_type = 'upv';
    } else if($type == 'dnv') {
        $comment_type = 'dnv';
    } else {
        return 0;
    }

    $count = $db_content->count("se_comments",[
        "comment_type" => $comment_type,
        "comment_relation_id" => $id,
        "comment_relation_type" => $section
    ]);

    return $count;
}


/**
 * check if user can vote on posts
 * $id = comment id
 * $name = user name or ip
 * $type = array("upv","dnv") or ("evc")
 * return true or false
 */
 
function se_check_user_legitimacy($id,$user,$type,$section=null) {
	
	global $db_content;
	
	$get_data = $db_content->select("se_comments", "*",[
			"AND" => [
				"OR" => [
					"comment_type" => $type
				],
				"comment_relation_id" => $id,
                "comment_relation_type" => $section,
				"comment_author" => $user
			]
		]);

		
		if(count($get_data) > 0) {
			return false;
		} else {
			return true;
		}
}




/**
 * get data for events guestlist
 * return number of commitments
 */
 
function se_get_event_confirmation_data($id) {
	
	global $db_content;
	$count_evc = $db_content->count("se_comments", [
		"AND" => [
			"comment_type" => "evc",
			"comment_relation_id" => $id
		]
	]);
	
	$event_data = array('evc' => $count_evc);
	
	return $event_data;
}


/**
 * generate anonymous voter name
 * we use this only if votings are allowed for all
 */
 
function se_generate_anonymous_voter() {
	
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
	
	
	return md5($ip);
}