<?php
	
//prohibit unauthorized access
require __DIR__.'/access.php';

/**
 * global variables in this file
 * @var array $lang
 * @var array $se_prefs
 * @var string $db_type
 */

$se_check_messages = array();

if(!is_file('../.htaccess')) {
    $se_check_messages[] = $lang['msg_error_no_htaccess'];
}

if(!is_dir(SE_CONTENT.'/cache/cache/')) {
	mkdir(SE_CONTENT.'/cache/cache/');
}

if(!is_dir(SE_CONTENT.'/cache/templates_c/')) {
	mkdir(SE_CONTENT.'/cache/templates_c/');
}

$writable_items = array(
	'../sitemap.xml',
	SE_CONTENT.'/',
	SE_CONTENT.'/avatars/',
	SE_CONTENT.'/cache/',
	SE_CONTENT.'/cache/cache/',
	SE_CONTENT.'/cache/templates_c/',
	SE_CONTENT.'/files/',
	SE_CONTENT.'/images/',
	SE_CONTENT.'/SQLite/',
	SE_CONTENT.'/SQLite/content.sqlite3',
	SE_CONTENT.'/SQLite/user.sqlite3',
	SE_CONTENT.'/SQLite/index.sqlite3'
);

foreach($writable_items as $f) {
	
	if(($f == '../sitemap.xml') AND ($se_prefs['prefs_xml_sitemap'] == 'off')) {
		continue;
	}
	
	if($db_type !== 'sqlite') {
		if($f == SE_CONTENT.'/SQLite/content.sqlite3') {
			continue;
		}
		if($f == SE_CONTENT.'/SQLite/user.sqlite3') {
			continue;
		}	
	}
	
	
	if(!is_writable($f)) {
        $se_check_messages[] = $lang['msg_error_not_writable']. ' (... '.basename($f).')';
	}

}

if($se_prefs['prefs_cms_domain'] == '') {
    $se_check_messages[] = str_replace('{setting}','CMS DOMAIN',$lang['msg_error_missing_setting']);
}

if($se_prefs['prefs_cms_base'] == '') {
    $se_check_messages[] = str_replace('{setting}','CMS BASE',$lang['msg_error_missing_setting']);
}

if($se_prefs['prefs_maxtmbwidth'] == '' || $se_prefs['prefs_maxtmbheight'] == '') {
    $se_check_messages[] = str_replace('{setting}','THUMBS WIDTH/HEIGHT',$lang['msg_error_missing_setting']);
}

/* check special pages */

foreach($se_page_types as $pt) {

    if($pt == 'normal') {
       continue;
    }

    $find_target_page = $db_content->select("se_pages", ["page_permalink","page_type_of_use"], [
        "page_type_of_use" => "$pt"
    ]);

    if(count($find_target_page) < 1) {
        $se_check_messages[] = 'Type of use <code>'.$pt.'</code> is not available ';
    }
}