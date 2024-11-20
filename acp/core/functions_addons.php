<?php
/**
 * prohibit unauthorized access
 */
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){ 
	die ('<h2>Direct File Access Prohibited</h2>');
}


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
	
	if($t == 'module') {
		$type = 'module';
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
 * store in ... cache/active_mods.php
 */

function mods_check_in() {
	
	global $db_content;
	
	$pages = array();
	$mods = array();
	$m = array();

	$mods = $db_content->select("se_addons", "addon_dir", [
	    "addon_type" => "module"
	]);
	
	for($i=0;$i<count($mods);$i++) {
		$m[]['page_modul'] = $mods[$i];
		$m[]['page_permalink'] = 'NULL';
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
		}
	
	}
	
	$str = "<?php\n$string\n?>";
		
	$file = SE_CONTENT . "/cache/active_mods.php";
	file_put_contents($file, $str, LOCK_EX);

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

/**
 * include script(s) from addons
 * example: se_get_hook('page_updated',$_POST);
 *
 * @param array $hooks the addon/hook data
 * @param array $data $_POST data
 * @return void
 *
 */

function se_run_hooks(array $hooks, array $data) {

    foreach ($hooks as $hook) {

        $get_hook_info = explode("<->", $hook);
        $addon = $get_hook_info[0];
        $action = $get_hook_info[1];
        $command = $get_hook_info[2];

        $hook_file = SE_CONTENT.'/modules/'.$addon.'/hooks/'.$action.'.php';
        if(is_file($hook_file)) {
            include $hook_file;
        }
    }
}

/**
 * get all hooks from addons
 * @return array
 */

function se_get_all_hooks() {

    global $all_mods;
    $get_hook = array();

    /*
    $hooks = [
        "page_updated" => [],
        "product_updated" => [],
        "dashboard_listed_all_addons" => []
    ];
    */

    $all_hook_commands = array();

    // loop through addons
    foreach($all_mods as $mod) {
        // loop through available hooks

        $hook_commands_file = SE_CONTENT.'/modules/'.$mod['folder'].'/hooks/index.php';

        /**
         * get $hook_commands from /hooks/index.php file
         * @var $hook_commands
         * */

        if(is_file($hook_commands_file)) {
            include($hook_commands_file);
            $this_hook_commands[$mod['folder']] = $hook_commands;
            if(is_array($hook_commands)) {
                $all_hook_commands = array_merge($this_hook_commands,$all_hook_commands);
            }
        }
    }
    return $all_hook_commands;
}

/**
 * @param string $command
 * @return array
 */
function se_get_hook($command) {

    global $all_hooks;
    $hooks = array();
    $x = 0;

    foreach($all_hooks as $key => $value) {

        // $key is the addon
        // $value the commands

        foreach($value as $commands => $actions) {

            if($commands == $command) {
                $hook_str = '';
                foreach($actions as $action => $v) {

                    $send_value = $key.'<->'.$commands.'<->'.$action;
                    $hook_str .= '<div class="mb-1 form-check">';
                    $hook_str .= '<input type="checkbox" name="send_hook[]" value="'.$send_value.'" id="id'.$x.'"> ';
                    $hook_str .= '<label for="id'.$x.'"><span class="badge text-bg-secondary">'.$key.'</span> '.$v.'</label>';
                    $hook_str .= '</div>';
                    $x++;
                }

                $hooks[] = $hook_str;
            }
        }
    }
    return $hooks;
}