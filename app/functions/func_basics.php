<?php


/**
 * check if user_id exists in usergroup
 * @param mixed $user_id
 * @param string $user_group
 * @return string
 */
function is_user_in_group(mixed $user_id, string $user_group): string {

	global $db_user;
	
	$result = $db_user->get("se_groups", ["group_name","group_user"], [
			"group_name" => $user_group
	]);
	  
	$arr_users = explode(" ", $result['group_user']);

	if(in_array($user_id,$arr_users)) {
		$in_group = "true";
	} else {
		$in_group = "false";
	}

	return $in_group;
}



/**
 * buffer scripts placed in the plugin folder
 * @param string $script filename of the script
 * @param string|null $parameters query
 */

function buffer_script(string $script, string $parameters=NULL) {

    $parameter = '';
    $buffer = '';

	if($parameters !== NULL) {
		$parameter = parse_str(html_entity_decode($parameters),$output);
        foreach($output as $plugin_key => $plugin_value) {
            ${$plugin_key} = $plugin_value;
        }
	}

	ob_start();
	if(is_file(SE_ROOT."plugins/$script/index.php")) {
		include SE_ROOT."plugins/$script/index.php";
	}

	$content = ob_get_clean();
	$buffer = $parameter . $content;
	
	return $buffer;
}


/**
 * get the image data from $db_content
 * if parameter data = array return only data
 * if no parameter is set, return the image data styled with tpl file image.tpl
 *
 * @param string $image filename
 * @param mixed $parameters
 */

function se_get_images_data($image,$parameters=NULL) {

	global $db_content;
	global $se_template;
	global $languagePack;
	
	if($parameters !== NULL) {
		$parameter = parse_str(html_entity_decode($parameters),$output);
	}
	foreach($output as $key => $val) {
		$$key = $val;
	}
	
	$imageData = $db_content->get("se_media", "*", [
			"AND" => [
			"media_file[~]" => "%$image",
			"media_lang" => "$languagePack"
			]
	]);
	
	if($data == 'array') {
		return $imageData;
	}
	
	$img_src = str_replace('../content/images/', '/content/images/', $imageData['media_file']);
	$tpl = file_get_contents('./styles/'.$se_template.'/templates/image.tpl');
	$tpl = str_replace('{$image_src}', $img_src, $tpl);
	$tpl = str_replace('{$image_title}', $imageData['media_title'], $tpl);
	$tpl = str_replace('{$image_alt}', $imageData['media_alt'], $tpl);
	$tpl = str_replace('{$image_caption}', $imageData['media_text'], $tpl);
	$tpl = str_replace('{$image_license}', $imageData['media_license'], $tpl);
	$tpl = str_replace('{$image_credits}', $imageData['media_credits'], $tpl);
	$tpl = str_replace('{$image_priority}', $imageData['media_priority'], $tpl);
	$tpl = str_replace('{$image_link_class}', $aclass, $tpl);
	$tpl = str_replace('{$image_class}', $iclass, $tpl);
	
	return $tpl;
	
}

/**
 * @param string $file filename
 * @param mixed $parameters
 * @return array|false|string|string[]
 */

function se_get_files_data($file,$parameters=NULL) {

	global $db_content, $se_template, $languagePack, $swifty_slug;

	if($parameters !== NULL) {
		$parameter = parse_str(html_entity_decode($parameters),$output);
	}
	foreach($output as $key => $val) {
		$$key = $val;
	}
	
	$fileData = $db_content->get("se_media", "*", [
			"AND" => [
			    "media_file[~]" => "%$file",
			    "media_lang" => "$languagePack"
			]
	]);

    $form_action = $swifty_slug;
    $form_action = str_replace('//','/',$form_action);
	
	$file_src = str_replace('../content/files/', '/files/', $fileData['media_file']);
	$tpl = file_get_contents(SE_PUBLIC.'/assets/themes/'.$se_template.'/templates/download.tpl');
    $tpl = str_replace('{$form_Action}', $form_action, $tpl);
    $tpl = str_replace('{$csrf_token}', $_SESSION['token'], $tpl);
	$tpl = str_replace('{$file_src}', $fileData['media_file'], $tpl);
	$tpl = str_replace('{$file_title}', $fileData['media_title'], $tpl);
	$tpl = str_replace('{$file_alt}', $fileData['media_alt'], $tpl);
	$tpl = str_replace('{$file_caption}', html_entity_decode($fileData['media_text']), $tpl);
	$tpl = str_replace('{$file_license}', $fileData['media_license'], $tpl);
    $tpl = str_replace('{$file_version}', $fileData['media_version'], $tpl);
	$tpl = str_replace('{$file_credits}', $fileData['media_credit'], $tpl);
	$tpl = str_replace('{$file_priority}', $fileData['media_priority'], $tpl);
    if($fileData['media_classes'] != '') {
        $tpl = str_replace('{$file_class}', $fileData['media_classes'], $tpl);
    }
	return $tpl;
	
}

