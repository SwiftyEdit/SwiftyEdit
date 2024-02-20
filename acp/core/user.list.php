<?php
//error_reporting(E_ALL ^E_NOTICE ^E_WARNING);

/**
 * SwiftyEdit backend
 *
 * global variables
 * @var object $db_user medoo database object
 * @var array $icon icons set in acp/core/icons.php
 * @var array $lang language
 * @var string $hidden_csrf_token
 */

//prohibit unauthorized access
require __DIR__.'/access.php';


// sorting
// set sort direction ASC or DESC
$_SESSION['sorting_user_dir'] = isset($_POST['sorting_user_dir']) && $_POST['sorting_user_dir'] == 'desc' ? 'DESC' : 'ASC';

if(!isset($_SESSION['sorting_user_dir'])) {
    $_SESSION['sorting_user_dir'] = 'DESC';
}

if($_SESSION['sorting_user_dir'] == 'ASC') {
    $sel_sort_value['sort_asc'] = 'active';
} else {
    $sel_sort_value['sort_desc'] = 'active';
}


if(isset($_POST['sorting_user'])) {
    if($_POST['sorting_user'] == 'username') {
        $_SESSION['sorting_user'] = 'user_nick';
    } else if($_POST['sorting_user'] == 'registerdate') {
        $_SESSION['sorting_user'] = 'user_registerdate';
    } else if($_POST['sorting_user'] == 'email') {
        $_SESSION['sorting_user'] = 'user_mail';
    } else {
        $_SESSION['sorting_user'] = 'lastname';
    }
}

if(!isset($_SESSION['sorting_user'])) {
    $_SESSION['sorting_user'] = 'user_registerdate';
}

if($_SESSION['sorting_user'] == 'user_nick') {
    $sel_sort_value['username'] = 'selected';
} else if ($_SESSION['sorting_user'] == 'user_registerdate') {
    $sel_sort_value['registerdate'] = 'selected';
} else if ($_SESSION['sorting_user'] == 'user_mail') {
    $sel_sort_value['email'] = 'selected';
} else {
    $sel_sort_value['realname'] = 'selected';
}

// switch user status

$user_status = array();
$user_status_vba = array();

if($_SESSION['checked_verified'] == '' AND $_SESSION['checked_waiting'] == '' AND $_SESSION['checked_paused'] == '' AND $_SESSION['set_user_status'] == false) {
	$_SESSION['checked_verified'] = 'checked';
}

if(isset($_POST['set_status_verified'])) {
    $_SESSION['checked_verified'] = ($_SESSION['checked_verified'] == 'checked') ? '' : 'checked';
}

if(isset($_POST['set_status_verified_by_admin'])) {
    $_SESSION['checked_verified_by_admin'] = ($_SESSION['checked_verified_by_admin'] == 'checked') ? '' : 'checked';
}

if(isset($_POST['set_status_waiting'])) {
    $_SESSION['checked_waiting'] = ($_SESSION['checked_waiting'] == 'checked') ? '' : 'checked';
}

if(isset($_POST['set_status_paused'])) {
    $_SESSION['checked_paused'] = ($_SESSION['checked_paused'] == 'checked') ? '' : 'checked';
}

if(isset($_POST['set_status_deleted'])) {
    $_SESSION['checked_deleted'] = ($_SESSION['checked_deleted'] == 'checked') ? '' : 'checked';
}


if($_SESSION['checked_waiting'] == "checked") {
	$btn_status_waiting = 'active';
    $user_status[] = 'waiting';
}

if($_SESSION['checked_paused'] == "checked") {
	$btn_status_paused = 'active';
    $user_status[] = 'paused';
}

if($_SESSION['checked_verified'] == "checked") {
	$btn_status_verified = 'active';
    $user_status[] = 'verified';
}


if($_SESSION['checked_verified_by_admin'] == "checked") {
    $btn_status_verified_by_admin = 'active';
    $user_status_vba[] = 'yes';
} else {
    $user_status_vba[] = 'no';
    $user_status_vba[] = 'null';
    $user_status_vba[] = '';
    $user_status_vba[] = null;
}

if($_SESSION['checked_deleted'] == "checked") {
	$btn_status_deleted = 'active';
    $user_status[] = '';
}

