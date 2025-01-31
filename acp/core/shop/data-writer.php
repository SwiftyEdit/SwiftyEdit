<?php
//error_reporting(E_ALL);

/**
 * pagination
 */

if(isset($_POST['pagination'])) {
    $_SESSION['pagination_products_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_products_list");
}

// save or update products
if(isset($_POST['save_product']) OR isset($_POST['save_variant'])) {

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = @htmlspecialchars($val, ENT_QUOTES);
        }
    }

    $releasedate = time();
    $lastedit = time();
    $lastedit_from = $_SESSION['user_nick'];
    $priority = (int) $_POST['priority'];
    $type = 'p';

    if(isset($_POST['type'])) {
        $type = clean_filename($_POST['type']);
    }

    if(isset($_POST['save_variant'])) {
        $type = 'v';
        $modus = 'save_variant';
        $parent_id = (int) $_POST['save_variant'];
    }

    $product_options = json_encode($_POST['option_keys'],JSON_FORCE_OBJECT);
    $product_accessories = json_encode($_POST['product_accessories'],JSON_FORCE_OBJECT);
    $product_related = json_encode($_POST['product_related'],JSON_FORCE_OBJECT);
    $filter = json_encode($_POST['product_filter'],JSON_FORCE_OBJECT);

    // translation url
    $translation_urls = '';
    if(is_array($_POST['translation_url'])) {
        foreach($_POST['translation_url'] as $k => $v) {
            $t_urls[$k] = se_clean_permalink($v);
        }
        $translation_urls = json_encode($t_urls,JSON_UNESCAPED_UNICODE);
    }

    if (isset($_POST['file_attachment_user']) && $_POST['file_attachment_user'] == '2'){
        $file_attachment_user = 2;
    } else {
        $file_attachment_user = 1;
    }

    if($_POST['date'] == "") {
        $date = time();
    }
    if($_POST['releasedate'] != "") {
        $releasedate = strtotime($_POST['releasedate']);
    }


    $clean_title = clean_filename($_POST['title']);
    $date_year = date("Y",$releasedate);
    $date_month = date("m",$releasedate);
    $date_day = date("d",$releasedate);


    if($_POST['slug'] == "") {
        $slug = $clean_title.'/';
    } else {
        $slug = se_clean_permalink($_POST['slug']);
    }

    $categories = '';
    if(isset($_POST['categories'])) {
        $categories = implode("<->", $_POST['categories']);
    }

    $images = '';
    if(isset($_POST['picker_0'])) {
        $product_images_string = implode("<->", $_POST['picker_0']);
        $product_images_string = "<->$product_images_string<->";
        $images = $product_images_string;
    }

    $product_price_net = str_replace('.', '', $_POST['product_price_net']);

    /* labels */
    $product_labels = '';
    if(isset($_POST['labels'])) {
        $labels = implode(",", $_POST['labels']);
    }

    /* fix on top */
    $fixed = 2;
    if(isset($_POST['fixed']) AND $_POST['fixed'] == 'fixed') {
        $post_fixed = 1;
    }

    $priority = (int) $_POST['priority'];

    /* stock mode */
    $product_stock_mode = 2;
    if(isset($_POST['product_ignore_stock']) AND $_POST['product_ignore_stock'] == 1) {
        // ignore stock
        $product_stock_mode = 1;
    }

    /* metas */
    if($_POST['meta_title'] == '') {
        $meta_title = $_POST['title'];
    } else {
        $meta_title = $_POST['meta_title'];
    }
    if($_POST['meta_description'] == '') {
        $meta_description = strip_tags($_POST['teaser']);
    } else {
        $meta_description = $_POST['meta_description'];
    }

    $meta_title = se_return_clean_value($meta_title);
    $meta_description = se_return_clean_value($meta_description);

    /* variants title and description */
    if($_POST['product_variant_title'] == '') {
        $product_variant_title = $_POST['title'];
    }
    if($_POST['product_variant_description'] == '') {
        $product_variant_description = $meta_description;
    }

    // volume discounts
    if(isset($_POST['product_vd_amount'])) {
        $cnt_vd_prices = count($_POST['product_vd_amount']);
        for($i=0;$i<$cnt_vd_prices;$i++) {

            if($_POST['product_vd_amount'][$i] == '') {
                continue;
            }

            $vd_price[] = [
                'amount' => (int) $_POST['product_vd_amount'][$i],
                'price' => $_POST['product_vd_price'][$i]
            ];

        }
        $product_price_volume_discount = json_encode($vd_price,JSON_FORCE_OBJECT);
    }

    /* get all $cols */

    require SE_ROOT.'install/contents/se_products.php';
    // build sql string -> f.e. "releasedate" => $releasedate,
    foreach($cols as $k => $v) {
        if($k == 'id') {continue;}
        $value = $$k;
        $inputs[$k] = "$value";
    }

    if(is_numeric($_POST['save_product']))	{
        $id = (int) $_POST['save_product'];
        $db_posts->update("se_products", $inputs, [
            "id" => $id
        ]);
        $form_header_message = $lang['msg_success_db_changed'];
        show_toast($lang['msg_success_db_changed'],'success');
        record_log($_SESSION['user_nick'],"updated product id: $id","6");
    } else if(is_numeric($_POST['save_variant'])) {
        $db_posts->insert("se_products", $inputs);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
        show_toast($lang['msg_success_db_changed'],'success');
        record_log($_SESSION['user_nick'],"new product variant id: $id","6");
    } else {
        $db_posts->insert("se_products", $inputs);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
        show_toast($lang['msg_success_db_changed'],'success');
        record_log($_SESSION['user_nick'],"new product id: $id","6");
    }


}

