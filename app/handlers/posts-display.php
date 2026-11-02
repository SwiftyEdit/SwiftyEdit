<?php

/**
 * SwiftyEdit frontend - a single post, included by posts.php (display_mode show_post)
 *
 * variables of posts.php
 * @var int $get_post_id
 *
 * global variables
 * @var array $page_contents
 * @var array $se_settings
 * @var array $lang
 * @var string $mod_slug
 * @var string $swifty_slug
 * @var string $se_base_url
 * @var string $cache_id
 * @var object $smarty
 */

$post_data = se_get_post_data($get_post_id);

$hits = (int) $post_data['post_hits'];
se_increase_posts_hits($get_post_id);

$post_teaser = text_parser(htmlspecialchars_decode($post_data['post_teaser']));
$post_text = text_parser(htmlspecialchars_decode($post_data['post_text']));

$post_images = explode("<->", $post_data['post_images']);


// date and time format of the settings, as in the post list
$post_releasedate_str = date($se_settings['dateformat'].' '.$se_settings['timeformat'], (int) $post_data['post_releasedate']);

/* tags */
$tags = se_get_content_tags('post', (int) $get_post_id);
$content_tags = array();
foreach ($tags as $tag) {
    $tag_href = se_get_tagged_page_url($tag['tag_name_clean'], $page_contents['page_language'])
        ?? ('/' . $swifty_slug . $mod_slug . '?tag=' . urlencode($tag['tag_name_clean']));
    $content_tags[] = array(
        "tag_href" => $tag_href,
        "tag_title" => $tag['tag_name']
    );
}
$smarty->assign('content_tags', $content_tags);


/* entry date */
$entrydate_year = date('Y',$post_data['post_date']);


/* images */

// the first image, stored as e.g. /images/example.jpg
$first_post_image = $post_images[1] ?? '';




$gallery_thumbs = array();
if($post_data['post_type'] == 'g') {

	$gallery_dir = 'assets/galleries/'.$entrydate_year.'/gallery'.$post_data['post_id'].'/';
	$fp = $gallery_dir.'*_tmb.jpg';
	$thumbs_array = glob("$fp");
	arsort($thumbs_array);
	if(count($thumbs_array) > 0) {

		foreach($thumbs_array as $tmb) {
			$tmb_src = '/'.$tmb;
            $img_src = str_replace('_tmb','_img',$tmb_src);
            $gallery_thumbs[] = array(
                "tmb_src" => $tmb_src,
                "img_src" => $img_src
            );
		}
	}

} else if($post_data['post_type'] == 'v') {
    $smarty->assign('video_id', se_get_youtube_video_id((string) $post_data['post_video_url']));
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
    $user_nick = (string) ($_SESSION['user_nick'] ?? '');
    if ($post_data['post_votings'] == 2) {
        if ($user_nick == '') {
            $voter_data = false;
        } else {
            $voter_data = se_check_user_legitimacy($post_data['post_id'], $user_nick, $voting_type);
        }
    }

    if ($post_data['post_votings'] == 3) {
        if ($user_nick == '') {
            $voter_name = se_generate_anonymous_voter();
            $voter_data = se_check_user_legitimacy($post_data['post_id'], $voter_name, $voting_type);
        } else {
            $voter_data = se_check_user_legitimacy($post_data['post_id'], $user_nick, $voting_type);
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

$smarty->assign('page_title', html_entity_decode($post_data['post_meta_title']));
$smarty->assign('page_meta_description', html_entity_decode($post_data['post_meta_description']));
$smarty->assign('page_meta_keywords', html_entity_decode($post_data['post_tags']));

// without an image, keep the page/global thumbnail set in template-setup.php -
// the stored path keeps the subfolders of the media library
if($first_post_image != '') {
	$page_contents['page_thumbnail'] = rtrim($se_base_url, '/').'/'.ltrim(str_replace('../', '/', $first_post_image), '/');
	$smarty->assign('page_thumbnail', $page_contents['page_thumbnail']);
}

// canonical link: stored on the post itself (auto-built from its main
// category page + slug at save time, see acp/core/blog/data-writer.php), same
// pattern as product canonical urls.
$smarty->assign('page_canonical_url', $post_data['post_canonical_url'] ?? '');

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