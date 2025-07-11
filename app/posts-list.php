<?php

/**
 * global variables
 * @var $db_content see database.php
 * @var array $page_contents
 * @var array $se_prefs
 * @var array $lang
 * @var string $mod_slug
 */

// get the posting-page by 'type_of_use' and $languagePack
$target_page = $db_content->select("se_pages", "page_permalink", [
	"AND" => [
		"page_type_of_use" => "display_post",
		"page_language" => $page_contents['page_language']
	]
]);

if(!isset($target_page[0]) OR $target_page[0] == '') {
	$target_page[0] = $swifty_slug;
}


$sql_start = ($posts_start*$posts_limit)-$posts_limit;
if($sql_start < 0) {
	$sql_start = 0;
}

$get_posts = se_get_post_entries($sql_start,$posts_limit,$posts_filter);
$cnt_filter_posts = $get_posts[0]['cnt_posts'];
$cnt_get_posts = count($get_posts);

$show_posts_list = true;
if($get_posts[0]['cnt_posts'] < 1) {
    // we have no products to show
    $show_posts_list = false;
}


$nextPage = $posts_start+$posts_limit;
$prevPage = $posts_start-$posts_limit;
$cnt_pages = ceil($cnt_filter_posts / $posts_limit);
$pagination = array();

if($cnt_pages > 1) {
    $show_pagination = true;
	
	for($i=0;$i<$cnt_pages;$i++) {
		
		$active_class = '';
		$set_start = $i+1;
		
		if($i == 0 && $posts_start < 1) {
			$set_start = 1;
			$active_class = 'active';
            $current_page = 1;
		}

		if($set_start == $posts_start) {
			$active_class = 'active';
			$current_page = $set_start;
		}
		
		$pagination_link = se_set_pagination_query($display_mode,$set_start);
        $pagination[] = array(
            "href" => $pagination_link,
            "nbr" => $set_start,
            "active_class" => $active_class
        );
		
	}
	
	$pag_start = $current_page-4;

    if ($pag_start < 0) {
        $pag_start = 0;
    }
    $pagination = array_slice($pagination, $pag_start, 5);
	
	$nextstart = $posts_start+1;
	$prevstart = $posts_start-1;
	
	$older_link_query = se_set_pagination_query($display_mode,$nextstart);
	$newer_link_query = se_set_pagination_query($display_mode,$prevstart);
	
	if($prevstart < 1) {
		$prevstart = 1;
		$newer_link_query = '#';
	}
	
	if($nextstart > $cnt_pages) {
		$older_link_query = '#';
	}

    $smarty->assign('pag_prev_href', $newer_link_query);
    $smarty->assign('pag_next_href', $older_link_query);
} else {
    $show_pagination = false;
}


$show_start = $sql_start+1;
$show_end = $show_start+($posts_limit-1);

if($show_end > $cnt_filter_posts) {
	$show_end = $cnt_filter_posts;
}

//eol pagination


