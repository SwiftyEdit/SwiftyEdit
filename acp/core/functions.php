<?php

include_once 'functions_addons.php';
include_once 'functions_cache.php';
include_once 'functions_shop.php';


/**
 * Sends a plain text response and terminates script execution.
 *
 * @param string $data
 * @param int $statusCode
 * @return never
 */
function se_plain_response($data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: text/plain; charset=utf-8');
    echo $data;
    exit;
}

/**
 * Sends an HTML response and terminates script execution.
 *
 * @param string $html        The HTML content to send.
 * @param int    $statusCode  Optional HTTP status code (default: 200).
 *
 * @return never
 */
function se_html_response($html, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}

/**
 * Sends a JSON response and terminates script execution.
 *
 * @param array $data         The associative array to encode as JSON.
 * @param int   $statusCode   Optional HTTP status code (default: 200).
 *
 * @return never
 */
function se_json_response(array $data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}


/**
 * get all installed language files
 * return as array
 */

function get_all_languages(): array {

    $d = SE_ROOT.'/languages/';

	$cntLangs = 0;
	$scanned_directory = array_diff(scandir($d), array('..', '.','.DS_Store'));
	
	foreach($scanned_directory as $lang_folder) {
		if(is_file("$d/$lang_folder/index.php")) {
			include $d.'/'.$lang_folder.'/index.php';
			$arr_lang[$cntLangs]['lang_sign'] = "$lang_sign";
			$arr_lang[$cntLangs]['lang_desc'] = "$lang_desc";
			$arr_lang[$cntLangs]['lang_folder'] = "$lang_folder";
			$cntLangs++;
		}
	}
	
	return $arr_lang;
}

/**
 * @param $lang
 * @return string
 */
function return_language_flag_src($lang): string {

    $real_img_src = SE_ROOT.'/languages/' . $lang . '/flag.png';
    $encoded_flag = base64_encode(file_get_contents($real_img_src));
    $encoded = 'data:image/png;base64,'.$encoded_flag;
    return $encoded;
}


/**
 * get all preferences
 * return as array
 * @deprecated - this function will be removed in the near future. Use se_get_preferences() from global functions.
 */
 
function get_preferences() {
	global $db_content;
	
	$prefs = $db_content->get("se_preferences", "*", [
		"prefs_id" => 1
	]);
	
	return $prefs;
}

/**
 * write preferences
 * table se_options
 */

function se_write_option($data,$module) {
	
	global $db_content;

	foreach($data as $key => $val) {
		
		if($key == '') {
			continue;
		}
		
		if(substr($key, 0, 6 ) !== "prefs_") {
			continue;
		}
		
		/* check if exists */
		$entry = $db_content->get("se_options","*", [
			"option_key" =>  $key,
			"option_module" => $module
		]);
		
		if($entry['option_key'] != '') {


			$data = $db_content->update("se_options", [
				"option_value" =>  $val,
			], [
				"AND" => [
					"option_key" => $key,
					"option_module" => $module
				]
			]);
			
		} else {

			$data = $db_content->insert("se_options", [
				"option_value" =>  $val,
				"option_key" => $key,
				"option_module" => $module
			]);
			
		}
				
		
		
	}	
	
}



/**
 * get all user groups
 * return as array
 */

function get_all_groups() {
	
	global $db_user;
	
	$groups = $db_user->select("se_groups", "*", [
	"ORDER" => ["group_id" => "ASC"]
	]);
	
	return $groups;
}


/**
 * get all admins
 * return as array
 */

function get_all_admins() {

	global $db_user;
		
	$admins = $db_user->select("se_user", "*", [
	"user_class" => "administrator"
	]);
	
	return $admins;
}





/**
 * show all images
 * return array
 */

function get_all_images() {

	global $img_path;
	$images = array();

	$dir = "../$img_path";
	$scan_dir = array_diff(scandir($dir), array('..', '.','.DS_Store'));
	$types = array('jpg','jpeg','png','gif');
	
	foreach($scan_dir as $key => $file) {
		$suffix = substr($file, strrpos($file, '.') + 1);
			if(in_array($suffix, $types)) {
			$images[] = basename($file);
		}
	}
	 return $images;
}

/**
 * show all images from images folder
 * optional filter by prefix
 * return array
 */

function se_get_all_images($prefix='') {

	global $img_path;
	$images = array();

	$dir = "$img_path";
	$scan_dir = array_diff(scandir($dir), array('..', '.','.DS_Store'));
	$types = array('jpg','jpeg','png','gif');
	
	foreach($scan_dir as $key => $file) {
		$suffix = substr($file, strrpos($file, '.') + 1);
		
			if(in_array($suffix, $types)) {
			
				if($prefix != '') {
					if(substr(basename($file), 0,strlen($prefix)) !== $prefix) {
						continue;
					}
				}
			
				$images[] = basename($file);
		  
		  }
	}
	 return $images;
}

/**
 * show all images from images folder and it's subfolders
 * optional filter by prefix
 * return array
 */