if(isset($_POST['findUser'])) {
    if($_POST['findUser'] != '') {
        $_SESSION['user_match'] = sanitizeUserInputs($_POST['findUser']);
    } else {
        $_SESSION['user_match'] = '';
    }
}

if(!isset($_SESSION['user_match'])) {
    $_SESSION['user_match'] = '';
}

// number to show
$items_per_page = 15;
$sql_start = 0;

if(isset($_GET['start'])) {
    $sql_start = (int) $_GET['start'];
}

$cnt_all_users = $db_user->count("se_user", [
	"user_id[>]" => 0
]);

$cnt_filter_users = $db_user->count("se_user", [
    "user_id[>]" => 0,
    "user_nick[~]" => $_SESSION['user_match'],
    "user_verified" => $user_status,
    "user_verified_by_admin" => $user_status_vba,
]);


$get_users = $db_user->select("se_user","*",[
	"user_id[>]" => 0,
    "user_nick[~]" => $_SESSION['user_match'],
    "user_verified" => $user_status,
    "user_verified_by_admin" => $user_status_vba,
	"LIMIT" => [$sql_start, $items_per_page],
    "ORDER" => [$_SESSION['sorting_user'] => $_SESSION['sorting_user_dir']]
]);

$cnt_get_users = count($get_users);

$pagination_query = '?tn=user&sub=list&start={page}';
$pagination = se_return_pagination($pagination_query,$cnt_filter_users,$sql_start,$items_per_page,10,3,2);

echo '<div class="subHeader d-flex">';
echo '<div class="me-auto"><h3>Userlist ('.$cnt_filter_users.' / '.$cnt_all_users.')</h3></div>';
echo '<div><a href="?tn=user&sub=new" class="btn btn-success align-self-end">'.$lang['new_user'].'</a></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';
//print the list

echo '<div class="card p-3">';

echo $pagination;

echo '<table class="table table-hover table-striped table-sm">';
echo '<thead>';
echo '<tr>';
echo '<th>#</th>';
echo '<th> </th>';
echo '<th>'.$lang['h_username'].'</th>';
echo '<th>'.$lang['h_registerdate'].'</th>';
echo '<th>'.$lang['h_realname'].'</th>';
echo '<th>'.$lang['h_email'].'</th>';
echo '<th>'.$lang['h_action'].'</th>';
echo '</tr>';
echo '</thead>';

for($i=0;$i<$cnt_get_users;$i++) {

	$user_id = (int) $get_users[$i]['user_id'];
	$user_nick = sanitizeUserInputs($get_users[$i]['user_nick']);
	$user_avatar_path = '../content/avatars/' . md5($user_nick) . '.png';
	$user_class = $get_users[$i]['user_class'];
	$user_mail = sanitizeUserInputs($get_users[$i]['user_mail']);
	$user_registerdate = (int) $get_users[$i]['user_registerdate'];
	$user_firstname = sanitizeUserInputs($get_users[$i]['user_firstname']);
	$user_lastname = sanitizeUserInputs($get_users[$i]['user_lastname']);
	$user_verified = sanitizeUserInputs($get_users[$i]['user_verified']);

	$show_registerdate = '';
	if($user_registerdate != '') {
        $show_registerdate = se_format_datetime($user_registerdate);
	}

	$user_avatar = '<img src="/acp/images/avatar.png" class="rounded-circle avatar" width="50" height="50" alt="no avatar image">';
	if(is_file("$user_avatar_path")) {
		$user_avatar = '<img src="'.$user_avatar_path.'" class="rounded-circle avatar" width="50" height="50">';
	}

	//show me in bold
	unset($td_class);
	if($user_nick == $_SESSION['user_nick']){
		$td_class = "bold";
	}

	//marking admins
	if($user_class == "administrator"){
		$admin_img = '<span class="text-bg-primary badge rounded-pill">'.$icon['user'].'</span>';
	} else {
		$admin_img = '<span class="text-bg-info badge rounded-pill">'.$icon['user'].'</span>';
	}

	//deleted user
	if($user_class == "deleted"){
		$user_nick = "<del>$user_nick</del>";
	}

	//status label
    $labelMap = [
        'waiting' => 'badge rounded-pill bg-info',
        'paused' => 'badge badge-pill bg-warning',
        'verified' => 'badge rounded-pill bg-success',
        '' => 'badge rounded-pill bg-danger',
    ];
    $label = $labelMap[$user_verified] ?? '';

	$btn_edit_user  = '<form action="?tn=user&sub=edit" method="POST">';
	$btn_edit_user .= '<button class="btn btn-sm btn-default w-100" name="edituser" value="'.$user_id.'">'.$icon['edit'].' '.$lang['edit'].'</button>';
	$btn_edit_user .= $hidden_csrf_token;
	$btn_edit_user .= '</form>';
	
	echo '<tr>';
	echo '<td class="'.$td_class.'" style="text-align:right;">'.$user_id.'</td>';
	echo '<td>'.$user_avatar.'</td>';
	echo '<td class="'.$td_class.'">'.$admin_img.' <span class="'.$label.'">'.$user_nick.'</span></td>';
	echo '<td class="'.$td_class.'">'.$show_registerdate.'</td>';
	echo '<td class="'.$td_class.'">'.$user_firstname.' '.$user_lastname.'</td>';
	echo '<td class="'.$td_class.'">'.$user_mail.'</td>';
	echo '<td class="'.$td_class.'">'.$btn_edit_user.'</td>';
	echo '</tr>';

}

