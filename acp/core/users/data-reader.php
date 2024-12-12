<?php
//error_reporting(E_ALL);
if($_REQUEST['action'] == "list_users") {

    $all_users = $db_user->select("se_user","*");




    echo '<table class="table table-striped table-hover table-sm">';
    echo '<tr>';
    echo '<td>#</td>';
    echo '<td>Avatar</td>';
    echo '<td>'.$lang['label_username'].'</td>';
    echo '<td>'.$lang['label_date'].'</td>';
    echo '<td>'.$lang['label_name'].'</td>';
    echo '<td>'.$lang['label_mail'].'</td>';
    echo '<td></td>';
    echo '</tr>';

    foreach($all_users as $user) {

        $user_avatar = '<img src="/themes/administration/images/avatar.png" class="rounded-circle avatar" width="50" height="50" alt="no avatar image">';
        $user_avatar_path = '/assets/avatars/' . md5($user['user_nick']) . '.png';
        if(is_file("../public$user_avatar_path")) {
            $user_avatar = '<img src="'.$user_avatar_path.'" class="rounded-circle avatar" width="50" height="50">';
        }

        //marking admins
        if($user['user_class'] == "administrator"){
            $admin_img = '<span class="text-bg-primary badge rounded-pill">'.$icon['user'].'</span>';
        } else {
            $admin_img = '<span class="text-bg-info badge rounded-pill">'.$icon['user'].'</span>';
        }

        //status label
        $labelMap = [
            'waiting' => 'badge rounded-pill bg-info',
            'paused' => 'badge badge-pill bg-warning',
            'verified' => 'badge rounded-pill bg-success',
            '' => 'badge rounded-pill bg-danger',
        ];
        $label = $labelMap[$user['user_verified']] ?? '';

        $names = sanitizeUserInputs($user['user_firstname']). ' '.sanitizeUserInputs($user['user_lastname']);

        echo '<tr>';
        echo '<td>'.$user['user_id'].'</td>';
        echo '<td>'.$user_avatar.'</td>';
        echo '<td>'.$admin_img.' <span class="'.$label.'">'.$user['user_nick'].'</span></td>';
        echo '<td>'.se_format_datetime($user['user_registerdate']).'</td>';
        echo '<td>'.$names.'</td>';
        echo '<td>'.$user['user_mail'].'</td>';
        echo '<td>';
        echo '<form action="/admin/users/edit/" method="post">';
        echo '<button name="user_id" value="'.$user['user_id'].'" class="btn btn-default text-success">'.$icon['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

}

if($_REQUEST['action'] == "list_usergroups") {

    $user_groups = se_get_usergroups();
    $cnt_user_groups = count($user_groups);

    echo '<div class="list-group">';
    foreach($user_groups as $group) {

        $count = 0;
        if($group['group_user'] != '') {
            $group_user = explode(" ", $group['group_user']);
            $count = count($group_user);
        }

        echo '<button hx-post="/admin/users/read/" hx-swap="innerHTML" hx-target="#groupForm" class="list-group-item list-group-item-action d-flex" name="open_user_group" value="'.$group['group_id'].'">';
        echo $group['group_name'];
        echo '<span class="badge text-bg-secondary ms-auto">'.$count.'</span>';
        echo '</button>';
    }
    echo '</div>';
}

if($_REQUEST['action'] == "list_active_searches") {
    if(isset($_SESSION['users_text_filter']) AND $_SESSION['users_text_filter'] != "") {
        unset($all_filter);
        $all_filter = explode(" ", $_SESSION['users_text_filter']);

        foreach($all_filter as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin/users/write/" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if(isset($btn_remove_keyword)) {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
}

/**
 * show the form
 */

if($_REQUEST['action'] == 'show_groups_form') {
    $show_form = true;
}

if(isset($_REQUEST['open_user_group'])) {

    $get_group_id = (int) $_REQUEST['open_user_group'];
    $get_group = $db_user->get("se_groups","*",[
        "group_id" => "$get_group_id"
    ]);

    $show_form = true;
}

if($show_form) {
    include 'user-groups-form.php';
}