/**
 * save or update price groups
 */
if(isset($_POST['save_price'])) {
    $group_title = sanitizeUserInputs($_POST['title']);
    $unit = sanitizeUserInputs($_POST['unit']);
    $price_net = sanitizeUserInputs($_POST['price_net']);
    $price_net = str_replace('.', '', $price_net);

    $amount = (int) $_POST['amount'];
    $tax = (int) $_POST['tax'];
    $hash = md5(time());

    $product_price_volume_discount = '';
    if(isset($_POST['product_vd_amount'])) {
        $cnt_vd_prices = count($_POST['product_vd_amount']);
        for($i=0;$i<$cnt_vd_prices;$i++) {

            if($_POST['product_vd_amount'][$i] == '') {
                continue;
            }

            $amount = (int) $_POST['product_vd_amount'][$i];
            $price = sanitizeUserInputs($_POST['product_vd_price'][$i]);
            $price = str_replace('.', '', $price);

            $vd_price[] = [
                'amount' => $amount,
                'price' => $price
            ];

        }
        $product_price_volume_discount = json_encode($vd_price,JSON_FORCE_OBJECT);
    }

    // new data
    if($_POST['id'] == 'new') {
        $data = $db_posts->insert("se_prices", [
            "title" => $group_title,
            "hash" => $hash,
            "amount" => $amount,
            "unit" => $unit,
            "tax" => $tax,
            "price_net" => $price_net,
            "price_volume_discount" => $product_price_volume_discount
        ]);
        $edit_id = $db_posts->id();
        record_log($_SESSION['user_nick'], "create new price group", "1");
        header( "HX-Trigger: update_price_groups");
    }
    // update data
    if(is_numeric($_POST['id'])) {
        $id = (int) $_POST['id'];
        $data = $db_posts->update("se_prices", [
            "title" => $group_title,
            "amount" => $amount,
            "unit" => $unit,
            "tax" => $tax,
            "price_net" => $price_net,
            "price_volume_discount" => $product_price_volume_discount
        ],[
            "id" => $id
        ]);
        $edit_id = $id;
        header( "HX-Trigger: update_price_groups");
    }
}

// change priority
if(isset($_POST['priority'])) {
    $change_id = (int) $_POST['prio_id'];
    $db_posts->update("se_products", [
        "priority" => (int) $_POST['priority']
    ],[
        "id" => $change_id
    ]);
    header( "HX-Trigger: update_products_list");
    exit;
}

// remove fixed status
if(isset($_POST['rfixed'])) {
    $change_id = (int) $_POST['rfixed'];
    $db_posts->update("se_products", [
        "fixed" => "2"
    ],[
        "id" => $change_id
    ]);
    header( "HX-Trigger: update_products_list");
    exit;
}

// set fixed status
if(isset($_POST['sfixed'])) {
    $change_id = (int) $_POST['sfixed'];
    $db_posts->update("se_products", [
        "fixed" => "1"
    ],[
        "id" => $change_id
    ]);
    header( "HX-Trigger: update_products_list");
    exit;
}

if(isset($_POST['set_filter_cat'])) {

    $set_filter_cat = sanitizeUserInputs($_POST['set_filter_cat']);

    $filter_prod_categories = explode(" ", $_SESSION['filter_prod_categories']);
    $filter_prod_categories = array_unique($filter_prod_categories);

    if(in_array($set_filter_cat, $filter_prod_categories)) {
        // remove this category
        $filter_prod_categories = array_diff($filter_prod_categories, array($set_filter_cat));
    } else {
        // add this category
        $filter_prod_categories[] = $set_filter_cat;
    }

    $_SESSION['filter_prod_categories'] = implode(" ", $filter_prod_categories);

    header( "HX-Trigger: update_products_list, update_filter_list");
}