function se_get_all_images_rec($prefix='',$dir=''): array {

	global $img_path;
	$images = array();
	
	if($dir == '') {
        $dir = "$img_path";
	}
	
	$scan_dir = array_diff(scandir($dir), array('..', '.','.DS_Store'));
	$types = array('jpg','jpeg','png','gif');
	
	foreach($scan_dir as $key => $file) {
		
		if(is_dir($dir . '/' . $file)) {
			$images[] = se_get_all_images_rec("$prefix",$dir . '/' . $file);
			continue;
		}
		$suffix = substr($file, strrpos($file, '.') + 1);
		
		if(in_array($suffix, $types)) {
			
			if($prefix != '') {
				if(substr(basename($file), 0,strlen($prefix)) !== $prefix) {
					continue;
				}
			}
			
			$images[] = $dir . '/' . $file;
		  
		}
	}
		
	
	$images = se_flatten_array($images);
	return $images;
}


/**
 * get all files from directory (recursive)
 * return array
 */

function se_scandir_rec($dir) { 
   
   $result = array(); 

   $cdir = scandir($dir); 
   foreach ($cdir as $key => $value) { 
      if(in_array($value,array('..', '.','.DS_Store','index.html'))) {
	      continue;
	    }
      if(is_dir($dir . '/' . $value)) {
	      $result[] = se_scandir_rec($dir . '/' . $value); 
      } else { 
        $result[] = $dir.'/'.$value; 
      } 
   } 
   $result = se_flatten_array($result);
   return $result; 
}

/**
 * get all (sub)directories from directory (recursive)
 * return array
 */

function se_get_dirs_rec($dir) { 
   
   $result = array(); 

   $cdir = scandir($dir); 
   foreach ($cdir as $key => $value) { 
      if(in_array($value,array('..', '.','.DS_Store','index.html'))) {
	      continue;
	    }
      if(is_dir($dir . '/' . $value)) {
	      $result[] = $dir.'/'.$value;
	      $result[] = se_get_dirs_rec($dir . '/' . $value); 
      }
   } 
   $result = se_flatten_array($result);
   return $result; 
}




function se_flatten_array(array $array) {
    $flattened_array = array();
    array_walk_recursive($array, function($a) use (&$flattened_array) { $flattened_array[] = $a; });
    return $flattened_array;
}



/**
 * converting bytes to KB, MB, GB
 * Snippet from PHP Share: http://www.phpshare.org
 */
 
function readable_filesize($bytes) {
  if($bytes >= 1073741824) {
    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
  } elseif ($bytes >= 1048576) {
      $bytes = number_format($bytes / 1048576, 2) . ' MB';
  } elseif ($bytes >= 1024) {
      $bytes = number_format($bytes / 1024, 2) . ' KB';
  } elseif ($bytes > 1) {
  	  $bytes = $bytes . ' bytes';
  } elseif ($bytes == 1) {
      $bytes = $bytes . ' byte';
  } else {
      $bytes = '0 bytes';
  }
  return $bytes;
}

/**
 * get size of a directory
 * returning bytes
 */

function se_dir_size($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : se_dir_size($each);
    }
    return $size;
}



/**
 * PRINT SYSTEM MESSAGE
 */

function print_sysmsg($msg) {

	$type = "{OKAY}";
	$pos = stripos($msg, $type);

	if($pos !== false) {
		$style = "alert alert-success";
	} else {
		$style = "alert alert-danger";
	}

	$msg = substr(strstr($msg, '}'), 2);
	echo '<div class="'.$style.' alert-dismissible" role="alert">';
	echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	echo $msg;
	echo '</div>';
}

/**
 * @param string $text
 * @param string $color success | danger | info
 * @return void
 */
function show_toast($text,$color='') {

    $time = se_format_datetime(time());
    $class = '';

    if($color == 'danger') {
        $class = 'text-bg-danger';
    } else if($color == 'success') {
        $class = 'text-bg-success';
    } else if($color == 'info') {
        $class = 'text-bg-info';
    }

    echo '<div class="toast-container position-fixed top-0 end-0 p-3">';
    echo '<div class="toast show alert-auto-close '.$class.'" role="alert" aria-live="polite" aria-atomic="true" data-bs-autohide="true" data-bs-delay="500">';
    echo '<div class="toast-header '.$class.'">';
    echo '<small>'.$time.'</small>';
    echo '<button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close">';
    echo '</div>';
    echo '<div class="toast-body">';
    echo $text;
    echo '</div>';
    echo '</div>';
    echo '</div>';
}





/**
 * Get Page Impression
 */

function get_page_impression($pid) {
	
	global $db_content;

	$counter = $db_content->get("se_pages", "page_hits", [
		"page_id" => $pid
	]);
		
	return $counter;
}



/**
 * show log entries
 * delete records that are older than 30 days
 */

function se_show_log($limit=10) {
	
	global $db_content;
	global $lang;
	$interval = time() - (30 * 86400); // 30 days
	$logs = '';

	$del = $db_content->delete("se_logs", [
	    "time[<]" => $interval
	]);

	$count = $del->rowCount();
	
	if($count > 0) {
        $logs .= '<div class="alert alert-info">Logs removed ('.$count.')</div>';
	}

	$result = $db_content->select("se_logs", "*", [
		"ORDER" => ["id" => "DESC"],
        "LIMIT" => $limit
	]);

	$cnt_result = count($result);

    if($cnt_result > 0) {

        $table = '<table class="table table-sm">';

        for ($i = 0; $i < $cnt_result; $i++) {

            $time = date("H:i:s", $result[$i]['time']);
            $date = date("d.m.Y", $result[$i]['time']);
            $log_priority = 'log_priority' . $result[$i]['priority'];

            $table .= '<tr>';
            $table .= '<td><span class="priority-indicator ' . $log_priority . '" title="' . $result[$i]['priority'] . '"></span></td>';
            $table .= '<td>' . $date . ' ' . $time . '</td>';
            $table .= '<td>' . $result[$i]['source'] . ' - ' . $result[$i]['entry'] . '</td>';
            $table .= '</tr>';

        }

        $table .= '</table>';

        $logs .= $table;

    } else {
        $logs .= '<div class="alert alert-secondary">'.$lang['msg_info_no_data_so_far'].'</div>';
	}

    return $logs;
}