/**
 * @param string $mod name of addon
 * @param mixed|null $params
 * @return mixed
 */

function se_global_mod_snippets(string $mod, mixed $params=NULL): mixed {

    $mod_str = '';

	if($params !== NULL) {
		$parameter = parse_str(html_entity_decode($params),$output);
	}
	
    if(is_file(SE_ROOT.'/plugins/'.$mod.'/global/snippets.php')) {
        include SE_ROOT.'/plugins/'.$mod.'/global/snippets.php';
    }
	
	return $mod_str;
}


/**
 * find [include] [script] [plugin] and [snippet]
 * except codes within <pre> … </pre> or <code> … </code>
 *
 * @param string $text
 * @return string
 */
	 
function text_parser($text) {

	global $shortcodes, $languagePack;

	if(!is_string($text)) {
		return;
	}

    /* remove <p> tag from shortcodes */
	$text = str_replace('<p>[', '[', $text);
	$text = str_replace(']</p>', ']', $text);

	if($text == '') {
		return;
	}

    /* don't replace within <pre> tags */
    if(preg_match_all('#\<pre.*?\>(.*?)\</pre\>#', $text, $matches)) {
        $match = $matches[0];
        foreach($match as $k => $v) {
            $o = $match[$k];
            $v = str_replace(array('[',']'),array('&#91','&#93'),$v);
            $text = str_replace($o, $v, $text);
        }
    }

    /* don't replace within <code> tags */
    if(preg_match_all('#\<code.*?\>(.*?)\</code\>#', $text, $matches)) {
        $match = $matches[0];
        foreach($match as $k => $v) {
            $o = $match[$k];
            $v = str_replace(array('[',']'),array('&#91','&#93'),$v);
            $text = str_replace($o, $v, $text);
        }
    }

    /* if the theme has an own text parser in styles/theme/php/index.php */
    if(function_exists('theme_text_parser')) {
        $text = theme_text_parser($text);
    }

    $text = preg_replace_callback(
        '/\[snippet\](.*?)\[\/snippet\]/si',
        function ($m) {
            global $languagePack;
            se_store_admin_helper('s',$m[1]);
            return se_get_textlib($m[1],$languagePack,'content');
        },
        $text
    );

    $text = preg_replace_callback(
        '/\[snippet=(.*?)\](.*?)\[\/snippet\]/si',
        function ($m) {
            global $languagePack;
            $tpl = 'content';

            if($m[2] == 'tpl') {
                $tpl = 'tpl';
            }
            se_store_admin_helper('s',$m[1]);
            return se_get_textlib($m[1],$languagePack,"$tpl");
        },
        $text
    );

    $text = preg_replace_callback(
        '/\[snippet=(.*?)\]/si',
        function ($m) {
            global $languagePack;
            se_store_admin_helper('s',$m[1]);
            return se_get_textlib($m[1],$languagePack,'content');
        },
        $text
    );

    /* replace all shortcodes */
    if(is_array($shortcodes)) {
        foreach($shortcodes as $k => $v) {

            $text = str_replace($v['snippet_shortcode'], $v['snippet_content'], $text,$count);
            if($count > 0) {
                se_store_admin_helper('sc',$v['snippet_shortcode']);
            }
        }
    }
	
	$text = preg_replace_callback(
	    '/\[include\](.*?)\[\/include\]/s',
	    function ($m) {
		   return file_get_contents("./content/plugins/$m[1]");
	    },
	    $text
	);
 
	$text = preg_replace_callback(
	    '/\[script\](.*?)\[\/script\]/s',
	    function ($m) {
		   return buffer_script($m[1]);
	    },
	    $text
	);
	
	$text = preg_replace_callback(
	    '/\[plugin=(.*?)\](.*?)\[\/plugin\]/si',
	    function ($m) {
		    se_store_admin_helper('p',$m[1]);
				return buffer_script($m[1],$m[2]);
	    },
	    $text
	);
	
	$text = preg_replace_callback(
	    '/\[image=(.*?)\](.*?)\[\/image\]/si',
	    function ($m) {
		    se_store_admin_helper('i',$m[1]);
				return se_get_images_data($m[1],$m[2]);
	    },
	    $text
	);

	$text = preg_replace_callback(
	    '/\[file=(.*?)\](.*?)\[\/file\]/si',
	    function ($m) {
		    se_store_admin_helper('f',$m[1]);
				return se_get_files_data($m[1],$m[2]);
	    },
	    $text
	);
			
	$text = preg_replace_callback(
	    '/\[mod=(.*?)\](.*?)\[\/mod\]/si',
	    function ($m) {
		   return se_global_mod_snippets($m[1],$m[2]);
	    },
	    $text
	);

	return $text;
}

