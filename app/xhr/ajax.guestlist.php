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

$guestlist_input = is_string($_POST['val'] ?? null) ? $_POST['val'] : '';

if($guestlist_input !== '') {

	/* check who wants to sign */

	if(is_numeric($_SESSION['user_id'] ?? null)) {
		$sender_id = $_SESSION['user_id'];
		$sender_name = $_SESSION['user_nick'];
	} else {
		// anonymous user
		$sender_id = '';
		$sender_name = se_generate_anonymous_voter();
	}

	$event_data = explode('-',$guestlist_input);

	/* post id */
	$event_relation_id = (int) ($event_data[1] ?? 0);
	$sender_type = 'evc';

	// the guestlist must be switched on for this event (2 = registered users
	// only, 3 = everybody) - the button alone doesn't enforce that.
	// The limit is deliberately only shown, not enforced (see docs 06-00-events).
	$guestlist_mode = $db_posts->get("se_events", "event_guestlist", ["id" => $event_relation_id]);
	if(!se_voting_allowed((int) ($guestlist_mode ?? 1))) {
		http_response_code(403);
		exit();
	}

	// remove IP hashes of anonymous confirmations past the retention period
	se_cleanup_anonymous_participation();

	// signed already? Checked without comment_relation_type - confirmations
	// never stored one (NULL or '' depending on the database), so
	// se_check_user_legitimacy() wouldn't find them on every database
	$already_signed = $db_content->count("se_comments", [
		"comment_type" => $sender_type,
		"comment_relation_id" => $event_relation_id,
		"comment_author" => $sender_name
	]);
	if($already_signed > 0) {
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