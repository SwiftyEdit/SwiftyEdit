<?php
//error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);

/**
 * SfiftyEdit frontend
 * display product from se_products
 *
 * @var integer $get_product_id is set in core/products.php
 * @var string $mod_slug is set in core/products.php
 * @var array $product_data product it's data
 *
 * global variables
 * @var object $db_content meedoo database object
 * @var object $smarty smarty templates
 * @var string $languagePack de | en ...
 * @var array $lang translations
 * @var string $swifty_slug query
 * @var array $se_prefs global preferences
 * @var string $se_base_url the base url
 * @var array $page_contents
 */



if(!is_array($product_data)){
    $show_404 = "true";
}

$hits = (int) $product_data['hits'];
se_increase_product_hits($get_product_id);

// get the product-page by 'type_of_use' and $languagePack
// we need this if we link to product variants
// if $swifty_slug is not equal, we set a canonical link
$target_page = $db_content->get("se_pages", "page_permalink", [
    "AND" => [
        "page_type_of_use" => "display_product",
        "page_language" => $page_contents['page_language']
    ]
]);

if ($target_page == '') {
    $target_page = $swifty_slug;
}

if ($target_page != $swifty_slug) {
    $canonical_url = $se_base_url.$target_page.$product_data['slug'];
    $smarty->assign('page_canonical_url', $canonical_url);
}

if($mod_slug != $product_data['slug']) {
    $canonical_url = $se_base_url.$target_page.$product_data['slug'];
    $smarty->assign('page_canonical_url', $canonical_url);
}

// get price from price groups or from products data
if($product_data['product_price_group'] != '' AND $product_data['product_price_group'] != 'null') {
    $price_data = se_get_price_group_data($product_data['product_price_group']);
    $product_tax = $price_data['tax'];
    $product_price_net = $price_data['price_net'];
    $product_volume_discounts_json = $price_data['price_volume_discount'];
} else {
    $product_tax = $product_data['product_tax'];
    $product_price_net = $product_data['product_price_net'];
    $product_volume_discounts_json = $product_data['product_price_volume_discount'];
}

if($product_tax == '1') {
    $tax = $se_prefs['prefs_posts_products_default_tax'];
} else if($product_tax == '2') {
    $tax = $se_prefs['prefs_posts_products_tax_alt1'];
} else {
    $tax = $se_prefs['prefs_posts_products_tax_alt2'];
}

$post_prices = se_posts_calc_price($product_price_net,$tax);
$post_price_net = $post_prices['net'];
$post_price_gross = $post_prices['gross'];

if ($se_prefs['prefs_posts_price_mode'] == 1) {
    // gross prices
    $product_price_tag = $post_price_gross;
    $product_tax_label = $lang['price_tag_label_gross'];
} else if($se_prefs['prefs_posts_price_mode'] == 2) {
    // gross and net prices
    $product_price_tag = $post_price_net. '/'. $post_price_gross;
    $product_tax_label = $lang['label_net']. ' / '. $lang['label_net'];
} else {
    // net only (b2b mode)
    $product_price_tag = $post_price_net;
    $product_tax_label = $lang['price_tag_label_net'];
}


/* volume discounts */
if($product_volume_discounts_json != '') {
    $product_volume_discounts = json_decode($product_volume_discounts_json,true);
    $smarty->assign('label_prices_discount', $lang['label_prices_discount']);

    // calculate gross prices
    foreach($product_volume_discounts as $k => $v) {
        $vd_price = se_posts_calc_price($v['price'],$tax);
        $show_volume_discounts[$k]['amount'] = $v['amount'];
        $show_volume_discounts[$k]['price_net'] = $vd_price['net'];
        $show_volume_discounts[$k]['price_gross'] = $vd_price['gross'];
    }
    $smarty->assign('show_volume_discounts', $show_volume_discounts);
}


$teaser = text_parser(htmlspecialchars_decode($product_data['teaser']));
$text = text_parser(htmlspecialchars_decode($product_data['text']));
$text_scope_of_delivery = text_parser(htmlspecialchars_decode($product_data['text_scope_of_delivery']));

