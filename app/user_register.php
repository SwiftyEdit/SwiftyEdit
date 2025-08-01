<?php

/**
 * prohibit unauthorized access
 */

//error_reporting(E_ALL ^E_NOTICE ^E_DEPRECATED);

if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){ 
	die ('<h2>Direct File Access Prohibited</h2>');
}

if($se_prefs['prefs_userregistration'] != 'yes') {
	die("unauthorized access");
}

$send_data = 'true';

// all incoming data -> strip_tags
// limit/trim string to 200 characters
foreach($_POST as $key => $val) {
	$$key = strip_tags(substr($val, 0, 200)); 
}

if($accept_terms == '') {
	$send_data = 'false';
	$register_message = $lang['msg_register_accept'].'<br>';
}

//required fields
if( ($username == "") || ($psw == "") || ($mail == "")  ){
	$send_data = "false";
	$register_message .= $lang['msg_register_requiredfields'].'<br>';
}

//mail and mailrepeat
if($mail != $mailrepeat) {
	$send_data = 'false';
	$register_message .= $lang['msg_register_mailrepeat_error'].'<br>';
}

if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
	$send_data = 'false';
	$register_message .= $lang['msg_invalid_mail_format'].'<br>';
}

//psw and psw_repeat
if($psw != $psw_repeat) {
	$send_data = 'false';
	$register_message .= $lang['msg_register_pswrepeat_error'].'<br>';
}


//no special chars are allowed
if(!preg_match("/^[a-zA-Z0-9-_]{2,20}$/",$username)) {
	$send_data = "false";
	$register_message .= $lang['msg_register_userchars'].'<br>';
}


//check existing usernames
$all_usernames_array = get_all_usernames();

foreach ($all_usernames_array as $entry) {
    if($username == $entry['user_nick']) {
    	$send_data = "false";
			$register_message .= $lang['msg_register_existinguser'].'<br>';
			break;
    }
}


//check existing E-Mail Adresses
$all_usermail_array = get_all_usermail("$se_db_user");

foreach ($all_usermail_array as $entry) {    
    if($mail == $entry['user_mail']) {
    	$send_data = "false";
			$register_message .= $lang['msg_register_existingusermail'].'<br>';
			break;
    }  
}

//yeah, create the new account
if($send_data == 'true') {

	$user_groups = "1";
	$user_registerdate = time();
	$user_verified = 'waiting';
    $user_verified_by_admin = 'no';
	$drm_string = '';
	$psw_string = md5("$psw$username");
	$user_psw_hash = password_hash($psw, PASSWORD_DEFAULT);
	$user_activationkey = random_text('alnum',32);
	$activation_url = $se_base_url."account/?user=$username&al=$user_activationkey";
	$user_activationlink = '<a href="'.$activation_url.'">'.$activation_url.'</a>';

	$db_user->insert("se_user", [
		"user_nick" => "$username",
		"user_registerdate" => "$user_registerdate",
		"user_verified" => "$user_verified",
        "user_verified_by_admin" => "$user_verified_by_admin",
		"user_groups" => "$user_groups",
		"user_drm" => "$drm_string",
		"user_firstname" => "$firstname",
		"user_lastname" => "$name",
		"user_company" => "$user_company",
		"user_street" => "$street",
		"user_street_nbr" => "$nr",
		"user_zip" => "$zip",
		"user_city" => "$city",
		"user_public_profile" => "$about_you",
		"user_mail" => "$mail",
		"user_psw_hash" => "$user_psw_hash",
		"user_activationkey" => "$user_activationkey",
        "ba_company" => "$ba_company",
        "ba_firstname" => "$ba_firstname",
        "ba_lastname" => "$ba_lastname",
        "ba_street" => "$ba_street",
        "ba_street_nbr" => "$ba_street_nbr",
        "ba_zip" => "$ba_zip",
        "ba_city" => "$ba_city",
        "ba_country" => "$ba_country",
        "ba_tax_number" => "$ba_tax_number",
        "ba_tax_id_number" => "$ba_tax_id_number",
        "ba_sales_tax_id_number" => "$ba_sales_tax_id_number",
        "sa_company" => "$sa_company",
        "sa_firstname" => "$sa_firstname",
        "sa_lastname" => "$sa_lastname",
        "sa_street" => "$sa_street",
        "sa_street_nbr" => "$sa_street_nbr",
        "sa_zip" => "$sa_zip",
        "sa_city" => "$sa_city",
        "sa_country" => "$sa_country"
	]);	
	
	/* generate the message */
	$email_msg = se_get_textlib("account_confirm_mail","$languagePack",'content');
	$email_msg = str_replace("{USERNAME}","$username",$email_msg);
	$email_msg = str_replace("{SITENAME}","$prefs_pagetitle",$email_msg);
	$email_msg = str_replace("{ACTIVATIONLINK}","$user_activationlink",$email_msg);

	$recipient = array('name' => $username, 'mail' => $mail);

    /* build mail content 'subject','preheader','title','salutation','body','footer','tpl' */
    $mail_data['tpl'] = 'mail.tpl';
    $mail_data['subject'] = "Account | ".$se_prefs['prefs_pagetitle'];
    $mail_data['preheader'] = "Welcome | $username $mail";
    $mail_data['title'] = "Welcome | $username $mail";
    $mail_data['salutation'] = "Welcome $username";
    $mail_data['body'] = "$email_msg";

    $build_html_mail = se_build_html_file($mail_data);

	$send_mail_to_user = se_send_mail($recipient,$mail_data['subject'],$build_html_mail);
	
	$smarty->assign("msg_status","alert alert-success",true);
	$smarty->assign("register_message",$lang['msg_register_success'],true);
	
	record_log("user_register","new user $username","6");
	
	/* send notification to admin */
    $admin_mail['name'] = $se_prefs['prefs_mailer_name'];
    $admin_mail['mail'] = $se_prefs['prefs_mailer_adr'];

	$admin_notification_text  = $lang['msg_register_admin_notification_text'].'<hr>';
	$admin_notification_text .= 'Username: <b>'.$username.'</b><br>';
	$admin_notification_text .= 'E-Mail: '.$mail.'<br>';
	$admin_notification_text .= 'Server: '.$prefs_pagetitle.'<br>';
	$copymail = se_send_mail($admin_mail,$lang['msg_register_admin_notification_subject'],$admin_notification_text);

} else {
	//oh no, don't create an new account
	
	$smarty->assign("msg_status","alert alert-danger",true);
	$smarty->assign("register_message",'<p><strong>'.$lang['msg_register_error'].'</strong></p><p>'.$register_message.'</p>',true);
	
	//show the entries again
	$smarty->assign("send_username",$username,true);
	$smarty->assign("send_mail",$mail,true);
	$smarty->assign("send_mailrepeat",$mailrepeat,true);
	$smarty->assign("send_firstname",$firstname,true);
	$smarty->assign("send_name",$name,true);
	$smarty->assign("send_zip",$zip,true);
	$smarty->assign("send_city",$city,true);
	$smarty->assign("send_street",$street,true);
	$smarty->assign("send_nr",$nr,true);
	$smarty->assign("about_you",$about_you,true);
}