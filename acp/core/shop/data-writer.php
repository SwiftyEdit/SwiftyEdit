<?php
error_reporting(E_ALL);
// save or update products
if(isset($_POST['save_product'])) {

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = @htmlspecialchars($val, ENT_QUOTES);
        }
    }

    $releasedate = time();
    $lastedit = time();
    $lastedit_from = $_SESSION['user_nick'];
    $priority = (int) $_POST['post_priority'];
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
    if(isset($_POST['picker1_images'])) {
        $product_images_string = implode("<->", $_POST['picker1_images']);
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
    // build sql string -> f.e. "post_releasedate" => $post_releasedate,
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
        record_log($_SESSION['user_nick'],"updated product id: $id","6");
    } else if($_POST['save_product'] == "save_variant") {
        $db_posts->insert("se_products", $inputs);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
        record_log($_SESSION['user_nick'],"new product variant id: $id","6");
    } else {
        $db_posts->insert("se_products", $inputs);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
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

    header( "HX-Trigger: update_products_list");
}