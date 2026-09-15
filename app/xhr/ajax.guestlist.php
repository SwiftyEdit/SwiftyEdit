<?php

/**
 * ajax guestlist confirmation
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

if(isset($_GET['evc'])) {
	$event_relation_id = (int) $_GET['evc'];
	$nbr_of_confirmations = se_get_event_confirmation_data($event_relation_id);
	echo (int) $nbr_of_confirmations['evc'];
	exit;
}

$time = time();

if(!empty($_POST['val'])) {

	/* check who wants to sign */

	if($_SESSION['user_id'] != '') {
		$sender_id = $_SESSION['user_id'];
		$sender_name = $_SESSION['user_nick'];
	} else {
		// anonymous user
		$sender_id = '';
		$sender_name = se_generate_anonymous_voter();
	}

	$event_data = explode('-',$_POST['val']);

	/* post id */
	$event_relation_id = (int) $event_data[1];
	$sender_type = 'evc';
	$type = array("evc");
	$check_sender = se_check_user_legitimacy($event_relation_id,$sender_name,$type);
	if($check_sender == false) {
		exit();
	}

	$db_content->insert("se_comments", [
		"comment_relation_id" => $event_relation_id,
		"comment_type" => $sender_type,
		"comment_time" => $time,
		"comment_author" => $sender_name,
		"comment_author_id" => $sender_id
	]);

	header( "HX-Trigger: update_guestlist_$event_relation_id");
}