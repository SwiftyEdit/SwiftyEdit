<?php
	
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * global functions
 * are used in frontend and backend
 * 
 */

include_once 'functions.helpers.php';
include_once 'functions.sanitizer.php';
include_once 'functions.posts.php';
include_once 'functions.shop.php';
include_once 'functions.user.php';
include_once 'functions.pages.php';
include_once 'functions.snippets.php';

/**
 * get active preferences
 */
 
function se_get_preferences() {
	
	global $db_content;
	
	$prefs = $db_content->select("se_options", "*", [
		"option_module" => "se"
	]);

	return $prefs;
}


/**
 * get the legal pages
 */
 
function se_get_legal_pages() {
	global $db_content;
	global $languagePack;
	
	$pages = $db_content->select("se_pages", ["page_linkname","page_title","page_permalink","page_type_of_use"], [
		"AND" => [
			"page_language" => $languagePack,
			"page_type_of_use" => ["imprint", "privacy_policy", "legal"]
		]
	]);
	
	return $pages;
}

/**
 * get data from se_media
 * by filename
 *
 */

function se_get_media_data($filename,$lang=NULL) {

    global $db_content;
    $media_data = $db_content->get("se_media","*",[

        "AND" => [
            "media_file[~]" => "$filename",
            "media_lang[~]" => "$lang"
        ]
    ]);

    return $media_data;
}


/**
 * get data from se_media
 * by id
 *
 */

function se_get_media_data_by_id($id) {

    global $db_content;
    $media_data = $db_content->get("se_media","*",[

        "media_id" => $id
    ]);

    return $media_data;
}


/**
 * get all categories
 * order by cat_sort
 */

function se_get_categories() {
	global $db_content;
	$categories = $db_content->select("se_categories", "*",
	[
		"ORDER" => ["cat_sort" => "DESC"]
	]);	
	return $categories;
}


/**
 * get all comments
 * $filter = array()
 * $filter['type'] -> p|b|c
 * $filter['status'] -> all|1|2
 */

function se_get_comments($start,$limit,$filter) {
	
	global $db_content;
	
	if(empty($start)) {
		$start = 0;
	}
	if(empty($limit)) {
		$limit = 100;
	}

    if(!isset($filter['status'])) {
        $filter['status'] = 2;
    }
	
	$filter_type = $filter['type'];
	if($filter_type == 'all') {
		$filter_type = ["p","b","c"];
	}



	if($filter['status'] == 'all') {
		$comment_status = ["1","2"];
	} else if($filter['status'] == '1') {
		$comment_status = "1";
	} else {
		$comment_status = 2;
	}
	
	
	$filter_relation_id = $filter['relation_id'];
	
	if($filter_relation_id == 'all') {

		$comments = $db_content->select("se_comments", "*",[
				"AND" => [
				"comment_type" => $filter_type,
				"comment_status" => $comment_status
			],
				"LIMIT" => [$start,$limit],
				"ORDER" => ["comment_time" => "DESC"]
		]);

	} else {

		$comments = $db_content->select("se_comments", "*",[
				"AND" => [
				"comment_type" => $filter_type,
				"comment_relation_id" => $filter_relation_id,
				"comment_status" => $comment_status
			],
				"LIMIT" => [$start,$limit],
				"ORDER" => ["comment_time" => "ASC"]
		]);		
		
	}
	
	return $comments;
}

/**
 * @param $array
 * @param $data
 * @return void
 */

