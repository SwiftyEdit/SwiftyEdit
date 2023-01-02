<?php
error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);

/**
 * SfiftyEdit frontend
 * display product from se_products
 *
 * @var integer $get_product_id is set in core/products.php
 *
 * global variables
 * @var object $db_content meedoo database object
 * @var string $languagePack de | en ...
 * @var string $swifty_slug query
 * @var array $se_prefs global preferences
 */

$product_data = se_get_product_data($get_product_id);

$hits = (int) $product_data['hits'];
se_increase_product_hits($get_product_id);

// get the posting-page by 'type_of_use' and $languagePack
// we need this if we link to product variants
$target_page = $db_content->select("se_pages", "page_permalink", [
    "AND" => [
        "page_type_of_use" => "display_product",
        "page_language" => $page_contents['page_language']
    ]
]);

if ($target_page[0] == '') {
    $target_page[0] = $swifty_slug;
}


$teaser = htmlspecialchars_decode($product_data['teaser']);
$text = htmlspecialchars_decode($product_data['text']);



$post_images = explode("<->", $product_data['images']);
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

if($post_images[1] != "") {
    $first_post_image = '/' . $img_path . '/' . str_replace('../content/images/','',$post_images[1]);
    $post_image_data = se_get_images_data($first_post_image,'data=array');
} else if($se_prefs['prefs_shop_default_banner'] == "without_image") {
    $first_post_image = '';
} else {
    $first_post_image = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
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

if($product_data['product_tax'] == '1') {
    $tax = $se_prefs['prefs_posts_products_default_tax'];
} else if($product_data['product_tax'] == '2') {
    $tax = $se_prefs['prefs_posts_products_tax_alt1'];
} else {
    $tax = $se_prefs['prefs_posts_products_tax_alt2'];
}

$post_product_price_addition = $product_data['product_price_addition'];
if($post_product_price_addition == '') {
    $post_product_price_addition = 0;
}

$post_prices = se_posts_calc_price($product_data['product_price_net'],$post_product_price_addition,$tax);
$post_price_net = $post_prices['net'];
$post_price_gross = $post_prices['gross'];

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


/* product features */
$product_features = json_decode($product_data['product_features'],JSON_FORCE_OBJECT);
if(is_array($product_features)) {
    $get_features = se_get_posts_features($product_features);
    $smarty->assign('product_features', $get_features);
    $smarty->assign('label_product_features', $lang['label_product_features']);
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
        $post_images = explode("<->",$v['images']);
        if ($post_images[1] != "") {
            $var[$k]['image'] = '/' . $img_path . '/' . str_replace('../content/images/', '', $post_images[1]);
        } else if ($se_prefs['prefs_posts_default_banner'] == "without_image") {
            $var[$k]['image'] = '';
        } else {
            $var[$k]['image'] = "/$img_path/" . $se_prefs['prefs_posts_default_banner'];
        }

        $product_slug = basename($v['slug']);
        $var[$k]['product_href'] = SE_INCLUDE_PATH . "/" . $target_page[0] . "$product_slug-" . $v['id'] . ".html";

        $var[$k]['class'] = '';
        if($v['id'] == $product_data['id']) {
            // mark this product as active
            $var[$k]['class'] = 'active';
        }

    }
    $smarty->assign('show_variants', $var);
}


if($product_data['meta_title'] == '') {
    $product_data['meta_title'] = $product_data['title'];
}

if($product_data['meta_description'] == '') {
    $product_data['meta_description'] = substr(strip_tags($post_teaser),0,160);
}

$page_contents['page_thumbnail'] = $se_base_url.$img_path.'/'.basename($first_post_image);

/* delivery time */
$product_delivery_time = (int) $product_data['product_delivery_time'];
$get_delivery_text = $db_content->get("se_snippets", ["snippet_title","snippet_content"], [
    "snippet_id" => $product_delivery_time
]);


/**
 * attachments
 */

if($product_data['file_attachment'] != '') {

    $file_name = '../content/files'.$product_data['file_attachment'];
    $download_target = SE_CONTENT.$product_data['file_attachment'];
    $file_data = se_get_media_data($file_name,$languagePack);

    $smarty->assign('download_title', $file_data['media_title']);
    $smarty->assign('download_text', $file_data['media_text']);
    $smarty->assign('attachment_filename', $file_name);
    $smarty->assign('attachment_download_url', $download_target);
}


/* start download */
if(isset($_POST['get_attachment'])) {

    se_increase_downloads_hits($get_product_id);
    $download_url = SE_CONTENT.'/files'.$product_data['file_attachment'];

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


$smarty->assign('votes_status_up', $product_data['votes_status_up']);
$smarty->assign('votes_status_dn', $product_data['votes_status_dn']);
$smarty->assign('votes_up', $product_data['votes_up']);
$smarty->assign('votes_dn', $product_data['votes_dn']);

$smarty->assign('show_voting', $show_voting);
$smarty->assign('product_img_src', $first_post_image);

$smarty->assign('product_id', $product_data['id']);
$smarty->assign('product_title', $product_data['title']);
$smarty->assign('product_teaser', $teaser);
$smarty->assign('product_text', $text);

$smarty->assign('product_pricetag_mode', $product_data['product_pricetag_mode']);
$smarty->assign('product_cart_mode', $product_data['product_cart_mode']);

$smarty->assign('form_action', $form_action);
$smarty->assign('btn_add_to_cart', $lang['btn_add_to_cart']);

$smarty->assign('label_delivery_time', $lang['label_product_delivery_time']);
$smarty->assign('product_delivery_time_title', $get_delivery_text['snippet_title']);
$smarty->assign('product_delivery_time_text', $get_delivery_text['snippet_content']);

$products_page = $smarty->fetch("products-display.tpl", $cache_id);
$smarty->assign('page_content', $products_page, true);