<?php
session_start();
error_reporting(0);

const SE_SECTION = 'frontend';
require SE_ROOT.'/vendor/autoload.php';
include_once SE_ROOT.'/config.php';
include_once SE_ROOT.'/database.php';
include_once SE_ROOT.'/app/functions/functions.posts.php';

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