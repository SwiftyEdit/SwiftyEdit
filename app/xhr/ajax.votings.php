<?php

/**
 * ajax voting
 * called via route.php, which already includes bootstrap.php
 * ($db_content, session, csrf validation, functions.posts.php)
 */

// Read-only w.r.t. $_SESSION (only read below, never assigned) - safe to
// release the session lock immediately. This fires alongside other
// hx-trigger="load" widgets on the same page, all sharing one PHPSESSID;
// without an early close here, the default file session handler would
// force them to queue up and run one at a time instead of concurrently.
// Do not add $_SESSION writes below without removing this first.
session_write_close();

$time = time();

if($_POST['vote']) {
	
	/* check who is voting */

	if($_SESSION['user_id'] != '') {
		$voter_id = $_SESSION['user_id'];
		$voter_name = $_SESSION['user_nick'];
	} else {
		// anonymous voter
		$voter_id = '';
		$voter_name = se_generate_anonymous_voter();		
	}

	$voting_data = explode('-',$_POST['vote']);
	
	/* post id */
	$vote_relation_id = (int) $voting_data[2];
	$type = array("upv","dnv");

    // section blog (b), events (e), shop/product (s) or page (p)
    $section = '';
    if($voting_data[1] == 'post') {
        $section = 'b';
    } else if($voting_data[1] == 'event') {
        $section = 'e';
    } else if($voting_data[1] == 'product') {
        $section = 's';
    } else if($voting_data[1] == 'page') {
        $section = 'p';
    }

    $check_voter = se_check_user_legitimacy($vote_relation_id,$voter_name,$type,$section);
	
	if($check_voter == false) {
		exit();
	}
	
	if($voting_data[0] == 'dn') {
		$vote_type = 'dnv'; // down vote
	} else {
		$vote_type = 'upv'; // up vote
	}
	
	$db_content->insert("se_comments", [
		"comment_relation_id" => $vote_relation_id,
        "comment_relation_type" => $section,
		"comment_type" => $vote_type,
		"comment_time" => $time,
		"comment_author" => $voter_name,
		"comment_author_id" => $voter_id
	]);

    header( "HX-Trigger: update_votings_$vote_relation_id");
}