<?php
session_start();
error_reporting(0);

define('SE_SECTION', 'frontend');
require '../core/vendor/autoload.php';
include_once '../config.php';
include_once '../database.php';
include_once '../core/functions/functions.posts.php';

$time = time();

if($_POST['val']) {
	
	/* check who want to signn */

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


	/* get the new number of confirmations */
	$nbr_of_confirmations = se_get_event_confirmation_data($event_relation_id);
	echo json_encode($nbr_of_confirmations);
}
?>