function se_build_thread_array(&$array, $data) {

    $comment_time = date('d.m.Y H:i',$data['comment_time']);
    /* default avatar image */
    $comment_avatar = '/themes/default/images/avatar.jpg';
    /* if it's a registrated user and if there is an avatar, use it */
    if($data['comment_author_id'] != '') {
        $check_avatar = SE_CONTENT.'avatars/'.md5($data['comment_author']).'.png';
        if(is_file($check_avatar)) {
            $comment_avatar = SE_CONTENT.'/avatars/'.md5($data['comment_author']).'.png';
        }
    }

    $avatar_img_src = $comment_avatar;
    $a_url = '/api/se/comments/?form=comments&parent_id='.$data['comment_id'].'&relation_id='.$data["comment_relation_id"].'#comment-form';

    if ($data["comment_parent_id"] == 0 OR $data["comment_parent_id"] == NULL) {
        $array[] = [
            "id" => $data["comment_id"],
            "author" => $data["comment_author"],
            "text" => $data["comment_text"],
            "avatar_img_src" => $avatar_img_src,
            "url_answer_comment" => $a_url,
            "time" => $comment_time,
            "childs" => []
        ];

        return $array;
    }
    foreach($array as &$e) {
        if ($e["id"] == $data["comment_parent_id"]) {
            $e["childs"][] = [
                "id" => $data["comment_id"],
                "author" => $data["comment_author"],
                "text" => $data["comment_text"],
                "avatar_img_src" => $avatar_img_src,
                "url_answer_comment" => $a_url,
                "time" => $comment_time,
                "childs"=> []
            ];

            break;
        }
        se_build_thread_array($e["childs"], $data);
    }
}


/**
 * store a comment
 * @var array $data 'input_name' 'input_mail' 'input_comment'
 * @return mixed id of the inserted comment
 */

function se_write_comment($data) {
	
	global $db_content;
	global $prefs_comments_mode;
	
	if($data['input_name'] != '' && $data['input_mail'] != '' && $data['input_comment'] != '') {
	
		foreach($data as $key => $val) {
			$$key = sanitizeUserInputs($val);
		}
		
		$type = 'p';
		$comment_status = 2;
		
		if($prefs_comments_mode == 1) {
			$comment_status = 1;
		}
		
		$comment_time = time();
		
		if(is_numeric($data['page_id'])) {
			$type = 'p';
			$relation_id = (int) $data['page_id'];
		}
		
		if(is_numeric($data['post_id'])) {
			$type = 'b';
			$relation_id = (int) $data['post_id'];
		}
	
		if(strlen($input_name) > 30) {
			$input_name = substr($input_name, 0,30);
		}
		
		if(strlen($input_mail) > 50) {
			$input_mail = substr($input_mail, 0,50);
		}
			
		if(strlen($input_comment) > 500) {
			$input_comment = substr($input_comment, 0,500);
		}
		
		if(is_numeric($data['parent_id'])) {
			$parent_id = (int) $data['parent_id'];
		}
		
		if(is_numeric($_SESSION['user_id'])) {
			$comment_author_id = $_SESSION['user_id'];
		} else {
			$comment_author_id = '';
		}
		
		
		$input_comment = nl2br($input_comment);
		
		
		$db_content->insert("se_comments", [
			"comment_type" =>  $type,
			"comment_relation_id" =>  $relation_id,
			"comment_parent_id" =>  $parent_id,
			"comment_status" =>  $comment_status,
			"comment_time" =>  $comment_time,
			"comment_author" =>  $input_name,
			"comment_author_id" =>  $comment_author_id,
			"comment_author_mail" =>  $input_mail,
			"comment_text" =>  $input_comment
		]);
		
		$insert_id = $db_content->id();
		
		return $insert_id;
		
	}
}


/**
 * return the first $nbr words of a string
 * @param string $string
 * @param int $nbr
 * @return string
 */
function se_return_words_str(string $string, int $nbr=5): string {
    $short_string = implode(' ', array_slice(explode(' ', $string), 0, $nbr));

    if(strlen($short_string) < strlen($string)) {
        $short_string .= ' (...)';
    }
    return $short_string;
}


/**
 * sending e-mails
 * store your smtp settings in /content/config_smtp.php
 *
 * @param array $recipient 'mail' and 'name'
 * @param string $subject subject of the email
 * @param string $message string/html content of the email
 * @return 1 if success or ErrorInfo if failed
 * @throws Exception
 */


