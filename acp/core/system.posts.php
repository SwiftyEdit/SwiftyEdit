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

echo '<div class="subHeader">'.$icon['file_earmark_post'].' '.$lang['nav_blog'].'</div>';

echo '<div class="card card-body">';

echo '<form action="?tn=system&sub=posts" method="POST">';

echo '<h5 class="heading-line">'.$lang['label_entries'].'</h5>';


echo '<div class="mb-3">';
echo '<label>'.$lang['label_entries_per_page'].'</label>';
echo '<input type="text" class="form-control" name="prefs_posts_entries_per_page" value="'.$prefs_posts_entries_per_page.'">';
echo '</div>';

echo '<h5 class="heading-line">'.$lang['label_images'].'</h5>';


echo '<div class="mb-3">';
echo '<label>'.$lang['label_images_prefix'].'</label>
			<input type="text" class="form-control" name="prefs_posts_images_prefix" value="'.$prefs_posts_images_prefix.'">
			</div>';

$all_images = se_get_all_images();
echo '<div class="mb-3">';
echo '<label>'.$lang['label_default_image'].'</label>';
				
echo '<select class="form-control custom-select" name="prefs_posts_default_banner">';
echo '<option value="use_standard">'.$lang['use_standard'].'</option>';

if($prefs_posts_default_banner == 'without_image') { $sel_without_image = 'selected'; }
echo '<option value="without_image" '.$sel_without_image.'>'.$lang['dont_use_an_image'].'</option>';
foreach ($all_images as $img) {
	unset($sel);
	if($prefs_posts_default_banner == $img) {
		$sel = "selected";
	}
	echo "<option $sel value='$img'>$img</option>";
}
				
echo '</select>';
				
echo '</div>';


echo '<input type="submit" class="btn btn-success" name="update_posts" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';
echo '</div>';