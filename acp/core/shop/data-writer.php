<?php

/**
 * SwiftyEdit /admin-xhr/shop/write/
 * list actions of products and orders, saving products, price groups,
 * features, options and filters, order status
 *
 * global variables
 * @var object $db_posts
 * @var object $db_content
 * @var array $lang
 * @var array $icon
 */

// pagination
if(isset($_POST['pagination'])) {
    $_SESSION['pagination_products_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_products_list");
    exit;
}

// pagination orders
if(isset($_POST['pagination_orders'])) {
    $_SESSION['pagination_orders'] = (int) $_POST['pagination_orders'];
    header( "HX-Trigger: update_orders_list");
}

// text search in products
if(isset($_POST['products_text_filter'])) {
    $add_text_filter = sanitizeUserInputs($_POST['products_text_filter']);
    $text_filter = $_SESSION['products_text_filter'] ?? '';
    if($add_text_filter != '' && !in_array($add_text_filter, explode(' ', $text_filter))) {
        $_SESSION['products_text_filter'] = trim($text_filter.' '.$add_text_filter);
    }
    $_SESSION['pagination_products_page'] = 0;
    header("HX-Trigger: update_products_list");
    exit;
}

// text search in orders
if(isset($_POST['orders_text_filter'])) {
    $_SESSION['pagination_orders'] = 0;
    $_SESSION['orders_text_filter'] = $_SESSION['orders_text_filter'] . ' ' . sanitizeUserInputs($_POST['orders_text_filter']);
    header( "HX-Trigger: update_orders_list");
}

// remove keyword from products filter list
if(isset($_POST['rmkey'])) {
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $keywords = explode(' ', $_SESSION['products_text_filter'] ?? '');
    $keywords = array_filter($keywords, static fn($keyword) => $keyword != '' && $keyword != $remove_keyword);
    $_SESSION['products_text_filter'] = implode(' ', $keywords);
    $_SESSION['pagination_products_page'] = 0;
    header("HX-Trigger: update_products_list");
    exit;
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

// keywords of the sidebar, add or remove one
if(isset($_POST['add_keyword']) || isset($_POST['remove_keyword'])) {
    // the buttons send the plain keyword, the tags are stored sanitized
    $keyword = trim(sanitizeUserInputs($_POST['add_keyword'] ?? $_POST['remove_keyword']));
    $keywords = array_filter(explode(",", $_SESSION['products_keyword_filter'] ?? ''), static fn($k) => $k != '' && $k != $keyword);
    if(isset($_POST['add_keyword']) && $keyword != '') {
        $keywords[] = $keyword;
    }
    $_SESSION['products_keyword_filter'] = implode(",", $keywords);
    $_SESSION['pagination_products_page'] = 0;
    header("HX-Trigger: update_products_list");
    exit;
}

// sorting, the column is checked again by the data-reader
if(isset($_POST['sorting_products'])) {
    $_SESSION['sorting_products'] = sanitizeUserInputs($_POST['sorting_products']);
    header( "HX-Trigger: update_products_list");
    exit;
}

if(isset($_POST['sorting_products_dir'])) {
    $_SESSION['sorting_products_direction'] = ($_POST['sorting_products_dir'] == 'ASC') ? 'ASC' : 'DESC';
    header( "HX-Trigger: update_products_list");
    exit;
}

// delete product
// if there are variants, delete them, too
if(isset($_POST['delete_product']) && is_numeric($_POST['delete_product'])) {
    $delete_id = (int) $_POST['delete_product'];

    // remember id and language of the product and its variants for the cache cleanup
    $deleted_products = $db_posts->select("se_products", ["id", "product_lang"], [
        "OR" => [
            "id" => $delete_id,
            "parent_id" => $delete_id
        ]
    ]);

    $cnt_changes = $db_posts->delete("se_products", [
        "OR" => [
            "id" => $delete_id,
            "parent_id" => $delete_id
        ]
    ]);

    if(($cnt_changes->rowCount()) > 0) {
        se_delete_wishlist_items_by_product($delete_id);
        $db_content->delete("se_tags_relations", [
            "content_type" => "product",
            "content_id" => $delete_id
        ]);

        se_deleteProductCache($deleted_products);
        se_generate_xml_sitemap('products');

        se_do_backend_hook('product.deleted', [
            'product_id' => $delete_id,
            'user_id'    => $_SESSION['user_id'],
        ]);

        show_toast($lang['msg_info_data_deleted'],'success');
        record_log($_SESSION['user_nick'],"deleted product id: $delete_id","10");
        header( "HX-Redirect: /admin/shop/");
        $modus = 'new';
    }
}

// save or update products
if(isset($_POST['save_product']) OR isset($_POST['save_variant'])) {

    // "context" = "cache" makes se_prepareProductData() take the values
    // unescaped (meant for rows from the database) - never from a form
    unset($_POST['context']);

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

    se_set_content_tags('product', $id, explode(',', $_POST['content_tags'] ?? ''));

    // build hook context
    $context = [
        'product_id' => $id,
        'data'    => $_POST,
        'user_id' => $_SESSION['user_id'],
    ];

    // selected hooks from POST (or empty array)
    $selectedHooks = $_POST['hooks']['product.updated'] ?? [];

    // run only selected callbacks for this hook
    if (!empty($selectedHooks)) {
        se_do_backend_hook_selected('product.updated', $selectedHooks, $context);
    }

}

// save or update price groups
if(isset($_POST['save_price'])) {
    $group_title = sanitizeUserInputs($_POST['title'] ?? '');
    $unit = sanitizeUserInputs($_POST['unit'] ?? '');
    $unit_content = sanitizeUserInputs($_POST['unit_content'] ?? '');
    // prices are entered and stored formatted (1.234,56), without thousands separator
    $price_net = str_replace('.', '', sanitizeUserInputs($_POST['price_net'] ?? ''));

    $amount = (int) ($_POST['amount'] ?? 0);
    $tax = (int) ($_POST['tax'] ?? 1);

    // volume discounts, rows without an amount are skipped
    $vd_price = [];
    foreach($_POST['product_vd_amount'] ?? [] as $i => $vd_amount) {
        if($vd_amount == '') {
            continue;
        }
        $vd_price[] = [
            'amount' => (int) $vd_amount,
            'price' => str_replace('.', '', sanitizeUserInputs($_POST['product_vd_price'][$i] ?? ''))
        ];
    }
    $product_price_volume_discount = $vd_price ? json_encode($vd_price, JSON_FORCE_OBJECT) : '';

    $group_data = [
        "title" => $group_title,
        "amount" => $amount,
        "unit" => $unit,
        "unit_content" => $unit_content,
        "tax" => $tax,
        "price_net" => $price_net,
        "price_volume_discount" => $product_price_volume_discount
    ];

    // new price group - products reference it by the hash
    if(($_POST['id'] ?? '') == 'new') {
        $group_data['hash'] = md5(uniqid('', true));
        $db_posts->insert("se_prices", $group_data);
        record_log($_SESSION['user_nick'], "create new price group id: ".$db_posts->id(), 1);
        show_toast($lang['msg_success_new_record'],'success');
        // reload the form empty, a second click must not create the group again
        header( "HX-Trigger: update_price_groups, show_price_groups_form");
        exit;
    }

    // update price group
    if(is_numeric($_POST['id'] ?? '')) {
        $id = (int) $_POST['id'];
        $db_posts->update("se_prices", $group_data, [
            "id" => $id
        ]);
        record_log($_SESSION['user_nick'], "updated price group id: $id", 3);
        show_toast($lang['msg_success_db_changed'],'success');
        header( "HX-Trigger: update_price_groups");
        exit;
    }
}

// delete price group
// refuse if the group is still assigned to products (they reference it by hash)
if(isset($_POST['delete_price_group']) && is_numeric($_POST['delete_price_group'])) {
    $delete_id = (int) $_POST['delete_price_group'];
    $price_group = $db_posts->get("se_prices", ["hash"], [
        "id" => $delete_id
    ]);

    if(empty($price_group)) {
        show_toast($lang['msg_error_db_changed'],'danger');
        exit;
    }

    $cnt_products = $db_posts->count("se_products", [
        "product_price_group" => $price_group['hash']
    ]);

    if($cnt_products > 0) {
        show_toast($lang['msg_error_price_group_in_use'].' ('.$cnt_products.')','danger');
        exit;
    }

    $db_posts->delete("se_prices", [
        "id" => $delete_id
    ]);
    show_toast($lang['msg_info_data_deleted'],'success');
    record_log($_SESSION['user_nick'],"deleted price group id: $delete_id",10);
    header( "HX-Trigger: update_price_groups, show_price_groups_form");
    exit;
}

// change priority, the input in the list
// (the product form posts "priority" too, but without prio_id)
if(isset($_POST['priority'], $_POST['prio_id'])) {
    $change_id = (int) $_POST['prio_id'];
    $db_posts->update("se_products", [
        "priority" => (int) $_POST['priority']
    ],[
        "id" => $change_id
    ]);
    header( "HX-Trigger: update_products_list");
    exit;
}

// pin (sfixed) or unpin (rfixed) a product, the star in the list
if(isset($_POST['sfixed']) || isset($_POST['rfixed'])) {
    $db_posts->update("se_products", [
        "fixed" => isset($_POST['sfixed']) ? "1" : "2"
    ],[
        "id" => (int) ($_POST['sfixed'] ?? $_POST['rfixed'])
    ]);
    header( "HX-Trigger: update_products_list");
    exit;
}

// filter by category, a click adds or removes the category (products and filters)
if(isset($_POST['set_filter_cat'])) {
    $set_filter_cat = sanitizeUserInputs($_POST['set_filter_cat']);
    $filter_prod_categories = array_filter(explode(" ", $_SESSION['filter_prod_categories'] ?? ''));
    if(in_array($set_filter_cat, $filter_prod_categories)) {
        $filter_prod_categories = array_diff($filter_prod_categories, [$set_filter_cat]);
    } else {
        $filter_prod_categories[] = $set_filter_cat;
    }
    $_SESSION['filter_prod_categories'] = implode(" ", array_unique($filter_prod_categories));
    $_SESSION['pagination_products_page'] = 0;
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
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        $db_content->insert("se_snippets", [
            "snippet_title" => $feature_title,
            "snippet_content" => $feature_text,
            "snippet_priority" => $feature_priority,
            "snippet_lastedit" => $lastedit,
            "snippet_lang" => $feature_lang,
            "snippet_type" => 'post_feature'
        ]);
        $new_id = $db_content->id();

        show_toast($lang['msg_success_new_record'],'success');
        // switch the form into edit mode for the new feature, so an
        // unintended second click on "save" updates it instead of
        // creating another one
        header( "HX-Redirect: /admin/shop/features/edit/$new_id/");
    }

    header( "HX-Trigger: update_feature_list");
}

// delete feature
if(isset($_POST['delete_feature']) && is_numeric($_POST['delete_feature'])) {
    $delete_id = (int) $_POST['delete_feature'];
    $cnt_changes = $db_content->delete("se_snippets", [
        "AND" => [
            "snippet_type" => "post_feature",
            "snippet_id" => $delete_id
        ]
    ]);

    if(($cnt_changes->rowCount()) > 0) {
        show_toast($lang['msg_info_data_deleted'],'success');
        record_log($_SESSION['user_nick'],"deleted feature id: $delete_id","10");
        header( "HX-Redirect: /admin/shop/features/");
    } else {
        show_toast($lang['msg_error_db_changed'],'error');
    }
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
        show_toast($lang['msg_success_db_changed'],'success');
        // reload the edit form so the value list (and the empty "add value"
        // field) reflect what was just saved - otherwise a newly added value
        // only becomes visible/addable-to after leaving and reopening the form
        header( "HX-Redirect: /admin/shop/options/edit/$id/");
    } else {
        $db_content->insert("se_snippets", $insert_data);
        $new_id = $db_content->id();

        show_toast($lang['msg_success_new_record'],'success');
        // switch the form into edit mode for the new option, so an
        // unintended second click on "save" updates it instead of
        // creating another one
        header( "HX-Redirect: /admin/shop/options/edit/$new_id/");
    }

    header( "HX-Trigger: update_options_list");
}

