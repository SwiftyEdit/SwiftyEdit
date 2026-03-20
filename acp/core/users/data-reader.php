<?php

/**
 * global variables
 * @var object $db_user
 * @var array $icon
 * @var array $lang
 */

if($_REQUEST['action'] == "list_users") {

    // defaults
    $order_by = 'user_id';
    $order_direction = 'ASC';
    $limit_start = $_SESSION['pagination_users_page'] ?? 0;
    $nbr_show_items = 10;

    $match_str = $_SESSION['users_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_users'] ?? $order_by;
    $order_direction = $_SESSION['sorting_users_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $conditions = [
        "user_id[>]" => 0
    ];


    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "user_nick[~]" => "%$f%",
                    "user_firstname[~]" => "%$f%",
                    "user_lastname[~]" => "%$f%",
                    "user_mail[~]" => "%$f%",
                    "user_company[~]" => "%$f%"
                ]
            ];
        }
    }

    // String-Filter
    if (!empty($filter_by_str)) {
        $conditions["OR #search"] = $filter_by_str["OR"];
    }

    // Status-Filter
    $filter_by_status = [];
    if (isset($_SESSION['set_status_filter'])) {
        if ($_SESSION['set_status_filter'] == 2) {
            $conditions["user_verified"] = "verified";
        }
        if ($_SESSION['set_status_filter'] == 3) {
            $conditions["OR #unverified"] = [
                "user_verified[!]" => "verified",
                "user_verified #empty" => "",
                "user_verified #empty2" => null
            ];
            $conditions["OR #class"] = [
                "user_class[!]" => "deleted",
                "user_class" => null
            ];
        }
        if ($_SESSION['set_status_filter'] == 4) {
            $conditions["user_verified"] = "paused";
        }
        if ($_SESSION['set_status_filter'] == 5) {
            $conditions["user_class"] = "deleted";
        }
    }

    $db_where = ["AND" => $conditions];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $users_data_cnt = $db_user->count("se_user", $db_where);

    $users_data = $db_user->select("se_user","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($users_data_cnt/$nbr_show_items);

    echo '<div class="card p-3">';
    echo se_print_pagination('/admin-xhr/users/write/',$nbr_pages,$_SESSION['pagination_users_page']);

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

    foreach($users_data as $user) {

        $user_avatar = '<img src="/themes/administration/images/avatar.png" class="rounded-circle avatar" width="75" height="75" alt="no avatar image">';
        $user_avatar_path = '/assets/avatars/' . md5($user['user_nick']) . '.png';
        if(is_file("../public$user_avatar_path")) {
            $user_avatar = '<img src="'.$user_avatar_path.'" class="rounded-circle avatar" width="75" height="75">';
        } else {
            $user_avatar = '<img src="'.se_identicon_data_url($user['user_mail']).'" class="rounded-circle avatar" width="75" height="75">';
        }

        //marking admins
        $admin_img = '';
        if($user['user_class'] == "administrator"){
            $admin_img = '<span class="position-absolute bottom-0 start-100 translate-middle-x badge rounded bg-primary border" title="Administrator">'.$icon['star'].'</span>';
        }

        //status label
        $labelMap = [
            'waiting' => 'text-info',
            'paused' => 'text-warning',
            'verified' => 'text-success',
            '' => 'text-danger',
        ];
        $label = $labelMap[$user['user_verified']] ?? '';

        $names = sanitizeUserInputs($user['user_firstname']). ' '.sanitizeUserInputs($user['user_lastname']);

        echo '<tr>';
        echo '<td>'.$user['user_id'].'</td>';
        echo '<td><span class="position-relative d-inline-block">'.$user_avatar.$admin_img.'</span></td>';
        echo '<td><span class="'.$label.' fs-6">'.$icon['circle_fill'].'</span> '.$user['user_nick'].'</td>';
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
    echo '</div>';

}

if($_REQUEST['action'] == "list_user_status") {

    $vals = ['csrf_token' => $_SESSION['token']];
    $writer_uri = '/admin-xhr/users/write/';

    if(!isset($_SESSION['set_status_filter'])) {
        $_SESSION['set_status_filter'] = 1;
    }

    $status_btns = [
        '1' => '<span class="text-secondary">'.$icon['users'] .'</span> '. $lang['btn_all'],
        '2' => '<span class="text-success">'.$icon['person_check'] .'</span> '. $lang['status_user_verified'],
        '3' => '<span class="text-info">'.$icon['clock'] .'</span> '. $lang['status_user_waiting'],
        '4' => '<span class="text-warning">'.$icon['person_x'] .'</span> '. $lang['status_user_paused'],
        '5' => '<span class="text-danger">'.$icon['trash'] .'</span> '. $lang['status_deleted']
    ];

    foreach($status_btns as $k => $v) {

        $class = ($k == $_SESSION['set_status_filter']) ? 'active' : '';

        echo '<button type="button" class="list-group-item list-group-item-action '.$class.'"
                hx-post="'.$writer_uri.'"
                hx-trigger="click" 
                hx-swap="none"
                hx-vals=\''.json_encode($vals).'\'
                name="set_status_filter"
                value="'.$k.'"
                >'.$v.'</button>';
    }
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

        echo '<button hx-post="/admin-xhr/users/read/" hx-trigger="click" hx-swap="innerHTML" hx-target="#groupForm" class="list-group-item list-group-item-action d-flex" name="open_user_group" value="'.$group['group_id'].'">';
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
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin-xhr/users/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
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