/**
 * add new item to the feeds table
 */

function add_feed($title, $text, $url, $sub_id, $feed_name, $time = NULL) {
	
	global $db_content;
	$interval = time() - (30 * 86400); // 30 days

	if(is_null($time)) {
		$time = time();
	}
		
	// remove old entries
	$db_content->delete("se_feeds", [
	"feed_time[<]" => $interval
	]);
	// remove duplicates
	$db_content->delete("se_feeds", [
	"feed_subid" => $sub_id
	]);
	
	$db_content->insert("se_feeds", [
		"feed_subid" => "$sub_id",
		"feed_time" => "$time",
		"feed_name" => "$feed_name",
		"feed_title" => "$title",
		"feed_text" => "$text",
		"feed_url" => "$url"
	]);
}





/**
 * Generate XML Sitemap
 */

function generate_xml_sitemap() {

	global $se_base_url;
	global $db_content;
    global $se_prefs;
	
	$file = SE_PUBLIC."/sitemap.xml";
	$tpl_sitemap = file_get_contents('../acp/templates/sitemap.tpl');
	$tpl_sitemap_urlset = file_get_contents('../acp/templates/sitemap_urlset.tpl');

		$results = $db_content->select("se_pages", "*", [
			"AND" => [
				"page_status[!]" => ["draft","private","ghost"],
                "page_meta_robots[!~]" => ["AND" => ["noindex", "none","noarchive"]]
			],
			"ORDER" => [
				"page_lastedit" => "DESC"
			]
		]);
		
		$cnt_results = count($results);

		/* generate content for xml file */	
		$url_set = "";
		
		for($i=0;$i<$cnt_results;$i++) {

			$page_permalink = $results[$i]['page_permalink'];
			$page_lastedit = date("Y-m-d",$results[$i]['page_lastedit']);
			
			$link = $se_base_url . $page_permalink;
			
			$link = str_replace("/acp","",$link);
			
			$url_set = str_replace('{url}', $link, $tpl_sitemap_urlset);
			$url_set = str_replace('{lastmod}', $page_lastedit, $url_set);
			$url_set_list .= $url_set."\r\n";			
		}

		$sitemap = str_replace('{url_set}', $url_set_list, $tpl_sitemap);	
		file_put_contents($file, $sitemap, LOCK_EX);

}




/**
 * get custom columns from table se_pages
 * return array
 */

function get_custom_fields() {
	
	global $db_content;

	$customs_fields = array();
	
	$cf = $db_content->get("se_pages", "*");
	
	$cf = array_keys($cf);
	$cnt_cf = count($cf);
	
	for($i=0;$i<$cnt_cf;$i++) {
		if(substr($cf[$i],0,7) == "custom_") {
			$customs_fields[] = $cf[$i];
		}
	}
	return $customs_fields;

}


/**
 * get custom columns from table se_user
 * return array
 */

function get_custom_user_fields() {
	
	global $db_user;

	$customs_fields = array();
	
	$cf = $db_user->get("se_user", "*");

	$cf = array_keys($cf);
	$cnt_cf = count($cf);
	
	for($i=0;$i<$cnt_cf;$i++) {
		if(substr($cf[$i],0,7) == "custom_") {
			$customs_fields[] = $cf[$i];
		}
	}
	
	return $customs_fields;

}



/**
 * show editor's switch buttons
 * for plain text, code or wysiwyg
 */

function show_editor_switch($tn,$sub) {

	$btn_wysiwyg_link = "acp.php?tn=$tn&sub=$sub&editor=wysiwyg";
	$btn_code_link = "acp.php?tn=$tn&sub=$sub&editor=code";
	$btn_text_link = "acp.php?tn=$tn&sub=$sub&editor=plain";
	
	if($_SESSION['editor_class'] == "plain") {
		$btn_wysiwyg = 'btn btn-light btn-sm';
		$btn_text = 'btn btn-primary btn-sm disabled';
		$btn_code = 'btn btn-light btn-sm';
	} elseif($_SESSION['editor_class'] == "wysiwyg") {
		$btn_wysiwyg = 'btn btn-primary btn-sm disabled';
		$btn_text = 'btn btn-light btn-sm';
		$btn_code = 'btn btn-light btn-sm';
	} else {
		$btn_wysiwyg = 'btn btn-light btn-sm';
		$btn_text = 'btn btn-light btn-sm';
		$btn_code = 'btn btn-primary btn-sm disabled';
	}
	
	
	echo '<div class="btn-group btn-group-justified">';
	echo '<a href="'.$btn_wysiwyg_link.'" class="'.$btn_wysiwyg.'">WYSIWYG</a>';
	echo '<a href="'.$btn_text_link.'" class="'.$btn_text.'">Text</a>';
	echo '<a href="'.$btn_code_link.'" class="'.$btn_code.'">Code</a>';
	echo '</div>';
	
}



/**
 * show the first xx words of a string
 * return string
 */

