<?php

/**
 *
 * global variables
 * @var string $lang_sign from languages/%lang%/index.php
 * @var string $lang_desc from languages/%lang%/index.php
 *
 */

include_once 'functions_addons.php';
include_once 'functions_cache.php';
include_once 'functions_shop.php';


/**
 * Permission a module's XHR readers/writers (/admin-xhr/<module>/read|write/)
 * require - the same right the module's UI router (acp/core/<module>/router.php)
 * checks. Without this, a gated module was only hidden in the UI, while its
 * XHR endpoints stayed reachable for every administrator.
 *
 * Modules not listed here are open to every administrator, same as their UI.
 * addons/ is deliberately not listed: its list is open to every admin, plugins
 * use addons/ for their own backend actions, and the sensitive actions
 * (install, update, delete) check drm_acp_sensitive_files themselves.
 *
 * @param string $query e.g. "users/write/"
 * @return string|null required permission, null if none
 */
function se_acp_xhr_permission(string $query): ?string {

    // personal settings of the logged-in user (core/users/settings-writer.php),
    // open to every backend user - checked before the users/ module below
    if (str_starts_with($query, 'users/settings/')) {
        return null;
    }

    $module_permissions = [
        'users/' => 'drm_acp_user',
        'settings/' => 'drm_acp_system',
        'blog/' => 'drm_can_publish',
        'shop/' => 'drm_can_publish',
        'events/' => 'drm_can_publish',
        'update/' => 'drm_acp_sensitive_files'
    ];

    foreach ($module_permissions as $prefix => $permission) {
        if (str_starts_with($query, $prefix)) {
            return $permission;
        }
    }

    return null;
}

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
 * @return void
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
 * @return void
 * @deprecated not used by the core, will be removed in a later version
 */
function se_json_response(array $data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}


/**
 * get all installed language files
 * @return array
 */

