<?php

/**
 * @var object $smarty
 * @var array $lang
 * @var object $db_user
 * @var object $db_content
 * @var string $se_base_url
 */

$get_my_userdata = get_my_userdata();

// Assign all language variables to template
foreach($lang as $key => $val) {
    $smarty->assign("lang_$key", $val);
}

// Assign all user data to template
foreach ($get_my_userdata as $key => $value) {
    $smarty->assign($key, $value ?? '', true);
}

// Handle billing and delivery address countries
$get_delivery_countries = $db_content->select("se_delivery_areas", ["name"],[
    "status" => 1
]);

foreach($get_delivery_countries as $countries) {
    $predefined_delivery_countries[] = $countries['name'];
}

if(is_array($predefined_delivery_countries) && count($predefined_delivery_countries) > 0) {
    // Show select dropdown
    $smarty->assign("show_ba_country_input","select");
    $smarty->assign("ba_countries",$predefined_delivery_countries);
    $smarty->assign("show_sa_country_input","select");
    $smarty->assign("sa_countries",$predefined_delivery_countries);

    if($get_my_userdata['ba_country'] != '') {
        $selected_ba_country = 'selected_ba_'.strtolower($get_my_userdata['ba_country']);
        $smarty->assign("$selected_ba_country","selected");
    }
    if($get_my_userdata['sa_country'] != '') {
        $selected_sa_country = 'selected_sa_'.strtolower($get_my_userdata['sa_country']);
        $smarty->assign("$selected_sa_country","selected");
    }
} else {
    // Show input type text
    $smarty->assign("show_ba_country_input","input");
    $smarty->assign("show_sa_country_input","input");
}

// display avatar form
if(isset($_GET['avatar'])) {
    $smarty->display('profile/avatar-form.tpl');
}

// display password change form
if(isset($_GET['password'])) {
    $smarty->display('profile/change-password.tpl');
}

// display mail change form
if(isset($_GET['mail'])) {
    $smarty->assign("get_user_mail", $get_my_userdata['user_mail']);
    $smarty->display('profile/change-mail.tpl');
}

// display address form
if(isset($_GET['address'])) {
    $smarty->display('profile/address.tpl');
}

// display billing address form
if(isset($_GET['address-ba'])) {
    $smarty->display('profile/address-ba.tpl');
}

// display shipping address form
if(isset($_GET['address-sa'])) {
    $smarty->display('profile/address-sa.tpl');
}