function first_words($string,$nbr=5) {
	$short_string = implode(' ', array_slice(explode(' ', $string), 0, $nbr));
	
	if(strlen($short_string) < strlen($string)) {
		$short_string .= ' (...)';
	}
	
	return $short_string;
}


/**
 * Return the first n chars of a string (without tags).
 *
 * @param string $str
 * @param integer $length
 * @return string
 */
function se_return_first_chars($str,$length=200) {

    $str = strip_tags(htmlspecialchars_decode($str));
    if(strlen($str) > $length) {
        $ellipses = ' <small><i>(...)</i></small>';
        $last_blank_pos = strrpos(substr($str, 0, $length), ' ');
        if($last_blank_pos !== false) {
            $trimmed_string = substr($str, 0, $last_blank_pos);
        } else {
            $trimmed_string = substr($str, 0, $length);
        }
        $trimmed_string .= $ellipses;
        return $trimmed_string;
    } else {
        return $str;
    }
}



/**
 * get data from se_media
 * all files bei type f.e. 'image'
 */
 
 function se_get_all_media_data($type) {
	 
	global $db_content;
	
	$media_data = $db_content->select("se_media","*",[
		"AND" => [
			"media_type[~]" => "$type"
		],
		"ORDER" => [
			"media_upload_time" => "DESC"
			]
	]);
	
	return $media_data;
 }


 






/**
 * clear all thumbnails
 * and subdirectories in /content/images_tmb/ 
 */

function se_clear_thumbs_directory($dir=NULL) {

	if($dir == NULL) {
		$dir = '../content/images_tmb/';
	}
	
	/* check if we are in the thumbnail directory */
	if(substr($dir,0,22) != '../content/images_tmb/') {
		return 'Sorry. No permissions to delete in:' . $dir;
	} else {
		
   if(is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if(filetype($dir."/".$object) == "dir") {
         		se_clear_thumbs_directory($dir."/".$object);
         	} else {
	        	unlink($dir."/".$object);
         	}
       }
     }
     reset($objects);
     if($dir != '../content/images_tmb/') {
	     rmdir($dir);
     }
     
   }
		
	}

}


/**
 * delete data from se_media
 * by filename
 *
 */

function se_delete_media_data($filename) {
	
	global $db_content;
	
	$db_content->delete("se_media", [
		"AND" => [
		"media_file" => "$filename"
		]
	]);
	
	$record_msg = 'delete media data: <strong>'.basename($filename).'</strong>';
	record_log($_SESSION['user_nick'],$record_msg,"2");

}


/**
 * write data into se_media
 * check by file name if data already exists
 *
 * filename,title,notes, keywords, text, url, alt, lang,
 * credit, priority, license, lastedit
 * filesize, version, labels
 *
 * @param array $data
 * @return mixed|string
 */

function se_write_media_data($data) {


	global $db_content;
	global $languagePack;

    if(!is_array($data)) {
        return 'error';
    }


	if(!isset($data['lang']) OR ($data['lang'] == '')) {
		$lang = $languagePack;
	} else {
        $lang = $data['lang'];
    }

    if(!isset($data['lastedit']) OR ($data['lastedit'] == '')) {
        $lastedit = time();
    } else {
        $lastedit = $data['lastedit'];
    }
	
	$title = se_return_clean_value($data['title']);
	$notes = se_return_clean_value($data['notes']);
	$keywords = se_return_clean_value($data['keywords']);
    $url = se_return_clean_value($data['url']);
	$text = se_return_clean_value($data['text']);
	$alt = se_return_clean_value($data['alt']);
	$priority = (int) $data['priority'];
	$credit = se_return_clean_value($data['credit']);
	$license = se_return_clean_value($data['license']);
	$version = se_return_clean_value($data['version']);
    $filesize = (int) $data['filesize'];
	
	/* labels */
	if(is_array($data['labels'])) {
		sort($data['labels']);
		$string_labels = implode(",", $data['labels']);
	} else {
		$string_labels = "";
	}	
		
	$filetype = mime_content_type(realpath('assets'.$data['filename']));

    $saved_filename = '..'.$data['filename'];
	
	$cnt = $db_content->count("se_media", [
		"AND" => [
		    "media_file" => $saved_filename,
		    "media_lang" => $lang
		]
	]);
	
	$columns = [
		"media_title" => "$title",
		"media_notes" => "$notes",
		"media_keywords" => "$keywords",
		"media_text" => "$text",
		"media_alt" => "$alt",
		"media_url" => "$url",
		"media_lang" => "$lang",
		"media_priority" => "$priority",
		"media_credit" => "$credit",
		"media_license" => "$license",
		"media_version" => "$version",
		"media_filesize" => "$filesize",
		"media_lastedit" => "$lastedit",
		"media_type" => "$filetype",
		"media_labels" => "$string_labels"
	];
	
	if($cnt > 0) {
		$cnt_changes = $db_content->update("se_media", $columns, [
			"AND" => [
				"media_file" => $saved_filename,
				"media_lang" => "$lang"
			]
		]);
		
	} else {
		$columns["media_file"] = $saved_filename;
		$cnt_changes = $db_content->insert("se_media", $columns);
	}

	if($cnt_changes->rowCount() > 0) {
		return 'success';
	} else {
		return 'error';
	}

}