function se_send_mail($recipient,$subject,$message,$bcc_admin=false) {

    global $se_settings;
    global $smtp_host, $smtp_port, $smtp_encryption, $smtp_username, $smtp_psw;
    $prefs_mailer_adr = $se_settings['mailer_adr'];
    $prefs_mailer_name = $se_settings['mailer_name'];
    $prefs_mailer_type = $se_settings['mailer_type'];

	$subject = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $subject );
	$message = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $message );


	
	$mail = new PHPMailer(true);
	
	if($prefs_mailer_type == 'smtp') {
		/* sending via smtp */

	  $mail->isSMTP();
	  $mail->Host = "$smtp_host";
	  $mail->SMTPAuth = true;
	  $mail->Username   = "$smtp_username";
	  $mail->Password   = "$smtp_psw";
	  if($smtp_encryption != '') {
	  	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	  }
	  $mail->Port = $smtp_port;
	
	  $mail->setFrom("$prefs_mailer_adr", "$prefs_mailer_name");
	  $mail->addAddress($recipient['mail'], $recipient['name']);
    }

	$mail->setFrom("$prefs_mailer_adr", "$prefs_mailer_name");
	$mail->addAddress($recipient['mail'], $recipient['name']);

    if($bcc_admin) {
        if($se_settings['notify_mail'] != '') {
            $mail->addBCC($se_settings['notify_mail']);
        } else {
            $mail->addBCC($se_settings['mailer_adr']);
        }
    }
	   
	$mail->isHTML(true);
	$mail->CharSet = 'utf-8';
	$mail->Subject = "$subject";
	$mail->Body = "$message";
	  
	  
	if(!$mail->send()) {
        $fail = 'Mailer Error: ' . $mail->ErrorInfo;
        $return = $fail;
	} else {
        $return = 1;
	}
	return $return;
}

/**
 * @param array $recipient 'type', 'name' and 'mail'
 * @param integer $order the order id
 * @param string $reason this will change the subject
 * @return void
 */

function se_send_order_status($recipient,$order,$reason) {

    global $se_settings, $lang;

    $order_id = (int) $order;
    $this_order = se_get_order_details($order_id);

    if($recipient['type'] == 'client') {
        // get client data
        $user_data = se_get_userdata_by_id($this_order['user_id']);
        $recipient['name'] = $user_data['user_firstname'].' '.$user_data['user_lastname'];
        $recipient['mail'] = $user_data['user_mail'];
        if($recipient['mail'] == '') {
            return 'error';
        }
    } else {
        // send it to admin
        $recipient['name'] = $se_settings['mailer_name'];
        $recipient['mail'] = $se_settings['mailer_adr'];
    }

    if($reason == 'notification') {
        $subject = "Notification: Order status # ".$this_order['order_nbr'];
    } else if($reason == 'change_payment_status'){
        $subject = "We changed the Payment Status # ".$this_order['order_nbr'];
    } else if($reason == 'change_shipping_status') {
        $subject = "We changed the Shipping Status # ".$this_order['order_nbr'];
    } else if($reason == 'order_confirmation') {
        $subject = "Your Order has been sent # ".$this_order['order_nbr'];
    } else {
        $subject = "We changed something in # ".$this_order['order_nbr'];
    }


    $order_invoice_address = html_entity_decode($this_order['order_invoice_address']);

    $mail_data['body_tpl'] = 'send-order-status.tpl';
    $mail_data['subject'] = $subject;
    $mail_data['salutation'] = $subject;

    $build_html_mail = se_build_html_file($mail_data);

    if($this_order['order_status_payment'] == 2) {
        $build_html_mail = str_replace("{payment_status}",$lang['status_payment_paid'],$build_html_mail);
    } else {
        $build_html_mail = str_replace("{payment_status}",$lang['status_payment_open'],$build_html_mail);
    }
    if($this_order['order_status_shipping'] == 2) {
        $build_html_mail = str_replace("{shipping_status}",$lang['status_shipping_done'],$build_html_mail);
    } else {
        $build_html_mail = str_replace("{shipping_status}",$lang['status_shipping_open'],$build_html_mail);
    }
    $build_html_mail = str_replace("{order_nbr}",$this_order['order_nbr'],$build_html_mail);
    $build_html_mail = str_replace("{invoice_address}",$order_invoice_address,$build_html_mail);
    $price_total = se_post_print_currency($this_order['order_price_total']). ' '.$this_order['order_currency'];
    $build_html_mail = str_replace("{price_total}",$price_total,$build_html_mail);

    $order_products = json_decode($this_order['order_products'],true);
    $cnt_order_products = count($order_products);

    $products_str = '<table role="presentation" border="0" cellpadding="0" cellspacing="0">';
    $products_str .= '<tr>';
    $products_str .= '<td>#</td>';
    $products_str .= '<td>'.$lang['label_product_info'].'</td>';
    $products_str .= '<td>'.$lang['label_product_amount'].'</td>';
    $products_str .= '<td>'.$lang['label_price'].' ('.$lang['label_gross'].')</td>';
    $products_str .= '</tr>';
    for($i=0;$i<$cnt_order_products;$i++) {
        $products_str .= '<tr>';
        $products_str .= '<td>'.$order_products[$i]['product_number'].'</td>';
        $products_str .= '<td>'.$order_products[$i]['title'];
        $products_str .= '<p>'.$order_products[$i]['options'].'</p>';
        $products_str .= '<p>'.$order_products[$i]['options_comment_label'].': '.$order_products[$i]['options_comment'].'</p>';
        $products_str .= '</td>';
        $products_str .= '<td>'.$order_products[$i]['amount'].'</td>';
        $products_str .= '<td>'.se_post_print_currency($order_products[$i]['price_gross_raw']).' '.$this_order['order_currency'].'</td>';
        $products_str .= '</tr>';
    }
    $products_str .= '</table>';

    $build_html_mail = str_replace("{order_products}",$products_str,$build_html_mail);

    foreach($lang as $key => $val) {
        $search = '{lang_'.$key.'}';
        $build_html_mail = str_replace("$search","$val",$build_html_mail);
    }

    $send_mail = se_send_mail($recipient, $subject, $build_html_mail,true);
    return $send_mail;
}


