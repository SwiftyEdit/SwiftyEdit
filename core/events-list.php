<?php

/**
 * global variables
 * @var $db_content set in database.php
 * @var $se_prefs set in index.php
 *
 * @var $page_contents
 */

// get the posting-page by 'type_of_use' and $languagePack
$target_page = $db_content->select("se_pages", "page_permalink", [
    "AND" => [
        "page_type_of_use" => "display_event",
        "page_language" => $page_contents['page_language']
    ]
]);

if ($target_page[0] == '') {
    $target_page[0] = $swifty_slug;
}


$sql_start = ($events_start * $events_limit) - $events_limit;
if ($sql_start < 0) {
    $sql_start = 0;
}

$get_events = se_get_event_entries($sql_start, $events_limit, $events_filter);
$cnt_filter_events = $get_events[0]['cnt_events'];
$cnt_get_events = count($get_events);

$show_events_list = true;
if($get_events[0]['cnt_events'] < 1) {
    // we have no products to show
    $show_events_list = false;
}

$nextPage = $events_start + $events_limit;
$prevPage = $events_start - $events_limit;
$cnt_pages = ceil($cnt_filter_events / $events_limit);

if ($cnt_pages > 1) {
    $show_pagination = true;
    $pagination = array();

    for ($i = 0; $i < $cnt_pages; $i++) {

        $active_class = '';
        $set_start = $i + 1;

        if ($i == 0 && $events_start < 1) {
            $set_start = 1;
            $active_class = 'active';
        }

        if ($set_start == $events_start) {
            $active_class = 'active';
            $current_page = $set_start;
        }

        $pagination_link = se_set_pagination_query($display_mode, $set_start);

        $pagination[] = array(
            "href" => $pagination_link,
            "nbr" => $set_start,
            "active_class" => $active_class
        );
    }

    $pag_start = $current_page - 4;

    if ($pag_start < 0) {
        $pag_start = 0;
    }
    $pagination = array_slice($pagination, $pag_start, 5);

    $nextstart = $events_start + 1;
    $prevstart = $events_start - 1;

    $older_link_query = se_set_pagination_query($display_mode, $nextstart);
    $newer_link_query = se_set_pagination_query($display_mode, $prevstart);

    if ($prevstart < 1) {
        $prevstart = 1;
        $newer_link_query = '#';
    }

    if ($nextstart > $cnt_pages) {
        $older_link_query = '#';
    }

    $smarty->assign('pag_prev_href', $newer_link_query);
    $smarty->assign('pag_next_href', $older_link_query);

} else {
    $show_pagination = false;
}


$show_start = $sql_start + 1;
$show_end = $show_start + ($events_limit - 1);

if ($show_end > $cnt_filter_events) {
    $show_end = $cnt_filter_events;
}
//eol pagination