/**
 * remove duplicate entries from multidimensional array
 * we use this for se_media entries
 * because we only want one result per upload
 * not an result for every language
 * $array = multidimensional array
 * $key = key you want to check for duplicates
 *
 * https://www.php.net/manual/de/function.array-unique.php#116302
 *
 */

function se_unique_multi_array($array, $key) { 
    $temp_array = array(); 
    $i = 0; 
    $key_array = array(); 
    
    foreach($array as $val) { 
      if(!in_array($val[$key], $key_array)) { 
      	$key_array[$i] = $val[$key]; 
        $temp_array[$i] = $val; 
      } 
      $i++; 
    }
    
    // re-index
    $temp_array= array_values($temp_array);
    return $temp_array; 
} 






/**
 * get all labels
 * return as array
 */

function se_get_labels() {

	global $db_content;

	$customs_fields = array();
	$labels = $db_content->select("se_labels", "*");
	
	return $labels;
}


/**
 * generate an select image widget
 * we use input type checkbox
 *
 * @param array $images
 * @param array $seleced_img
 * @param string $prefix
 * @param integer $id
 * @return string
 */

function se_select_img_widget($images,$selected_img,$prefix='',$id=1) {

    global $lang;
    $path = '../public/assets';

    if(!array($selected_img)) {
        $cnt_selected_img = 0;
    } else {
        $selected_img = array_filter($selected_img);
        $cnt_selected_img = count($selected_img);
    }


    $images_container  = '<div class="scroll-container scroll-container-h500 p-1">';

    /* if we have selected images, show them first */
    if($cnt_selected_img > 0) {
        $images_container .= '<h6>'.$lang['label_images_selected'].' ('.$cnt_selected_img.')</h6>';
        $images_container .= '<div class="sortableListGroup list-group my-1">';
        foreach($selected_img as $sel_images) {
            $sel_images_src = str_replace('../',$path.'/',$sel_images);
            $sel_images_abs = str_replace('../','/',$sel_images);

            if(file_exists("$sel_images_src")) {
                $images_container .= '<div class="list-group-item p-1">';
                $images_container .= '<div class="image-checkbox image-checkbox-checked">';
                $images_container .= '<div class="row">';
                $images_container .= '<div class="col-3">';
                $images_container .= '<div class="image-select-preview image-select-preview-sm" style="background-image: url('.$sel_images_abs.')">';
                $images_container .= '</div>';
                $images_container .= '</div>';
                $images_container .= '<div class="col-9">';
                $images_container .= '<span class="input-group-text d-inline-block float-end" id="basic-addon1">';
                $images_container .= '<i class="bi bi-arrows-move" aria-hidden="true"></i>';
                $images_container .= '</span>';
                $images_container .= '<input name="picker'.$id.'_images[]" value="'.$sel_images.'" type="checkbox" checked>';
                $images_container .= '<div class="small text-muted">'.basename($sel_images).'</div>';
                $images_container .= '</div>';
                $images_container .= '</div>';
                $images_container .= '</div>';
                $images_container .= '</div>';
            }
        }
        $images_container .= '</div>';
    }

    $images_container .= '<input class="filter-images form-control" name="filter-images" placeholder="Filter ..." type="text">';
    $images_container .= '<div class="row g-1">';

    $cnt_images = count($images);

    for($i=0;$i<$cnt_images;$i++) {

        $img_filename = basename($images[$i]['media_file']);
        $img_filename_short = se_return_first_chars($img_filename,15);
        $image_name = $images[$i]['media_file'];
        $image_tmb_name = $images[$i]['media_thumb'];
        $lastedit = (int) $images[$i]['media_lastedit'];
        $lastedit_year = date('Y',$lastedit);
        $filemtime = $lastedit_year;

        if($prefix != '') {
            if((strpos($image_name, $prefix)) === false) {
                continue;
            }
        }

        if(file_exists($image_tmb_name)) {
            $preview = $image_tmb_name;
        } else {
            $preview = $image_name;
        }

        $preview = str_replace("../","/",$preview);

        /* new label for each year */
        $prev_image_ts = (int) $images[$i-1]['media_lastedit'];
        if(date('Y',$prev_image_ts) != $lastedit_year) {
            $images_container .= '<div class="col-12 mt-2"><div class="card p-1"><h6 class="m-0">'.$filemtime.'</h6></div></div>';
        }

        if(!in_array($image_name, $selected_img)) {
            $images_container .= '<div class="col col-lg-3" title="'.$img_filename.'">';
            $images_container .= '<div class="image-checkbox h-100">';
            $images_container .= '<div class="card h-100">';
            $images_container .= '<div class="card-body p-0">';
            $images_container .= '<div class="image-select-preview" style="background-image: url('.$preview.')"></div>';
            $images_container .= '<input name="picker'.$id.'_images[]" value="'.$image_name.'" type="checkbox">';
            $images_container .= '</div>';
            $images_container .= '<div class="text-muted small">'.$img_filename_short.'</div>';

            $images_container .= '</div>';
            $images_container .= '</div>';
            $images_container .= '</div>';
        }

    }

    $images_container .= '</div>';
    $images_container .= '</div>';

    return $images_container;

}


