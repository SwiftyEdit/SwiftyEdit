<?php
error_reporting(E_ALL ^E_NOTICE ^E_WARNING);
//prohibit unauthorized access
require 'core/access.php';

$sort = (int) $_GET['sort'];

// sort by reference
switch ($_GET['sort']) {
case "1":
    $order_by = "user_nick";
    break;
case "2":
    $order_by = "user_registerdate";
    break;
case "3":
    $order_by = "user_lastname";
    break;
case "4":
    $order_by = "user_mail";
    break;
case "5":
    $order_by = "user_verified";
    break;
default:
	$order_by = "user_id";
}

/* sort up or down */

if($_GET['way'] == "up"){
	$way = "ASC";
	$set_way = "down";
} else {
	$way = "DESC";
	$set_way = "up";
}

/* switch user status */

if(isset($_GET['switch'])) {
	$_SESSION['set_user_status'] = true;
}

if($_SESSION['checked_verified'] == '' AND $_SESSION['checked_waiting'] == '' AND $_SESSION['checked_paused'] == '' AND $_SESSION['set_user_status'] == false) {
	$_SESSION['checked_verified'] = 'checked';
}


if($_GET['switch'] == 'statusWaiting' AND $_SESSION['checked_waiting'] == '') {
	$_SESSION['checked_waiting'] = "checked";
} elseif($_GET['switch'] == 'statusWaiting' AND $_SESSION['checked_waiting'] == 'checked') {
	$_SESSION['checked_waiting'] = "";
}

if($_GET['switch'] == 'statusPaused' && $_SESSION['checked_paused'] == 'checked') {
	$_SESSION['checked_paused'] = "";
} elseif($_GET['switch'] == 'statusPaused' && $_SESSION['checked_paused'] == '') {
	$_SESSION['checked_paused'] = "checked";
}

if($_GET['switch'] == 'statusVerified' && $_SESSION['checked_verified'] == 'checked') {
	$_SESSION['checked_verified'] = "";
} elseif($_GET['switch'] == 'statusVerified' && $_SESSION['checked_verified'] == '') {
	$_SESSION['checked_verified'] = "checked";
}

if($_GET['switch'] == 'statusDeleted' && $_SESSION['checked_deleted'] == 'checked') {
	$_SESSION['checked_deleted'] = "";
} elseif($_GET['switch'] == 'statusDeleted' && $_SESSION['checked_deleted'] == '') {
	$_SESSION['checked_deleted'] = "checked";
}

$set_status_filter = "user_id != NULL ";

if($_SESSION['checked_waiting'] == "checked") {
	$set_status_filter .= "OR user_verified = 'waiting' ";
	$btn_status_waiting = 'active';
}

if($_SESSION['checked_paused'] == "checked") {
	$set_status_filter .= "OR user_verified = 'paused' ";
	$btn_status_paused = 'active';
}

if($_SESSION['checked_verified'] == "checked") {
	$set_status_filter .= "OR user_verified = 'verified' ";
	$btn_status_verified = 'active';
}

if($_SESSION['checked_deleted'] == "checked") {
	$set_status_filter .= "OR user_verified = '' ";
	$btn_status_deleted = 'active';
}



$status_btn_group  = '<div class="btn-group d-flex">';
$status_btn_group .= '<a href="acp.php?tn=user&sub=list&switch=statusVerified" class="btn btn-default w-100 '.$btn_status_verified.'">'.$icon['check'].'</span></a>';
$status_btn_group .= '<a href="acp.php?tn=user&sub=list&switch=statusWaiting" class="btn btn-default w-100 '.$btn_status_waiting.'">'.$icon['clock'].'</a>';
$status_btn_group .= '<a href="acp.php?tn=user&sub=list&switch=statusPaused" class="btn btn-default w-100 '.$btn_status_paused.'">'.$icon['lock'].'</a>';
$status_btn_group .= '<a href="acp.php?tn=user&sub=list&switch=statusDeleted" class="btn btn-default w-100 '.$btn_status_deleted.'">'.$icon['trash'].'</a>';
$status_btn_group .= '</div>';


$whereString = "WHERE user_nick != '' ";

if(!empty($_POST['findUser'])) {
	$find_user = "%".sanitizeUserInputs($_POST['findUser'])."%";
	$search_user = "user_nick LIKE '$find_user' ";
}



if($set_status_filter != "") {
	$whereString .= " AND ($set_status_filter) ";
}

if($search_user != "") {
	$whereString .= " AND ($search_user) ";
}

unset($result);

$sql = "SELECT user_id, user_nick, user_class, user_firstname, user_lastname, user_registerdate, user_verified, user_mail
    		FROM se_user
    		$whereString
    		ORDER BY $order_by $way";
    		

