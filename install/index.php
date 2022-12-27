<?php

/**
 * SwiftyEdit
 * Installer/Updater
 */

session_start();
error_reporting(0);

require '../config.php';

$modus = 'install';
define('INSTALLER', TRUE);

if(isset($_GET['l']) && is_dir('../lib/lang/'.basename($_GET['l']).'/')) {
	$_SESSION['lang'] = basename($_GET['l']);
}

if(!isset($_SESSION['lang']) || $_SESSION['lang'] == '') {
	$l = 'de';
	$modus = 'choose_lang';
} else {
	$l = $_SESSION['lang'];
}


include 'php/functions.php';
include '../lib/lang/'.$l.'/dict-install.php';
include '../acp/core/icons.php';

if(is_file("$se_db_content")) {
	$modus = "update";
	$db_type = 'sqlite';
}

if(is_file("../config_database.php")) {
	$modus = "update";
	$db_type = 'mysql';
}

if(isset($_POST['check_database'])) {
	include 'php/check_connection.php';
}

if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == "administrator") {
	$modus = "update";
}

if($modus == "update") {
	/* updates for admins only */
	if($_SESSION['user_class'] != "administrator"){
		die("PERMISSION DENIED!");
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo"$modus"; ?> SwiftyEdit | Content Management System</title>
	<script src="../styles/default/js/main.js"></script>
	<link media="screen" rel="stylesheet" type="text/css" href="../acp/theme/css/styles_dark_mono.css" />
	<link media="screen" rel="stylesheet" type="text/css" href="css/styles.css?v=20" />
</head>
<body>
<div class="container">
	<div id="inst-background">
		<div id="inst-header" class="position-relative">
			<div><span class="badge text-bg-primary position-absolute top-100 start-50 translate-middle p-2"><?php echo"$modus" ?></span></div>
			<h1>SwiftyEdit <small>Installation & Setup</small></h1>
		</div>
		<div id="inst-body">
			<?php
			if($modus == "install") {
				include("inc.install.php");
			} else if($modus == "update") {
				include("inc.update.php");
			} else {
				echo '<h3 class="text-center">Choose your Language ...</h3><hr>';
				echo '<div class="row">';
				echo '<div class="col-md-6">';
				echo '<p class="text-center"><a href="index.php?l=de"><img src="../lib/lang/de/flag.png" class="img-rounded"><br>DE</a></p>';
				echo '</div>';
				echo '<div class="col-md-6">';
				echo '<p class="text-center"><a href="index.php?l=en"><img src="../lib/lang/en/flag.png" class="img-rounded"><br>EN</a></p>';
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>
		<div id="inst-footer">
			<a href="https://www.SwiftyEdit.com">
			<p class="h4">SwiftyEdit<br><small>Content Management System</small></p>
			</a>
		</div>
	</div>
	<p class="text-center"><?php echo date("Y/m/d H:i:s",time());  ?></p>
</div>
</body>
</html>