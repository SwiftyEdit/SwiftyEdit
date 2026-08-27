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


/**
 * rebuild the cache file for every snippet from the database
 * mirrors the per-snippet write-through cache written on save in
 * acp/core/snippets/data-writer.php ("save_snippet") - same file naming
 * ({snippet_name}_{snippet_lang}.json) and same row shape, just for all
 * snippets at once instead of the one just saved.
 *
 * @return int number of snippet cache files written
 */
function se_rebuild_all_snippets_cache(): int {

    global $db_content;

    $cache_dir = SE_CONTENT . '/cache/snippets';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }

    $snippets = $db_content->select("se_snippets", "*");

    $count = 0;
    foreach ($snippets as $snippet) {
        $file = $cache_dir . '/' . $snippet['snippet_name'] . '_' . $snippet['snippet_lang'] . '.json';
        file_put_contents($file, json_encode($snippet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $count++;
    }

    return $count;
}