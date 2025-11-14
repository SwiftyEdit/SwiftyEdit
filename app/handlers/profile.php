<?php
/**
 * Profile Management Handler
 * SwiftyEdit CMS
 */
error_reporting(E_ALL);

// Check if user is logged in
if($_SESSION['user_nick'] == "") {
    $text = se_get_snippet("no_access",$languagePack,'all');
    $smarty->assign('page_content', $text, true);
    return;
}

$get_my_userdata = get_my_userdata();

// Delete the account
if(isset($_POST['delete_my_account'])) {
    $delete_id = (int) $_SESSION['user_id'];

    $get_columns = $db_user->get('se_user', '*', [
        'user_id' => $delete_id,
        'LIMIT' => 1
    ]);


    if (!empty($get_columns)) {
        // get the columns
        $columns = array_keys($get_columns);

        $updateData = [];
        foreach ($columns as $column) {
            // skip user_nick and user_id
            if($column == 'user_nick' || $column == 'user_id') {
                continue;
            }

            if ($column === 'user_class') {
                $updateData[$column] = 'deleted';
            } else {
                $updateData[$column] = '';
            }
        }

        $count = $db_user->update('se_user', $updateData, [
            'user_id' => $delete_id
        ]);

        if($count->rowCount() == 1) {
            $smarty->assign("msg_status","alert alert-success",true);
            $smarty->assign("register_message",$lang['msg_delete_account_success'],true);
            session_destroy();
            unset($_SESSION['user_nick']);
        } else {
            $smarty->assign("msg_status","alert alert-danger",true);
            $smarty->assign("register_message",$lang['msg_delete_account_error'],true);
        }
    } else {
        $smarty->assign("msg_status","alert alert-warning",true);
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