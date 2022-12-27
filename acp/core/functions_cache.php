<?php
/**
 * prohibit unauthorized access
 */
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){ 
	die ('<h2>Direct File Access Prohibited</h2>');
}



/**
 * delete smarty cache files
 * $cache_id	(string)	md5(page_permalink) -> delete pages cache
 * 				(string) 'all' -> delete complete cache
 */

function se_delete_smarty_cache($cache_id) {
	
	require_once '../lib/Smarty/Smarty.class.php';
	$smarty = new Smarty;
	$smarty->cache_dir = SE_CONTENT.'/cache/cache/';
	$smarty->compile_dir = SE_CONTENT.'/cache/templates_c/';
	
	if($cache_id == 'all') {
		$smarty->clearAllCache();
		$smarty->clearCompiledTemplate();
	} else {
		$smarty->clearCache(null,$cache_id);
		$smarty->clearCompiledTemplate(null,$cache_id);		
	}

}




/**
 * cache all saved url paths
 * generate array from pages where permalink is not empty
 * store in ... cache/active_urls.php
 */

function cache_url_paths() {

	global $db_content;
	
	$result = $db_content->select("se_pages", "*");	
	$count_result = count($result);
	
	$x = 0;
	$string = "\$existing_url = array();\n";
	for($i=0;$i<$count_result;$i++) {
		
		if($result[$i]['page_permalink'] != "") {
			$string .= "\$existing_url[$x] = \"" . $result[$i]['page_permalink'] . "\";\n";
			$x++;
		}
	}
	
	$str = "<?php\n$string\n?>";
	$file = SE_CONTENT . "/cache/active_urls.php";
	file_put_contents($file, $str, LOCK_EX);
}


?>