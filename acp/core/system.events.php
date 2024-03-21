<?php

//prohibit unauthorized access
require 'core/access.php';

/* save upload preferences */
if(isset($_POST['update_posts'])) {
	
	foreach($_POST as $key => $val) {
		$data[htmlentities($key)] = htmlentities($val);
	}
	se_write_option($data,'se');
}



if(isset($_POST)) {
	/* read the preferences again */
	$se_get_preferences = se_get_preferences();
	
	foreach($se_get_preferences as $k => $v) {
		$key = $se_get_preferences[$k]['option_key'];
		$value = $se_get_preferences[$k]['option_value'];
		$se_prefs[$key] = $value;
	}
	
	foreach($se_prefs as $k => $v) {
	   $$k = stripslashes($v);
	}
}

echo '<div class="subHeader">'.$icon['calendar_event'].' '.$lang['nav_btn_settings'].' / '.$lang['nav_btn_events'].'</div>';

echo '<div class="card card-body">';

echo '<form action="?tn=system&sub=events" method="POST">';

echo '<h5 class="heading-line">'.$lang['label_entries'].'</h5>';

$input_entries_per_page = [
    "input_name" => "prefs_events_entries_per_page",
    "input_value" => $se_prefs['prefs_events_entries_per_page'],
    "label" => $lang['label_entries_per_page']
];

echo tpl_form_input_text($input_entries_per_page);

$input_event_time_offset = [
    "input_name" => "prefs_posts_event_time_offset",
    "input_value" => $se_prefs['prefs_posts_event_time_offset'],
    "label" => $lang['label_events_time_offset'],
    "form_text" => $lang['label_events_time_offset_text']
];

echo tpl_form_input_text($input_event_time_offset);

echo '<h5 class="heading-line">'.$lang['images'].'</h5>';

$input_images_prefix = [
    "input_name" => "prefs_events_images_prefix",
    "input_value" => $se_prefs['prefs_events_images_prefix'],
    "label" => $lang['label_settings_prefix']
];

echo tpl_form_input_text($input_images_prefix);


$all_images = se_get_all_images();
echo '<div class="mb-3">';
echo '<label>'.$lang['label_settings_default_image'].'</label>';
				
echo '<select class="form-control custom-select" name="prefs_events_default_banner">';
echo '<option value="use_standard">'.$lang['label_use_default'].'</option>';

if($prefs_events_default_banner == 'without_image') { $sel_without_image = 'selected'; }
echo '<option value="without_image" '.$sel_without_image.'>'.$lang['use_no_image'].'</option>';
foreach ($all_images as $img) {
	unset($sel);
	if($prefs_events_default_banner == $img) {
		$sel = "selected";
	}
	echo "<option $sel value='$img'>$img</option>";
}
				
echo '</select>';
echo '</div>';


echo '<h5 class="heading-line">'.$lang['label_guestlist'].'</h5>';

$sel_guestlist1 = '';
$sel_guestlist2 = '';
$sel_guestlist3 = '';

if($prefs_posts_default_guestlist == 1 OR $prefs_posts_default_guestlist == '') {
	$sel_guestlist1 = 'selected';
} else if($prefs_posts_default_guestlist == 2) {
	$sel_guestlist2 = 'selected';
} else if($prefs_posts_default_guestlist == 3) {
	$sel_guestlist3 = 'selected';
}

echo '<div class="mb-3">';
echo '<label>' . $lang['label_guestlist'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_default_guestlist">';
echo '<option value="1" '.$sel_guestlist1.'>'.$lang['label_guestlist_status_off'].'</option>';
echo '<option value="2" '.$sel_guestlist2.'>'.$lang['label_guestlist_status_registered'].'</option>';
echo '<option value="3" '.$sel_guestlist3.'>'.$lang['label_guestlist_status_global'].'</option>';
echo '</select>';
echo '</div>';		

echo '<input type="submit" class="btn btn-success" name="update_posts" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';
echo '</div>';