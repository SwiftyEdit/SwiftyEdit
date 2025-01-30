<?php

/**
 * copy maintenance.hml to root folder
 * update existing tables
 */

if(!defined('INSTALLER')) {
	header("location:login.php");
	die("PERMISSION DENIED!");
}

if($_SESSION['user_class'] != "administrator"){
	//move to login or die
	header("location:login.php");
	die("PERMISSION DENIED!");
}

copy('maintenance.html', '../maintenance.html');

use Medoo\Medoo;

require '../config.php';
if(is_file('../'.SE_CONTENT.'/config.php')) {
	include '../'.SE_CONTENT.'/config.php';
}


if(is_file('../config_database.php')) {
	include '../config_database.php';
	$db_type = 'mysql';
	
	$database = new Medoo([

		'type' => 'mysql',
		'database' => "$database_name",
		'host' => "$database_host",
		'username' => "$database_user",
		'password' => "$database_psw",
	 
		'charset' => 'utf8',
		'port' => $database_port,
	 
		'prefix' => DB_PREFIX
	]);
	
	$db_content = $database;
	$db_user = $database;
	$db_posts = $database;
	
	
} else {
	$db_type = 'sqlite';

	define("CONTENT_DB", "$se_db_content");
	define("USER_DB", "$se_db_user");
	define("POSTS_DB", "$se_db_posts");

	$db_content = new Medoo([
		'type' => 'sqlite',
		'database' => CONTENT_DB
	]);
	
	$db_user = new Medoo([
		'type' => 'sqlite',
		'database' => USER_DB
	]);

	$db_posts = new Medoo([
		'type' => 'sqlite',
		'database' => POSTS_DB
	]);
	
}


echo '<h2>UPDATE</h2>';

/* build an array from all php files in folder contents */
$all_tables = glob("contents/*.php");


for($i=0;$i<count($all_tables);$i++) {

	unset($db_path,$table_name,$table_type);
	include $all_tables[$i]; // returns $cols and $table_name
	
	$is_table = table_exists("$database","$table_name");
	
	if($is_table < 1) {
        add_table("$database","$table_name",$cols);
		
		$table_updates[] = "New $table_type Table: <b>$table_name</b> in Database <b>$database</b>";
	}


	$existing_cols = get_columns("$database","$table_name");


	foreach ($cols as $k => $v) {
   
  		if(!array_key_exists("$k", $existing_cols)) {
  			//update_table -> column, type, table, database
  			update_table("$k","$cols[$k]","$table_name","$database");
  			$col_updates[] = "New Column: <b>$k</b> in table <b>$table_name</b>";	
  		}
     
	} // eo foreach




	/* updates are done, check all columns again */

	$existing_cols = get_columns("$database","$table_name");

	foreach ($cols as $b => $x) {
       
  		if(!array_key_exists("$b", $existing_cols)) {
  			$fails[] = "Missing Column: <b>$b</b> - table: <b>$table_name</b> ($database)";  	
  		} else {
  			$wins[] = "Column <b>$b</b> in table <b>$table_name</b> is ready";
  	}
  
	} // eo foreach


} // EO $i



/* echo fails and wins */

if(is_array($fails)) {
	echo "<h3>" . count($fails) . " ERRORS</h3>";
	
	foreach ($fails as $value) {
			echo"<span class='text-danger'>$value</span><br />";
		}
	
}
if(is_array($wins)) {
	echo "<h3>" . count($wins) . " Columns are ready</h3>";
	
	if(is_array($table_updates)) {
		foreach ($table_updates as $value) {
			echo"<span class='text-success'>$value</span><br />";
		}
	}
	
	if(is_array($col_updates)) {
		foreach ($col_updates as $value) {
			echo"<span class='text-success'>$value</span><br />";
		}
	}
	
}

echo '<a href="/install/" class="btn btn-primary me-1">Reload</a>';
echo '<a href="/admin/" class="btn btn-primary">ACP</a>';


if(is_file('../maintenance.html')) {
	unlink('../maintenance.html');
}


?>