function get_all_languages(): array {

    $d = SE_ROOT.'/languages/';
	$scanned_directory = array_diff(scandir($d), array('..', '.','.DS_Store'));

    $arr_lang = [];
    foreach($scanned_directory as $lang_folder) {
        $lang_file = "$d/$lang_folder/index.php";
        if(is_file($lang_file)) {
            $lang_data = [];
            require $lang_file;  // $lang_sign, $lang_desc

            $arr_lang[] = [
                'lang_sign' => $lang_sign ?? '',
                'lang_desc' => $lang_desc ?? '',
                'lang_folder' => $lang_folder
            ];
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
 * write preferences table se_options
 *
 * @param array $data
 * @param string $module
 * @return void
 */

function se_write_option($data,$module): void
{
	
	global $db_content;

	foreach($data as $key => $val) {
		
		if($key == '') {
			continue;
		}
		
		if(substr($key, 0, 6 ) !== "prefs_") {
			continue;
		}
		
		// check if exists
		$entry = $db_content->get("se_options","*", [
			"option_key" =>  $key,
			"option_module" => $module
		]);
		
		if($entry['option_key'] != '') {

			$db_content->update("se_options", [
				"option_value" =>  $val,
			], [
				"AND" => [
					"option_key" => $key,
					"option_module" => $module
				]
			]);
			
		} else {

			$db_content->insert("se_options", [
				"option_value" =>  $val,
				"option_key" => $key,
				"option_module" => $module
			]);

		}
	}

	// se_get_preferences() serves the "se" module from a cache file (see
	// app/functions/functions.php) - keep it in sync right away instead of
	// leaving it stale until the next unrelated rebuild
	if ($module === 'se') {
		se_build_preferences_cache();
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
	$types = array('jpg','jpeg','png','gif','webp');
	
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
      // skip dotfiles (., .., .DS_Store, .htaccess, ...) and placeholder files
      if(str_starts_with($value, '.') || $value === 'index.html') {
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
      // skip dotfiles/hidden folders and placeholder files
      if(str_starts_with($value, '.') || $value === 'index.html') {
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
 * @deprecated not used by the core, will be removed in a later version -
 *             use show_toast() instead
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
 * Delete log entries that are older than $days.
 *
 * Runs on every dashboard load, so the oldest entry is checked first: that
 * only reads one row through the primary key, the delete has to scan the
 * table. There is no cron in the core yet (see TODO.md section 12).
 */

function se_cleanup_logs(int $days = 90): void {

	global $db_content;

	$cutoff = time() - ($days * 86400);

	$oldest = $db_content->get("se_logs", "time", [
		"ORDER" => ["id" => "ASC"]
	]);

	if($oldest === null || (int) $oldest >= $cutoff) {
		return;
	}

	$db_content->delete("se_logs", [
		"time[<]" => $cutoff
	]);
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
 * @param string $mode
 * @return void
 */
function se_generate_xml_sitemap($mode): void
{

    global $se_base_url,$db_content,$db_posts,$twig;

    if($mode == '') { return; }

    $se_base_url = rtrim($se_base_url, '/');

    $file_sitemap_index = SE_PUBLIC."/sitemap.xml";
    $file_sitemap_pages = SE_PUBLIC . "/sitemap-pages.xml";
    $file_sitemap_products = SE_PUBLIC . "/sitemap-products.xml";
    $file_sitemap_posts = SE_PUBLIC . "/sitemap-posts.xml";

    if ($mode == 'pages') {

        $pages_data = $db_content->select("se_pages", "*", [
            "AND" => [
                "page_status[!]" => ["draft", "private", "ghost"],
                "page_meta_robots[!~]" => ["AND" => ["noindex", "none", "noarchive"]]
            ],
            "ORDER" => [
                "page_lastedit" => "DESC"
            ]
        ]);

        $entries = [];
        foreach ($pages_data as $page) {

            // pages with a redirect must not be listed in the sitemap
            if (trim((string) $page['page_redirect']) !== '') {
                continue;
            }

            $entries[] = [
                'url' => $se_base_url.'/'.$page['page_permalink'],
                'lastmod' => date("Y-m-d", $page['page_lastedit'])
            ];
        }

        se_write_xml_sitemap_file($file_sitemap_pages, $entries);
    }

    if ($mode == 'products') {

        $products_data = $db_posts->select("se_products", "*", [
            "AND" => [
                "type" => "p",
                "status" => "1"
            ],
            "ORDER" => [
                "lastedit" => "DESC"
            ]
        ]);

        $entries = [];
        foreach ($products_data as $product) {
            $entries[] = [
                'url' => $se_base_url.'/'.$product['main_catalog_slug'].$product['slug'],
                'lastmod' => date("Y-m-d", $product['lastedit'])
            ];
        }

        se_write_xml_sitemap_file($file_sitemap_products, $entries);
    }

    if ($mode == 'posts') {
        $posts_data = $db_posts->select("se_posts", "*", [
            "AND" => [
                "post_type" => ["m", "g", "i","v","f"],
                "post_status" => "1"
            ],
            "ORDER" => [
                "post_lastedit" => "DESC"
            ]
        ]);

        // detail page per language - same lookup as acp/core/blog/data-writer.php
        $post_target_pages = [];

        $entries = [];
        foreach ($posts_data as $post) {

            if ($post['post_hide_detail_page'] == 1) {
                continue;
            }

            $post_lastedit = date("Y-m-d", $post['post_lastedit']);

            // prefer the stored canonical url (built at save time from detail
            // page + slug + id), but only if it points to this site
            $link = (string) $post['post_canonical_url'];
            if ($link === '' || !str_starts_with($link, $se_base_url.'/')) {
                $post_lang = $post['post_lang'];
                if (!isset($post_target_pages[$post_lang])) {
                    $target = $db_content->get("se_pages", "page_permalink", [
                        "AND" => [
                            "page_type_of_use" => "display_post",
                            "page_language" => $post_lang
                        ]
                    ]);
                    if (empty($target)) {
                        $target = $db_content->get("se_pages", "page_permalink", [
                            "AND" => [
                                "page_posts_types[~]" => "m",
                                "page_language" => $post_lang
                            ]
                        ]);
                    }
                    $post_target_pages[$post_lang] = (string) $target;
                }
                $filename = str_replace("/", "", $post['post_slug']) . '-' . $post['post_id'] . '.html';
                $link = $se_base_url.'/'.$post_target_pages[$post_lang].$filename;
            }

            $entries[] = [
                'url' => $link,
                'lastmod' => $post_lastedit
            ];
        }
        se_write_xml_sitemap_file($file_sitemap_posts, $entries);

    }

    // create index - only list sitemaps which exist and contain entries
    $index_entries = [];
    foreach ([$file_sitemap_pages, $file_sitemap_products, $file_sitemap_posts] as $file) {
        if (!is_file($file)) {
            continue;
        }
        $index_entries[] = [
            'url' => $se_base_url.'/'.basename($file),
            'lastmod' => date("Y-m-d", filemtime($file))
        ];
    }

    $sitemap_index = $twig->render('sitemap/index.xml.twig', ['entries' => $index_entries]);
    file_put_contents($file_sitemap_index, $sitemap_index, LOCK_EX);
}

/**
 * write a single sitemap file
 * if there are no entries, an existing file is removed
 * so it will not be listed in the sitemap index
 *
 * @param string $file
 * @param array $entries list of ['url' => ..., 'lastmod' => ...]
 * @return void
 */
function se_write_xml_sitemap_file(string $file, array $entries): void
{
    global $twig;

    if (empty($entries)) {
        if (is_file($file)) {
            unlink($file);
        }
        return;
    }

    // Twig's autoescaping turns "&" in urls into "&amp;" as required by XML
    $sitemap_data = $twig->render('sitemap/urlset.xml.twig', ['entries' => $entries]);
    file_put_contents($file, $sitemap_data, LOCK_EX);
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
function se_return_first_chars($str,$length=200,$html_ellipsis=true) {

    $str = strip_tags(htmlspecialchars_decode($str));
    if(strlen($str) > $length) {
        $last_blank_pos = strrpos(substr($str, 0, $length), ' ');
        if($last_blank_pos !== false) {
            $trimmed_string = substr($str, 0, $last_blank_pos);
        } else {
            $trimmed_string = substr($str, 0, $length);
        }
        // markup is opt-out so callers can render the trimmed text autoescaped
        // and add their own (static) ellipsis instead of relying on |raw
        if($html_ellipsis) {
            $trimmed_string .= ' <small><i>(...)</i></small>';
        }
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
	
	se_log_content('delete', 'media', null, basename($filename), (string) $filename);

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
		se_log_content('update', 'media', null, basename($saved_filename), $saved_filename);
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
 * @deprecated not used by the core, will be removed in a later version -
 *             use array_values(array_column($array, null, $key)) instead
 *             (keeps the last entry per key instead of the first)
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

		$thumbs .= '<div class="tmb draggable" data-id="'.$tmb.'">';
		$thumbs .= '<div class="tmb-drag-handle" title="Drag to reorder">'.$icon['grip_vertical'].'</div>';
		$thumbs .= '<div class="tmb-preview"><img src="'.$tmb_src.'" class="img-fluid"></div>';
        $thumbs .= '<form hx-post="/admin-xhr/blog/write/">';
		$thumbs .= '<div class="tmb-actions d-flex btn-group">';
		$thumbs .= '<button type="submit" name="delete_gallery_tmb" value="'.$tmb.'" class="btn btn-sm btn-danger w-100">'.$icon['trash_alt'].'</button>';
		$thumbs .= '</div>';
        $thumbs .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
		$thumbs .= '</form>';
        $thumbs .= '</div>';
	}


	// wrap everything in a drag-and-drop sortable container; drag order is
	// submitted to se_reorder_gallery_tmbs() via JS (see backend.js)
	$str = '<div class="gallery-thumbs-sortable" data-gallery-id="'.$gid.'" data-csrf="'.$_SESSION['token'].'">';
	$str .= $thumbs;
	$str .= '</div>';


	return $str;

}



function se_rename_gallery_image($thumb, $timestring = null) {

	if($timestring === null) {
		$timestring = microtime(true);
	}

	$path_parts = pathinfo($thumb);
	$dir = $path_parts['dirname'].'/';
	$tmb = $dir.$path_parts['basename'];
	$img = str_replace("_tmb", "_img", $tmb);

	$new_tmb = $dir.$timestring.'_tmb.jpg';
	$new_img = $dir.$timestring.'_img.jpg';


	rename("$tmb", "$new_tmb");
	rename("$img", "$new_img");

}



/**
 * Apply a drag-and-drop reorder of a gallery's thumbnails.
 *
 * The gallery thumbnail order is derived purely from the filename timestamp
 * (see se_list_gallery_thumbs(), which sorts the files with arsort()) - there
 * is no separate "sort order" column in the database. To keep existing
 * galleries working exactly as before, this reuses that same mechanism: it
 * simply re-applies se_rename_gallery_image() to every thumbnail in the
 * requested order, handing out strictly descending timestamps so the first
 * entry in $order ends up with the newest (and therefore first-sorted)
 * filename.
 *
 * @param int   $gid   gallery / post id
 * @param array $order list of thumbnail paths (as sent back by the client),
 *                      top to bottom in the desired new order
 */
function se_reorder_gallery_tmbs($gid, $order) {

	global $db_posts;
	$gid = (int) $gid;

	if(!is_array($order) || empty($order)) {
		return;
	}

	$date = $db_posts->get("se_posts","post_date", [
	"post_id" => $gid
	]);

	$gallery_dir = SE_PUBLIC.'/assets/galleries/'.date('Y',$date).'/gallery'.$gid.'/';

	// only accept thumbnails that really exist inside this gallery's folder
	$valid_order = [];
	foreach($order as $tmb) {
		$tmb = se_filter_filepath($tmb);
		if(str_starts_with($tmb, $gallery_dir) && str_ends_with($tmb, '_tmb.jpg') && is_file($tmb)) {
			$valid_order[] = $tmb;
		}
	}

	$timestamp = microtime(true);
	foreach($valid_order as $tmb) {
		se_rename_gallery_image($tmb, $timestamp);
		$timestamp -= 0.01;
	}

}



/**
 * delete the images of a gallery post and its directory
 * public/assets/galleries/{year of post_date}/gallery{id}/ (see acp/core/widgets/upload.php)
 *
 * @param int|string $id post_id of the gallery
 * @param int|string $dir year of the post_date
 */
function se_remove_gallery($id,$dir) {

	$fp = SE_PUBLIC.'/assets/galleries/'.(int) $dir.'/gallery'.(int) $id.'/';
	if(!is_dir($fp)) {
		return;
	}

	// the directory belongs to this gallery only, remove everything in it
	foreach(glob($fp.'*') ?: [] as $file) {
		if(is_file($file)) {
			unlink($file);
		}
	}

	rmdir($fp);
}




/**
 * @param $file
 * @return array
 */
function se_parse_docs_file($file): array {

    global $languagePack,$Parsedown;

    // not every caller sets up the parser (e.g. plugin readers via xhr)
    if(!$Parsedown instanceof ParsedownExtra) {
        $Parsedown = new ParsedownExtra();
    }

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
                $target = $m[1];
                // a bare filename inside theme/plugin docs links to the same theme/plugin
                if(!str_contains($target, '/') && preg_match('#/(themes|plugins)/([^/]+)/docs/[^/]+$#', $dir, $ctx)) {
                    $target = '/'.$ctx[1].'/'.$ctx[2].'/'.$target;
                }
                $link = '<a class="" hx-get="/admin-xhr/docs/read/?file='.$target.'" hx-target="#helpModal">'.$m[1].'</a>';
                return $link;
            },
            $content
        );

        // shared Bootstrap contextual types for {alert:} and {badge:}
        $bs_context_types = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

        // {alert:info}...{/alert} -> Bootstrap alert box, content still parsed as markdown
        $content = preg_replace_callback(
            '/\{alert:([a-z0-9_-]+)\}/i',
            function ($m) use ($bs_context_types) {
                $type = in_array(strtolower($m[1]), $bs_context_types, true) ? strtolower($m[1]) : 'info';
                return '<div class="alert alert-'.$type.'" role="alert" markdown="1">';
            },
            $content
        );
        $content = preg_replace('/\{\/alert\}/i', '</div>', $content);

        // {badge:info}text{/badge} -> inline Bootstrap badge
        $content = preg_replace_callback(
            '/\{badge:([a-z0-9_-]+)\}(.*?)\{\/badge\}/si',
            function ($m) use ($bs_context_types) {
                $type = in_array(strtolower($m[1]), $bs_context_types, true) ? strtolower($m[1]) : 'info';
                return '<span class="badge text-bg-'.$type.'">'.$m[2].'</span>';
            },
            $content
        );

        // colorize the breadcrumb arrow used between <kbd> tags (e.g. <kbd>Backend</kbd> ▶ <kbd>Snippets</kbd>)
        $content = str_replace('▶', '<span class="doc-arrow">▶</span>', $content);

        // a broken front matter must not break the help page
        try {
            $parsed_header = \Symfony\Component\Yaml\Yaml::parse($src_content[1] ?? '');
        } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
            $parsed_header = [];
        }
        if (!is_array($parsed_header)) {
            $parsed_header = [];
        }
        $parsed_header['title'] ??= '';
        $parsed_content = $Parsedown->text("$content");

        $parsed_content = preg_replace_callback(
            '/class="language-([^"]+)"/',
            fn($m) => 'class="language-' . strtolower($m[1]) . '"',
            $parsed_content
        );

        // mark inline shortcodes like [snippet]name[/snippet] or [plugin=...] for separate styling
        $parsed_content = preg_replace(
            '#(?<!<pre>)<code>(\[/?(?:snippet|plugin|include|script)\b[^<]*)</code>#i',
            '<code class="code-shortcode">$1</code>',
            $parsed_content
        );

        // mark template file names (single token ending in .tpl) for separate styling
        $parsed_content = preg_replace(
            '#(?<!<pre>)<code>([^<\s]+\.tpl)</code>#i',
            '<code class="code-template">$1</code>',
            $parsed_content
        );

        $filemtime = filemtime($file);
    } else {
        $parsed_header['title'] = 'FILE NOT FOUND ('.$file.')';
        $parsed_content = 'FILE NOT FOUND ('.$file.')';
        $filemtime = null;
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
 * sections of a core docs file (docs/v2/{lang}/), e.g. as help texts next to the settings -
 * so the text is only written once, in the docs
 *
 * a section is a heading with an {#anchor} and everything up to the next heading
 * of the same or a higher level, rendered like the help modal (se_parse_docs_file())
 *
 * @param string $file name of the docs file, e.g. 08-00-settings.md
 * @param array $anchors the anchors of the sections
 * @return array anchor => {title, content (html), link (button that opens the docs there)},
 *   anchors that are not found are missing
 */
function se_docs_sections(string $file, array $anchors): array {

    global $languagePack, $languagePackFallback, $Parsedown;

    $docs_root = SE_ROOT.'docs/v2';
    $doc_filepath = $docs_root.'/'.$languagePack.'/'.basename($file);
    if(!is_file($doc_filepath)) {
        $doc_filepath = $docs_root.'/'.($languagePackFallback ?? 'en').'/'.basename($file);
    }
    if(!is_file($doc_filepath)) {
        return [];
    }

    // se_parse_docs_file() uses the global parser
    if(!$Parsedown instanceof ParsedownExtra) {
        $Parsedown = new ParsedownExtra();
    }
    $html = se_parse_docs_file($doc_filepath)['content'];

    $dom = new DOMDocument();
    $previous_errors = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);
    $xpath = new DOMXPath($dom);

    $sections = [];
    foreach($anchors as $anchor) {
        if(!preg_match('/^[a-z0-9_-]+$/i', $anchor)) {
            continue;
        }
        $heading = $xpath->query('//*[@id="'.$anchor.'"]')->item(0);
        if(!$heading instanceof DOMElement || !preg_match('/^h([1-6])$/i', $heading->nodeName, $match)) {
            continue;
        }

        $content = '';
        for($node = $heading->nextSibling; $node !== null; $node = $node->nextSibling) {
            if($node instanceof DOMElement && preg_match('/^h([1-6])$/i', $node->nodeName, $next) && (int) $next[1] <= (int) $match[1]) {
                break;
            }
            $content .= $dom->saveHTML($node);
        }

        $sections[$anchor] = [
            'title' => trim($heading->textContent),
            'content' => trim($content),
            'link' => se_print_docs_link($file, null, $anchor, 'btn btn-link btn-sm p-0')
        ];
    }

    return $sections;
}

/**
 * @param string $file
 * @param string|null $text
 * @param string|null $anchor
 * @param string $class CSS classes of the button
 * @return string
 */
function se_print_docs_link(string $file, ?string $text = null, ?string $anchor = null, string $class = 'btn btn-link'): string {
    global $icon, $lang;
    $title = $lang['label_show_help'];

    if ($text == null || $text == 'icon') {
        $text = $icon['question_circle'];
    }

    $vals = ['file' => $file . ($anchor ? '#' . $anchor : '')];
    // hex-encode quotes, the JSON is placed in a single-quoted attribute
    $hx_vals = json_encode($vals, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);

    return '<button data-bs-toggle="modal"
                data-bs-target="#helpModal"
                hx-get="/admin-xhr/docs/read/"
                hx-vals=\'' . $hx_vals . '\'
                hx-target="#helpModal"
                hx-trigger="click"
                class="' . $class . '" title="' . $title . '" aria-label="' . $title . '">' . $text . '</button>';
}

/**
 * Help link to a doc of a theme (themes/{theme}/docs/{lang}/{file}).
 * Inside a theme, pass basename(dirname(__DIR__)) from php/*.php as $theme.
 *
 * @param string $theme theme folder name
 * @param string $file e.g. 'index.md' or 'readme.md'
 * @param string|null $text
 * @param string|null $anchor
 * @param string $class CSS classes of the button
 * @return string
 */
function se_print_theme_docs_link(string $theme, string $file = 'index.md', ?string $text = null, ?string $anchor = null, string $class = 'btn btn-link'): string {
    return se_print_docs_link('/themes/' . basename($theme) . '/' . basename($file), $text, $anchor, $class);
}

/**
 * Help link to a doc of a plugin (plugins/{plugin}/docs/{lang}/{file}).
 *
 * @param string $plugin plugin folder name
 * @param string $file e.g. 'index.md' or 'readme.md'
 * @param string|null $text
 * @param string|null $anchor
 * @param string $class CSS classes of the button
 * @return string
 */
function se_print_plugin_docs_link(string $plugin, string $file = 'index.md', ?string $text = null, ?string $anchor = null, string $class = 'btn btn-link'): string {
    return se_print_docs_link('/plugins/' . basename($plugin) . '/' . basename($file), $text, $anchor, $class);
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

/**
 * Plugin directories the logged-in user has marked as favorites
 * (stored per user in user_acp_settings, toggled in users/settings-writer.php).
 *
 * @return list<string>
 */
function se_get_my_favorite_plugins(): array {
    $favorites = se_get_my_presets()['fav_plugins'] ?? [];
    if(!is_array($favorites)) {
        return [];
    }
    return array_values(array_filter($favorites, 'is_string'));
}

/**
 * Read the current OPcache state into a flat array for display (Dashboard
 * "OPcache" card, see acp/core/dashboard/data-reader.php). Tolerates OPcache
 * being off entirely, or opcache_get_status()/opcache_get_configuration()
 * being blocked via php.ini's disable_functions - both just report as
 * unavailable instead of throwing.
 *
 * @return array{available: bool, enabled?: bool, cache_full?: bool,
 *     memory_used_mb?: float, memory_free_mb?: float, memory_wasted_mb?: float,
 *     memory_used_percent?: float, num_cached_scripts?: int,
 *     max_accelerated_files?: int, hit_rate?: float,
 *     validate_timestamps?: bool, start_time?: int|null}
 */
function se_get_opcache_status(): array {

    if(!function_exists('opcache_get_status')) {
        return ['available' => false];
    }

    // false = skip the per-script listing, we only need the aggregate numbers
    $status = @opcache_get_status(false);

    if($status === false) {
        return ['available' => false];
    }

    $config = function_exists('opcache_get_configuration') ? @opcache_get_configuration() : false;
    $directives = $config['directives'] ?? [];
    $memory = $status['memory_usage'] ?? [];
    $stats = $status['opcache_statistics'] ?? [];

    $used = $memory['used_memory'] ?? 0;
    $free = $memory['free_memory'] ?? 0;
    $wasted = $memory['wasted_memory'] ?? 0;
    $total = $used + $free + $wasted;

    return [
        'available' => true,
        'enabled' => (bool) ($status['opcache_enabled'] ?? false),
        'cache_full' => (bool) ($status['cache_full'] ?? false),
        'memory_used_mb' => round($used / 1048576, 1),
        'memory_free_mb' => round($free / 1048576, 1),
        'memory_wasted_mb' => round($wasted / 1048576, 1),
        'memory_used_percent' => $total > 0 ? round((($used + $wasted) / $total) * 100, 1) : 0,
        'num_cached_scripts' => $stats['num_cached_scripts'] ?? 0,
        'max_accelerated_files' => $directives['opcache.max_accelerated_files'] ?? 0,
        'hit_rate' => round($stats['opcache_hit_rate'] ?? 0, 1),
        'validate_timestamps' => (bool) ($directives['opcache.validate_timestamps'] ?? true),
        'start_time' => $stats['start_time'] ?? null,
    ];
}

/**
 * categories for the checkbox list in the edit forms (products, blog, events),
 * rendered by the categories() macro in components/form/macros.twig
 *
 * @param array $checked_hashes cat_hash values of the assigned categories
 * @return array list of id, hash, name, description, flag, checked
 */
function se_get_category_checklist(array $checked_hashes): array {

    $categories = [];
    foreach(se_get_categories() as $cat) {
        // stored html-encoded, Twig escapes on output
        $description = html_entity_decode((string) ($cat['cat_description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $categories[] = [
            'id' => (int) $cat['cat_id'],
            'hash' => $cat['cat_hash'],
            'name' => html_entity_decode((string) $cat['cat_name'], ENT_QUOTES, 'UTF-8'),
            'description' => trim(first_words($description, 10)) ?: '...',
            'flag' => return_language_flag_src($cat['cat_lang']),
            'checked' => in_array($cat['cat_hash'], $checked_hashes)
        ];
    }
    return $categories;
}

/**
 * labels for the checkboxes in the edit forms (products, blog, events),
 * rendered by the labels() macro in components/form/macros.twig
 *
 * @param array $checked_ids label_id values of the assigned labels
 * @return array list of id, color, title, checked
 */
function se_get_label_checklist(array $checked_ids): array {

    global $se_labels;

    // $se_labels is set for full pages (acp/index.php), xhr requests load the labels here
    $all_labels = $se_labels ?? se_get_labels();

    $labels = [];
    foreach($all_labels as $label) {
        $labels[] = [
            'id' => $label['label_id'],
            'color' => $label['label_color'],
            // stored html-encoded, Twig escapes on output
            'title' => html_entity_decode((string) $label['label_title'], ENT_QUOTES, 'UTF-8'),
            'checked' => in_array((string) $label['label_id'], $checked_ids)
        ];
    }
    return $labels;
}

/**
 * Medoo conditions for the global filter (status, labels, languages) of the
 * module lists, the filter is set in the offcanvas (layout/nav-top-filter.twig)
 * and stored as json in the session (acp/core/widgets/data-writer.php)
 *
 * the global status values are 1 public, 2 draft, 3 private, 4 ghost, 5 redirect,
 * $status_map translates them to the stored values of the module - a selected
 * status that the module does not know matches no entry
 *
 * @param string $lang_column column of the language code
 * @param string $status_column column of the status
 * @param string $labels_column column of the label ids (comma separated)
 * @param array $status_map global status => stored status
 * @return array conditions for the "AND" of a select, empty without filter
 */
function se_get_global_filter_where(string $lang_column, string $status_column, string $labels_column, array $status_map): array {

    $decode_filter = static fn(string $key): array => array_values(array_filter(
        (array) (json_decode($_SESSION[$key] ?? '', true) ?? []),
        static fn($value) => $value !== '' && $value !== null
    ));

    $where = [];

    $languages = $decode_filter('global_filter_languages');
    if($languages) {
        $where[$lang_column] = $languages;
    }

    $status = $decode_filter('global_filter_status');
    if($status) {
        $stored_status = [];
        foreach($status as $value) {
            if(isset($status_map[$value])) {
                $stored_status[] = (string) $status_map[$value];
            }
        }
        // only unknown status selected: an impossible value, the list stays empty
        $where[$status_column] = $stored_status ?: ['-1'];
    }

    // labels are stored as "1,3,12" - match the whole id, label 1 must not match 12
    $labels = array_map('intval', $decode_filter('global_filter_label'));
    if($labels) {
        $patterns = [];
        foreach($labels as $id) {
            $patterns[] = "$id,%";
            $patterns[] = "%,$id";
            $patterns[] = "%,$id,%";
        }
        $where["OR #global_filter_labels"] = [
            $labels_column => array_map('strval', $labels),
            $labels_column.'[~]' => $patterns
        ];
    }

    return $where;
}