foreach($get_posts as $k => $post) {
		
	$post_releasedate = date($se_prefs['prefs_dateformat'],$get_posts[$k]['post_releasedate']);
	$post_releasedate_year = date('Y',$get_posts[$k]['post_releasedate']);
	$post_releasedate_month = date('m',$get_posts[$k]['post_releasedate']);
	$post_releasedate_day = date('d',$get_posts[$k]['post_releasedate']);
	$post_releasedate_time = date($se_prefs['prefs_timeformat'],$get_posts[$k]['post_releasedate']);

    $get_posts[$k]['btn_open_post'] = $lang['btn_read_more'];
    $form_action = '/'.$swifty_slug.$mod_slug;

	
	/* entry date */
	$entrydate_year = date('Y',$get_posts[$k]['post_date']);
	
	/* post images */
	$first_post_image = '';
	$post_images = explode("<->", $get_posts[$k]['post_images']);
	if(isset($post_images[1])) {
        $get_posts[$k]['post_tmb_src'] = $post_images[1];
	} else if($se_prefs['prefs_posts_default_banner'] == "without_image") {
        $get_posts[$k]['post_tmb_src'] = '';
	} else {
        $get_posts[$k]['post_tmb_src'] = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
	}
	

	if($get_posts[$k]['post_type'] == 'g') {

		$gallery_dir = 'assets/galleries/'.$entrydate_year.'/gallery'.$get_posts[$k]['post_id'].'/';
		$fp = $gallery_dir.'*_tmb.jpg';
		$thumbs_array = glob("$fp");
		arsort($thumbs_array);
		$cnt_thumbs_array = count($thumbs_array);
		if($cnt_thumbs_array > 0) {

			$x = 0;
            $gallery_thumbs = array();
			foreach($thumbs_array as $tmb) {
				$x++;
				$tmb_src = '/'.$tmb;
				$img_src = str_replace('_tmb','_img',$tmb_src);

                $gallery_thumbs[] = array(
                    "tmb_src" => $tmb_src,
                    "img_src" => $img_src
                );
				
				if($x == 5) {
					break;
				}
				
			}
		}
        $get_posts[$k]['post_thumbnails'] = $gallery_thumbs;
        $btn_show_gallery = str_replace("{cnt_images}", $cnt_thumbs_array, $lang['btn_show_gallery']);
        $get_posts[$k]['btn_open_post'] = $btn_show_gallery;
	}

	if($get_posts[$k]['post_type'] == 'v') {
		$vURL = parse_url($get_posts[$k]['post_video_url']);
		parse_str($vURL['query'],$video); //$video['v'] -> youtube video id
        $get_posts[$k]['video_id'] = $video['v'];
	}


	$post_filename = basename($get_posts[$k]['post_slug']);
    $get_posts[$k]['post_href'] = SE_INCLUDE_PATH . "/".$target_page[0]."$post_filename-".$get_posts[$k]['post_id'].".html";

    $get_posts[$k]['post_teaser'] = htmlspecialchars_decode($get_posts[$k]['post_teaser']);
    $get_posts[$k]['post_text'] = htmlspecialchars_decode($get_posts[$k]['post_text']);

    /* categories */
    $post_categories = explode('<->', $get_posts[$k]['post_categories']);
    $category = array();
    foreach ($all_categories as $cats) {
        if (in_array($cats['cat_hash'], $post_categories)) {
            $cat_href = '/' . $swifty_slug . $cats['cat_name_clean'] . '/';
            $category[] = array(
                "cat_href" => $cat_href,
                "cat_title" => $cats['cat_name']
            );
        }
    }
    $get_posts[$k]['post_categories'] = $category;
	

	/* vote up or down this post */
	if($get_posts[$k]['post_votings'] == 2 || $get_posts[$k]['post_votings'] == 3) {
        $get_posts[$k]['show_voting'] = true;
		$voter_data = false;
		$voting_type = array("upv","dnv");
		if($get_posts[$k]['post_votings'] == 2) {
			if($_SESSION['user_nick'] == '') {
				$voter_data = false;
			} else {
				$voter_data = se_check_user_legitimacy($get_posts[$k]['post_id'],$_SESSION['user_nick'],$voting_type);
			}
		}
		
		if($get_posts[$k]['post_votings'] == 3) {
			if(!isset($_SESSION['user_nick'])) {
				$voter_name = se_generate_anonymous_voter();
				$voter_data = se_check_user_legitimacy($get_posts[$k]['post_id'],$voter_name,$voting_type);	
			} else {
				$voter_data = se_check_user_legitimacy($get_posts[$k]['post_id'],$_SESSION['user_nick'],$voting_type);
			}
		}
				
		if($voter_data == true) {
            // user can vote
            $get_posts[$k]['votes_status_up'] = '';
            $get_posts[$k]['votes_status_dn'] = '';
        } else {
            $get_posts[$k]['votes_status_up'] = 'disabled';
            $get_posts[$k]['votes_status_dn'] = 'disabled';
        }
		
		$votes = se_get_voting_data('post',$get_posts[$k]['post_id']);
        $get_posts[$k]['votes_up'] = (int) $votes['upv'];
        $get_posts[$k]['votes_dn'] = (int) $votes['dnv'];
		
	} else {
        $get_posts[$k]['show_voting'] = false;
	}
	

    $get_posts[$k]['post_releasedate_str'] = date($se_prefs['prefs_dateformat'], $get_posts[$k]['post_releasedate']);

	/* links */
	$redirect = $swifty_slug.'?goto='.$get_posts[$k]['post_id'];
    $get_posts[$k]['post_external_link'] = $get_posts[$k]['post_link'];
    $get_posts[$k]['post_external_redirect'] = $redirect;


    if ($get_posts[$k]['post_status'] == '2') {
        $get_posts[$k]['draft_message'] = '<div class="alert alert-draft"><small>' . $lang['post_is_draft'] . '</small></div>';
        $get_posts[$k]['post_css_classes'] = 'draft';
    }


	
}


$smarty->assign('lang_entries', $lang['label_entries']);
$smarty->assign('lang_entries_total', $lang['label_entries_total']);
$smarty->assign('post_cnt', $cnt_filter_posts);
$smarty->assign('post_start_nbr', $show_start);
$smarty->assign('post_end_nbr', $show_end);

$smarty->assign('posts', $get_posts);

$form_action = '/' . $swifty_slug . $mod_slug;
$smarty->assign('form_action', $form_action);
$smarty->assign('btn_download', $lang['btn_download']);

$smarty->assign('show_posts_list', $show_posts_list);
$smarty->assign('show_pagination', $show_pagination);
$smarty->assign('pagination', $pagination);



$posts_page = $smarty->fetch("posts-list.tpl", $cache_id);
$smarty->assign('page_content', $posts_page, true);

$smarty->assign('categories', $categories);