if($product_data['text_label'] != '') {
    $text_label = text_parser(htmlspecialchars_decode($product_data['text_label']));
} else {
    $text_label = $lang['label_product_description'];
}

if($product_data['product_features_label'] != '') {
    $label_product_features = text_parser(htmlspecialchars_decode($product_data['product_features_label']));
} else {
    $label_product_features = $lang['label_product_features'];
}

/* additional text sections 1-5 */

for($i=1;$i<6;$i++) {
    $key_text = 'text_additional'.$i;
    $key_label = 'text_additional'.$i.'_label';
    $var_text = 'text_additional'.$i;
    $var_label = 'text_additional'.$i.'_label';

    $$var_text = text_parser(htmlspecialchars_decode($product_data[$key_text]));
    $$var_label = text_parser(htmlspecialchars_decode($product_data[$key_label]));

    $smarty->assign($var_label, $$var_label);
    $smarty->assign($var_text, $$var_text);

}

$product_images = explode("<->", $product_data['images']);
$product_images = array_filter($product_images); /* remove empty elements */

$product_datetimeformat = $se_prefs['dateformat'].' '.$se_prefs['timeformat'];

$post_releasedate = date("$product_datetimeformat",$product_data['releasedate']);
$post_releasedate_year = date('Y',$product_data['releasedate']);
$post_releasedate_month = date('m',$product_data['releasedate']);
$post_releasedate_day = date('d',$product_data['releasedate']);
$post_releasedate_time = date('H:i:s',$product_data['releasedate']);

$post_lastedit = date('Y-m-d H:i',$product_data['lastedit']);
$post_lastedit_from = $product_data['lastedit_from'];



/* entry date */
$entrydate_year = date('Y',$product_data['date']);


/* images */

/* if we have more than one image */
if(is_array($product_images) && count($product_images) > 0) {
    foreach($product_images as $image) {
        $show_images[] = se_get_images_data($image,'data=array');
    }

    if($show_images[0]['media_file'] == "") {
        /* fallback if there are no informations in database - maybe if we have more than one language */
        $show_images[0]['media_file'] = reset($product_images);
    }

    /* replace img src with absolute path */
    $cnt_images = count($show_images);
    for($i=0;$i<$cnt_images;$i++) {
        $show_images[$i]['media_file'] =  str_replace('../images/','/images/',$show_images[$i]['media_file']);
        $show_images[$i]['media_thumb'] = str_replace('../images_tmb/','/images_tmb/',$show_images[$i]['media_thumb']);
    }
}

if($show_images[0]['media_file'] != "") {
    $first_product_img_src = $show_images[0]['media_file'];
    $first_product_img_alt = $show_images[0]['media_alt'];
    $first_product_img_title = $show_images[0]['media_title'];
    $first_product_img_caption = $show_images[0]['media_text'];
    $first_product_image = $first_product_img_src;
} else if($se_prefs['prefs_shop_default_banner'] == "without_image") {
    $first_product_image = '';
} else {
    $first_product_image = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
}


/* vote up or down this post */
if($product_data['votings'] == 2 || $product_data['votings'] == 3) {
    $show_voting = true;
    $voter_data = false;
    $voting_type = array("upv", "dnv");
    if ($product_data['votings'] == 2) {
        if ($_SESSION['user_nick'] == '') {
            $voter_data = false;
        } else {
            $voter_data = se_check_user_legitimacy($product_data['id'], $_SESSION['user_nick'], $voting_type);
        }
    }

    if ($product_data['post_votings'] == 3) {
        if ($_SESSION['user_nick'] == '') {
            $voter_name = se_generate_anonymous_voter();
            $voter_data = se_check_user_legitimacy($product_data['id'], $voter_name, $voting_type);
        } else {
            $voter_data = se_check_user_legitimacy($product_data['id'], $_SESSION['user_nick'], $voting_type);
        }
    }

    if ($voter_data == true) {
        // user can vote
        $product_data['votes_status_up'] = '';
        $product_data['votes_status_dn'] = '';
    } else {
        $product_data['votes_status_up'] = 'disabled';
        $product_data['votes_status_dn'] = 'disabled';
    }


    $votes = se_get_voting_data('post', $product_data['id']);

    $product_data['votes_up'] = (int) $votes['upv'];
    $product_data['votes_dn'] = (int) $votes['dnv'];

} else {
    // display no votings
    $show_voting = false;
}


