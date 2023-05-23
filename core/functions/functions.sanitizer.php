<?php


/**
 * CLEAN VARS // URL PARAMETERS
 */

function clean_vars($var) {
	$chars = array('<', '>', '\\', '/', '=','..'); 
	$var = str_replace($chars, "", $var);
	$var = strip_tags($var);
	return $var;
}

function clean_filename($str) {
	$str = strtolower($str);
	$a = array('ä','ö','ü','ß',' - ',' + ',' / ','/'); 
	$b = array('ae','oe','ue','ss','-','-','-','-');
	$str = str_replace($a, $b, $str);
	$str = preg_replace('/\s/s', '_', $str);  // replace blanks -> '_'
	$str = preg_replace('/[^a-z0-9_-]/isU', '', $str); // only a-z 0-9
	$str = trim($str); 
	return $str; 
}

function se_filter_filepath($str) {
	$str = strip_tags($str);
	$remove_chars = array('<','>','\\','=','@','(',')',' ',',','%','');
	$str = preg_replace('/\s/s', '_', $str);
	$str = str_replace($remove_chars, "", $str);
	return $str; 
}

function se_return_clean_value($string) {
	$string = stripslashes($string);
	$remove_chars = array('$','`','{','}');
	$string = htmlentities($string, ENT_QUOTES, "UTF-8");
	$string = str_replace($remove_chars, "", $string);
	return $string;
}

function se_clean_permalink($str) {
	$str = stripslashes($str);
	$str = strip_tags($str);
	$str = strtolower($str);
	$a = array('ä','ö','ü','ß',' + ','//','(',')',';','\'','\\','.','`','<','>','$'); 
	$b = array('ae','oe','ue','ss','-','/','','','','','','','','','','');
	$str = str_replace($a, $b, $str);
	$str = preg_replace('/\s/s', '_', $str);  // replace blanks -> '_'
	$str = htmlentities($str, ENT_QUOTES, "UTF-8");
	$str = trim($str);
	
	return $str; 
}

function se_clean_query($str) {
	$str = stripslashes($str);
	$str = strip_tags($str);
	$str = strtolower($str);
	
	/* remove unsecure chars */
	$remove_chars = array('<','>','\\',';','@','(',')','`',',','%','$');
	$str = str_replace($remove_chars, "", $str);
	
	$a = array('ä','ö','ü','ß','+','//'); 
	$b = array('ae','oe','ue','ss','-','/');
	$str = str_replace($a, $b, $str);
	
	$str = preg_replace('/\s/s', '_', $str);  // replace blanks -> '_'
	$str = htmlentities($str, ENT_QUOTES, "UTF-8");
	$str = trim($str);
	
	return $str; 	
}


/**
 * sanitize user inputs
 */

function sanitizeUserInputs($str,$type='str',$flags=NULL) {

	if($type == 'str') {
		$str = trim($str);	
		$str = strip_tags($str);
		$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}
	
	return $str;

}

/**
 * remove tags [include] [script] [plugin] and [snippet]
 */

function clean_visitors_input($text) {

	$text = preg_replace("/\[snippet\](.*?)\[\/snippet\]/si","",$text);
	$text = preg_replace("/\[script\](.*?)\[\/script\]/si","",$text);
	$text = preg_replace("/\[include\](.*?)\[\/include\]/si","",$text);
	$text = preg_replace("/\[plugin=(.*?)\](.*?)\[\/plugin\]/si","",$text);
    $text = preg_replace("/\[image=(.*?)\](.*?)\[\/image\]/si","",$text);
    $text = preg_replace("/\[file=(.*?)\](.*?)\[\/file\]/si","",$text);

    $text = sanitizeUserInputs($text,"str","");
	
	return $text;

}

/**
 * sanitize inputs for pages
 * we use this for new pages or update pages
 *
 * @param array $data $_POST Data
 * @return array
 */
