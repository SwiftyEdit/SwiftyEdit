<?php

//prohibit unauthorized access
require 'core/access.php';


if($_GET['sub'] == "new"){
	$sub = "new";
} else {
	$sub = "edit";
}

echo '<form action="acp.php?tn=user&sub='.$sub.'" class="form-horizontal" method="POST" enctype="multipart/form-data">';

$custom_fields = get_custom_user_fields();
sort($custom_fields);
$cnt_custom_fields = count($custom_fields);

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="card">';
echo '<div class="card-header">';

echo '<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">';
echo '<li class="nav-item"><a class="nav-link active" href="#info" data-bs-toggle="tab">'.$lang['nav_btn_info'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#contact" data-bs-toggle="tab">'.$lang['nav_btn_contact'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#psw" data-bs-toggle="tab">'.$lang['nav_btn_psw'].'</a></li>';
if($cnt_custom_fields > 0) {
	echo '<li class="nav-item"><a class="nav-link" href="#custom" data-bs-toggle="tab">'.$lang['nav_btn_custom_inputs'].'</a></li>';
}
echo '</ul>';

echo '</div>';
echo '<div class="card-body">';


echo '<div class="tab-content">';
echo '<div class="tab-pane fade show active" id="info">';


echo tpl_form_control_group('',$lang['label_username'],"<input class='form-control' type='text' name='user_nick' value='$user_nick'>");
echo '<hr>';

echo '<div class="row">';
echo '<div class="col-md-6">';


if($user_verified == ""){
	$user_verified = "waiting";
}


$select_user_status .= tpl_radio('user_verified','verified','verified',$lang['status_user_verified'],($user_verified == "verified" ? 'checked' :''));
$select_user_status .= tpl_radio('user_verified','waiting','waiting',$lang['status_user_waiting'],($user_verified == "waiting" ? 'checked' :''));
$select_user_status .= tpl_radio('user_verified','paused','paused',$lang['status_user_paused'],($user_verified == "paused" ? 'checked' :''));

echo '<fieldset>';
echo '<legend>'.$lang['label_status'].'</legend>';
echo $select_user_status;

echo '<hr>';

$check_user_unlocked_by_admin = '';
if($user_verified_by_admin == 'yes') {
    $check_user_unlocked_by_admin = 'checked';
}

echo '<div class=form-check mt-1">';
echo '<input type="checkbox" id="unlocked_by_admin" class="form-check-input" name="user_unlocked_by_admin" value="yes" '.$check_user_unlocked_by_admin.'>';
echo '<label for="unlocked_by_admin" class="form-check-label">User unlocked by admin</label>';
echo '</div>';

echo '</fieldset>';


echo '</div>';
echo '<div class="col-md-6">';

$all_groups = get_all_groups();

if(is_array($all_groups)) {
	$nbr_of_groups = count($all_groups);

	echo '<input type="hidden" name="nbr_of_groups" value="'.$nbr_of_groups.'">';

	for($i=0;$i<$nbr_of_groups;$i++) {
		
		$get_group_id = $all_groups[$i]['group_id'];
		$get_group_name = $all_groups[$i]['group_name'];
		$get_group_user = $all_groups[$i]['group_user'];
		
		$array_group_user = explode(" ", $get_group_user);
		
		$checked = "";
		if(in_array("$edituser", $array_group_user)) {
			$checked = "checked";
		}
		
		if($sub == "new") {
			$checked = "";
		}
		
		$cb_usergroup .= tpl_checkbox("user_groups[$i]","$get_group_id","check_group_$i",$get_group_name,$checked);
		$cb_usergroup .= '<input type="hidden" name="this_group['.$i.']" value="'.$get_group_id.'">';
	}

}

echo '<fieldset>';
echo '<legend>'.$lang['label_groups'].'</legend>';
echo $cb_usergroup;
echo '</fieldset>';



echo '</div>';
echo '</div>';


echo '</div>';

/* tab_contact */

echo '<div class="tab-pane fade" id="contact">';


echo tpl_form_control_group('',$lang['label_firstname'],"<input type='text' class='form-control' name='user_firstname' value='$user_firstname'>");
echo tpl_form_control_group('',$lang['label_lastname'],"<input type='text' class='form-control' name='user_lastname' value='$user_lastname'>");
echo tpl_form_control_group('',$lang['label_mail'],"<input type='text' class='form-control' name='user_mail' value='$user_mail'>");
echo tpl_form_control_group('',$lang['label_company'],"<input type='text' class='form-control' name='user_company' value='$user_company'>");
echo tpl_form_control_group('',"$lang[label_street]/$lang[label_nr]","<div class='row'><div class='col-md-9'><input type='text' class='form-control' name='user_street' value='$user_street'></div><div class='col-md-3'><input class='form-control' type='text' name='user_street_nbr' value='$user_street_nbr'></div></div>");
echo tpl_form_control_group('',"$lang[label_zip]/$lang[label_town]","<div class='row'><div class='col-md-3'><input type='text' class='form-control' name='user_zipcode' value='$user_zipcode'></div><div class='col-md-9'><input class='form-control' type='text' name='user_city' value='$user_city'></div></div>");



echo '</div>';

/* EOL tab_contact ### ### ### */


echo'<div class="tab-pane fade" id="psw">';

echo '<div class="alert alert-info">'.$lang['msg_edit_psw'].'</div>';

echo tpl_form_control_group('',$lang['label_psw'],"<input class='form-control' type='password' name='user_psw_new' value=''>");
echo tpl_form_control_group('',$lang['label_psw_repeat'],"<input class='form-control' type='password' name='user_psw_reconfirmation' value=''>");
echo '<input type="hidden" name="user_psw" value="'.$user_psw.'">';
echo '<input type="hidden" name="user_psw_hash" value="'.$user_psw_hash.'">';



/* DRM */

//String = $user_drm

if($user_class == "administrator")	{  $checked_class = "checked";  }

$arr_drm = explode("|", $user_drm);

if($arr_drm[0] == "drm_acp_pages")	{  $checked_pages = "checked";  }
if($arr_drm[1] == "drm_acp_files")	{  $checked_files = "checked";  }
if($arr_drm[2] == "drm_acp_user")	{  $checked_user = "checked";  }
if($arr_drm[3] == "drm_acp_system")	{  $checked_system = "checked";  }
if($arr_drm[4] == "drm_acp_editpages")	{  $checked_editpages = "checked";  }
if($arr_drm[5] == "drm_acp_editownpages")	{  $checked_editownpages = "checked";  }
if($arr_drm[6] == "drm_moderator")	{  $checked_moderator = "checked";  }
if($arr_drm[7] == "drm_can_publish")	{  $checked_can_publish = "checked";  }
if($arr_drm[8] == "drm_acp_sensitive_files")	{  $checked_sensitive_files = "checked";  }

echo '<fieldset class="mt-5 fieldset-danger">';
echo '<legend>'.$lang['label_rm'].'</legend>';

echo '<h6>'.$lang['rm_description'].'</h6>';




echo '<div class="alert alert-danger" role="alert">';
$label_admin = $lang['rm_is_admin'].'<br>'.$lang['rm_is_admin_txt'];
echo tpl_checkbox('drm_acp_class','administrator','check_admin',$label_admin,$checked_class);
echo '<hr>';
$label_drm = $lang['rm_can_edit_user'].'<br>'.$lang['rm_can_edit_user_txt'];
echo tpl_checkbox('drm_acp_user','drm_acp_user','check_user',$label_drm,$checked_user);
echo '<hr>';
$label_sensitive_files = $lang['rm_upload_sensitive_files'].'<br>'.$lang['rm_upload_sensitive_files_txt'];
echo tpl_checkbox('drm_acp_sensitive_files','drm_acp_sensitive_files','check_sensitive_files',$label_sensitive_files,$checked_sensitive_files);
echo '</div>';


echo tpl_checkbox('drm_acp_pages','drm_acp_pages','check_page',$lang['rm_create_new_pages'],$checked_pages);
echo tpl_checkbox('drm_acp_editpages','drm_acp_editpages','check_editpages',$lang['rm_edit_all_pages'],$checked_editpages);
echo tpl_checkbox('drm_acp_editownpages','drm_acp_editownpages','check_ownpages',$lang['rm_edit_own_pages'],$checked_editownpages);
echo tpl_checkbox('drm_can_publish','drm_can_publish','check_pub',$lang['rm_can_publish'],$checked_can_publish);
echo '<hr>';
echo tpl_checkbox('drm_acp_files','drm_acp_files','check_files',$lang['rm_upload_files'],$checked_files);

echo '<hr>';
echo tpl_checkbox('drm_acp_system','drm_acp_system','check_system',$lang['rm_can_edit_settings'],$checked_system);
echo '<hr>';
echo tpl_checkbox('drm_moderator','drm_moderator','check_mod',$lang['rm_is_moderator'],$checked_moderator);

echo '</fieldset>';


echo '</div>';


if($cnt_custom_fields > 0) {

	/* tab custom fields */
	echo '<div class="tab-pane fade" id="custom">';

	for($i=0;$i<$cnt_custom_fields;$i++) {	
		if(substr($custom_fields[$i],0,10) == "custom_one") {
			$label = substr($custom_fields[$i],11);
			echo tpl_form_control_group('',$label,"<input type='text' class='form-control' name='$custom_fields[$i]' value='" . ${$custom_fields[$i]} . "'>");
		}	elseif(substr($custom_fields[$i],0,11) == "custom_text") {
			$label = substr($custom_fields[$i],12);
			echo tpl_form_control_group('',$label,"<textarea class='form-control' rows='6' name='$custom_fields[$i]'>" . ${$custom_fields[$i]} . "</textarea>");
		}	elseif(substr($custom_fields[$i],0,14) == "custom_wysiwyg") {
			$label = substr($custom_fields[$i],15);
			echo tpl_form_control_group('',$label,"<textarea class='mceEditor_small form-control' name='$custom_fields[$i]'>" . ${$custom_fields[$i]} . "</textarea>");
		}		
	}

	echo '</div>';

}




echo '</div>';



echo '</div>'; // card-body
echo '</div>'; // card

echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card p-3">';

echo '<fieldset>';
echo '<legend>Avatar</legend>';
if(is_file("$user_avatar_path")) {
	echo '<p class="text-center"><img src="'.$user_avatar_path.'" class="rounded-circle avatar"></p>';
	echo '<div class="checkbox">';
	echo '<label><input type="checkbox" name="deleteAvatar"> ' . $lang['delete'] . '</label>';
	echo '</div>';
} else {
	echo '<p class="text-center"><img src="images/avatar.png" class="rounded-circle avatar"></p>';
}

echo '<hr><input name="avatar" class="form-control" type="file" size="50">';

echo '</fieldset>';

if($user_registerdate != "") {
	$show_registerdate = @date("d.m.Y H:i:s",$user_registerdate);
}



echo tpl_form_control_group('',$lang['label_time_registerdate'],"<pre>$show_registerdate</pre>");

echo '<input type="hidden" name="user_registerdate" value="'.$user_registerdate.'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
if(is_numeric($edituser)) {
	echo '<input  type="hidden" name="edituser" value="'.$edituser.'">';
}
echo '<hr>';
echo '<div class="btn-group d-flex" role="group">';
echo $delete_button;
echo $submit_button;
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</form>';



?>