function se_list_gallery_thumbs($gid) {
	
	global $db_posts, $icon;
	$gid = (int) $gid;
	
	
	$date = $db_posts->get("se_posts","post_date", [
	"post_id" => $gid
	]);
	
	$filepath = SE_PUBLIC.'/assets/galleries/'.date('Y',$date).'/gallery'.$gid.'/*_tmb.jpg';
	$thumbs_array = glob("$filepath");
	arsort($thumbs_array);

	$thumbs = '';
	foreach($thumbs_array as $tmb) {

        $tmb_src = str_replace(SE_PUBLIC.'/assets/galleries/','/galleries/',$tmb);

		$thumbs .= '<div class="tmb">';
		$thumbs .= '<div class="tmb-preview"><img src="'.$tmb_src.'" class="img-fluid"></div>';
        $thumbs .= '<form hx-post="/admin-xhr/blog/write/">';
		$thumbs .= '<div class="tmb-actions d-flex btn-group">';
		$thumbs .= '<button type="submit" name="sort_gallery_tmb" value="'.$tmb.'" class="btn btn-sm btn-primary w-100">'.$icon['arrow_up'].'</button>';
		$thumbs .= '<button type="submit" name="delete_gallery_tmb" value="'.$tmb.'" class="btn btn-sm btn-danger w-50">'.$icon['trash_alt'].'</button>';
		$thumbs .= '</div>';
        $thumbs .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
		$thumbs .= '</form>';
        $thumbs .= '</div>';
	}
	
	
	$str = '';
	$str .= $thumbs;
	
	
	return $str;
		
}



function se_rename_gallery_image($thumb) {
	
	$timestring = microtime(true);
	
	$path_parts = pathinfo($thumb);
	$dir = $path_parts['dirname'].'/';
	$tmb = $dir.$path_parts['basename'];
	$img = str_replace("_tmb", "_img", $tmb);
	
	$new_tmb = $dir.$timestring.'_tmb.jpg';
	$new_img = $dir.$timestring.'_img.jpg';

	
	rename("$tmb", "$new_tmb");
	rename("$img", "$new_img");
	
}



function se_remove_gallery($id,$dir) {

	$fp = '../content/galleries/'.$dir.'/gallery'.$id.'/';
	$files = glob("$fp*jpg");

	foreach($files as $file) {
		unlink($file);
	}
	
	rmdir($fp);
	
	
}



/**
 * please use the new function se_create_thumbnail()
 */
 
function se_create_tmb($img_src, $tmb_name, $tmb_width, $tmb_height, $tmb_quality) {
	
	global $img_tmb_path;
	
	/* thumbnail directories */
	$tmb_dir = '../'.$img_tmb_path;
	$tmb_dir_year = $tmb_dir.'/'.date('Y',time());
	$tmb_destination = $tmb_dir_year.'/'.date('m',time());
	if(!is_dir($tmb_dir_year)) {
		mkdir($tmb_dir_year);
	}
	if(!is_dir($tmb_destination)) {
		mkdir($tmb_destination);
	}

	$arr_image_details	= GetImageSize("$img_src");
	$original_width		= $arr_image_details[0];
	$original_height	= $arr_image_details[1];
	$a = $tmb_width / $tmb_height;
    $b = $original_width / $original_height;
	
	
	if ($a<$b) {
     $new_width = $tmb_width;
     $new_height	= intval($original_height*$new_width/$original_width);
  } else {
     $new_height = $tmb_height;
     $new_width	= intval($original_width*$new_height/$original_height);
  }
	
	if(($original_width <= $tmb_width) AND ($original_height <= $tmb_height)) {
	  $new_width = $original_width;
	  $new_height = $original_height;
  }
  
	if($arr_image_details[2]==1) { $imgt = "imagegif"; $imgcreatefrom = "imagecreatefromgif";  }
	if($arr_image_details[2]==2) { $imgt = "imagejpeg"; $imgcreatefrom = "imagecreatefromjpeg";  }
	if($arr_image_details[2]==3) { $imgt = "imagepng"; $imgcreatefrom = "imagecreatefrompng";  }
	
	
	if($imgt) { 
		$old_image	= $imgcreatefrom("$img_src");
		$new_image	= imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
		imagejpeg($new_image,"$tmb_destination/$tmb_name",$tmb_quality);
		imagedestroy($new_image);
	}
	
}


/**
 * @param string $img_src path to original image
 * @param string $tmb_name name of the new thumbnail
 * @param string $tmb_dir directory where the thumb should be saved
 * @param integer $tmb_width size
 * @param integer $tmb_height size
 * @param integer $tmb_quality quality
 * @return void
 */

function se_create_thumbnail($img_src, $tmb_name, $tmb_dir=NULL, $tmb_width=100, $tmb_height=100, $tmb_quality=50) {
	
	global $img_tmb_path;

    $arr_image_details	= GetImageSize("$img_src");
    $original_width		= $arr_image_details[0];
    $original_height	= $arr_image_details[1];

    if($original_width < 1) {
        return;
    }

    $a = $tmb_width / $tmb_height;
    $b = $original_width / $original_height;


		
	/* thumbnail directories */
	if($tmb_dir == NULL OR $tmb_dir == '') {
		$dir = '../'.$img_tmb_path;
		$dir_year = $tmb_dir.'/'.date('Y',time());
		$tmb_destination = $tmb_dir_year.'/'.date('m',time());
	} else {
		$tmb_destination = $tmb_dir;
	}
	
	if(!is_dir($tmb_destination)) {
		mkdir($tmb_destination,0777,true);
	}
	

	
	
	if ($a<$b) {
     $new_width = $tmb_width;
     $new_height	= intval($original_height*$new_width/$original_width);
  } else {
     $new_height = $tmb_height;
     $new_width	= intval($original_width*$new_height/$original_height);
  }
	
	if(($original_width <= $tmb_width) AND ($original_height <= $tmb_height)) {
	  $new_width = $original_width;
	  $new_height = $original_height;
  }
  
	if($arr_image_details[2]==1) { $imgt = "imagegif"; $imgcreatefrom = "imagecreatefromgif";  }
	if($arr_image_details[2]==2) { $imgt = "imagejpeg"; $imgcreatefrom = "imagecreatefromjpeg";  }
	if($arr_image_details[2]==3) { $imgt = "imagepng"; $imgcreatefrom = "imagecreatefrompng";  }
	
	
	if($imgt) { 
		$old_image	= $imgcreatefrom("$img_src");
		$new_image	= imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
		imagejpeg($new_image,"$tmb_destination/$tmb_name",$tmb_quality);
		imagedestroy($new_image);
	}
}


