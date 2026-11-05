<?php
/**
 * User Registration Handler
 * SwiftyEdit CMS
 *
 * @var object $db_content
 * @var array $se_settings
 * @var object $db_user
 * @var array $page_contents
 * @var array $lang
 * @var string $cache_id
 * @var string $languagePack
 * @var string $se_base_url
 * @var object $smarty
 */

// Set form URL
if($page_contents['page_permalink'] != '') {
    $smarty->assign("form_url", '/'.$page_contents['page_permalink']);
} else {
    $form_url = SE_INCLUDE_PATH . "/register/";
    $smarty->assign("form_url","$form_url");
}

// Check if registration is enabled
if($se_settings['userregistration'] != "yes") {
    $smarty->assign("msg_title",$lang['legend_register']);
    $smarty->assign("msg_text",$lang['msg_register_intro_disabled']);
    $output = $smarty->fetch("status_message.tpl",$cache_id);
    $smarty->assign('page_content', $output, true);
    return;
}

// Include agreement text
$agreement_txt = se_get_snippet("agreement_text", $languagePack,'content');
$smarty->assign("agreement_text",$agreement_txt);


// get all countries
$all_countries = se_get_countries_options();

// get predefined delivery countries
$get_delivery_countries = $db_content->select("se_delivery_areas", ["code","name"],[
    "status" => 1
]);

// create an array of predefined delivery countries. F.e. ['DE','AT']
$predefined_delivery_countries = !empty($get_delivery_countries)
    ? array_column($get_delivery_countries, 'code')
    : [];

$delivery_countries_options = array_intersect_key(
    $all_countries,
    array_flip($predefined_delivery_countries)
);

$other_countries_options = array_diff_key(
    $all_countries,
    $delivery_countries_options
);

$smarty->assign('delivery_countries_options', $delivery_countries_options);
$smarty->assign('other_countries_options', $other_countries_options);


