<?php
	
if(!defined('INSTALLER')) {
	header("location:login.php");
	die("PERMISSION DENIED!");
}

if(isset($_POST['prefs_cms_domain'])) {
	$_SESSION['temp_prefs_cms_domain'] = $_POST['prefs_cms_domain'];
}

if(isset($_POST['prefs_cms_ssl_domain'])) {
	$_SESSION['temp_prefs_cms_ssl_domain'] = $_POST['prefs_cms_ssl_domain'];
}

if(isset($_POST['prefs_cms_base'])) {
	$_SESSION['temp_prefs_cms_base'] = $_POST['prefs_cms_base'];
}



echo '<div class="card">';
echo '<h5 class="card-header">SQLite</h5>';
echo '<div class="card-body">';
echo '<div class="alert alert-info">'.$lang['db_sqlite_help'].'</div>';
echo '<form action="index.php" method="POST">';

echo '<input type="submit" class="btn btn-info" name="step3" value="'.$lang['prev_step'].'"> ';
echo '<input type="submit" class="btn btn-success" name="install_sqlite" value="'.$lang['start_install'].'">';
echo '</form>';
echo '</div>';
echo '</div>';

echo '<hr>';


echo '<div class="card" id="mysql">';
echo '<h5 class="card-header">MySQL</h5>';

echo '<div class="card-body">';
if(isset($_POST['check_connection'])) {
	include __DIR__.'/check_connection.php';
}

echo '<form action="index.php#mysql" method="POST">';

echo '<div class="mb-2">';
echo '<label>'.$lang['db_host'].'</label>';
echo '<input type="text" class="form-control" name="prefs_database_host" placeholder="localhost" value="'.$prefs_database_host.'">';
echo '<small class="form-text text-muted">'.$lang['db_host_help'].'</small>';
echo '</div>';

echo '<div class="mb-2">';
echo '<label>'.$lang['db_port'].'</label>';
echo '<input type="text" class="form-control" name="prefs_database_port" placeholder="" value="'.$prefs_database_port.'">';
echo '<small class="form-text text-muted">'.$lang['db_port_help'].'</small>';
echo '</div>';

echo '<div class="mb-2">';
echo '<label>'.$lang['db_name'].'</label>';
echo '<input type="text" class="form-control" name="prefs_database_name" placeholder="" value="'.$prefs_database_name.'">';
echo '</div>';

echo '<div class="mb-2">';
echo '<label>'.$lang['db_username'].'</label>';
echo '<input type="text" class="form-control" name="prefs_database_username" placeholder="" value="'.$prefs_database_username.'">';
echo '<small class="form-text text-muted">'.$lang['db_username_help'].'</small>';
echo '</div>';

echo '<div class="mb-2">';
echo '<label>'.$lang['db_psw'].'</label>';
echo '<input type="text" class="form-control" name="prefs_database_psw" placeholder="" value="'.$prefs_database_psw.'">';
echo '<small class="form-text text-muted">'.$lang['db_psw_help'].'</small>';
echo '</div>';

echo '<input type="submit" class="btn btn-info" name="step3" value="'.$lang['prev_step'].'"> ';
echo '<input type="submit" class="btn btn-outline-success" name="check_connection" value="'.$lang['check_connection'].'"> ';

if($conn === true) {
	echo '<input type="submit" class="btn btn-success" name="install_mysql" value="'.$lang['start_install'].'">';
} else {
	echo $fail_msg;
}
echo '</form>';
echo '</div>';
echo '</div>';