/**
 * @param $file
 * @return array
 */
function se_parse_docs_file($file): array {

    global $languagePack;
    $Parsedown = new Parsedown();

    if(is_file($file)) {
        $src = file_get_contents($file);

        if(str_starts_with($src, "---")) {
            $src_content = explode('---',$src);
            $header_length = strlen($src_content[1])+6;
            $content = substr($src, $header_length);
        } else {
            $content = $src;
        }

        $path_info = pathinfo($file);
        $dir = $path_info['dirname'];

        // look for included .md files
        $content = preg_replace_callback(
            '/\{inc=(.*?)\}/si',
            function ($m) use ($dir) {
                global $languagePack;
                global $file;
                $inc_file = "$dir/$m[1]";
                if(is_file($inc_file)) {
                    $inc_file_content = file_get_contents("$inc_file");
                    $inc_file_content_array = explode('---',$inc_file_content);
                    $inc_content = substr($inc_file_content, strlen($inc_file_content_array[1])+6);
                    return $inc_content;
                } else {
                    return '';
                }
            },
            $content
        );

        $content = preg_replace_callback(
            '/\{link=(.*?)\}/sim',
            function ($m) use ($dir) {
                global $languagePack;
                $link = '<a class="" hx-get="/admin-xhr/docs/read/?file='.$m[1].'" hx-target="#helpModal">'.$m[1].'</a>';
                return $link;
            },
            $content
        );

        $parsed_header = Spyc::YAMLLoadString($src_content[1]);
        $parsed_content = $Parsedown->text("$content");

        $filemtime = filemtime($file);
    } else {
        $parsed_header['title'] = 'FILE NOT FOUND ('.$file.')';
        $parsed_content = 'FILE NOT FOUND ('.$file.')';
    }

    $parsed['header'] = $parsed_header;
    $parsed['title'] = $parsed_header['title'];
    $parsed['content'] = $parsed_content;
    $parsed['filemtime'] = $filemtime;
    $parsed['filename_orig'] = basename($file);
    $parsed['filepath_orig'] = $file;
    return $parsed;
}

/**
 * @param string $file
 * @param string|null $text
 * @param string|null $section
 * @return string
 */
function se_print_docs_link(string $file, string $text = null, string $section = null): string {
    global $icon, $lang;
    $link = '';
    $title = $lang['label_show_help'];

    if ($text == null or $text == 'icon') {
        $text = $icon['question_circle'];
    }

    if ($section == null or $section == '') {
        $section = 'swiftyedit';
    }

    $link = '<button data-bs-toggle="modal"
                    data-bs-target="#helpModal"
                        hx-get="/admin-xhr/docs/read/"
                        hx-vals=\'{"file":"' . $file . '","section":"' . $section . '"}\'
                        hx-target="#helpModal"
                        hx-trigger="click"
                        class="btn btn-link" title="' . $title . '">' . $text . '</button>';
    return $link;
}

/**
 * @param string $file
 * @param string|null $text
 * @return string
 */
function se_print_docs_tip(string $file, string $text = null) :string {
    global $icon, $languagePack;

    if ($text == null or $text == 'icon') {
        $text = $icon['question_circle'];
    }

    $doc_filepath = '../acp/docs/'.$languagePack.'/tooltips/'.$file;
    $show_file = se_parse_docs_file($doc_filepath);

    $tooltip = '<span 
                    class="d-inline-block"
                    data-bs-toggle="popover"
                    data-bs-title="'.$show_file['title'].'" data-bs-content="'.$show_file['content'].'"
                    data-bs-html="true"
                    data-bs-trigger="hover">'.$text.'</span>';

    return $tooltip;
}


/**
 * display pagination
 *
 * @param string $query will be set as href="$query" - {page} will be replaced
 * @param integer $pages_limit how many numbers should be displayed (max)
 * @param integer $align 1 = left, 2 = center, 3 = right
 * @param integer $size 1 = sm, 2 = default, 3 = lg
 * @param integer $items_cnt number of items
 * @param integer $sql_start_nbr start from the sql query
 * @param integer $items_per_page how many items per page
 * @return string
 */