// save features
if(isset($_POST['save_feature'])) {

    $lastedit = time();
    $feature_title = se_return_clean_value($_POST['feature_title']);
    $feature_text = $_POST['feature_text'];
    $feature_priority = (int) $_POST['feature_priority'];
    $feature_lang = $_POST['feature_lang'];

    if(is_numeric($_POST['save_feature'])) {
        $id = (int) $_POST['save_feature'];
        $db_content->update("se_snippets", [
            "snippet_title" => $feature_title,
            "snippet_content" => $feature_text,
            "snippet_priority" => $feature_priority,
            "snippet_lastedit" => $lastedit,
            "snippet_lang" => $feature_lang,
            "snippet_type" => 'post_feature'
        ],[
            "snippet_id" => $id
        ]);
    } else {
        $db_content->insert("se_snippets", [
            "snippet_title" => $feature_title,
            "snippet_content" => $feature_text,
            "snippet_priority" => $feature_priority,
            "snippet_lastedit" => $lastedit,
            "snippet_lang" => $feature_lang,
            "snippet_type" => 'post_feature'
        ]);
    }


    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: update_feature_list");
}

// save options
if(isset($_POST['save_option'])) {
    $option_title = se_return_clean_value($_POST['option_title']);
    $option_priority = (int) $_POST['option_priority'];
    $option_lang = $_POST['option_lang'];
    $option_text = array_filter($_POST['option_text']);
    $option_text = json_encode($option_text,JSON_FORCE_OBJECT);

    $insert_data = [
        "snippet_lastedit" =>  time(),
        "snippet_priority" => $option_priority,
        "snippet_title" =>  $option_title,
        "snippet_content" =>  $option_text,
        "snippet_lang" =>  $option_lang,
        "snippet_type" => 'post_option'
    ];

    if(is_numeric($_POST['save_option'])) {
        $id = (int)$_POST['save_option'];
        $db_content->update("se_snippets", $insert_data, [
            "snippet_id" => $id
        ]);
    } else {
        $db_content->insert("se_snippets", $insert_data);
    }
    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: update_options_list");
}

// save filter group
if(isset($_POST['save_filter_group'])) {

    $filter_type = 1;
    $filter_priority = (int) $_POST['filter_priority'];
    $filter_input_type = (int) $_POST['filter_input_type'];
    $filter_title = se_return_clean_value($_POST['filter_title']);
    $filter_description = $_POST['filter_description'];
    $filter_lang = $_POST['filter_lang'];

    if(is_array($_POST['filter_cats'])) {
        $filter_cats = implode(",", $_POST['filter_cats']);
    } else {
        $filter_cats = '';
    }

    $insert_data = [
        "filter_type" =>  $filter_type,
        "filter_input_type" =>  $filter_input_type,
        "filter_priority" => $filter_priority,
        "filter_title" =>  $filter_title,
        "filter_description" =>  $filter_description,
        "filter_lang" =>  $filter_lang,
        "filter_categories" => $filter_cats
    ];

    if(is_numeric($_POST['save_filter_group'])) {
        // update
        $id = (int) $_POST['save_filter_group'];
        $db_content->update("se_filter", $insert_data, [
            "filter_id" => $id
        ]);
    } else {
        // new
        $db_content->insert("se_filter", $insert_data);
    }


    show_toast($lang['msg_success_db_changed'],'success');
}

// save filter value
if(isset($_POST['save_filter_value'])) {

    $filter_type = 2;
    $filter_priority = (int) $_POST['filter_priority'];
    $filter_title = se_return_clean_value($_POST['filter_title']);
    $filter_description = $_POST['filter_description'];
    $filter_parent_id = $_POST['filter_parent_id'];

    $insert_data = [
        "filter_type" =>  $filter_type,
        "filter_priority" => $filter_priority,
        "filter_title" =>  $filter_title,
        "filter_description" =>  $filter_description,
        "filter_parent_id" => $filter_parent_id
    ];

    if(is_numeric($_POST['save_filter_value'])) {
        // new
        $id = (int) $_POST['save_filter_value'];
        $db_content->update("se_filter", $insert_data, [
            "filter_id" => $id
        ]);
    } else {
        // new
        $db_content->insert("se_filter", $insert_data);
    }
    show_toast($lang['msg_success_db_changed'],'success');
}