$form_action = '/'.$swifty_slug.$mod_slug;
$this_entry = str_replace("{form_action}", $form_action, $this_entry);

if($se_prefs['prefs_posts_products_cart'] == 1) {
    // all shopping carts are disabled - overwrite products settings
    $product_data['product_cart_mode'] = 2;
}

$smarty->assign('product_price_tag', $product_price_tag);
$smarty->assign('product_tax_label', $product_tax_label);

$smarty->assign('product_price_gross', $post_price_gross);
$smarty->assign('product_price_net', $post_price_net_calculated);
$smarty->assign('product_price_tax', $tax);
$smarty->assign('product_currency', $product_data['product_currency']);
$smarty->assign('product_unit', $product_data['product_unit']);
$smarty->assign('product_amount', $product_data['product_amount']);
$smarty->assign('product_price_label', $product_data['product_price_label']);
$smarty->assign('product_price_tag_label_gross', $lang['price_tag_label_gross']);
$smarty->assign('product_price_tag_label_net', $lang['price_tag_label_net']);

if($product_data['product_textlib_content'] != 'no_snippet' AND $product_data['product_textlib_content'] != '') {
    $product_snippet_content = se_get_textlib($product_data['product_textlib_content'],$languagePack,'all');
    $smarty->assign('product_snippet_text', $product_snippet_content['snippet_content']);
    $smarty->assign('product_snippet_title', $product_snippet_content['snippet_title']);
}

if($product_data['product_textlib_price'] != 'no_snippet' AND $product_data['product_textlib_price'] != '') {
    $product_snippet_price = se_get_textlib($product_data['product_textlib_price'],$languagePack,'all');
    $smarty->assign('label_prices_snippet', $lang['label_prices_snippet']);
    $smarty->assign('product_snippet_price', $product_snippet_price['snippet_content']);
}


/* product options */
$product_options = json_decode($product_data['product_options'],JSON_FORCE_OBJECT);
if(is_array($product_options)) {
    $get_options = se_get_posts_options($product_options);

    foreach($get_options as $option) {
        $select_options[] = [
            "title" => $option['snippet_title'],
            "values" => json_decode($option['snippet_content'],JSON_FORCE_OBJECT)
        ];

    }

    $smarty->assign('select_options', $select_options);
}

/**
 * user file upload
 * check if item has a file upload option
 */

if($product_data['file_attachment_user'] == 2 && $_SESSION['user_nick'] != "") {
    $smarty->assign('file_upload_message', $lang['msg_item_needs_upload']);
}


/* product features */
$product_features = json_decode($product_data['product_features'],JSON_FORCE_OBJECT);
$product_features_values = json_decode($product_data['product_features_values'],JSON_FORCE_OBJECT);

if(is_array($product_features)) {
    $get_features = se_get_posts_features($product_features);
    foreach($get_features as $k => $v) {
        $array_key = $v['snippet_id'];
        if(array_key_exists($array_key, $product_features_values)) {
            /* overwrite value */
            if($product_features_values[$array_key] == '') {
                continue;
            }
            $get_features[$k]['snippet_content'] = $product_features_values[$array_key];
        }
    }
    $smarty->assign('product_features', $get_features);
    $smarty->assign('label_product_features', $label_product_features);
}

/* check for variants  */
$variants = array();
if($product_data['type'] == 'v') {
    // this product is a variant of 'parent_id'
    $variants = se_get_product_variants($product_data['parent_id']);
} else {
    $variants = se_get_product_variants($product_data['id']);
}