function se_return_pagination(string $query, int $items_cnt, int $sql_start_nbr, int $items_per_page, int $pages_limit=10, int $align=1, int $size=2): string
{

    $cnt_pages = ceil($items_cnt / $items_per_page);
    if($cnt_pages < 2) {
        // no pages, no pagination
        return '';
    }

    $nextPage = $sql_start_nbr+$items_per_page;
    $lastPage = ($cnt_pages*$items_per_page)-$items_per_page;
    if($nextPage >= $lastPage) {
        $nextPage = $lastPage;
    }
    $prevPage = max(0,$sql_start_nbr-$items_per_page);

    $next_query = str_replace("{page}","$nextPage",$query);
    $prev_query = str_replace("{page}","$prevPage",$query);

    if($align == 1) {
        $align_class = '';
    } else if($align == 2) {
        $align_class = 'justify-content-center';
    } else if($align == 3) {
        $align_class = 'justify-content-end';
    }

    if($size == 1) {
        $size_class = 'pagination-sm';
    } else if($size == 2) {
        $size_class = '';
    } else if($size == 3) {
        $size_class = 'pagination-lg';
    }

    $pagination = '<nav aria-label="Pagination">';
    $pagination .= '<ul class="pagination '.$align_class.' '.$size_class.'">';
    $pagination .= '<li class="page-item">
                    <a class="page-link" href="'.$prev_query.'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
                  </li>';

    $activePage = ceil(($sql_start_nbr*$items_per_page)/$cnt_pages);
    $activePage = ceil(($sql_start_nbr/$items_per_page)+1);

    if($activePage < 2) {
        $activePage = 1;
    }

    $pagination_start = $activePage-($pages_limit/2);
    if($pagination_start < 1) {
        $pagination_start = 1;
    }
    $pagination_end = $activePage+($pages_limit/2);
    if($pagination_end > $cnt_pages) {
        $pagination_end = $cnt_pages;
    }
    
    for($i=1;$i<=$cnt_pages;$i++) {

        $active_class = '';
        //$page_nbr = $i+1;
        $thisPage = ($i*$items_per_page)-$items_per_page;

        if($activePage == $i) {
            $active_class = 'active';
        }

        if($i > $pagination_end) {
            continue;
        }
        if($i < $pagination_start) {
            continue;
        }

        $href = str_replace("{page}","$thisPage",$query);

        $pagination .= '<li class="page-item '.$active_class.'">
                        <a class="page-link" href="'.$href.'">'.$i.'</a>
                    </li>';
    }

    $pagination .= '<li class="page-item">
                    <a class="page-link" href="'.$next_query.'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                </li>';
    $pagination .= '</ul>';
    $pagination .= '</nav>';

    return $pagination;
}

function se_print_pagination($url, $pages, $active_page,$steps=10,$classes=NULL,$post_name='pagination'): string {

    if($pages < 2) {
        return '';
    }

    $class_pagination = 'justify-content-center';

    if(is_array($classes)) {
        if($classes['class_pagination'] != '') {
            $class_pagination = $classes['class_pagination'];
        }
    }

    $vals = [
        'csrf_token' => $_SESSION['token']
    ];

    $pagination = '<nav aria-label="Pagination" hx-params="'.$post_name.',csrf_token" hx-vals=\''.json_encode($vals).'\' hx-swap="none">';
    $pagination .= '<ul class="pagination '.$class_pagination.'">';

    // jump to the first page
    $pagination .= '<li class="page-item">';
    $pagination .= '<button class="page-link ms-1" hx-post="'.$url.'"  hx-trigger="click" name="'.$post_name.'" value="0"><i class="bi bi-arrow-bar-left"></i></button>';
    $pagination .= '</li>';

    // jump to the previous page
    $previous_page = max($active_page - 1, 0);

    $pagination .= '<li class="page-item">';
    $pagination .= '<button class="page-link" hx-post="'.$url.'" hx-trigger="click" name="'.$post_name.'" value="'.$previous_page.'"><i class="bi bi-arrow-left-short"></i></button>';
    $pagination .= '</li>';

    // loop
    for($i=0;$i<$pages;$i++) {

        $show_number = $i+1;
        $show_number_start = $active_page-($steps/2);
        $show_number_end = $active_page+($steps/2);
        // skip pages which doesn't match the range from $nbr_show_pages
        if(($show_number < $show_number_start) || ($show_number > $show_number_end)) {
            continue;
        }

        $active = '';
        if($i == $active_page) {
            $active = 'active';
        }
        $pagination .= '<li class="page-item">';
        $pagination .= '<button class="page-link '.$active.'" hx-post="'.$url.'" hx-trigger="click" name="'.$post_name.'" value="'.$i.'" hx-swap="none">'.($i+1).'</button>';
        $pagination .= '</li>';
    }

    // jump to the next page
    $next_page = min($active_page + 1, $pages - 1);
    $pagination .= '<li class="page-item">';
    $pagination .= '<button class="page-link" hx-post="'.$url.'" hx-trigger="click" name="'.$post_name.'" value="'.$next_page.'" hx-swap="none"><i class="bi bi-arrow-right-short"></i></button>';
    $pagination .= '</li>';
    // jump to the last page
    $pagination .= '<li class="page-item">';
    $pagination .= '<button class="page-link" hx-post="'.$url.'" hx-trigger="click" name="'.$post_name.'" value="'.($pages-1).'" hx-swap="none"><i class="bi bi-arrow-bar-right"></i></button>';
    $pagination .= '</li>';

    $pagination .= '</ul>';
    $pagination .= '</nav>';

    return $pagination;
}

function se_get_my_presets() {
    global $db_user;
    $presets = $db_user->get("se_user","user_acp_settings",["user_id"=>$_SESSION['user_id']]);
    return json_decode($presets,true);
}