/**
 * We store possibly existing plugins or snippets ...
 * so we can enable faster access from frontend
 * use smarty variable {$admin_helpers} in frontend
 *
 * $trigger p 	= plugin
 * 			s 	= snippet
 *			i|f	= media file / image or file from se_media
 *          prod = product
 * $lang = language
 */
function se_store_admin_helper($trigger,$val) {
	
	global $languagePack;
	
	/* skip this function for visitors */
	if(!isset($_SESSION['user_class']) OR $_SESSION['user_class'] !== 'administrator') {
		return;
	}

	if(!isset($_SESSION['se_admin_helpers'])) {
		$_SESSION['se_admin_helpers'] = array();
	}
	
	$store = $_SESSION['se_admin_helpers'];
	
	/* add a plugin */
	if($trigger == 'p') {
        $plugin_name = htmlentities($val);
        $stored_plg = '<a href="/admin/addons/plugin/'.$plugin_name.'/start/" class="btn btn-secondary w-100">'.$plugin_name.'</a>';
		$store['plugin'][] = $stored_plg;
	}
	
	/* add a image */
	if($trigger == 'i') {
		$store['images'][] = $val;
	}
	
	/* add a file */
	if($trigger == 'f') {
		$store['files'][] = $val;
	}

    /* add a product */
    if($trigger == 'prod') {

        $product_id = (int) $val;
        $get_product_data = se_get_product_data($product_id);
        if(is_array($get_product_data)) {
            $prod_id = (int) $get_product_data['id'];
            $stored_prod = '<form action="/admin/shop/edit/" method="POST" class="d-inline">';
            $stored_prod .= '<button class="btn btn-sm btn-secondary m-1" name="product_id" value="'.$prod_id.'">';
            $stored_prod .= htmlentities($get_product_data['title']);
            $stored_prod .= '</button>';
            $stored_prod .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
            $stored_prod .= '</form>';
            $store['products'][] = $stored_prod;
        }
    }
	
	/* add a snippet */
	if($trigger == 's') {
		
		$snippet_data = se_get_textlib($val,$languagePack,'all');
		
		$stored_snippet = '<form action="/admin/snippets/edit/" method="POST" class="d-inline">';
		$stored_snippet .= '<button class="btn btn-sm btn-secondary m-1">'.$val.'</button>';
		$stored_snippet .= '<input type="hidden" name="snippet_id" value="'.$snippet_data['snippet_id'].'">';
		$stored_snippet .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
		$stored_snippet .= '</form>';
		
		$store['snippet'][] = $stored_snippet;
		
	}
		
	$_SESSION['se_admin_helpers'] = $store;	
}


/**
 * Generate cryptographically secure random strings
 * from https://gist.github.com/raveren/5555297
 */