$posts_list = '';
foreach ($get_events as $k => $post) {

    /* build data for template */

    $get_events[$k]['event_title'] = $get_events[$k]['title'];
    $get_events[$k]['event_teaser'] = htmlspecialchars_decode($get_events[$k]['teaser']);
    $get_events[$k]['event_text'] = htmlspecialchars_decode($get_events[$k]['text']);
    $get_events[$k]['event_id'] = $get_events[$k]['id'];

    /* post images */
    $first_post_image = '';
    $post_images = explode("<->", $get_events[$k]['images']);
    if ($post_images[1] != "") {
        $get_events[$k]['event_img_src'] = '/' . $img_path . '/' . str_replace('../content/images/', '', $post_images[1]);
    } else if ($se_prefs['prefs_posts_default_banner'] == "without_image") {
        $get_events[$k]['event_img_src'] = '';
    } else {
        $get_events[$k]['event_img_src'] = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
    }

    $post_filename = basename($get_events[$k]['slug']);
    $get_events[$k]['event_href'] = SE_INCLUDE_PATH . "/" . $target_page[0] . "$post_filename-" . $get_events[$k]['id'] . ".html";


    $post_releasedate = date($prefs_dateformat, $get_events[$k]['releasedate']);
    $post_releasedate_year = date('Y', $get_events[$k]['releasedate']);
    $post_releasedate_month = date('m', $get_events[$k]['releasedate']);
    $post_releasedate_day = date('d', $get_events[$k]['releasedate']);
    $post_releasedate_time = date($prefs_timeformat, $get_events[$k]['releasedate']);

    $get_events[$k]['event_releasedate'] = $post_releasedate;

    /* event dates */

    $get_events[$k]['event_start_day'] = date('d',$get_events[$k]['event_startdate']);
    $get_events[$k]['event_start_month'] = date('m',$get_events[$k]['event_startdate']);
    $get_events[$k]['event_start_month_text'] = $lang["m".$get_events[$k]['event_start_month']];
    $get_events[$k]['event_start_year'] = date('Y',$get_events[$k]['event_startdate']);
    $get_events[$k]['event_end_day'] = date('d',$get_events[$k]['event_enddate']);
    $get_events[$k]['event_end_month'] = date('m',$get_events[$k]['event_enddate']);
    $get_events[$k]['event_end_year'] = date('Y',$get_events[$k]['event_enddate']);

    /* entry date */
    $entrydate_year = date('Y', $get_events[$k]['date']);


    /* event categories */
    $post_categories = explode('<->', $get_events[$k]['categories']);
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
    $get_events[$k]['event_categories'] = $category;

    /* vote up or down this product */
    if ($get_events[$k]['post_votings'] == 2 || $get_events[$k]['votings'] == 3) {
        $get_events[$k]['show_voting'] = true;
        $voter_data = false;
        $voting_type = array("upv", "dnv");
        if ($get_events[$k]['votings'] == 2) {
            if ($_SESSION['user_nick'] == '') {
                $voter_data = false;
            } else {
                $voter_data = se_check_user_legitimacy($get_events[$k]['id'], $_SESSION['user_nick'], $voting_type);
            }
        }

        if ($get_events[$k]['votings'] == 3) {
            if ($_SESSION['user_nick'] == '') {
                $voter_name = se_generate_anonymous_voter();
                $voter_data = se_check_user_legitimacy($get_events[$k]['id'], $voter_name, $voting_type);
            } else {
                $voter_data = se_check_user_legitimacy($get_events[$k]['id'], $_SESSION['user_nick'], $voting_type);
            }
        }

        if ($voter_data == true) {
            // user can vote
            $get_events[$k]['votes_status_up'] = '';
            $get_events[$k]['votes_status_dn'] = '';
        } else {
            $get_events[$k]['votes_status_up'] = 'disabled';
            $get_events[$k]['votes_status_dn'] = 'disabled';
        }


        $votes = se_get_voting_data('post', $get_events[$k]['id']);

        $get_events[$k]['votes_up'] = (int) $votes['upv'];
        $get_events[$k]['votes_dn'] = (int) $votes['dnv'];

    } else {
        $get_events[$k]['show_voting'] = false;
    }



    if ($get_events[$k]['status'] == '2') {
        $get_events[$k]['draft_message'] = '<div class="alert alert-draft"><small>' . $lang['post_is_draft'] . '</small></div>';
        $get_events[$k]['product_css_classes'] = 'draft';
    }

}



if ($display_mode == 'list_posts_category') {
    $category_message = str_replace('{categorie}', $selected_category_title, $lang['posts_category_filter']);
    $page_content = str_replace("{category_filter}", $category_message, $page_content);
} else {
    $page_content = str_replace("{category_filter}", '', $page_content);
}

$form_action = '/' . $swifty_slug . $mod_slug;
$smarty->assign('form_action', $form_action);
$smarty->assign('events_cnt', $cnt_filter_products);
$smarty->assign('events', $get_events);

$smarty->assign('show_events_list', $show_events_list);
$smarty->assign('show_pagination', $show_pagination);
$smarty->assign('pagination', $pagination);

$smarty->assign('btn_read_more', $lang['btn_open_product']);

$events_page = $smarty->fetch("events-list.tpl", $cache_id);
$smarty->assign('page_content', $events_page, true);

$smarty->assign('categories', $categories);