echo '</table>';

echo $pagination;

echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

/* sidebar */
echo '<div class="card p-2">';

echo '<form action="?tn=user" class="form-inline" method="POST">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input type="text" name="findUser" class="form-control" placeholder="Filter" value="'.$_SESSION['user_match'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<hr>';


echo '<div class="card">';
echo '<div class="card-header">'.$icon['filter'].' Filter</div>';
echo '<div class="card-body">';
echo '<form action="?tn=user" method="POST">';
echo '<div class="btn-group d-flex">';
echo '<button type="submit" title="E-Mail is verfified" name="set_status_verified" class="btn btn-default w-100 '.$btn_status_verified.'">'.$icon['check'].'</button>';
echo '<button type="submit" title="User is verified by an admin" name="set_status_verified_by_admin" class="btn btn-default w-100 '.$btn_status_verified_by_admin.'">'.$icon['patch_check'].'</button>';
echo '<button type="submit" title="User is not verified" name="set_status_waiting" class="btn btn-default w-100 '.$btn_status_waiting.'">'.$icon['clock'].'</button>';
echo '<button type="submit" title="The user has been temporarily blocked by an admin" name="set_status_paused" class="btn btn-default w-100 '.$btn_status_paused.'">'.$icon['lock'].'</button>';
echo '<button type="submit" title="The user has been deleted or the user name has been blocked" name="set_status_deleted" class="btn btn-default w-100 '.$btn_status_deleted.'">'.$icon['trash'].'</button>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';


echo '<div class="mt-3">';
echo '<label class="form-label">'.$lang['h_page_sort'].'</label>';
echo '<form action="?tn=user&sub=user-list" method="post" class="dirtyignore">';

echo '<div class="row g-1">';
echo '<div class="col-md-8">';

echo '<select class="form-control form-select-sm" name="sorting_user" onchange="this.form.submit()">';
echo '<option value="username" '.$sel_sort_value['username'].'>'.$lang['h_username'].'</option>';
echo '<option value="registerdate" '.$sel_sort_value['registerdate'].'>'.$lang['h_registerdate'].'</option>';
echo '<option value="realname" '.$sel_sort_value['realname'].'>'.$lang['h_realname'].'</option>';
echo '<option value="email" '.$sel_sort_value['email'].'>'.$lang['h_email'].'</option>';
echo '</select>';

echo '</div>';
echo '<div class="col-md-4">';

echo '<div class="btn-group d-flex">';
echo '<button name="sorting_user_dir" value="asc" title="'.$lang['btn_sort_asc'].'" class="btn btn-sm btn-default w-100 '.$sel_sort_value['sort_asc'].'">'.$icon['arrow_up'].'</button> ';
echo '<button name="sorting_user_dir" value="desc" title="'.$lang['btn_sort_desc'].'" class="btn btn-sm btn-default w-100 '.$sel_sort_value['sort_desc'].'">'.$icon['arrow_down'].'</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';
echo '</div>';

echo '</div>';

echo '</div>'; /* end of sidebar */

echo '</div>';
echo '</div>';