// delete option
if(isset($_POST['delete_option']) && is_numeric($_POST['delete_option'])) {
    $delete_id = (int) $_POST['delete_option'];
    $cnt_changes = $db_content->delete("se_snippets", [
        "AND" => [
            "snippet_type" => "post_option",
            "snippet_id" => $delete_id
        ]
    ]);

    if(($cnt_changes->rowCount()) > 0) {
        show_toast($lang['msg_info_data_deleted'],'success');
        record_log($_SESSION['user_nick'],"deleted option id: $delete_id","10");
        header( "HX-Redirect: /admin/shop/options/");
    } else {
        show_toast($lang['msg_error_db_changed'],'error');
    }
}

// save filter group
if(isset($_POST['save_filter_group'])) {

    $filter_type = 1;
    $filter_priority = (int) $_POST['filter_priority'];
    $filter_input_type = (int) $_POST['filter_input_type'];
    $filter_title = se_return_clean_value($_POST['filter_title']);
    $filter_description = $_POST['filter_description'];
    $filter_lang = $_POST['filter_lang'];
    $filter_slug = se_generate_slug($filter_title);

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
        "filter_slug" =>  $filter_slug,
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
    $filter_slug = se_generate_slug($_POST['filter_title']);

    $insert_data = [
        "filter_type" =>  $filter_type,
        "filter_priority" => $filter_priority,
        "filter_title" =>  $filter_title,
        "filter_slug" =>  $filter_slug,
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
    se_update_order_payment_status($order_id, $set_payment, 'admin');
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

// acknowledge / clear a customer's withdrawal request
if(isset($_POST['clear_order_withdrawal_requested'])) {
    $order_id = (int) $_POST['order_id'];
    $update = $db_content->update("se_orders", [
        "order_withdrawal_requested" => null
    ],[
        "id" => $order_id
    ]);
    header( "HX-Trigger: update_orders_list");
}

if(isset($_POST['products_cache'])) {

   if($_POST['products_cache'] == 'update') {

       se_rebuild_all_product_cache();
       echo '<span class="badge rounded-pill text-bg-success alert-auto-close">'.$icon['check'].'</span>';
       exit;
   }

    if($_POST['products_cache'] == 'clear') {
        $nbr_deleted_files = se_clearProductCache();
        echo '<span class="badge rounded-pill text-bg-success alert-auto-close">'.$icon['check'].' ('.$nbr_deleted_files.')</span>';
        exit;
    }
}