// Process registration form if submitted
if(!empty($_POST['send_registerform'])) {

    if($se_settings['userregistration'] != 'yes') {
        die("unauthorized access");
    }

    $send_data = 'true';
    $register_message = '';

    // Process the form fields -> strip_tags and limit to 200 characters.
    // Only this fixed list - looping over all of $_POST let a visitor set any
    // variable here, e.g. $se_base_url, which ends up in the activation mail.
    $register_fields = [
        'username', 'mail', 'mailrepeat', 'psw', 'psw_repeat', 'accept_terms',
        'firstname', 'name', 'user_company', 'street', 'nr', 'zip', 'city', 'about_you',
        'ba_company', 'ba_firstname', 'ba_lastname', 'ba_street', 'ba_street_nbr', 'ba_zip',
        'ba_city', 'ba_country', 'ba_tax_number', 'ba_tax_id_number',
        'sa_company', 'sa_firstname', 'sa_lastname', 'sa_street', 'sa_street_nbr', 'sa_zip',
        'sa_city', 'sa_country'
    ];
    $form = [];
    foreach($register_fields as $field) {
        $value = $_POST[$field] ?? '';
        $form[$field] = is_string($value) ? strip_tags(substr($value, 0, 200)) : '';
    }

    // check required fields from settings
    if($se_settings['required_fields_registration'] != '') {
        $required_fields = (array) json_decode($se_settings['required_fields_registration']);
        foreach($required_fields as $field) {
            if(($_POST[$field] ?? '') == '') {
                $send_data = "false";
                $register_message .= $lang['msg_register_requiredfields'].' ('.$field.')<br>';
            }
        }
    }

    // Validation: Accept terms
    if($form['accept_terms'] == '') {
        $send_data = 'false';
        $register_message .= $lang['msg_register_accept'].'<br>';
    }

    // Validation: Required fields
    if( ($form['username'] == "") || ($form['psw'] == "") || ($form['mail'] == "")  ){
        $send_data = "false";
        $register_message .= $lang['msg_register_requiredfields'].'<br>';
    }

    // Validation: Mail and mail repeat
    if($form['mail'] != $form['mailrepeat']) {
        $send_data = 'false';
        $register_message .= $lang['msg_register_mailrepeat_error'].'<br>';
    }

    // Validation: Email format
    if(!filter_var($form['mail'], FILTER_VALIDATE_EMAIL)) {
        $send_data = 'false';
        $register_message .= $lang['msg_invalid_mail_format'].'<br>';
    }

    // Validation: Password and password repeat
    if($form['psw'] != $form['psw_repeat']) {
        $send_data = 'false';
        $register_message .= $lang['msg_register_pswrepeat_error'].'<br>';
    }

    // Validation: Username format (no special chars)
    if(!preg_match("/^[a-zA-Z0-9-_]{2,20}$/",$form['username'])) {
        $send_data = "false";
        $register_message .= $lang['msg_register_userchars'].'<br>';
    }

    // Check for existing usernames
    if(se_username_exists($form['username']) === true) {
        $send_data = "false";
        $register_message .= $lang['msg_register_existinguser'].'<br>';
    }

    // Check for existing email addresses
    if(se_email_exists($form['mail']) === true) {
        $send_data = "false";
        $register_message .= $lang['msg_register_existingusermail'].'<br>';
    }

    // Create new account if validation passed
    if($send_data == 'true') {

        $user_groups = "1";
        $user_registerdate = time();
        $user_verified = 'waiting';
        $user_verified_by_admin = 'no';
        $drm_string = '';
        $user_psw_hash = password_hash($form['psw'], PASSWORD_DEFAULT);
        $user_activationkey = random_text('alnum',32);
        $activation_url = $se_base_url."account/?user={$form['username']}&al=$user_activationkey";
        $user_activationlink = '<a href="'.$activation_url.'">'.$activation_url.'</a>';

        // Insert new user into database
        $db_user->insert("se_user", [
            "user_uuid" => se_generate_uuid(),
            "user_nick" => $form['username'],
            "user_registerdate" => "$user_registerdate",
            "user_verified" => "$user_verified",
            "user_verified_by_admin" => "$user_verified_by_admin",
            "user_groups" => "$user_groups",
            "user_drm" => "$drm_string",
            "user_firstname" => $form['firstname'],
            "user_lastname" => $form['name'],
            "user_company" => $form['user_company'],
            "user_street" => $form['street'],
            "user_street_nbr" => $form['nr'],
            "user_zip" => $form['zip'],
            "user_city" => $form['city'],
            "user_public_profile" => $form['about_you'],
            "user_mail" => $form['mail'],
            "user_psw_hash" => "$user_psw_hash",
            "user_activationkey" => "$user_activationkey",
            "ba_company" => $form['ba_company'],
            "ba_firstname" => $form['ba_firstname'],
            "ba_lastname" => $form['ba_lastname'],
            "ba_street" => $form['ba_street'],
            "ba_street_nbr" => $form['ba_street_nbr'],
            "ba_zip" => $form['ba_zip'],
            "ba_city" => $form['ba_city'],
            "ba_country" => $form['ba_country'],
            "ba_tax_number" => $form['ba_tax_number'],
            "ba_tax_id_number" => $form['ba_tax_id_number'],
            "sa_company" => $form['sa_company'],
            "sa_firstname" => $form['sa_firstname'],
            "sa_lastname" => $form['sa_lastname'],
            "sa_street" => $form['sa_street'],
            "sa_street_nbr" => $form['sa_street_nbr'],
            "sa_zip" => $form['sa_zip'],
            "sa_city" => $form['sa_city'],
            "sa_country" => $form['sa_country']
        ]);
        $new_user_id = (int) $db_user->id();

        // Generate confirmation email
        $email_msg = se_get_snippet("account_confirm_mail","$languagePack",'content');
        $email_msg = str_replace("{USERNAME}",$form['username'],$email_msg);
        $email_msg = str_replace("{SITENAME}",$se_settings['pagetitle'],$email_msg);
        $email_msg = str_replace("{ACTIVATIONLINK}","$user_activationlink",$email_msg);

        $recipient = array('name' => $form['username'], 'mail' => $form['mail']);

        // Build mail content
        $mail_data = [];
        $mail_data['tpl'] = 'mail.tpl';
        $mail_data['subject'] = "Account | ".$se_settings['pagetitle'];
        $mail_data['preheader'] = "Welcome | {$form['username']} {$form['mail']}";
        $mail_data['title'] = "Welcome | {$form['username']} {$form['mail']}";
        $mail_data['salutation'] = "Welcome {$form['username']}";
        $mail_data['body'] = "$email_msg";

        $build_html_mail = se_build_html_file($mail_data);
        $send_mail_to_user = se_send_mail($recipient,$mail_data['subject'],$build_html_mail);

        // Success message
        $smarty->assign("msg_status","alert alert-success",true);
        $smarty->assign("register_message",$lang['msg_register_success'],true);

        // Log registration
        record_log("user_register","new user id: $new_user_id",6);

        // Send notification to admin
        $admin_mail = [];
        $admin_mail['name'] = $se_settings['mailer_name'];
        $admin_mail['mail'] = $se_settings['mailer_adr'];

        $admin_notification_text  = $lang['msg_register_admin_notification_text'].'<hr>';
        $admin_notification_text .= 'Username: <b>'.$form['username'].'</b><br>';
        $admin_notification_text .= 'E-Mail: '.$form['mail'].'<br>';
        $admin_notification_text .= 'Server: '.$se_settings['pagetitle'].'<br>';
        $copymail = se_send_mail($admin_mail,$lang['msg_register_admin_notification_subject'],$admin_notification_text);

    } else {
        // Validation failed - show error message
        $smarty->assign("msg_status","alert alert-danger",true);
        $smarty->assign("register_message",'<p><strong>'.$lang['msg_register_error'].'</strong></p><p>'.$register_message.'</p>',true);

        // Preserve form entries
        $smarty->assign("send_username",$form['username'],true);
        $smarty->assign("send_mail",$form['mail'],true);
        $smarty->assign("send_mailrepeat",$form['mailrepeat'],true);
        // passwords are never sent back into the form
        $smarty->assign("send_firstname",$form['firstname'],true);
        $smarty->assign("send_name",$form['name'],true);
        $smarty->assign("send_zip",$form['zip'],true);
        $smarty->assign("send_city",$form['city'],true);
        $smarty->assign("send_street",$form['street'],true);
        $smarty->assign("send_nr",$form['nr'],true);
        $smarty->assign("about_you",$form['about_you'],true);
    }
}

// Display registration form
$output = $smarty->fetch("registerform.tpl",$cache_id);
$smarty->assign('page_content', $output, true);