$cnt_variants = count($variants);
if($cnt_variants > 1) {
    $var = array();
    foreach($variants as $k => $v) {
        $var[$k]['title'] = $v['title'];
        $var[$k]['teaser'] = se_return_words_str(html_entity_decode($v['teaser']),10);
        $product_images = explode("<->",$v['images']);
        if ($product_images[1] != "") {
            $var[$k]['image'] = str_replace('../images/', '/images/', $product_images[1]);
        } else if ($se_prefs['prefs_posts_default_banner'] == "without_image") {
            $var[$k]['image'] = '';
        } else {
            $var[$k]['image'] = str_replace('../images/', '/images/', $se_prefs['prefs_posts_default_banner']);
        }

        $product_slug = basename($v['slug']);
        $var[$k]['product_href'] = SE_INCLUDE_PATH . "/" . $target_page . "$product_slug-" . $v['id'] . ".html";

        $var[$k]['class'] = '';
        if($v['id'] == $product_data['id']) {
            // mark this product as active
            $var[$k]['class'] = 'active';
        }

    }
    $smarty->assign('show_variants', $var);
}

/* check for related products */
if($product_data['product_related'] != '') {

    $related_products_array = json_decode($product_data['product_related'],true);
    $cnt_related_products = count((array) $related_products_array);
    for($i=0;$i<$cnt_related_products;$i++) {

        $related_product = se_get_product_data($related_products_array[$i]);

        $rp[$i]['title'] = $related_product['title'];
        $rp[$i]['teaser'] = se_return_words_str(html_entity_decode($related_product['teaser']),10);
        $rp[$i]['product_number'] = $related_product['product_number'];
        $rp[$i]['product_currency'] = $related_product['product_currency'];
        $rp[$i]['product_unit'] = $related_product['product_unit'];
        $rp[$i]['product_amount'] = $related_product['product_amount'];

        $product_slug = basename($related_product['slug']);
        $product_images = explode("<->",$related_product['images']);
        if ($product_images[1] != "") {
            $rp[$i]['image'] = str_replace('../images/', '/images/', $product_images[1]);
        } else if ($se_prefs['prefs_posts_default_banner'] == "without_image") {
            $rp[$i]['image'] = '';
        } else {
            $rp[$i]['image'] = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
        }
        $rp[$i]['product_href'] = SE_INCLUDE_PATH . "/" . $target_page . "$product_slug-" . $related_product['id'] . ".html";
    }
    $smarty->assign('show_related', $rp);
}

/* check for accessories */
if($product_data['product_accessories'] != '') {

    $products_accessories_array = json_decode($product_data['product_accessories'],true);
    $cnt_products_accessories = count((array) $products_accessories_array);
    for($i=0;$i<$cnt_products_accessories;$i++) {
        $accessories_product = se_get_product_data($products_accessories_array[$i]);

        $ap[$i]['title'] = $accessories_product['title'];
        $ap[$i]['teaser'] = se_return_words_str(html_entity_decode($accessories_product['teaser']),10);
        $ap[$i]['product_number'] = $accessories_product['product_number'];
        $ap[$i]['product_currency'] = $accessories_product['product_currency'];
        $ap[$i]['product_unit'] = $accessories_product['product_unit'];
        $ap[$i]['product_amount'] = $accessories_product['product_amount'];

        $product_slug = basename($accessories_product['slug']);
        $product_images = explode("<->",$accessories_product['images']);
        if ($product_images[1] != "") {
            $ap[$i]['image'] = str_replace('../images/', '/images/', $product_images[1]);
        } else if ($se_prefs['prefs_posts_default_banner'] == "without_image") {
            $ap[$i]['image'] = '';
        } else {
            $ap[$i]['image'] = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
        }

        $ap[$i]['product_href'] = SE_INCLUDE_PATH . "/" . $target_page . "$product_slug-" . $accessories_product['id'] . ".html";
    }
    $smarty->assign('show_accessories', $ap);
}