/**
 * create html file resp. string
 * send via se_send_mail() or force download as file
 * get the content from mail template f.e. /styles/default/templates-mail/mail.tpl
 * get the styles /styles/default/templates-mail/styles.css
 * get the mail body template from $data['tpl']
 * bring everything together and return as string
 * @param array $data 'subject','preheader','title','salutation','body','footer','tpl', 'body_tpl'
 * @return string html formatted string
 */

function se_build_html_file($data) {
	
	global $se_settings;
	
	$tpl_dir = SE_ROOT.'/public/assets/themes/'.$se_settings['template'];
	$tpl_style = file_get_contents($tpl_dir.'/templates-mail/styles.css');

    if($data['tpl'] == '') {
        $tpl_file = file_get_contents($tpl_dir.'/templates-mail/mail.tpl');
    } else {
        $tpl_file = file_get_contents($tpl_dir.'/templates-mail/'.basename($data['tpl']));
    }

    if($data['body_tpl'] != '') {
        $tpl_body_file = file_get_contents($tpl_dir . '/templates-mail/' . basename($data['body_tpl']));
        $tpl_file = str_replace('{mail_body}', $tpl_body_file, $tpl_file);
    }

	$footer = $data['footer'];
	if($data['footer'] == '') {
		$footer = se_get_textlib('footer_text_mail','','content');
	}

	$tpl_file = str_replace('{styles}', $tpl_style, $tpl_file);
    $tpl_file = str_replace('{mail_subject}', $data['subject'], $tpl_file);
    $tpl_file = str_replace('{mail_salutation}', $data['salutation'], $tpl_file);
	$tpl_file = str_replace('{mail_body}', $data['body'], $tpl_file);
	$tpl_file = str_replace('{mail_title}', $data['title'], $tpl_file);
    $tpl_file = str_replace('{mail_preheader}', $data['preheader'], $tpl_file);
	$tpl_file = str_replace('{mail_footer}', $footer, $tpl_file);
	
	return $tpl_file;	
}


/**
 * get textlib content
 * @param string $name name of the entry
 * @param string $lang language of the entry
 * @param string $type 'all' returns all contents as array
 * @param string $type 'content' returns only the text as string
 * @param string $type 'tpl' use snippet it's template. Return as string.
 *
 * @global $db_content database settings
 * @global $languagePack the language required
 */


