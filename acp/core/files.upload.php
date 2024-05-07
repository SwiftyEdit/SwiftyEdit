<?php

//prohibit unauthorized access
require 'core/access.php';


$path_img = '../'.IMAGES_FOLDER;
$img_dirs = se_get_dirs_rec($path_img);

$path_files = '../'.FILES_FOLDER;
$files_dirs = se_get_dirs_rec($path_files);

$img_folder = basename($path_img);
$files_folder = basename($path_files);
?>

<form action="core/files.upload-script.php" id="myDropzone" method="post" class="dropzone dropzone-default">
<div class="row">
	<div class="col-md-9">
		<label><?php echo $lang['upload_destination']; ?></label>
		<select name="upload_destination" class="form-control custom-select">
			<optgroup label="<?php echo $lang['images']; ?>">
				<option value="<?php echo $path_img; ?>"><?php echo $img_folder; ?></option>
				<?php
				foreach($img_dirs as $d) {
					$short_d = str_replace($path_img, '', $d);
					echo '<option value="'.$d.'">'.$img_folder.$short_d.'</option>';
				}
				?>
			</optgroup>
			<optgroup label="<?php echo $lang['files']; ?>">
				<option value="<?php echo $path_files; ?>"><?php echo $files_folder; ?></option>
				<?php
				foreach($files_dirs as $d) {
					$short_d = str_replace($path_files, '', $d);
					echo '<option value="'.$d.'">'.$files_folder.$short_d.'</option>';
				}
				?>
			</optgroup>
		</select>
	</div>
	<div class="col-md-3">
		<div class="form-check">
			<input class="form-check-input" type="checkbox" name="file_mode" value="overwrite" id="overwrite">
			<label class="form-check-label" for="overwrite">
				<?php echo $lang['upload_overwrite_existing_files']; ?>
			</label>
		</div>
	</div>
</div>

<div class="fallback">
	<input name="file" type="file" multiple />
</div>
		
<input type="hidden" name="w" value="<?php echo $se_prefs['prefs_maximagewidth']; ?>" />
<input type="hidden" name="w_tmb" value="<?php echo $se_prefs['prefs_maxtmbwidth']; ?>" />
<input type="hidden" name="h" value="<?php echo $se_prefs['prefs_maximageheight']; ?>" />
<input type="hidden" name="h_tmb" value="<?php echo $se_prefs['prefs_maxtmbheight']; ?>" />
<input type="hidden" name="fz" value="<?php echo $se_prefs['prefs_maxfilesize']; ?>" />
<input type="hidden" name="unchanged" value="<?php echo $se_prefs['prefs_uploads_remain_unchanged']; ?>" />
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">

</form>
<hr>
<p class="text-center">
<?php

echo $icon['images'] . ' ';
foreach($se_upload_img_types as $s) {
	echo '<span class="badge badge-secondary">'.$s.'</span> ' ;
}
echo '<br>'.$icon['folder_open'] . ' ';
foreach($se_upload_file_types as $s) {
	echo '<span class="badge badge-secondary">'.$s.'</span> ' ;
}
?>
</p>