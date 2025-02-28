<?php
//error_reporting(E_ALL);

/**
 * pagination
 */

if(isset($_POST['pagination'])) {
    $_SESSION['pagination_users_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_users_list");
}

// save or update user
if(isset($_POST['save_user'])) {

    $columns = array();
    $set_psw = 'false';

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = htmlentities($val);
        }
    }

    if(is_numeric($_POST['save_user'])) {
        $edit_user_id = (int) $_POST['save_user'];
    }

    $drm_string = "$drm_acp_pages|$drm_acp_files|$drm_acp_user|$drm_acp_system|$drm_acp_editpages|$drm_acp_editownpages|$drm_moderator|$drm_can_publish|$drm_acp_sensitive_files";

    $user_psw_new	= $_POST['user_psw_new'];
    $user_psw_reconfirmation = $_POST['user_psw_reconfirmation'];

    $columns = [
        "user_nick" => $user_nick,
        "user_mail" => $user_mail,
        "user_verified" => $user_verified,
        "user_verified_by_admin" => '',
        "user_drm" => $drm_string,
        "user_class" => $user_class,
        "user_firstname" => $user_firstname,
        "user_lastname" => $user_lastname,
        "user_company" => $user_company,
        "user_street" => $user_street,
        "user_street_nbr" => $user_street_nbr,
        "user_zip" => $user_zip,
        "user_city" => $user_city
    ];


    if($_POST['user_psw_new'] != "") {
        if($_POST['user_psw_new'] == $_POST['user_psw_reconfirmation']) {
            $user_psw = password_hash($_POST['user_psw_new'], PASSWORD_DEFAULT);
            $columns["user_psw_hash"] = $user_psw;
            $set_psw = 'true';
        }
    }

    if((isset($_FILES['avatar'])) && ($_FILES['avatar']['full_path'] != '')) {
        $upload_avatar = se_upload_avatar($_FILES,$user_nick);
        if($upload_avatar !== true) {
            echo '<div class="alert alert-warning">'.$upload_avatar.'</div>';
        }
    }

    if(isset($_POST['user_unlocked_by_admin'])) {
        $columns["user_verified_by_admin"] = 'yes';
    }

    if(isset($_POST['user_groups'])) {
        // ad this user to user group(s)
        print_r($_POST['user_groups']);
        foreach($_POST['user_groups'] as $group) {
            se_add_user_to_group($edit_user_id,$group);
        }
    }

    if(is_numeric($_POST['save_user'])) {

        $edit_user_id = (int) $_POST['save_user'];
        $cnt_changes = $db_user->update("se_user",$columns,[
            "user_id" => $edit_user_id
        ]);

        if($cnt_changes->rowCount() > 0) {
            echo '<div class="alert alert-success">'.$lang['msg_success_db_changed'].'</div>';
            record_log($_SESSION['user_nick'],"updated user <i>$user_nick</i>","5");
        }

        if($_POST['deleteAvatar'] == 'on') {
            $user_avatar_path = SE_PUBLIC."/assets/avatars/" . md5($user_nick) . '.png';
            if(is_file($user_avatar_path)) {
                unlink($user_avatar_path);
            }
        }


    } else {

        $columns["user_registerdate"] = time();
        $cnt_changes = $db_user->insert("se_user",$columns);
        $edituser = $db_user->id();
        if($edituser > 0) {
            echo '<div class="alert alert-success">'.$lang['msg_success_new_record'].'</div>';
            record_log($_SESSION['user_nick'],"new user <i>$user_nick</i>","5");
        }
    }

}

// save or update a group
if(isset($_POST['save_user_group'])) {

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = htmlentities($val);
        }
    }

    $group_user_str = '';
    $group_name = sanitizeUserInputs($_POST['group_name']);
    $group_type = sanitizeUserInputs($_POST['group_type']);

    $group_user = $_POST['incUser'];
    if(is_array($group_user)) {
        sort($group_user);
        $group_user_str = implode(" ", $group_user);
    }

    $columns = [
        "group_type" => $group_type,
        "group_name" => $group_name,
        "group_description" => $group_description,
        "group_user" => $group_user_str
    ];

    if(is_numeric($_POST['save_user_group'])) {

        $group_id = $_POST['save_user_group'];
        $data = $db_user->update("se_groups",$columns,["group_id" => $group_id]);
        if($data->rowCount()) {
            echo '<div class="alert alert-success">'.$lang['msg_success_db_changed'].'</div>';
            record_log($_SESSION['user_nick'],"new user group <i>$group_name</i>","5");
            header( "HX-Trigger: update_groups_list");
        }

    } else {
        $cnt_changes = $db_user->insert("se_groups",$columns);
        $editgroup = $db_user->id();
        if($editgroup > 0) {
            echo '<div class="alert alert-success">'.$lang['msg_success_new_record'].'</div>';
            record_log($_SESSION['user_nick'],"new user group <i>$group_name</i>","5");
            header( "HX-Trigger: update_groups_list");
        }
    }
}

// delete a group by id
if(isset($_POST['delete_user_group'])) {
    $delete_id = (int) $_POST['delete_user_group'];
    $delete_group = $db_user->delete("se_groups",[
        "group_id" => $delete_id
    ]);
    if(($delete_group->rowCount()) > 0) {
        record_log($_SESSION['user_nick'],"deleted usergroup id: $editgroup","10");
        header( "HX-Trigger: update_groups_list");
    }
}

// search user data
if(isset($_POST['users_text_filter'])) {
    $add_text_filter = sanitizeUserInputs($_POST['users_text_filter']);
    if($_SESSION['users_text_filter'] == '') {
        $_SESSION['users_text_filter'] = $add_text_filter;
    } else {
        if(!str_contains($_SESSION['users_text_filter'], $add_text_filter)) {
            $_SESSION['users_text_filter'] .= ' '. $add_text_filter;
        }
    }
    header( "HX-Trigger: update_users_list");
}

/* remove search string from filter list */
if(isset($_POST['rmkey'])) {
    $all_filter = explode(" ", $_SESSION['users_text_filter']);
    $_SESSION['users_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['users_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_users_list");
}

if(isset($_POST['set_status_filter'])) {
    echo "FOOBAR";
    if($_POST['set_status_verified'] == '') {
        if($_SESSION['set_status_verified'] == 'on') {
            $_SESSION['set_status_verified'] = '';
        } else {
            $_SESSION['set_status_verified'] = 'on';
        }
    }
    // set_status_verified
}