function se_get_textlib($name,$lang,$type) {

	global $db_content;
	global $languagePack;
	
	if($lang == '') {
		$lang = $languagePack;
	}

	/* get snippet by name and lang */
	$textlibData = $db_content->get("se_snippets", "*", [
		"AND" => [
			"snippet_name" => "$name",
			"snippet_lang" => "$lang"
		]
	]);

    /* no snippet found - try without language */
    if(!is_array($textlibData)) {
        $textlibData = $db_content->get("se_snippets", "*", [
            "snippet_name" => "$name"
        ]);
    }

	if($type == 'all') {
		return $textlibData;
	}
	
	if($type == 'content') {
		return $textlibData['snippet_content'];
	}
	
	if($type == 'tpl') {
		
		foreach($textlibData as $k => $v) {
	   		$$k = stripslashes($v);
		}
		
		$get_tpl_file = SE_ROOT.'/public/themes/default/templates/snippet.tpl';
		
		if($snippet_theme != '' AND $snippet_theme != 'use_standard') {
			$get_tpl_file = SE_ROOT.'/public/themes/'.$snippet_theme.'/templates/'.$snippet_template;
		}

		if(is_file("$get_tpl_file")) {
			$tpl_file = file_get_contents($get_tpl_file);
			
			$snippet_thumbnail_array = explode("<->", $snippet_images);
			if(count($snippet_thumbnail_array) > 0) {
				foreach($snippet_thumbnail_array as $img) {
					$img = str_replace('../content/', '/content/', $img);
					$tpl_file = str_replace('{$snippet_img_src}',$img,$tpl_file);						
				}
			}
			
			$tpl_file = str_replace('{$snippet_title}',$snippet_title,$tpl_file);
			$tpl_file = str_replace('{$snippet_text}',$snippet_content,$tpl_file);
			$tpl_file = str_replace('{$snippet_teaser}',$snippet_teaser,$tpl_file);
			$tpl_file = str_replace('{$snippet_classes}',$snippet_classes,$tpl_file);
			$tpl_file = str_replace('{$snippet_url}',$snippet_permalink,$tpl_file);
			$tpl_file = str_replace('{$snippet_url_name}',$snippet_permalink_name,$tpl_file);
			$tpl_file = str_replace('{$snippet_url_title}',$snippet_permalink_title,$tpl_file);
			$tpl_file = str_replace('{$snippet_url_classes}',$snippet_permalink_classes,$tpl_file);
			return $tpl_file;
		}
	}
    return '';
}

/**
 * @return array all snippets from se_snippets
 */

function se_get_all_snippets() {
    global $db_content;
    $snippets = $db_content->select("se_snippets", ["snippet_name", "snippet_content","snippet_lang"]);
    return $snippets;
}



/**
 * get all shortcodes or filter by label
 * example for filters
 * $filter['labels'] = '1-2-3-4';
 */
function se_get_shortcodes($filter=NULL) {
	
	global $db_content;
	global $se_labels;
		
	/* label filter */
	if($filter == NULL OR $filter['labels'] == 'all' OR $filter['labels'] == '') {
		
		$set_label_filter = '';
		
	} else {
			
		$filter_labels = explode('-', $filter['labels']);
		
		for($i=0;$i<count($se_labels);$i++) {
			$label = $se_labels[$i]['label_id'];
			if(in_array($label, $filter_labels)) {
				$set_label_filter .= "snippet_labels LIKE '%,$label,%' OR snippet_labels LIKE '%,$label' OR snippet_labels LIKE '$label,%' OR snippet_labels = '$label' OR ";
			}
		}
		
		$set_label_filter = substr("$set_label_filter", 0, -3); // cut the last ' OR'
		
	}
	
	$sql_filter = "WHERE snippet_type LIKE 'shortcode' ";
	
	if($set_label_filter != "") {
		$sql_filter .= " AND ($set_label_filter) ";
	}
	
	$sql = "SELECT * FROM se_snippets $sql_filter";
	$shortcodes = $db_content->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	return $shortcodes;
}



