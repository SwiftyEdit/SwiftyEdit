<?php

/**
 * @var array $lang
 * @var array $mod
 */


/**
 * get all installed Plugins
 * return as array
 */

function get_all_plugins() {
	
	$plugins = array();
	$scanned_directory = array_diff(scandir('assets/plugins/'), array('..', '.','.DS_Store'));
	foreach($scanned_directory as $p) {
		
		$path_parts = pathinfo($p);
		if($path_parts['extension'] == 'php') {
			$plugins[] = $p;
		} else {
			if((is_dir('assets/plugins/'.$p)) && (is_file('assets/plugins/'.$p.'/index.php'))) {
				$plugins[] = $p;
			}
		}
		
	}
	return $plugins;
}



function se_get_all_addons(): array {

    $addons_root = SE_PLUGINS;
    $scanned_directory = array_diff(scandir($addons_root), array('..', '.','.DS_Store'));

    foreach($scanned_directory as $plugin_dir) {
        $addon_info_file = "$addons_root/$plugin_dir/info.json";
        if(is_file("$addon_info_file")) {
            $info_json = file_get_contents("$addon_info_file");
            $addon_info[$plugin_dir] = json_decode($info_json, true);
        }
    }
    return $addon_info;
}


function se_check_addon_update(array $addon_info): array {

    // Return unknown if update_url or build is not defined
    if(!isset($addon_info['addon']['update_url']) || !isset($addon_info['addon']['build'])) {
        return ['status' => 'unknown'];
    }

    // Load remote info.json
    $json = @file_get_contents($addon_info['addon']['update_url']);

    if($json === false) {
        return ['status' => 'unknown'];
    }

    $remote = json_decode($json, true);

    if(!$remote || !isset($remote['versions'])) {
        return ['status' => 'unknown'];
    }

    // Load SwiftyEdit build number
    $se_version = json_decode(file_get_contents(SE_ROOT.'version.json'), true);
    $se_build = $se_version['build'];

    // Find most recent compatible version
    $compatible_version = null;

    foreach($remote['versions'] as $v) {
        if($se_build >= $v['requires_build']) {
            $compatible_version = $v;
            break;
        }
    }

    if($compatible_version === null) {
        return ['status' => 'unknown'];
    }

    // Compare build numbers
    if($compatible_version['build'] > $addon_info['addon']['build']) {
        return [
            'status' => 'update_available',
            'version' => $compatible_version['version'],
            'build' => $compatible_version['build'],
            'download_url' => $compatible_version['download_url']
        ];
    }

    return [
        'status' => 'up_to_date',
        'version' => $addon_info['addon']['version'],
        'build' => $addon_info['addon']['build'],
        'download_url' => null
    ];
}


function se_install_plugin(string $plugin_id, string $download_url): array {

    // Download ZIP to temporary file
    $tmp_zip = tempnam(sys_get_temp_dir(), 'se_plugin_');
    $zip_content = @file_get_contents($download_url);

    if($zip_content === false) {
        unlink($tmp_zip);
        return ['success' => false, 'message' => 'Could not download plugin ZIP.'];
    }

    file_put_contents($tmp_zip, $zip_content);

    // Open and validate ZIP
    $zip = new ZipArchive();
    if($zip->open($tmp_zip) !== true) {
        unlink($tmp_zip);
        return ['success' => false, 'message' => 'Could not open ZIP file.'];
    }

    // Validate file types – only allowed extensions
    $allowed_extensions = ['php', 'tpl', 'json', 'js', 'css', 'html', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'md', 'sqlite3'];

    for($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if($ext !== '' && !in_array($ext, $allowed_extensions)) {
            $zip->close();
            unlink($tmp_zip);
            return ['success' => false, 'message' => 'ZIP contains invalid file type: '.$filename];
        }
    }

    // Determine plugin path
    $plugin_path = SE_PLUGINS . DIRECTORY_SEPARATOR . $plugin_id;

    // Create plugin directory if necessary
    if(!is_dir($plugin_path)) {
        mkdir($plugin_path, 0755, true);
    }

    // Extract ZIP – strip root folder, skip /data/ directory
    for($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);

        // Strip first folder
        $relative_path = preg_replace('#^[^/]+/#', '', $filename);

        // Skip empty paths, directories and /data/
        if(empty($relative_path) || str_ends_with($relative_path, '/') || str_starts_with($relative_path, 'data/')) {
            continue;
        }

        // Write file to target path
        $target = $plugin_path . DIRECTORY_SEPARATOR . $relative_path;

        // Create subdirectory if necessary
        if(!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        file_put_contents($target, $zip->getFromIndex($i));
    }

    $zip->close();
    unlink($tmp_zip);

    return ['success' => true, 'message' => 'Plugin successfully installed.'];
}

/**
 * @param string $addon directory of the addon
 * @return array
 */
function se_return_addon_translations($addon): array {
    global $languagePack;
    $translations = [];
    $addons_lang_file = SE_ROOT."/plugins/".$addon."/lang/".$languagePack.'.json';
    $addons_lang_file_alt = SE_ROOT."/plugins/".$addon."/lang/en.json";

    if(is_file($addons_lang_file)) {
        $translations = json_decode(file_get_contents($addons_lang_file), true);
    } else {
        if(is_file($addons_lang_file_alt)) {
            $translations = json_decode(file_get_contents($addons_lang_file_alt), true);
        }
    }
    return $translations;
}


/**
 * get all installed Moduls
 * return as array
 */