if($product_data['meta_title'] == '') {
    $product_data['meta_title'] = $product_data['title'];
}

if($product_data['meta_description'] == '') {
    $product_data['meta_description'] = substr(strip_tags($post_teaser),0,160);
}

$page_contents['page_thumbnail'] = $se_base_url.$img_path.'/'.basename($first_product_image);

/* delivery time */
$product_delivery_time = (int) $product_data['product_delivery_time'];
$get_delivery_text = $db_content->get("se_snippets", ["snippet_title","snippet_content"], [
    "snippet_id" => $product_delivery_time
]);


/**
 * attachments
 */

if($product_data['file_attachment'] != '') {

    $file_name = '../files'.$product_data['file_attachment'];
    $download_target = SE_PUBLIC.'/assets/files'.$product_data['file_attachment'];
    $file_data = se_get_media_data($file_name,$languagePack);

    $smarty->assign('download_title', $file_data['media_title']);
    $smarty->assign('download_text', $file_data['media_text']);
    $smarty->assign('download_credit', $file_data['media_credit']);
    $smarty->assign('download_version', $file_data['media_version']);
    $smarty->assign('download_license', $file_data['media_license']);
    $smarty->assign('attachment_filename', $file_name);
    $smarty->assign('attachment_download_url', $download_target);
}


/* start download */
if(isset($_POST['get_attachment'])) {

    se_increase_downloads_hits($get_product_id);
    $download_url = SE_PUBLIC.'/assets/files'.$product_data['file_attachment'];

    if(file_exists($download_url)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($download_url).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($download_url));
        flush(); // Flush system output buffer
        readfile($download_url);
        die();
    }
}

$smarty->assign('page_title', html_entity_decode($product_data['meta_title']));
$smarty->assign('page_meta_description', html_entity_decode($product_data['meta_description']));
$smarty->assign('page_meta_keywords', html_entity_decode($product_data['tags']));
$smarty->assign('page_thumbnail', $page_contents['page_thumbnail']);

$smarty->assign('product_options_comment_label', $product_data['product_options_comment_label']);

$smarty->assign('votes_status_up', $product_data['votes_status_up']);
$smarty->assign('votes_status_dn', $product_data['votes_status_dn']);
$smarty->assign('votes_up', $product_data['votes_up']);
$smarty->assign('votes_dn', $product_data['votes_dn']);

$smarty->assign('show_voting', $show_voting);

/* first image */
$smarty->assign('product_img_src', $first_product_img_src);
$smarty->assign('product_img_alt', $first_product_img_alt);
$smarty->assign('product_img_title', $first_product_img_title);
$smarty->assign('product_img_caption', $first_product_img_caption);

/* all images (array) */
$smarty->assign('product_show_images', $show_images);


$smarty->assign('product_id', $product_data['id']);
$smarty->assign('product_title', $product_data['title']);
$smarty->assign('product_number', $product_data['product_number']);
$smarty->assign('product_teaser', $teaser);
$smarty->assign('product_text', $text);
$smarty->assign('text_scope_of_delivery', $text_scope_of_delivery);
$smarty->assign('product_text_label', $text_label);
$smarty->assign('product_href', $swifty_slug.$product_data['slug']);


$smarty->assign('product_pricetag_mode', $product_data['product_pricetag_mode']);
$smarty->assign('product_cart_mode', $product_data['product_cart_mode']);

$smarty->assign('form_action', $form_action);
$smarty->assign('btn_add_to_cart', $lang['btn_add_to_cart']);

$smarty->assign('label_delivery_time', $lang['label_product_delivery_time']);
$smarty->assign('product_delivery_time_title', $get_delivery_text['snippet_title']);
$smarty->assign('product_delivery_time_text', $get_delivery_text['snippet_content']);

$products_page = $smarty->fetch("products-display.tpl", $cache_id);
$smarty->assign('page_content', $products_page, true);