$result = $db_user->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$cnt_result = count($result);

// number to show
$loop = 20;


$start = 0;
if(isset($_GET['start'])) {
	$start = (int) $_GET['start'];
}

$cnt_pages = ceil($cnt_result/$loop);

if($start<0) {
	$start = 0;
}

$end = $start+$loop;

if($end > $cnt_result) {
	$end = $cnt_result;
}


//next step
$next_start = $end;
$prev_start = $start-$loop;



if($start>($cnt_result-$loop)) {
	$next_start = $start;
}

if($prev_start <= 0){
	$prev_start = 0;
}


$pag_backlink = "<li class='page-item'><a class='page-link' href='acp.php?tn=user&sub=list&start=$prev_start&sort=$sort'>$lang[pagination_backward]</a></li>";


for($x=0;$x<$cnt_pages;$x++) {

	$page_start = $x*$loop;
	$page_nbr = $x+1;

	if($page_start == $start) {
		$aclass = "page-link active";
	} else {
		$aclass = "page-link";
	}

	$pag_string .= "<li class='page-item'><a class='$aclass' href='acp.php?tn=user&sub=list&start=$page_start'>$page_nbr</a></li>";
}


$pag_forwardlink = "<li class='page-item'><a class='page-link' href='acp.php?tn=user&sub=list&start=$next_start&sort=$sort'>$lang[pagination_forward]</a></li>";

echo '<div class="subHeader d-flex">';

echo '<div class="me-auto"><h3>Userlist</h3></div>';

echo '<div><a href="?tn=user&sub=new" class="btn btn-success align-self-end">'.$lang['new_user'].'</a></div>';

echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';
//print the list

echo '<div class="card p-3">';

echo '<table class="table table-hover table-striped table-sm">';
echo '<thead>';
echo '<tr>';
echo '<th><a href="acp.php?tn=user&sub=list&sort=0&way='.$set_way.'">ID</a></th>';
echo '<th></th>';
echo '<th><a href="acp.php?tn=user&sub=list&sort=1&way='.$set_way.'">'.$lang['h_username'].'</a></th>';
echo '<th><a href="acp.php?tn=user&sub=list&sort=2&way='.$set_way.'">'.$lang['h_registerdate'].'</a></th>';
echo '<th><a href="acp.php?tn=user&sub=list&sort=3&way='.$set_way.'">'.$lang['h_realname'].'</a></th>';
echo '<th><a href="acp.php?tn=user&sub=list&sort=4&way='.$set_way.'">'.$lang['h_email'].'</a></th>';
echo '<th>'.$lang['h_action'].'</th>';
echo '</tr>';
echo '</thead>';

for($i=$start;$i<$end;$i++) {

	$user_id = $result[$i]['user_id'];
	$user_nick = $result[$i]['user_nick'];
	$user_avatar_path = '../content/avatars/' . md5($user_nick) . '.png';
	$user_class = $result[$i]['user_class'];
	$user_mail = $result[$i]['user_mail'];
	$user_registerdate = $result[$i]['user_registerdate'];
	$user_firstname = $result[$i]['user_firstname'];
	$user_lastname = $result[$i]['user_lastname'];
	$user_verified = $result[$i]['user_verified'];
	$user_groups = $result[$i]['user_groups'];

	$show_registerdate = '';
	if($user_registerdate != '') {
		$show_registerdate = @date("d.m.Y", $user_registerdate);
	}

	$user_avatar = '<img src="images/avatar.png" class="rounded-circle avatar" width="50" height="50">';
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
		$user_nick = "<strike>$user_nick</strike>";
	}


	//status image
	switch ($user_verified) {
		case "waiting":
			$label = 'badge rounded-pill bg-info';
			break;
		case "paused":
			$label = 'badge badge-pill bg-warning';
			break;
		case "verified":
			$label = 'badge rounded-pill bg-success';
			break;
		case "":
			$label = 'badge rounded-pill bg-danger';
			break;
	}
	
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




} // eol for $i

echo '</table>';


echo '<nav>';
echo '<ul class="pagination justify-content-center">';
echo "$pag_backlink $pag_string $pag_forwardlink";
echo '</ul>';
echo '</nav>';


echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

/* sidebar */
echo '<div class="card p-2">';

echo "<form action='acp.php?tn=user' class='form-inline' method='POST'>";
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input type="text" name="findUser" class="form-control" placeholder="Filter">';
echo $hidden_csrf_token;
echo '</div>';
echo "</form>";

echo '<hr>';

echo '<fieldset class="mt-4">';
echo '<legend>'.$icon['filter'].' Filter</legend>';
echo $status_btn_group;
echo '</fieldset>';

echo '</div>'; /* end of sidebar */

echo '</div>';
echo '</div>';



?>