/**
 * get posts features from se_snippets
 * snippet_type = post_feature
 */

function se_get_posts_features($mode=null) {
	
	global $db_content;

    if($mode === null) {
        $features = $db_content->select("se_snippets", "*", [
            "snippet_type" => 'post_feature',
            "ORDER" => [
                "snippet_priority" => "DESC"
            ]
        ]);
    }

    if(is_array($mode)) {
        $features = $db_content->select("se_snippets", "*", [
            "snippet_type" => 'post_feature',
            "snippet_id" => $mode,
            "ORDER" => [
                "snippet_priority" => "DESC"
            ]
        ]);
    }
	
	return $features;
}

/**
 * get posts options from se_snippets
 * snippet_type = post_option
 */

function se_get_posts_options($mode=null) {

    global $db_content;

    if($mode === null) {
        $features = $db_content->select("se_snippets", "*",[
            "snippet_type" => 'post_option',
            "ORDER" => [
                "snippet_priority" => "DESC"
            ]
        ]);
    }

    if(is_array($mode)) {
        $features = $db_content->select("se_snippets", "*",[
            "snippet_type" => 'post_option',
            "snippet_id" => $mode,
            "ORDER" => [
                "snippet_priority" => "DESC"
            ]
        ]);
    }


    return $features;
}




/**
 * get saved data from table se_themes
 * $theme (string) name of the theme
 */
function se_get_theme_options($theme) {

	global $db_content;
	
	$theme_data = $db_content->select("se_themes", "*",[
		"theme_name" => $theme
	]);
	
	return $theme_data;		
}



/**
 * upload avatar
 * convert to png and square format
 * rename file to md5(username)
 *
 * $file (array) data from upload form
 * $username (string) 
 */

function se_upload_avatar($file,$username) {

    if(!is_array($file)) {
        return false;
    }

    $uploads_dir = SE_PUBLIC."/assets/avatars";
	$max_width = 100;
		
	$tmp_name = $file['avatar']['tmp_name'];
	$new_name = md5($username);
	$new_avatar_src = $uploads_dir.'/'.$new_name.'.png';
		
	list($width_upl, $height_upl, $type_upl) = getimagesize($tmp_name);
    
		if ($width_upl > $height_upl) {
		  $y = 0;
		  $x = ($width_upl - $height_upl) / 2;
		  $smallestSide = $height_upl;
		} else {
		  $x = 0;
		  $y = ($height_upl - $width_upl) / 2;
		  $smallestSide = $width_upl;
		}
    
		$imgt = '';
		if($type_upl==1) { $imgt = imagecreatefromgif($tmp_name);  }
		if($type_upl==2) { $imgt = imagecreatefromjpeg($tmp_name);  }
		if($type_upl==3) { $imgt = imagecreatefrompng($tmp_name);  }
		
		
		if($imgt != '') {

			$new_image = imagecreatetruecolor($max_width, $max_width);
			imagecopyresampled($new_image, $imgt, 0, 0, $x, $y, $max_width, $max_width, $smallestSide, $smallestSide);
			
					
			if(imagepng($new_image, $new_avatar_src,9) === true) {
				imagedestroy($new_image);
				return true;			
			} else {
                return "Cannot create PNG on $uploads_dir";
            }
			
		
		} else {
			return "Cannot create image";
		}
}

/**
 * create logs
 * @param string $log_trigger system or username
 * @param string $log_entry what's happened
 * @param integer $log_priority 0-10
 * @example record_log("$_SESSION[user_nick]","the message","5");
 */

function record_log($log_trigger, $log_entry, $log_priority = '0') {

    global $db_content;

    if(empty($log_trigger)) {
        $log_trigger = 'undefined';
    }

    $log_time = time();

    $db_content->insert("se_logs", [
        "time" => "$log_time",
        "source" => "$log_trigger",
        "entry" => "$log_entry",
        "priority" => $log_priority
    ]);

}

/**
 * sort arrays like SQL Results
 * example:
 * $s = se_array_multisort($pages, 'lang', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);
 *
 */

function se_array_multisort() {
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}