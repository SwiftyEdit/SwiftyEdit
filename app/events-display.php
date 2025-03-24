<?php

$event_data = se_get_event_data($get_event_id);
$hits = (int) $event_data['hits'];
se_increase_posts_hits($get_event_id);


$event_teaser = text_parser(htmlspecialchars_decode($event_data['teaser']));
$event_text = text_parser(htmlspecialchars_decode($event_data['text']));

$event_images = explode("<->", $event_data['images']);

$event_releasedate = date("$prefs_dateformat $prefs_timeformat",$event_data['releasedate']);
$event_releasedate_year = date('Y',$event_data['releasedate']);
$event_releasedate_month = date('m',$event_data['releasedate']);
$event_releasedate_day = date('d',$event_data['releasedate']);
$event_releasedate_time = date('H:i:s',$event_data['releasedate']);

$event_lastedit = date('Y-m-d H:i',$event_data['lastedit']);
$event_lastedit_from = $event_data['lastedit_from'];

$event_start_day = date('d',$event_data['event_startdate']);
$event_start_month = date('m',$event_data['event_startdate']);
$event_start_month_text = $lang["m".$event_start_month];
$event_start_year = date('Y',$event_data['event_startdate']);
$event_end_day = date('d',$event_data['event_enddate']);
$event_end_month = date('m',$event_data['event_enddate']);
$event_end_year = date('Y',$event_data['event_enddate']);

$smarty->assign('event_start_day', $event_start_day);
$smarty->assign('event_start_month', $event_start_month);
$smarty->assign('event_start_month_text', $event_start_month_text);
$smarty->assign('event_start_year', $event_start_year);
$smarty->assign('event_end_day', $event_end_day);
$smarty->assign('event_end_month', $event_end_month);
$smarty->assign('event_end_year', $event_end_year);

/* entry date */
$entrydate_year = date('Y',$event_data['date']);


/* images */

if($event_images[1] != "") {
    $first_image = '/' . $img_path . '/' . str_replace('../content/images/','',$event_images[1]);
    $event_image_data = se_get_images_data($first_image,'data=array');
} else if($se_prefs['prefs_posts_default_banner'] == "without_image") {
    $first_image = '';
} else {
    $first_image = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
}

/* show guestlist */
$show_guestlist = false;
if($event_data['event_guestlist'] == 2 OR $event_data['event_guestlist'] == 3) {
    $show_guestlist = true;

    if($event_data['event_guestlist'] == 2 AND $_SESSION['user_nick'] == '') {
        /* only registered user can confirm */
        $smarty->assign('disabled', "disabled");
    } else {
        $smarty->assign('disabled', "");
    }

    if($event_data['event_guestlist_limit'] != '') {
        $smarty->assign('label_nbr_total_available', $lang['guestlist_label_nbr_total_available']);
        $smarty->assign('nbr_available_total', $event_data['event_guestlist_limit']);
    } else {
        $smarty->assign('label_nbr_total_available', "");
        $smarty->assign('nbr_available_total', "");
    }

    if($event_data['event_guestlist_public_nbr'] == 2) {
        $cnt_commitments = se_get_event_confirmation_data($event_data['id']);
        $guestlist = str_replace("{label_nbr_commitments}", $lang['guestlist_label_nbr_commitments'], $guestlist);
        $guestlist = str_replace("{nbr_commitments}", $cnt_commitments['evc'], $guestlist);
        $smarty->assign('label_nbr_commitments', $lang['guestlist_label_nbr_commitments']);
        $smarty->assign('nbr_commitments', $cnt_commitments['evc']);
    } else {
        $smarty->assign('label_nbr_commitments', "");
        $smarty->assign('nbr_commitments', "");
    }

    $smarty->assign('sign_guestlist', $lang['btn_guestlist_sign']);
    $smarty->assign('description_guestlist', $lang['guestlist_description']);
}
$smarty->assign('show_guestlist', $show_guestlist);



/* vote up or down this post */
if($event_data['votings'] == 2 || $event_data['votings'] == 3) {
    $show_voting = true;
    $voter_data = false;
    $voting_type = array("upv", "dnv");
    if ($event_data['votings'] == 2) {
        if ($_SESSION['user_nick'] == '') {
            $voter_data = false;
        } else {
            $voter_data = se_check_user_legitimacy($event_data['id'], $_SESSION['user_nick'], $voting_type);
        }
    }

    if ($event_data['votings'] == 3) {
        if ($_SESSION['user_nick'] == '') {
            $voter_name = se_generate_anonymous_voter();
            $voter_data = se_check_user_legitimacy($event_data['id'], $voter_name, $voting_type);
        } else {
            $voter_data = se_check_user_legitimacy($event_data['id'], $_SESSION['user_nick'], $voting_type);
        }
    }

    if ($voter_data == true) {
        // user can vote
        $event_data['votes_status_up'] = '';
        $event_data['votes_status_dn'] = '';
    } else {
        $event_data['votes_status_up'] = 'disabled';
        $event_data['votes_status_dn'] = 'disabled';
    }


    $votes = se_get_voting_data('post', $event_data['id']);

    $event_data['votes_up'] = (int) $votes['upv'];
    $event_data['votes_dn'] = (int) $votes['dnv'];

} else {
    // display no votings
    $show_voting = false;
}


$form_action = '/'.$swifty_slug.$mod_slug;
$this_entry = str_replace("{form_action}", $form_action, $this_entry);


if($event_data['product_textlib_content'] != 'no_snippet') {
    $textlib_content = se_get_textlib($event_data['product_textlib_content'],$languagePack,'all');
    $smarty->assign('product_snippet_text', $textlib_content);
}



$form_action = '/'.$swifty_slug.$mod_slug;


if($event_data['meta_title'] == '') {
    $event_data['meta_title'] = $event_data['title'];
}

if($event_data['meta_description'] == '') {
    $event_data['meta_description'] = substr(strip_tags($event_teaser),0,160);
}


$page_contents['page_thumbnail'] = $se_base_url.$img_path.'/'.basename($first_image);

$smarty->assign('page_title', html_entity_decode($event_data['meta_title']));
$smarty->assign('page_meta_description', html_entity_decode($event_data['meta_description']));
$smarty->assign('page_meta_keywords', html_entity_decode($event_data['tags']));
$smarty->assign('page_thumbnail', $page_contents['page_thumbnail']);


$smarty->assign('votes_status_up', $event_data['votes_status_up']);
$smarty->assign('votes_status_dn', $event_data['votes_status_dn']);
$smarty->assign('votes_up', $event_data['votes_up']);
$smarty->assign('votes_dn', $event_data['votes_dn']);

$smarty->assign('show_voting', $show_voting);
$smarty->assign('event_img_src', $first_image);

$smarty->assign('event_id', $event_data['id']);
$smarty->assign('event_title', $event_data['title']);
$smarty->assign('event_teaser', $event_teaser);
$smarty->assign('event_text', $event_text);

$smarty->assign('event_price_note', html_entity_decode($event_data['event_price_note']));

$smarty->assign('form_action', $form_action);
$smarty->assign('btn_add_to_cart', $lang['btn_add_to_cart']);

$event_page = $smarty->fetch("events-display.tpl", $cache_id);
$smarty->assign('page_content', $event_page, true);