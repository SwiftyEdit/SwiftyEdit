<?php

/**
 * global variables
 * @var string $languagePack
 * @var array $lang
 * @var array $se_prefs
 * @var object $smarty
 */

$start_search = "true";

$s = sanitizeUserInputs($_REQUEST['s']);

if($s != '' && strlen($s) < 3) {
	$start_search = "false";
	$search_msg = $lang['msg_search_undersized'];
}

$msg_no_search_results = se_get_textlib('no_search_results',$languagePack,'content');
if($msg_no_search_results == '') {
    $msg_no_search_results = $lang['msg_search_no_results'];
}

if($s != '' && $start_search == "true") {

	$sr = se_search($s,1,10);
	$cnt_result = count($sr);
	if($cnt_result < 1) {
		$search_msg = $msg_no_search_results;
	} else {
		$search_msg = sprintf($lang['msg_search_results'], $cnt_result);


		for($i=0;$i<$cnt_result;$i++) {

            if($sr[$i]['post_type'] == 'm') {
                // post message
                $sr[$i]['set_type'] = 'post';
                $sr[$i]['title'] = $sr[$i]['post_title'];
                $sr[$i]['description'] = $sr[$i]['post_meta_description'];
                $sr[$i]['set_link'] = $sr[$i]['post_rss_url'];
                $image = explode('<->',$sr[$i]['post_images']);
                if($image[1] != "") {
                    $sr[$i]['thumb'] = $image[1];
                }
            }

            if($sr[$i]['page_permalink'] != '') {
                // page
                $sr[$i]['set_type'] = 'page';
                $sr[$i]['set_link'] = $sr[$i]['page_permalink'];
                $page_image = explode('<->',html_entity_decode($sr[$i]['page_thumbnail']));
                if($page_image[0] != "") {
                    $sr[$i]['thumb'] = $page_image[0];
                }
                $sr[$i]['description'] = $sr[$i]['page_meta_description'];
                $sr[$i]['title'] = $sr[$i]['page_title'];
            }

            if($sr[$i]['type'] == 'p') {
                // product
                $sr[$i]['set_type'] = 'product';
                $sr[$i]['description'] = $sr[$i]['meta_description'];
                $sr[$i]['set_link'] = $sr[$i]['rss_url'];

                $image = explode('<->',$sr[$i]['images']);
                if($image[1] != "") {
                    $sr[$i]['thumb'] = $image[1];
                }
            }

			
		}

	}
}


$page_title = $lang['headline_searchresults'] . ' '.$s;


$smarty->assign('page_title', $page_title, true);
$smarty->assign('arr_results', $sr, true);

$smarty->assign('headline_searchresults', $lang['headline_searchresults'], true);

$smarty->assign('msg_searchresults', $search_msg, true);
$smarty->assign('search_string', $s, true);
$search_tpl = $smarty->fetch("search.tpl");
$output = $smarty->fetch("searchresults.tpl");
$smarty->assign('page_content', "$search_tpl $output", true);