function get_all_modules() {

	$mdir = "assets/modules/";
	$cntMods = 0;
	$arr_iMods = array();
	$scanned_directory = array_diff(scandir($mdir), array('..', '.','.DS_Store'));
		
	foreach($scanned_directory as $mod_folder) {
		if(is_file("$mdir/$mod_folder/info.inc.php")) {
			include $mdir.'/'.$mod_folder.'/info.inc.php';
			$arr_iMods[$cntMods]['name'] = $mod['name'];
			$arr_iMods[$cntMods]['folder'] = $mod_folder;
			$cntMods++;		
		}
	}

	return($arr_iMods);
}

/**
 * get all addons stored in table se_addons
 * type = theme | module
 */
 
function se_get_addons($t='module') {
	
	global $db_content;
	$result = array();
	
	if($t == 'module' OR $t == 'plugin') {
		$type = 'plugin';
	} else {
		$type = 'theme';
	}

	$result = $db_content->select("se_addons", "*", [
	    "addon_type" => "$type"
	]);
	
	return $result;
}


/**
 * delete addon and its contents
 */
 
function se_delete_addon($addon,$type) {
	
	if($type == 'm') {
		$dir = SE_CONTENT.'/modules';
	} else if($type == 'p') {
		$dir = SE_CONTENT.'/plugins';
	} else if($type == 't') {
		$dir = '../styles';
	}
	
	$remove_dir = $dir.'/'.basename($addon);
	se_reomove_addon_files($remove_dir);
	$record_msg = 'removed addon: <strong>'.$addon.' ('.$type.')</strong>';
	record_log($_SESSION['user_nick'],$record_msg,"8");
}

/**
 * remove addon contents
 * folders (recursive) and/or files
 */

 function se_reomove_addon_files($item) {
   if(is_dir($item)){
     $objects = scandir($item);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if(filetype($item."/".$object) == "dir") {
         	se_reomove_addon_files($item."/".$object);
         } else {
         		unlink($item."/".$object);
         }
       }
     }
     reset($objects);
     rmdir($item);
   }
   
   if(is_file($item)) {
	   unlink($item);
   }
   
 }



/**
 * show all installed templates
 * return as array
 */

function get_all_templates() {

	//templates folder
	$sdir = SE_ROOT."/public/assets/themes/";
	$cntStyles = 0;
	$scanned_directory = array_diff(scandir($sdir), array('..', '.','.DS_Store'));
	
	foreach($scanned_directory as $tpl_folder) {
		if(is_dir("$sdir/$tpl_folder")) {
			$arr_Styles[] = "$tpl_folder";
		}	
	}

	return($arr_Styles);
}

/**
 * return available stylesheets from a theme
 * find css files theme_*.css
 */
 
function se_get_stylesheets($theme){
	
	$stylesheets = glob('../styles/'.$theme.'/css/theme_*.css');
	
	if(is_array($stylesheets) && (count($stylesheets) > 0)){
		return $stylesheets;
	} else {
		return '0';
	}
	
	
}



/**
 * check in active modules and pages with posts
 * generate array from pages containing a module or post categories
 * and from addon_dir -> content.sqlite3
 * store in ... cache/active_addons.json
 */

function mods_check_in() {
	
	global $db_content;

    $m = [];

	$mods = $db_content->select("se_addons", "addon_dir", [
	    "addon_type" => ["module","plugin"]
	]);

    foreach ($mods as $mod) {
        $m[] = [
            'page_modul' => $mod,
            'page_permalink' => 'NULL'
        ];
    }
		
	$pages = $db_content->select("se_pages", ["page_modul","page_permalink","page_posts_categories","page_type_of_use"]);	
	$items = array_merge($pages, $m);
	
	$cnt_items = count($items);
	$x = 0;
	for($i=0;$i<$cnt_items;$i++) {
	
		if($items[$i]['page_modul'] != "" OR
            $items[$i]['page_posts_categories'] != "" OR
            $items[$i]['page_type_of_use'] == "display_post" OR
            $items[$i]['page_type_of_use'] == "display_product" OR
            $items[$i]['page_type_of_use'] == "display_event") {
			
			if($items[$i]['page_posts_categories'] != '') {
				$items[$i]['page_modul'] = 'se_post';
			}
			
			if($items[$i]['page_type_of_use'] == 'display_post') {
				$items[$i]['page_modul'] = 'se_post';
			}
            if($items[$i]['page_type_of_use'] == 'display_product') {
                $items[$i]['page_modul'] = 'se_shop';
            }
            if($items[$i]['page_type_of_use'] == 'display_event') {
                $items[$i]['page_modul'] = 'se_events';
            }
			
			$string .= "\$active_mods[$x]['page_modul'] = \"" . $items[$i]['page_modul'] . "\";\n";
			$string .= "\$active_mods[$x]['page_permalink'] = \"" . $items[$i]['page_permalink'] . "\";\n";			
			$x++;

            $data[] = [
                'page_modul' => $items[$i]['page_modul'],
                'page_permalink' => $items[$i]['page_permalink']
            ];

		}
	
	}

    $file = SE_CONTENT . "/cache/active_addons.json";
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * write/update theme options
 * $data (array) $data['theme'] -> name of the theme
 * values are prefixed by 'theme' f.e. $data['theme_']
 */

function se_write_theme_options($data) {
	
	global $db_content;
	
	$db_content->delete("se_themes", [
		"theme_name" => $data['theme']
	]);
	
	foreach($data as $key => $value) {
		
		if($key == 'theme') {
			$theme = $value;
			continue;
		}
		
		if((strstr($key, '_', true)) == 'theme') {	
			$db_content->insert("se_themes", ["theme_name" => $data['theme'],"theme_label" => "$key","theme_value" => "$value"]);
		}
		
		
	}
}