<?php
/**
 * prohibit unauthorized access
 */
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){ 
	die ('<h2>Direct File Access Prohibited</h2>');
}

use Smarty\Smarty;

/**
 * delete smarty cache files
 * $cache_id	(string)	md5(page_permalink) -> delete pages cache
 * 				(string) 'all' -> delete complete cache
 */

function se_delete_smarty_cache($cache_id): void {

	$smarty = new Smarty;
	$smarty->setCacheDir(SE_CONTENT.'/cache/cache/');
	$smarty->setCompileDir(SE_CONTENT.'/cache/templates_c/');
	
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
 * store in ... cache/active_urls.json
 */

function cache_url_paths() {

	global $db_content;

    $pages = $db_content->select("se_pages", "*", [
        "page_permalink[!]" => ""
    ]);

    $data = [];
    foreach($pages as $page) {
        $data[] = [
            'page_id' => $page['page_id'],
            'page_language' => $page['page_language'],
            'page_permalink' => $page['page_permalink'],
            'page_type_of_use' => $page['page_type_of_use']
        ];
    }

    $file = SE_CONTENT . "/cache/active_urls.json";
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}