function se_sanitize_page_inputs($data) {

    global $se_prefs;

    foreach($data as $key => $val) {

        // main content
        if($key == 'page_content') {
            $sanitized[$key] = $val;
        }

        // strings
        if($key == 'page_title' || $key == 'page_linkname' || $key == 'page_custom_id'
            || $key == 'page_classes' || $key == 'page_custom_classes'
            || $key == 'page_meta_keywords' || $key == 'page_meta_author'
            || $key == 'page_meta_description' || $key == 'page_modul'
            || $key == 'page_language' || $key == 'page_status' || $key == 'page_type_of_use'
            || $key == 'page_funnel_uri' || $key == 'page_target' || $key == 'page_template_stylesheet') {
            $sanitized[$key] = se_return_clean_value($val);
        }

        // filenames
        if($key == 'page_hash') {
            $sanitized[$key] = clean_filename($val);
        }

        // urls
        if($key == 'page_permalink' || $key == 'page_permalink_short' || $key == 'page_redirect') {
            $sanitized[$key] = se_clean_permalink($val);
        }

        // integers
        if($key == 'page_priority' || $key == 'page_categories_mode' || $key == 'page_comments' || $key == 'editpage'
            || $key == 'page_redirect_code') {
            $sanitized[$key] = (int) $val;
        }

        // thumbnails
        if($key == 'picker1_images') {
            if(count($data['picker1_images']) > 1) {
                $page_thumbnail = implode("<->", $_POST['picker1_images']);
            } else {
                $pt = $_POST['picker1_images'];
                $page_thumbnail = $pt[0];
            }
            $sanitized['page_thumbnail'] = se_return_clean_value($page_thumbnail);
        }

        // password
        if($key == 'page_psw') {
            if($val != '') {
                $sanitized['page_psw'] = md5($val);
            }
        }
        if($key == 'page_psw_reset') {
            if($val == 'reset') {
                $sanitized['page_psw'] = '';
            }
        }

        // labels
        if($key == 'set_page_labels') {
            if(is_array($val)) {
                sort($val);
                $sanitized['page_labels'] = implode(",", $val);
            }
        }

        // categories
        if($key == 'set_page_categories') {
            if(is_array($val)) {
                sort($val);
                $sanitized['page_categories'] = implode(",", $val);
            }
        }

        // usergroups
        if($key == 'set_usergroup') {
            if(is_array($val)) {
                sort($val);
                $sanitized['page_usergroup'] = implode(",", $val);
            }
        }

        // set_authorized_admins
        if($key == 'set_authorized_admins') {
            if(is_array($val)) {
                sort($val);
                $sanitized['page_authorized_users'] = implode(",", $val);
            }
        }

        // page meta robots
        if($key == 'page_meta_robots') {
            if(is_array($val)) {
                sort($val);
                $sanitized['page_meta_robots'] = implode(",", $val);
            }
        }

        // select template
        if($key == 'select_template') {
            $tpl_parts = explode("<|-|>",$val);
            $sanitized['page_template'] = $tpl_parts[0];
            $sanitized['page_template_layout'] = $tpl_parts[1];
        }

        // post categories
        if($key == 'page_post_categories') {
            $sanitized['page_posts_categories'] = implode(",", $val);
        }

        // post types
        if($key == 'page_post_types') {
            if(is_array($val)) {
                $sanitized['page_posts_types'] = implode(",", $val);
            }
        }

        // custom columns
        if(str_starts_with($key,"custom_one_")) {
            $sanitized[$key] = se_return_clean_value($val);
        }
        if(str_starts_with($key,"custom_text_")) {
            $sanitized[$key] = se_return_clean_value($val);
        }
        if(str_starts_with($key,"custom_wysiwyg_")) {
            $sanitized[$key] = $val;
        }

        // addon_values
        if($key == 'addon_values') {
            if(is_array($val)) {
                foreach($val as $k => $v) {
                    $addon_values[$k] = htmlentities(stripslashes($v), ENT_QUOTES);
                }
                $sanitized['page_addon_string'] = json_encode($addon_values,JSON_UNESCAPED_UNICODE);
            }
        }

        // theme values
        if($key == 'theme_values') {
            if(is_array($val)) {
                foreach($val as $k => $v) {
                    $theme_values[$k] = htmlentities(stripslashes($v), ENT_QUOTES);
                }
                $sanitized['page_template_values'] = json_encode($theme_values,JSON_UNESCAPED_UNICODE);
            }
        }


    }

    $sanitized['page_version'] = (int) $data['page_version']+1;
    $sanitized['page_version_date'] = time();
    $sanitized['page_lastedit'] = time();
    $sanitized['page_meta_date'] = time();
    $sanitized['page_lastedit_from'] = $_SESSION['user_nick'];

    // page sort
    if($data['page_position'] == "portal") {
        $sanitized['page_sort'] = "portal";
    } else if($data['page_position'] == "mainpage") {
        $sanitized['page_sort'] = (int) $data['page_order'];
    } else if($data['page_position'] == "null") {
        $sanitized['page_sort'] = "";
    } else {
        $page_order = (int) $data['page_order'];
        if(strlen($page_order) < $se_prefs['prefs_pagesort_minlength']) {
            $page_order = str_pad($page_order, $se_prefs['prefs_pagesort_minlength'], "0", STR_PAD_LEFT);
        }
        $sanitized['page_sort'] = $data['page_position'].'.'.$page_order;
    }

    // resets
    if(!is_array($data['set_authorized_admins'])) {
        $sanitized['page_authorized_users'] = "";
    }
    if(!is_array($data['set_usergroup'])) {
        $sanitized['page_usergroup'] = "";
    }
    if(!is_array($data['set_page_categories'])) {
        $sanitized['page_categories'] = "";
    }
    if(!is_array($data['set_page_labels'])) {
        $sanitized['page_labels'] = "";
    }
    if(!is_array($data['page_post_types'])) {
        $sanitized['page_posts_types'] = "";
    }
    if(!is_array($data['page_post_categories'])) {
        $sanitized['page_posts_categories'] = "";
    }
    if(!is_array($data['theme_values'])) {
        $sanitized['page_template_values'] = "";
    }


    return $sanitized;
}