function random_text( $type = 'alnum', $length = 8 ) {
	switch ( $type ) {
		case 'alnum':
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'alpha':
			$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'hexdec':
			$pool = '0123456789abcdef';
			break;
		case 'numeric':
			$pool = '0123456789';
			break;
		case 'nozero':
			$pool = '123456789';
			break;
		case 'distinct':
			$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
		default:
			$pool = (string) $type;
			break;
	}


	$crypto_rand_secure = function ( $min, $max ) {
		$range = $max - $min;
		if ( $range < 0 ) return $min; // not so random...
		$log    = log( $range, 2 );
		$bytes  = (int) ( $log / 8 ) + 1; // length in bytes
		$bits   = (int) $log + 1; // length in bits
		$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ( $rnd >= $range );
		return $min + $rnd;
	};

	$token = "";
	$max   = strlen( $pool );
	for ( $i = 0; $i < $length; $i++ ) {
		$token .= $pool[$crypto_rand_secure( 0, $max )];
	}
	return $token;
}


/**
 * get avatar via email address
 *
 * @param $user_mail
 * @return string
 */

function get_avatar($user_mail) {

	global $se_include_path;
	global $se_template;

	$mail_hash = md5($user_mail);
	$avatar_str = "$se_include_path/styles/$se_template/images/user_icon.jpg";

	if(file_exists("content/avatars/$mail_hash".".png")) {
		$avatar_str = "$se_include_path/content/avatars/$mail_hash".".png";
	}

	return $avatar_str;
}


/**
 * get user avatar via md5(user_name)
 *
 * @param $user_name
 * @return string
 */

function get_avatar_by_username($user_name) {

	global $se_include_path;
	global $se_template;

	$avatar_hash = md5($user_name);
	$avatar_str = "$se_include_path/styles/$se_template/images/user_icon.jpg";

	if(file_exists("content/avatars/$avatar_hash".".png")) {
		$avatar_str = "$se_include_path/content/avatars/$avatar_hash".".png";
	}

	return $avatar_str;
}



/**
 * send a notification to admin
 */

function mailto_admin($subject,$message) {

	global $prefs_mailer_adr;
	global $prefs_mailer_name;
	
	$subject = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $subject );
	$message = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $message );

	require_once("lib/Swift/lib/swift_required.php");
	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);
	$msg = Swift_Message::newInstance()
		->setSubject("SwiftyEdit Notification - $subject")
  		->setFrom(array("$prefs_mailer_adr" => "SwiftyEdit Notification"))
  		->setTo(array("$prefs_mailer_adr" => "$prefs_mailer_name"))
  		->setBody("$message", 'text/html')
  	;
  	$result = $mailer->send($msg);

	if(!$result) {
		echo"<hr>ERROR<hr>";
	}
}




/**
 * returns the part of the $string
 * before the first occurrence of $separator
 */

function get_left_string($string,$separator) {
  $string = explode("$separator", $string);
  return $string[0];
}



/**
 * get all active mods
 * from cached file /cache/active_mods.php
 */

function se_get_active_mods() {
	
	$active_mods = array();
	$cached_mods = SE_CONTENT . "/cache/active_mods.php";
	
	if(is_file($cached_mods)) {
		include $cached_mods;
	}
	
	return $active_mods;	
}


/**
 * @param $query
 * @param $currentPage
 * @param $itemsPerPage
 * @return array|false
 */

function se_search($query, $currentPage=1, $itemsPerPage=10) {
	
	global $db_content;
	
	$query = str_replace('-', ' ', $query);

    $find_pages = $db_content->select("se_pages", "*", [
        "OR" => [
                "page_content[~]" => "$query",
                "page_title[~]" => "$query",
                "page_description[~]" => "$query"
            ]
    ]);

	return $find_pages;
}




/**
 * @param $id
 * @return void
 */

function se_increase_pageimpression($id) {

    global $db_content;

    if(!is_int($id)) {
        return;
    }

    if(isset($_POST['crawler'])) {
        return;
    }

    $counter = $db_content->get("se_pages", "page_hits",
        [
            "page_id" => $id
        ]);


    if($counter != '') {
        $set_counter = (int) $counter + 1;

        $db_content->update("se_pages", [
            "page_hits" => $set_counter
        ],[
            "page_id" => $id
        ]);
    }

    if($counter == '' OR $counter == NULL) {
        $db_content->update("se_pages", [
            "page_hits" => 1
        ],[
            "page_id" => $id
        ]);
    }
}