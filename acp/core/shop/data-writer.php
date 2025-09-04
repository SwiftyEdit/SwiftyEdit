<?php
//error_reporting(E_ALL);

// pagination
if(isset($_POST['pagination'])) {
    $_SESSION['pagination_products_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_products_list");
}

// pagination orders
if(isset($_POST['pagination_orders'])) {
    $_SESSION['pagination_orders'] = (int) $_POST['pagination_orders'];
    header( "HX-Trigger: update_orders_list");
}

// text search in products
if(isset($_POST['products_text_filter'])) {
    $_SESSION['pagination_products_page'] = 0;
    $_SESSION['products_text_filter'] = $_SESSION['products_text_filter'] . ' ' . sanitizeUserInputs($_POST['products_text_filter']);
    header( "HX-Trigger: update_products_list");
}

// text search in orders
if(isset($_POST['orders_text_filter'])) {
    $_SESSION['pagination_orders'] = 0;
    $_SESSION['orders_text_filter'] = $_SESSION['orders_text_filter'] . ' ' . sanitizeUserInputs($_POST['orders_text_filter']);
    header( "HX-Trigger: update_orders_list");
}

// remove keyword from products filter list
if(isset($_POST['rmkey'])) {
    $all_filter = explode(" ", $_SESSION['products_text_filter']);
    $_SESSION['products_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['products_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_products_list");
}

// remove keyword from orders filter list
if(isset($_POST['rmkey_orders'])) {
    $all_filter = explode(" ", $_SESSION['orders_text_filter']);
    $_SESSION['orders_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey_orders'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['orders_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_orders_list");
}

// search by keyword
if(isset($_POST['add_keyword'])) {
    $_SESSION['products_keyword_filter'] = $_SESSION['products_keyword_filter'] . ',' . sanitizeUserInputs($_POST['add_keyword']);
    header( "HX-Trigger: update_products_list");
    exit;
}

// remove keyword from filter
if(isset($_POST['remove_keyword'])) {
    $all_keywords_filter = explode(",", $_SESSION['products_keyword_filter']);
    $_SESSION['products_keyword_filter'] = '';
    foreach($all_keywords_filter as $f) {
        if($_POST['remove_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['products_keyword_filter'] .= $f.',';
    }
    header( "HX-Trigger: update_products_list");
    exit;
}

// sorting
if(isset($_POST['sorting_products'])) {
    $_SESSION['sorting_products'] = sanitizeUserInputs($_POST['sorting_products']);
    header( "HX-Trigger: update_products_list");
    exit;
}

if(isset($_POST['sorting_products_dir'])) {
    $_SESSION['sorting_products_direction'] = sanitizeUserInputs($_POST['sorting_products_dir']);
    header( "HX-Trigger: update_products_list");
    exit;
}

// delete product
// if there are variants, delete them, too
if(isset($_POST['delete_product']) && is_numeric($_POST['delete_product'])) {
    $delete_id = (int) $_POST['delete_product'];
    $cnt_changes = $db_posts->delete("se_products", [
        "OR" => [
            "id" => $delete_id,
            "parent_id" => $delete_id
        ]
    ]);

    if(($cnt_changes->rowCount()) > 0) {
        show_toast($lang['msg_info_data_deleted'],'success');
        record_log($_SESSION['user_nick'],"deleted product id: $delete_id","10");
        header( "HX-Redirect: /admin/shop/");
        $modus = 'new';
    }
}

// save or update products
if(isset($_POST['save_product']) OR isset($_POST['save_variant'])) {

    if(is_numeric($_POST['save_product']))	{
        $id = (int) $_POST['save_product'];
        $prepared_data = se_prepareProductData($_POST,$id);
        $db_posts->update("se_products", $prepared_data, [
            "id" => $id
        ]);
        $form_header_message = $lang['msg_success_db_changed'];
        show_toast($lang['msg_success_db_changed'],'success');
        record_log($_SESSION['user_nick'],"updated product id: $id","6");
    } else if(is_numeric($_POST['save_variant'])) {
        $prepared_data = se_prepareProductData($_POST);
        $db_posts->insert("se_products", $prepared_data);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
        show_toast($lang['msg_success_db_changed'],'success');
        record_log($_SESSION['user_nick'],"new product variant id: $id","6");

    } else {
        $prepared_data = se_prepareProductData($_POST);
        $db_posts->insert("se_products", $prepared_data);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
        show_toast($lang['msg_success_db_changed'],'success');
        record_log($_SESSION['user_nick'],"new product id: $id","6");
        // redirect to edit form
        header( "HX-Redirect: /admin/shop/edit/$id/");
    }

    // create cache files
    // cache file for the product, rebuild slug map

    se_updateProductCache($id, $prepared_data);
    se_generate_xml_sitemap('products');

}

// save or update price groups
if(isset($_POST['save_price'])) {
    $group_title = sanitizeUserInputs($_POST['title']);
    $unit = sanitizeUserInputs($_POST['unit']);
    $unit_content = sanitizeUserInputs($_POST['unit_content']);
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
            "unit_content" => $unit_content,
            "tax" => $tax,
            "price_net" => $price_net,
            "price_volume_discount" => $product_price_volume_discount
        ]);
        $edit_id = $db_posts->id();
        record_log($_SESSION['user_nick'], "create new price group", "1");
        header( "HX-Trigger: update_price_groups");
        exit;
    }
    // update data
    if(is_numeric($_POST['id'])) {
        $id = (int) $_POST['id'];
        $data = $db_posts->update("se_prices", [
            "title" => $group_title,
            "amount" => $amount,
            "unit" => $unit,
            "unit_content" => $unit_content,
            "tax" => $tax,
            "price_net" => $price_net,
            "price_volume_discount" => $product_price_volume_discount
        ],[
            "id" => $id
        ]);
        $edit_id = $id;
        header( "HX-Trigger: update_price_groups");
        exit;
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
    exit;
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
        $data = $db_content->update("se_filter", $insert_data, [
            "filter_id" => $id
        ]);
    } else {
        // new
        $data = $db_content->insert("se_filter", $insert_data);
    }
    if($data->rowCount() > 0) {
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        show_toast($lang['msg_error_db_changed'],'error');
    }
}

// save filter value
if(isset($_POST['save_filter_value'])) {

    $filter_type = 2;
    $filter_priority = (int) $_POST['filter_priority'];
    $filter_title = se_return_clean_value($_POST['filter_title']);
    $filter_description = $_POST['filter_description'];
    $filter_parent_id = $_POST['filter_parent_id'];
    $filter_hash = $_POST['filter_hash'];

    $insert_data = [
        "filter_type" =>  $filter_type,
        "filter_priority" => $filter_priority,
        "filter_title" =>  $filter_title,
        "filter_description" =>  $filter_description,
        "filter_parent_id" => $filter_parent_id,
        "filter_hash" => $filter_hash
    ];

    if(is_numeric($_POST['save_filter_value'])) {
        // update
        $id = (int) $_POST['save_filter_value'];
        $data = $db_content->update("se_filter", $insert_data, [
            "filter_id" => $id
        ]);
    } else {
        // new
        $data = $db_content->insert("se_filter", $insert_data);
    }
    if($data->rowCount() > 0) {
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        show_toast($lang['msg_error_db_changed'],'error');
    }
}

// delete filter value
if(isset($_POST['delete_filter_value'])) {
    $id = (int) $_POST['delete_filter_value'];
    $data = $db_content->delete("se_filter", [
        "filter_id" => $id
    ]);
    if($data->rowCount() > 0) {
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        show_toast($lang['msg_error_db_changed'],'error');
    }
    header( "HX-Redirect: /admin/shop/filters/");
}

// delete filter group
// delete all values from this group
if(isset($_POST['delete_filter_group'])) {
    $id = (int) $_POST['delete_filter_group'];
    $data = $db_content->delete("se_filter", [
        "OR" => [
            "filter_id" => $id,
            "filter_parent_id" => $id
        ]
    ]);
    if($data->rowCount() > 0) {
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        show_toast($lang['msg_error_db_changed'],'error');
    }
}

// change payment status
if(isset($_POST['set_payment'])) {
    $set_payment = (int) $_POST['set_payment'];
    $order_id = (int) $_POST['order_id'];
    $update = $db_content->update("se_orders", [
        "order_status_payment" => $set_payment
    ],[
        "id" => $order_id
    ]);
    header( "HX-Trigger: update_orders_list");
}

// change order status
if(isset($_POST['set_order_status'])) {
    $set_status = (int) $_POST['set_order_status'];
    $order_id = (int) $_POST['order_id'];
    $update = $db_content->update("se_orders", [
        "order_status" => $set_status
    ],[
        "id" => $order_id
    ]);
    header( "HX-Trigger: update_orders_list");
}

if(isset($_POST['products_cache'])) {

   if($_POST['products_cache'] == 'update') {

       $products = $db_posts->select("se_products", "*", [
           "status" => ['1','3']
       ]);
       foreach ($products as $product) {
           $product['context'] = 'cache';
           $prepared = se_prepareProductData($product);
           se_updateProductCache($product['id'], $prepared);
       }
       echo '<div class="alert alert-info my-1">Cache files updated</div>';
       exit;
   }

    if($_POST['products_cache'] == 'clear') {
        $nbr_deleted_files = se_clearProductCache();
        echo '<div class="alert alert-info my-1">Cache files deleted ('.$nbr_deleted_files.')</div>';
        exit;
    }
}