// change password
if(isset($_POST['change_password'])) {

    $user_psw_hash = $get_my_userdata['user_psw_hash'];
    $new_user_psw_hash = '';

    if (isset($_POST['s_psw']) && trim($_POST['s_psw']) !== '') {

        $password = $_POST['s_psw'];
        $passwordRepeat = $_POST['s_psw_repeat'];
        if ($password == $passwordRepeat) {
            $new_user_psw_hash = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if($new_user_psw_hash !== '') {
        $update_psw = $db_user->update("se_user", [
            "user_psw_hash" => $new_user_psw_hash
        ], [
            "user_id" => (int) $_SESSION['user_id']
        ]);

        if($update_psw->rowCount() == 1){
            $smarty->assign("alert_text",$lang['msg_update_profile']);
            $smarty->display('alert/alert-success.tpl');
            header("HX-Trigger: changed_password");
        }

    } else {
        $smarty->assign("alert_text",$lang['msg_update_profile_error']);
        $smarty->display('alert/alert-danger.tpl');
    }
}

// change mail
if(isset($_POST['change_mail'])) {
    $new_user_mail = '';

    // check if new mail is set and matching repeat mail
    if(isset($_POST['set_mail']) AND trim($_POST['set_mail']) !== '') {
        if($_POST['set_mail'] === $_POST['set_mail_repeat']) {
            $new_user_mail = sanitizeUserInputs($_POST['set_mail']);
        }
    }

    // check if mail exists
    $all_registered_mails = get_all_usermail();
    foreach ($all_registered_mails as $user_mails) {
        if($new_user_mail == $user_mails['user_mail']) {
            // The address is already registered - do nothing
            $new_user_mail = '';
        }
    }

    // write in user_mail_temp and user_activationkey
    if($new_user_mail != '') {
        $user_activationkey = bin2hex(random_bytes(16));
        $update_mail = $db_user->update("se_user", [
            "user_mail_temp" => "$new_user_mail",
            "user_activationkey" => "$user_activationkey"
        ], [
            "user_id" => (int) $_SESSION['user_id']
        ]);

        if($update_mail->rowCount() == 1) {

            // send mail to the user
            $unlock_link = $se_base_url."account/?change_mail=$user_activationkey";
            $email_msg = str_replace("{USERNAME}",$get_my_userdata['user_nick'],$lang['msg_update_profile_mail_tpl']);
            $email_msg = str_replace("{EMAIL}","$new_user_mail",$email_msg);
            $email_msg = str_replace("{RESET_LINK}","$unlock_link",$email_msg);

            $mail_data = [
                'tpl' => 'mail.tpl',
                'subject' => 'E-Mail Reset / '.$se_base_url,
                'preheader' => 'E-Mail Reset / '.$se_base_url,
                'title' => 'E-Mail Reset / '.$se_base_url,
                'salutation' => "E-Mail Reset | ".$get_my_userdata['user_nick'],
                'body' => "$email_msg"
            ];

            $build_html_mail = se_build_html_file($mail_data);

            $recipient = array('name' => $get_my_userdata['user_nick'], 'mail' => $new_user_mail);
            $send_reset_mail = se_send_mail($recipient,$mail_data['subject'],$build_html_mail);


            $smarty->assign("alert_text",$lang['msg_update_profile_mail']);
            $smarty->display('alert/alert-success.tpl');
            header("HX-Trigger: changed_mail_temp");
        }

    } else {
        $smarty->assign("alert_text",$lang['msg_update_profile_mail_error']);
        $smarty->display('alert/alert-danger.tpl');
    }
}

// update address
if(isset($_POST['update_address'])) {

    foreach ($_POST as $key => $val) {
        $$key = sanitizeUserInputs($val);
    }

    $update_address_data = $db_user->update("se_user", [
        "user_firstname" => "$user_firstname",
        "user_lastname" => "$user_lastname",
        "user_street" => "$user_street",
        "user_street_nbr" => "$user_street_nbr",
        "user_zip" => "$user_zip",
        "user_city" => "$user_city",
        "user_public_profile" => "$user_public_profile"
    ], [
        "user_id" => (int) $_SESSION['user_id']
    ]);

    if($update_address_data->rowCount() == 1){
        $smarty->assign("alert_text",$lang['msg_update_profile']);
        $smarty->display('alert/alert-success.tpl');
    } else {
        $smarty->assign("alert_text",$lang['msg_update_profile_error']);
        $smarty->display('alert/alert-danger.tpl');
    }
}

// update billing address
if(isset($_POST['update_address_ba'])) {
    foreach($_POST as $key => $val) {
        $$key = sanitizeUserInputs($val);
    }

    $update_ba_data = $db_user->update("se_user", [
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
        "ba_sales_tax_id_number" => "$ba_sales_tax_id_number"
    ], [
        "user_id" => (int) $_SESSION['user_id']
    ]);

    if($update_ba_data->rowCount() == 1){
        $smarty->assign("alert_text",$lang['msg_update_profile']);
        $smarty->display('alert/alert-success.tpl');
    } else {
        $smarty->assign("alert_text",$lang['msg_update_profile_error']);
        $smarty->display('alert/alert-danger.tpl');
    }
}

// update shipping address
if(isset($_POST['update_address_sa'])) {
    foreach($_POST as $key => $val) {
        $$key = sanitizeUserInputs($val);
    }

    $update_sa_data = $db_user->update("se_user", [
        "sa_company" => "$sa_company",
        "sa_firstname" => "$sa_firstname",
        "sa_lastname" => "$sa_lastname",
        "sa_street" => "$sa_street",
        "sa_street_nbr" => "$sa_street_nbr",
        "sa_zip" => "$sa_zip",
        "sa_city" => "$sa_city",
        "sa_country" => "$sa_country"
    ], [
        "user_id" => (int) $_SESSION['user_id']
    ]);

    if($update_sa_data->rowCount() == 1){
        $smarty->assign("alert_text",$lang['msg_update_profile']);
        $smarty->display('alert/alert-success.tpl');
    } else {
        $smarty->assign("alert_text",$lang['msg_update_profile_error']);
        $smarty->display('alert/alert-danger.tpl');
    }
}

if(isset($_POST['delete_avatar'])) {
    $avatar_img = md5($_SESSION['user_nick']) . '.png';
    if(is_file(__DIR__.'/../../public/assets/avatars/'.$avatar_img)) {
        unlink(__DIR__.'/../../public/assets/avatars/'.$avatar_img);
    }
    header("HX-Trigger: changed_avatar");
    exit;
}

// show avatar
if(isset($_GET['show_avatar'])) {
    $avatar_img = md5($_SESSION['user_nick']) . '.png';

    if(is_file(__DIR__.'/../../public/assets/avatars/'.$avatar_img)) {
        $avatar_src = '/avatars/' . md5($_SESSION['user_nick']) . '.png';
    } else {
        $avatar_src = '/themes/default/images/avatar.jpg';
    }

    $avatar_src .= '?v=' . time();

    $smarty->assign("avatar_src",$avatar_src);
    $smarty->display('profile/avatar.tpl');
    exit;
}

// upload avatar
if(isset($_POST['upload_avatar'])) {
    $upload_avatar = se_upload_avatar($_FILES,$_SESSION['user_nick']);
    if($upload_avatar === true) {
        $smarty->assign("alert_text",$lang['msg_upload_avatar_success']);
        $smarty->display('alert/alert-success.tpl');
        header("HX-Trigger: changed_avatar");
        exit;
    } else {
        $smarty->assign("alert_text",$lang['msg_upload_avatar_filetype']);
        $smarty->display('alert/alert-danger.tpl');
    }
}