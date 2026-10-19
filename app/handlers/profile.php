<?php
/**
 * Profile Management Handler
 * SwiftyEdit CMS
 */

// Check if user is logged in
if($_SESSION['user_nick'] == "") {
    $text = se_get_snippet("no_access",$languagePack,'all');
    $smarty->assign('page_content', $text, true);
    return;
}

$get_my_userdata = get_my_userdata();

// Delete the account
if(isset($_POST['delete_my_account'])) {
    // see app/functions/functions.user_delete.php
    $delete_id = (int) $_SESSION['user_id'];
    $delete_blocker = se_user_delete_blocker($delete_id, 'self');

    if($delete_blocker !== '') {
        $smarty->assign("msg_status","alert alert-warning",true);
        $smarty->assign("register_message",$lang[$delete_blocker] ?? $lang['msg_delete_account_error'],true);
    } else if(se_delete_user($delete_id)) {
        $smarty->assign("msg_status","alert alert-success",true);
        $smarty->assign("register_message",$lang['msg_delete_account_success'],true);
        session_destroy();
        unset($_SESSION['user_nick']);
    } else {
        $smarty->assign("msg_status","alert alert-danger",true);
        $smarty->assign("register_message",$lang['msg_delete_account_error'],true);
    }

}

// Set form URL
if($page_contents['page_permalink'] != '') {
    $smarty->assign("form_url", '/'.$page_contents['page_permalink']);
} else {
    $form_url = SE_INCLUDE_PATH . "/profile/";
    $smarty->assign('form_url', $form_url);
}

// Assign all user data to template
foreach ($get_my_userdata as $key => $value) {
    $smarty->assign($key, $value ?? '', true);
}

// Assign all language variables to template
foreach($lang as $key => $val) {
    $smarty->assign("lang_$key", $val);
}

// Render profile template
$output = $smarty->fetch("profile_main.tpl",$cache_id);
$smarty->assign('page_content', $output, true);