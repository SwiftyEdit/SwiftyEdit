<?php

$post_data = se_get_post_data($get_post_id);

$hits = (int) $post_data['hits'];
se_increase_posts_hits($get_product_id);

$post_teaser = text_parser(htmlspecialchars_decode($post_data['post_teaser']));
$post_text = text_parser(htmlspecialchars_decode($post_data['post_text']));

$post_images = explode("<->", $post_data['post_images']);


$post_releasedate_str = date("$prefs_dateformat $prefs_timeformat",$post_data['post_releasedate']);
$post_releasedate_year = date('Y',$post_data['post_releasedate']);
$post_releasedate_month = date('m',$post_data['post_releasedate']);
$post_releasedate_day = date('d',$post_data['post_releasedate']);
$post_releasedate_time = date('H:i:s',$post_data['post_releasedate']);

$post_lastedit = date('Y-m-d H:i',$post_data['lastedit']);
$post_lastedit_from = $post_data['post_lastedit_from'];

/* categories */
$cat_links_array = explode('<->',$post_data['post_categories']);

foreach($all_categories as $cats) {

	if(in_array($cats['cat_id'],$cat_links_array)) {
		$post_cats_string .= $cats['cat_name'] .' ';
		$cat_href = '/'.$swifty_slug.$cats['cat_name_clean'].'/';
		$link = str_replace('{cat_href}', $cat_href, $link);
		$link = str_replace('{cat_name}', $cats['cat_name'], $link);
		$post_cats_btn .= $link;
		
	}
}


/* entry date */
$entrydate_year = date('Y',$post_data['post_date']);


/* images */

if($post_images[1] != "") {
    $first_post_image = $post_images[1];
	$post_image_data = se_get_images_data($first_post_image,'data=array');
} else if($se_prefs['prefs_posts_default_banner'] == "without_image") {
	$first_post_image = '';
} else {
	$first_post_image = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
}




if($post_data['post_type'] == 'g') {

	$gallery_dir = 'assets/galleries/'.$entrydate_year.'/gallery'.$post_data['post_id'].'/';
	$fp = $gallery_dir.'*_tmb.jpg';
	$thumbs_array = glob("$fp");
	arsort($thumbs_array);
	$cnt_thumbs_array = count($thumbs_array);
    $gallery_thumbs = array();
	if($cnt_thumbs_array > 0) {

		$x = 0;
		foreach($thumbs_array as $tmb) {
			$x++;
			$tmb_src = '/'.$tmb;
            $img_src = str_replace('_tmb','_img',$tmb_src);
            $gallery_thumbs[] = array(
                "tmb_src" => $tmb_src,
                "img_src" => $img_src
            );
		}
	}

} else if($post_data['post_type'] == 'v') {
	$vURL = parse_url($post_data['post_video_url']);
	parse_str($vURL['query'],$video); //$video['v'] -> youtube video id
    $smarty->assign('video_id', $video['v']);
}

$show_comments = false;
if ($post_data['post_comments'] == 1) {
    $show_comments = true;
}


/* vote up or down this post */
if($post_data['post_votings'] == 2 || $post_data['post_votings'] == 3) {
    $show_voting = true;
    $voter_data = false;
    $voting_type = array("upv", "dnv");
    if ($post_data['post_votings'] == 2) {
        if ($_SESSION['user_nick'] == '') {
            $voter_data = false;
        } else {
            $voter_data = se_check_user_legitimacy($post_data['post_id'], $_SESSION['user_nick'], $voting_type);
        }
    }

    if ($post_data['post_votings'] == 3) {
        if ($_SESSION['user_nick'] == '') {
            $voter_name = se_generate_anonymous_voter();
            $voter_data = se_check_user_legitimacy($post_data['post_id'], $voter_name, $voting_type);
        } else {
            $voter_data = se_check_user_legitimacy($post_data['post_id'], $_SESSION['user_nick'], $voting_type);
        }
    }

    if ($voter_data == true) {
        // user can vote
        $post_data['votes_status_up'] = '';
        $post_data['votes_status_dn'] = '';
    } else {
        $post_data['votes_status_up'] = 'disabled';
        $post_data['votes_status_dn'] = 'disabled';
    }


    $votes = se_get_voting_data('post', $post_data['post_id']);

    $post_data['votes_up'] = (int) $votes['upv'];
    $post_data['votes_dn'] = (int) $votes['dnv'];

} else {
    // display no votings
    $show_voting = false;
}


/* file */
$post_file_attachment = str_replace('../','/',$post_data['post_file_attachment']);

$form_action = '/'.$swifty_slug.$mod_slug;


$redirect = $swifty_slug.'?goto='.$post_data['post_id'];
$smarty->assign('post_external_link', $post_data['post_link']);
$smarty->assign('post_external_redirect', $redirect);
$smarty->assign('post_link_text', $post_data['post_link_text']);


if($post_data['post_meta_title'] == '') {
	$post_data['post_meta_title'] = $post_data['post_title'];
}

if($post_data['post_meta_description'] == '') {
	$post_data['post_meta_description'] = substr(strip_tags($post_teaser),0,160);
}

$page_contents['page_thumbnail'] = $se_base_url.$img_path.'/'.basename($first_post_image);


$smarty->assign('page_title', html_entity_decode($post_data['post_meta_title']));
$smarty->assign('page_meta_description', html_entity_decode($post_data['post_meta_description']));
$smarty->assign('page_meta_keywords', html_entity_decode($post_data['post_tags']));
$smarty->assign('page_thumbnail', $page_contents['page_thumbnail']);

$smarty->assign('post_id', $get_post_id);
$smarty->assign('post_type', $post_data['post_type']);

$smarty->assign('post_title', $post_data['post_title']);
$smarty->assign('post_teaser', $post_teaser);
$smarty->assign('post_text', $post_text);

$smarty->assign('post_author', $post_data['post_author']);
$smarty->assign('post_releasedate_str', $post_releasedate_str);

$smarty->assign('votes_status_up', $post_data['votes_status_up']);
$smarty->assign('votes_status_dn', $post_data['votes_status_dn']);
$smarty->assign('votes_up', $post_data['votes_up']);
$smarty->assign('votes_dn', $post_data['votes_dn']);
$smarty->assign('show_voting', $show_voting);
$smarty->assign('show_comments', $show_comments);

$smarty->assign('post_tmb_src', $first_post_image);
$smarty->assign('gallery_thumbs', $gallery_thumbs);

$smarty->assign('form_action', $form_action);
$smarty->assign('btn_download', $lang['btn_download']);

$smarty->assign('post_file_version', $post_data['post_file_version']);
$smarty->assign('post_file_license', $post_data['post_file_license']);
$smarty->assign('post_file_attachment', $post_file_attachment);
$smarty->assign('post_file_attachment_external', $post_data['post_file_attachment_external']);

$posts_page = $smarty->fetch("posts-display.tpl", $cache_id);
$smarty->assign('page_content', $posts_page, true);