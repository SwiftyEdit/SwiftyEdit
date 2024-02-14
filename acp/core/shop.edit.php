<?php
//error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
//prohibit unauthorized access
require __DIR__.'/access.php';

if((isset($_POST['delete_product'])) && is_numeric($_POST['delete_product'])) {
    $delete_product_id = (int) $_POST['delete_product'];
    $cnt_delete_product = se_delete_product($delete_product_id);
    if($cnt_delete_product > 0) {
        echo '<div class="alert alert-success">'.$lang['msg_post_deleted'].' ('.$cnt_delete_product.')</div>';
        record_log($_SESSION['user_nick'],"delete product id: $delete_product_id","6");
    }
}

/* set modus */

$modus = 'new';
$form_header_mode = 'New item';
if((!empty($_POST['duplicate'])) OR ($_POST['modus'] == 'duplicate')) {
    $id = (int) $_POST['duplicate'];
    $modus = "duplicate";
    $form_header_mode = 'Duplicate: '.$id;
    $product_data = se_get_product_data($id);
    $submit_btn = '<input type="submit" class="btn btn-success w-100" name="save_product" value="'.$lang['duplicate'].'">';
    $submit_variant_btn = '<button type="submit" class="btn btn-default w-100 my-1" name="save_variant" value="'.$id.'">'.$lang['btn_submit_variant'].'</button>';
}

if((!empty($_POST['edit_id'])) && is_numeric($_POST['edit_id'])) {
    $id = (int) $_POST['edit_id'];
    $modus = 'update';
    $form_header_mode = 'Edit: '.$id;
    $product_data = se_get_product_data($id);
    $submit_btn = '<button type="submit" class="btn btn-success w-100" name="save_product" value="'.$id.'">'.$lang['update'].'</button>';
    $submit_delete_btn = '<button onclick="return confirm(\''.$lang['confirm_delete_data'].'\');" type="submit" class="btn btn-danger w-100 my-1" name="delete_product" value="'.$id.'">'.$lang['delete'].'</button>';
}

if($modus == 'new') {
    $id = '';
    $product_data = array();
    $submit_btn = '<input type="submit" class="btn btn-success w-100" name="save_product" value="'.$lang['save'].'">';
    $submit_variant_btn = '';
    /* reset values */
    require '../install/contents/se_products.php';
    foreach($cols as $k => $v) {
        $product_data[$k] = '';
    }
    $product_data['date'] = time();
    $product_data['priority'] = 0;
}



/* save or update post data */

if(isset($_POST['save_product']) OR isset($_POST['save_variant']) OR isset($_POST['del_tmb']) OR isset($_POST['sort_tmb'])) {

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

    $product_features = '';
    if(isset($_POST['product_features'])) {
        $product_features = json_encode($_POST['product_features'],JSON_FORCE_OBJECT);
    }
    $product_features_values = '';
    if(isset($_POST['product_features_values'])) {
        $product_features_values = json_encode($_POST['product_features_values'],JSON_FORCE_OBJECT);
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


    /* save or update data */

    /* get all $cols */
    require '../install/contents/se_products.php';
    // build sql string -> f.e. "post_releasedate" => $post_releasedate,
    foreach($cols as $k => $v) {
        if($k == 'id') {continue;}
        $value = $$k;
        $inputs[$k] = "$value";
    }

    if($modus == "update")	{
        $db_posts->update("se_products", $inputs, [
            "id" => $id
        ]);
        $form_header_message = $lang['db_record_changed'];
        record_log($_SESSION['user_nick'],"updated product id: $id","6");
    } else if($modus == "save_variant") {
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

    /* update the rss url */

    // get the product-page by 'type_of_use' and $languagePack
    $target_page = $db_content->select("se_pages", "page_permalink", [
        "AND" => [
            "page_type_of_use" => "display_product",
            "page_language" => $product_lang
        ]
    ]);

    // if we have no target page - find a shop page
    if($target_page[0] == '') {
        $target_page = $db_content->select("se_pages", "page_permalink", [
            "AND" => [
                "page_posts_types[~]" => "p",
                "page_language" => $product_lang
            ]
        ]);
    }

    if($target_page[0] != '') {
        $rss_url = $se_base_url.$target_page[0].$clean_title.'-'.$id.'.html';
        $db_posts->update("se_products", [
            "rss_url" => $rss_url
        ], [
            "id" => $id
        ]);

        /* send to rss feed */
        if($_POST['rss'] == 'on') {
            add_feed("$post_title",$_POST['teaser'],"$rss_url","$id","",$releasedate);
        }
    }


    /* re load the posts data */
    $product_data = se_get_product_data($id);
}





/* language */
$product_lang = '';
if(isset($product_data['product_lang'])) {
    $product_lang = $product_data['product_lang'];
}

if($product_lang == '' && $default_lang_code != '') {
    $product_lang = $default_lang_code;
}

$select_lang  = '<select name="product_lang" class="custom-select form-control">';
foreach($lang_codes as $lang_code) {
    $select_lang .= "<option value='$lang_code'".($product_lang == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";
}
$select_lang .= '</select>';



/* categories */

$cats = se_get_categories();
$cnt_cats = count($cats);
$checkboxes_cat = '';
$array_categories = array();

for($i=0;$i<$cnt_cats;$i++) {
    $category = $cats[$i]['cat_name'];
    if(isset($product_data['categories'])) {
        $array_categories = explode("<->", $product_data['categories']);
    }

    $checked = "";
    if(in_array($cats[$i]['cat_hash'], $array_categories)) {
        $checked = "checked";
    }
    $checkboxes_cat .= '<div class="form-check">';
    $checkboxes_cat .= '<input class="form-check-input" id="cat'.$i.'" type="checkbox" name="categories[]" value="'.$cats[$i]['cat_hash'].'" '.$checked.'>';
    $checkboxes_cat .= '<label class="form-check-label" for="cat'.$i.'">'.$category.' <small>('.$cats[$i]['cat_lang'].')</small></label>';
    $checkboxes_cat .= '</div>';
}


/* release date */
if(isset($product_data['releasedate']) AND $product_data['releasedate'] > 0) {
    $releasedate = date('Y-m-d H:i:s', $product_data['releasedate']);
} else {
    $releasedate = date('Y-m-d H:i:s', time());
}


/* priority */
$select_priority = "<select name='priority' class='form-control custom-select'>";
for($i=1;$i<11;$i++) {
    $option_add = '';
    $sel_prio = '';
    if($i == 1) {
        $option_add = ' ('.$lang['label_priority_bottom'].')';
    }
    if($i == 10) {
        $option_add = ' ('.$lang['label_priority_top'].')';
    }
    if(isset($product_data['priority']) AND $product_data['priority'] == $i) {
        $sel_prio = 'selected';
    }
    $select_priority .= '<option value="'.$i.'" '.$sel_prio.'>'.$i.' '.$option_add.'</option>';
}
$select_priority .= '</select>';



/* fix post on top */
$checked_fixed = '';
if($product_data['fixed'] == '1') {
    $checked_fixed = 'checked';
}
$checkbox_fixed  = '<div class="form-check">';
$checkbox_fixed .= '<input class="form-check-input" id="fix" type="checkbox" name="fixed" value="fixed" '.$checked_fixed.'>';
$checkbox_fixed .= '<label class="form-check-label" for="fix">'.$lang['label_fixed'].'</label>';
$checkbox_fixed .= '</div>';


/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("<->", $product_data['images']);
$choose_images = se_select_img_widget($images,$array_images,$se_prefs['prefs_shop_images_prefix'],1);

/* status | draft or published */
$sel_status_draft = '';
$sel_status_published = '';
$sel_status_ghost = '';
if($product_data['status'] == "2") {
    $sel_status_draft = "selected";
} else if($product_data['status'] == "1") {
    $sel_status_published = "selected";
} else if($product_data['status'] == "3") {
    $sel_status_ghost = "selected";
}

$select_status = "<select name='status' class='form-control custom-select'>";
if($_SESSION['drm_can_publish'] == "true") {
    $select_status .= '<option value="2" '.$sel_status_draft.'>'.$lang['status_draft'].'</option>';
    $select_status .= '<option value="1" '.$sel_status_published.'>'.$lang['status_public'].'</option>';
    $select_status .= '<option value="3" '.$sel_status_ghost.'>'.$lang['status_ghost'].'</option>';
} else {
    /* user can not publish */
    $select_status .= '<option value="draft" selected>'.$lang['status_draft'].'</option>';
}
$select_status .= '</select>';

/* comments yes/no */

if($product_data['comments'] == 1) {
    $sel_comments_yes = 'selected';
    $sel_comments_no = '';
} else {
    $sel_comments_no = 'selected';
    $sel_comments_yes = '';
}

$select_comments  = '<select id="select_comments" name="comments"  class="custom-select form-control">';
$select_comments .= '<option value="1" '.$sel_comments_yes.'>'.$lang['yes'].'</option>';
$select_comments .= '<option value="2" '.$sel_comments_no.'>'.$lang['no'].'</option>';
$select_comments .= '</select>';

/* votings/reactions no, yes for registered users, yes for all */

if($product_data['votings'] == '') {
    $product_data['votings'] = $se_prefs['prefs_posts_default_votings'];
}

if($product_data['votings'] == 1 OR $product_data['votings'] == '') {
    $sel_votings_1 = 'selected';
    $sel_votings_2 = '';
    $sel_votings_3 = '';
} else if($product_data['post_votings'] == 2) {
    $sel_votings_1 = '';
    $sel_votings_2 = 'selected';
    $sel_votings_3 = '';
} else {
    $sel_votings_1 = '';
    $sel_votings_2 = '';
    $sel_votings_3 = 'selected';
}

$select_votings  = '<select id="select_votings" name="votings"  class="custom-select form-control">';
$select_votings .= '<option value="1" '.$sel_votings_1.'>'.$lang['label_votings_off'].'</option>';
$select_votings .= '<option value="2" '.$sel_votings_2.'>'.$lang['label_votings_on_registered'].'</option>';
$select_votings .= '<option value="3" '.$sel_votings_3.'>'.$lang['label_votings_on_global'].'</option>';
$select_votings .= '</select>';


/* author */

if($product_data['author'] == '') {
    $product_data['author'] = $_SESSION['user_firstname'] .' '. $_SESSION['user_lastname'];
}

if($product_data['author'] == "" && $se_prefs['prefs_default_publisher'] != '') {
    $product_data['author'] = $se_prefs['prefs_default_publisher'];
}

if($se_prefs['prefs_publisher_mode'] == 'overwrite') {
    $product_data['author'] = $se_prefs['prefs_default_publisher'];
}


/* RSS */
$sel_rss_on = '';
$sel_rss_off = '';
if($product_data['rss'] == "on") {
    $sel_rss_on = "selected";
} else {
    $sel_rss_off = "selected";
}
$select_rss = "<select name='rss' class='form-control custom-select'>";
$select_rss .= '<option value="on" '.$sel_rss_on.'>'.$lang['yes'].'</option>';
$select_rss .= '<option value="off" '.$sel_rss_off.'>'.$lang['no'].'</option>';
$select_rss .=	'</select>';


if($product_data['translation_urls'] != '') {
    $product_translation_urls = html_entity_decode($product_data['translation_urls']);
    $translation_urls_array = json_decode($product_translation_urls,true);
}

$translation_inputs = '';
foreach($active_lang as $k => $v) {
    $ls = $v['sign'];
    $translation_inputs .= '<div class="input-group mb-3">';
    $translation_inputs .= '<span class="input-group-text"><i class="bi bi-translate me-1"></i> '.$ls.'</span>';
    $translation_inputs .= '<input class="form-control" type="text" autocomplete="off" name="translation_url['.$ls.']" id="set_canonical_url_'.$ls.'" value="'.$translation_urls_array[$ls].'">';
    $translation_inputs .= '</div>';
}


/* select tax */

$get_tax = 0;
if($product_data['product_tax'] == '2') {
    $sel_tax_2 = 'selected';
    $sel_tax_1 = '';
    $sel_tax_3 = '';
    $get_tax = $se_prefs['prefs_posts_products_tax_alt1'];
} else if($product_data['product_tax'] == '3') {
    $sel_tax_3 = 'selected';
    $sel_tax_2 = '';
    $sel_tax_1 = '';
    $get_tax = $se_prefs['prefs_posts_products_tax_alt2'];
} else {
    $sel_tax_1 = 'selected';
    $sel_tax_2 = '';
    $sel_tax_3 = '';
    $get_tax = $se_prefs['prefs_posts_products_default_tax'];
}

$select_tax = "<select name='product_tax' class='form-control custom-select' id='tax'>";
$select_tax .= '<option value="1" '.$sel_tax_1.'>'.$se_prefs['prefs_posts_products_default_tax'].'</option>';
$select_tax .= '<option value="2" '.$sel_tax_2.'>'.$se_prefs['prefs_posts_products_tax_alt1'].'</option>';
$select_tax .= '<option value="3" '.$sel_tax_3.'>'.$se_prefs['prefs_posts_products_tax_alt2'].'</option>';
$select_tax .= '</select>';

/* select shipping mode */

if(($product_data['product_shipping_mode'] == '1') OR ($product_data['product_shipping_mode'] == '')) {
    $sel_shipping_mode_1 = 'selected';
    $sel_shipping_mode_2 = '';
} else {
    $sel_shipping_mode_2 = 'selected';
    $sel_shipping_mode_1 = '';
}

$select_shipping_mode = "<select name='product_shipping_mode' class='form-control custom-select' id='shipping_mode'>";
$select_shipping_mode .= '<option value="1" '.$sel_shipping_mode_1.'>'.$lang['label_shipping_mode_digital'].'</option>';
$select_shipping_mode .= '<option value="2" '.$sel_shipping_mode_2.'>'.$lang['label_shipping_mode_deliver'].'</option>';
$select_shipping_mode .= '</select>';

/* select shipping category */
if($product_data['product_shipping_cat'] == '0' OR $product_data['product_shipping_cat'] == '') {
    $sel_shipping_cat_0 = 'selected';
    $sel_shipping_cat_1 = '';
    $sel_shipping_cat_2 = '';
    $sel_shipping_cat_3 = '';
} else if($product_data['product_shipping_cat'] == '1') {
    $sel_shipping_cat_0 = '';
    $sel_shipping_cat_1 = 'selected';
    $sel_shipping_cat_2 = '';
    $sel_shipping_cat_3 = '';
} else if($product_data['product_shipping_cat'] == '2') {
    $sel_shipping_cat_0 = '';
    $sel_shipping_cat_1 = '';
    $sel_shipping_cat_2 = 'selected';
    $sel_shipping_cat_3 = '';
} else if($product_data['product_shipping_cat'] == '3') {
    $sel_shipping_cat_0 = '';
    $sel_shipping_cat_1 = '';
    $sel_shipping_cat_2 = '';
    $sel_shipping_cat_3 = 'selected';
}

$select_shipping_category = "<select name='product_shipping_cat' class='form-control custom-select' id='shipping_mode_cat'>";
$select_shipping_category .= '<option value="0" '.$sel_shipping_cat_0.'>'.$lang['label_shipping_costs_no_cat'].'</option>';
$select_shipping_category .= '<option value="1" '.$sel_shipping_cat_1.'>'.$lang['label_shipping_costs_cat1'].'</option>';
$select_shipping_category .= '<option value="2" '.$sel_shipping_cat_2.'>'.$lang['label_shipping_costs_cat2'].'</option>';
$select_shipping_category .= '<option value="3" '.$sel_shipping_cat_3.'>'.$lang['label_shipping_costs_cat3'].'</option>';
$select_shipping_category .= '</select>';

/* product filter */

$all_filters = se_get_product_filter_groups('all');
$get_product_filter = json_decode($product_data['filter'],true);


$filter_list = '';
foreach($all_filters as $k => $v) {

    $group_categories = explode(",",$v['filter_categories']);
    $filter_cats = '';
    foreach($cats as $key => $value) {
        if (in_array($value['cat_hash'], $group_categories)) {
            $filter_cats .= '<span class="badge badge-se text-opacity-50">'.$value['cat_name'].'</span>';
        }
    }

    $filter_list .= '<div class="card mb-1">';
    $filter_list .= '<div class="card-header">'.$v['filter_title'].' <div class="float-end">'.$filter_cats.'</div></div>';
    $filter_list .= '<div class="card-body">';
    $get_filter_items = se_get_product_filter_values($v['filter_id']);
    foreach($get_filter_items as $filter_item) {
        $filter_id = $filter_item['filter_id'];

        $checked_filter = '';
        if(is_array($get_product_filter)) {
            if(array_search("$filter_id", $get_product_filter) !== false) {
                $checked_filter = 'checked';
            }
        }

        $filter_list .= '<div class="form-check form-check-inline pe-3 border-end">';
        $filter_list .= '<input class="form-check-input" id="filter_'.$filter_id.'" type="checkbox" name="product_filter[]" value="'.$filter_id.'" '.$checked_filter.'>';
        $filter_list .= '<label class="form-check-label" for="filter_'.$filter_id.'">'.$filter_item['filter_title'].'</label>';
        $filter_list .= '</div>';
    }
    $filter_list .= '</div>';
    $filter_list .= '</div>';
}


/* features */
$all_posts_features = se_get_posts_features();
$get_post_features = json_decode($product_data['product_features'],true);
$get_post_features_values = json_decode($product_data['product_features_values'],true);
$checkbox_features = '';
foreach($all_posts_features as $feature) {

    $feature_id = $feature['snippet_id'];
    $checked_feature = '';
    if(is_array($get_post_features)) {
        if(array_search("$feature_id", $get_post_features) !== false) {
            $checked_feature = 'checked';
        }
    }

    $feature_title = $feature['snippet_title'];
    $feature_text = strip_tags(first_words($feature['snippet_content'],15));

    $checkbox_features .= '<div class="form-check">';
    $checkbox_features .= '<input class="form-check-input" id="feature_'.$feature_id.'" type="checkbox" name="product_features[]" value="'.$feature_id.'" '.$checked_feature.'>';
    $checkbox_features .= '<label class="form-check-label" for="feature_'.$feature_id.'">'.$feature_title.' <small class="text-muted">'.$feature_text.'</small></label>';
    $checkbox_features .= '</div>';
    $checkbox_features .= '<div class="mb-3">';
    $this_value = '';
    if($get_post_features_values[$feature_id] != '') {
        $this_value = $get_post_features_values[$feature_id];
    }
    $checkbox_features .= '<input type="text" class="form-control" name="product_features_values['.$feature_id.']" value="'.$this_value.'">';
    $checkbox_features .= '</div>';
}


/* related products and accessories */

$all_products = se_get_all_products();

$get_prod_related = json_decode($product_data['product_related'],true);
$checkbox_related_prod = '';

foreach($all_products as $prod) {

    $prod_id = $prod['id'];
    $prod_title = $prod['title'];
    $checked_prod = '';
    if(is_array($get_prod_related)) {
        if(array_search("$prod_id", $get_prod_related) !== false) {
            $checked_prod = 'checked';
        }
    }

    $checkbox_related_prod .= '<div class="form-check">';
    $checkbox_related_prod .= '<input class="form-check-input" id="related_'.$prod_id.'" type="checkbox" name="product_related[]" value="'.$prod_id.'" '.$checked_prod.'>';
    $checkbox_related_prod .= '<label class="form-check-label" for="related_'.$prod_id.'">'.$prod_title.' <small class="text-muted">('.$prod_id.')</small></label>';
    $checkbox_related_prod .= '</div>';
}

$get_prod_accessories = json_decode($product_data['product_accessories'],true);
$checkbox_accessories_prod = '';

foreach($all_products as $prod) {

    $prod_id = $prod['id'];
    $prod_title = $prod['title'];
    $checked_accessory = '';
    if(is_array($get_prod_accessories)) {
        if(array_search("$prod_id", $get_prod_accessories) !== false) {
            $checked_accessory = 'checked';
        }
    }

    $checkbox_accessories_prod .= '<div class="form-check">';
    $checkbox_accessories_prod .= '<input class="form-check-input" id="accessories_'.$prod_id.'" type="checkbox" name="product_accessories[]" value="'.$prod_id.'" '.$checked_accessory.'>';
    $checkbox_accessories_prod .= '<label class="form-check-label" for="accessories_'.$prod_id.'">'.$prod_title.' <small class="text-muted">'.$prod_id.'</small></label>';
    $checkbox_accessories_prod .= '</div>';
}

/* product options */

if($product_data['product_options'] != '' OR $product_data['product_options'] != null) {
    $product_options = json_decode($product_data['product_options'],JSON_OBJECT_AS_ARRAY);
}

$get_options = se_get_posts_options();
$cnt_options = count($get_options);
$options_input_str = '';
for($i=0;$i<$cnt_options;$i++) {
    $option_values = '';
    $option_title = $get_options[$i]['snippet_title'];
    $option_values_array = json_decode($get_options[$i]['snippet_content']);
    $sel = '';
    if(is_array($product_options)) {
        if(in_array($get_options[$i]['snippet_id'],$product_options)) {
            $sel = 'checked';
        }
    }

    foreach($option_values_array as $value) {
        $option_values .= '<span class="badge badge-secondary">'.$value.'</span> ';
    }
    $option_label = $option_title.'<br>'.$option_values;
    $options_input_str .= '<div class="form-check">';
    $options_input_str .= '<input type="checkbox" id="opt_input'.$i.'" class="form-check-input" name="option_keys[]" value="'.$get_options[$i]['snippet_id'].'" '.$sel.'>';
    $options_input_str .= '<label class="form-check-label" for="opt_input'.$i.'">'.$option_label.'</label>';
    $options_input_str .= '</div>';
}

$checked_user_uploads = '';
if($product_data['file_attachment_user'] == '2') {
    $checked_user_uploads = 'checked';
}

// template variable
$options_input = $options_input_str;


/* variants */

$variants = array();
if((isset($product_data['id'])) && (is_numeric($product_data['id']))) {
    $variants = se_get_product_variants($product_data['id']);
    $cnt_variants = count($variants);
}

$edit_variant_btn = '';
if($cnt_variants > 1) {
    foreach($variants as $variant) {
        $variants_list .= '<button class="btn btn-default btn-sm" type="submit" name="edit_id" value="'.$variant['id'].'">'.$icon['edit'].' '.$variant['title'].' (#: '.$variant['id'].')</button> ';
    }
}

$product_price_net = $product_data['product_price_net'];
if($product_price_net == '') {
    $product_price_net = '0,00';
}

$product_currency = $product_data['product_currency'];
if($product_currency == '') {
    $product_currency = $se_prefs['prefs_posts_products_default_currency'];
}

if($product_data['product_price_addition'] == '') {
    $product_data['product_price_addition'] = 0;
}

$product_price_net_purchasing = $product_data['product_price_net_purchasing'];
if($product_price_net_purchasing == '') {
    $product_price_net_purchasing = '0,00';
}

// select for price groups

$get_price_groups = se_get_price_groups();

$select_price_groups = '<select class="form-control custom-select" name="product_price_group">';
$select_price_groups .= '<option value="null">'.$lang['label_select_price_group'].'</option>';
foreach($get_price_groups as $price_group) {
    $selected = "";
    if($price_group['hash'] == $product_data['product_price_group']) {
        $selected = 'selected';
    }
    $select_price_groups .= '<option '.$selected.' value='.$price_group['hash'].'>'.$price_group['title'].'</option>';
}
$select_price_groups .= '</select>';


/* volume discounts */

$volume_discounts = json_decode($product_data['product_price_volume_discount'],true);
$cnt_volume_discounts = 1;
if(is_array($volume_discounts)) {
    $cnt_volume_discounts = count($volume_discounts);
}


$show_price_volume_discount = '<div class="card my-2">';
$show_price_volume_discount .= '<div class="card-header">';
$show_price_volume_discount .= '<span>'.$lang['label_scaling_prices'].'</span>';
$show_price_volume_discount .= '<button class="btn btn-default btn-sm float-end" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVDP" aria-expanded="false" aria-controls="collapseExample">+</button>';
$show_price_volume_discount .= '</div>';
$show_price_volume_discount .= '<div class="card-body collapse" id="collapseVDP">';


for($i=0;$i<($cnt_volume_discounts+5);$i++) {

    $this_ammount = $volume_discounts[$i]['amount'];
    $price_net = $volume_discounts[$i]['price'];

    $show_price_volume_discount .= '<div class="calculate_price">';
    $show_price_volume_discount .= '<div class="row">';
    $show_price_volume_discount .= '<div class="col-md-2">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_amount'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control" name="product_vd_amount[]" type="number" value="'.$this_ammount.'">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '<div class="col-md-3">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_price_net'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control prod_price_net" name="product_vd_price[]" type="text" value="'.$price_net.'">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '<div class="col-md-3">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_price_gross'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control prod_price_gross" name="product_vd_price_gross[]" type="text" value="">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';
}

$show_price_volume_discount .= '</div>';
$show_price_volume_discount .= '</div>';

/* select delivery time */

$snippets_delivery_time = $db_content->select("se_snippets", "*", [
    "snippet_name" => "shop_delivery_time"
]);

$snippet_select_delivery_time = '<select class="form-control custom-select" name="product_delivery_time">';
$snippet_select_delivery_time .= '<option value="no_specification">'.$lang['product_no_delivery_time'].'</option>';
foreach($snippets_delivery_time as $snippet) {
    $selected = "";
    if($snippet['snippet_id'] == $product_data['product_delivery_time']) {
        $selected = 'selected';
    }
    $snippet_select_delivery_time .= '<option '.$selected.' value='.$snippet['snippet_id'].'>'.$snippet['snippet_title'].'</option>';
}
$snippet_select_delivery_time .= '</select>';



/* add text snippet to prices */

$snippet_select_pricelist = '<select class="form-control custom-select" name="product_textlib_price">';
$snippet_select_pricelist .= '<option value="no_snippet">'.$lang['product_no_snippet'].'</option>';

$snippets_price_list = $db_content->select("se_snippets", "*", [
    "snippet_name[~]" => "%post_price%"
]);

foreach($snippets_price_list as $snippet) {
    $selected = "";
    if($snippet['snippet_name'] == $product_data['post_product_snippet_price']) {
        $selected = 'selected';
    }
    $snippet_select_pricelist .= '<option '.$selected.' value='.$snippet['snippet_name'].'>'.$snippet['snippet_name']. ' - ' .$snippet['snippet_title'].'</option>';
}
$snippet_select_pricelist .= '</select>';


/* add text snippet to text */

$snippet_select_text = '<select class="form-control custom-select" name="product_textlib_content" id="snippet_tex">';
$snippet_select_text .= '<option value="no_snippet">'.$lang['product_no_snippet'].'</option>';
$snippets_text_list = $db_content->select("se_snippets", "*", [
    "snippet_name[~]" => "%post_text%"
]);
foreach($snippets_text_list as $snippet) {
    $selected = "";
    if($snippet['snippet_name'] == $product_data['product_textlib_content']) {
        $selected = 'selected';
    }
    $snippet_select_text .= '<option '.$selected.' value='.$snippet['snippet_name'].'>'.$snippet['snippet_name']. ' - ' .$snippet['snippet_title'].'</option>';
}
$snippet_select_text .= '</select>';


/* select file from /content/files/ */

$files_directory = SE_CONTENT.'/files';
$all_files = se_scandir_rec($files_directory);

/* pre-sale files */
$select_file = '<select class="form-control custom-select" name="file_attachment">';
$select_file .= '<option value="">-- '.$lang['label_file_select_no_file'].' --</option>';

foreach($all_files as $file) {
    //$se_upload_file_types is set in config.php
    $file_info = pathinfo($file);
    if(in_array($file_info['extension'],$se_upload_file_types)) {
        $short_path = str_replace("$files_directory","",$file);
        $selected = "";
        if($product_data['file_attachment'] == $short_path) {
            $selected = 'selected';
        }
        $select_file .= '<option '.$selected.' value='.$short_path.'>'.$short_path.'</option>';
    }
}
$select_file .= '</select>';

/* after-sale files */
$select_file_as = '<select class="form-control custom-select" name="file_attachment_as">';
$select_file_as .= '<option value="">-- '.$lang['label_file_select_no_file'].' --</option>';

foreach($all_files as $file) {
    //$se_upload_file_types is set in config.php
    $file_info = pathinfo($file);
    if(in_array($file_info['extension'],$se_upload_file_types)) {
        $short_path = str_replace("$files_directory","",$file);
        $selected = "";
        if($product_data['file_attachment_as'] == $short_path) {
            $selected = 'selected';
        }
        $select_file_as .= '<option '.$selected.' value='.$short_path.'>'.$short_path.'</option>';
    }
}
$select_file_as .= '</select>';


/* select for shopping cart mode */
$selected_scm_off = '';
$selected_scm_on = '';

if($product_data['product_cart_mode'] == 2) {
    $selected_scm_off = 'selected';
} else {
    $selected_scm_on = 'selected';
}

$select_cart_mode = '<select class="form-control custom-select" name="product_cart_mode">';
$select_cart_mode .= '<option '.$selected_scm_on.' value="1">'.$lang['product_cart_mode_on'].'</option>';
$select_cart_mode .= '<option '.$selected_scm_off.' value="2">'.$lang['product_cart_mode_off'].'</option>';
$select_cart_mode .= '</select>';


/* select for pricetag mode */
$selected_pricetag_off = '';
$selected_pricetag_on = '';

if($product_data['product_pricetag_mode'] == 2) {
    $selected_pricetag_off = 'selected';
} else {
    $selected_pricetag_on = 'selected';
}

$select_pricetag_mode = '<select class="form-control custom-select" name="product_pricetag_mode">';
$select_pricetag_mode .= '<option '.$selected_pricetag_on.' value="1">'.$lang['product_pricetag_mode_on'].'</option>';
$select_pricetag_mode .= '<option '.$selected_pricetag_off.' value="2">'.$lang['product_pricetag_mode_off'].'</option>';
$select_pricetag_mode .= '</select>';

/* stock */

$product_data['product_nbr_stock'] = (int) $product_data['product_nbr_stock'];
$product_data['product_cnt_sales'] = (int) $product_data['product_cnt_sales'];

if($product_data['product_stock_mode'] == 1) {
    $checkIgnoreStock = 'checked';
} else {
    $checkIgnoreStock = '';
}



/* print the form */

$form_tpl = file_get_contents('templates/post_product.tpl');

if(!isset($product_data['type']) OR $product_data['type'] == '') {
    $product_data['type'] = 'p';
}


/* replace all entries from $lang */
foreach($lang as $k => $v) {
    $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
}


/* labels */

$arr_checked_labels = explode(",", $product_data['labels']);
$checkbox_set_labels = '';
for($i=0;$i<$cnt_labels;$i++) {
    $label_title = $se_labels[$i]['label_title'];
    $label_id = $se_labels[$i]['label_id'];
    $label_color = $se_labels[$i]['label_color'];

    if(in_array("$label_id", $arr_checked_labels)) {
        $checked_label = "checked";
    } else {
        $checked_label = "";
    }

    $checkbox_set_labels .= '<div class="form-check form-check-inline" style="border-bottom: 1px solid '.$label_color.'">';
    $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="labels[]" value="'.$label_id.'">';
    $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
    $checkbox_set_labels .= '</div>';
}

$form_tpl = str_replace('{show_price_volume_discount}', $show_price_volume_discount, $form_tpl);

$form_tpl = str_replace('{product_labels}', $checkbox_set_labels, $form_tpl);
$form_tpl = str_replace('{checkIgnoreStock}', $checkIgnoreStock, $form_tpl);

/* user inputs */

$form_tpl = str_replace('{title}', $product_data['title'], $form_tpl);
$form_tpl = str_replace('{teaser}', $product_data['teaser'], $form_tpl);

$form_tpl = str_replace('{link_name}', $product_data['link_name'], $form_tpl);
$form_tpl = str_replace('{link_classes}', $product_data['link_classes'], $form_tpl);

$form_tpl = str_replace('{text}', $product_data['text'], $form_tpl);
$form_tpl = str_replace('{text_label}', $product_data['text_label'], $form_tpl);
$form_tpl = str_replace('{text_additional_1}', $product_data['text_additional1'], $form_tpl);
$form_tpl = str_replace('{text_label_additional_1}', $product_data['text_additional1_label'], $form_tpl);
$form_tpl = str_replace('{text_additional_2}', $product_data['text_additional2'], $form_tpl);
$form_tpl = str_replace('{text_label_additional_2}', $product_data['text_additional2_label'], $form_tpl);
$form_tpl = str_replace('{text_additional_3}', $product_data['text_additional3'], $form_tpl);
$form_tpl = str_replace('{text_label_additional_3}', $product_data['text_additional3_label'], $form_tpl);
$form_tpl = str_replace('{text_additional_4}', $product_data['text_additional4'], $form_tpl);
$form_tpl = str_replace('{text_label_additional_4}', $product_data['text_additional4_label'], $form_tpl);
$form_tpl = str_replace('{text_additional_5}', $product_data['text_additional5'], $form_tpl);
$form_tpl = str_replace('{text_label_additional_5}', $product_data['text_additional5_label'], $form_tpl);


$form_tpl = str_replace('{author}', $product_data['author'], $form_tpl);
$form_tpl = str_replace('{slug}', $product_data['slug'], $form_tpl);
$form_tpl = str_replace('{translation_inputs}', $translation_inputs, $form_tpl);

$form_tpl = str_replace('{tags}', $product_data['tags'], $form_tpl);
$form_tpl = str_replace('{rss_url}', $product_data['rss_url'], $form_tpl);
$form_tpl = str_replace('{select_rss}', $select_rss, $form_tpl);
$form_tpl = str_replace('{select_status}', $select_status, $form_tpl);

$form_tpl = str_replace('{meta_title}', $product_data['meta_title'], $form_tpl);
$form_tpl = str_replace('{meta_description}', $product_data['meta_description'], $form_tpl);

$form_tpl = str_replace('{checkboxes_lang}', $select_lang, $form_tpl);
$form_tpl = str_replace('{checkbox_categories}', $checkboxes_cat, $form_tpl);
$form_tpl = str_replace('{releasedate}', $releasedate, $form_tpl);
$form_tpl = str_replace('{widget_images}', $choose_images, $form_tpl);


$form_tpl = str_replace('{input_priority}', $product_data['priority'], $form_tpl);
$form_tpl = str_replace('{checkbox_fixed}', $checkbox_fixed, $form_tpl);
$form_tpl = str_replace('{select_status}', $select_status, $form_tpl);
$form_tpl = str_replace('{select_comments}', $select_comments, $form_tpl);
$form_tpl = str_replace('{select_votings}', $select_votings, $form_tpl);


$form_tpl = str_replace('{list_products_filter}', $filter_list, $form_tpl);
$form_tpl = str_replace('{variants_list}', $variants_list, $form_tpl);
$form_tpl = str_replace('{product_variant_title}', $product_data['product_variant_title'], $form_tpl);
$form_tpl = str_replace('{product_variant_description}', $product_data['product_variant_description'], $form_tpl);
$form_tpl = str_replace('{options_input}', $options_input, $form_tpl);
$form_tpl = str_replace('{product_options_comment_label}', $product_data['product_options_comment_label'], $form_tpl);

$form_tpl = str_replace('{product_list_related}', $checkbox_related_prod, $form_tpl);
$form_tpl = str_replace('{product_list_accessories}', $checkbox_accessories_prod, $form_tpl);


/* links */
$form_tpl = str_replace('{link}', $product_data['link'], $form_tpl);

/* files */
$form_tpl = str_replace('{file_attachment_external}', $product_data['file_attachment_external'], $form_tpl);
$form_tpl = str_replace('{file_license}', $product_data['file_license'], $form_tpl);
$form_tpl = str_replace('{file_version}', $product_data['file_version'], $form_tpl);
$form_tpl = str_replace('{select_file}', $select_file, $form_tpl);
$form_tpl = str_replace('{select_file_as}', $select_file_as, $form_tpl);
$form_tpl = str_replace('{cnt_attachment_as_hits}', $product_data['file_attachment_as_hits'], $form_tpl);
$form_tpl = str_replace('{cnt_attachment_hits}', $product_data['file_attachment_hits'], $form_tpl);
$form_tpl = str_replace('{checked_user_uploads}', $checked_user_uploads, $form_tpl);

/* product */
$form_tpl = str_replace('{product_number}', $product_data['product_number'], $form_tpl);
$form_tpl = str_replace('{product_manufacturer}', $product_data['product_manufacturer'], $form_tpl);
$form_tpl = str_replace('{product_supplier}', $product_data['product_supplier'], $form_tpl);
$form_tpl = str_replace('{product_currency}', $product_currency, $form_tpl);
$form_tpl = str_replace('{product_price_label}', $product_data['product_price_label'], $form_tpl);
$form_tpl = str_replace('{product_amount}', $product_data['product_amount'], $form_tpl);
$form_tpl = str_replace('{product_unit}', $product_data['product_unit'], $form_tpl);
$form_tpl = str_replace('{product_price_net}', $product_price_net, $form_tpl);
$form_tpl = str_replace('{select_tax}', $select_tax, $form_tpl);
$form_tpl = str_replace('{select_shipping_mode}', $select_shipping_mode, $form_tpl);
$form_tpl = str_replace('{select_shipping_category}', $select_shipping_category, $form_tpl);
$form_tpl = str_replace('{select_price_group}', $select_price_groups, $form_tpl);


$form_tpl = str_replace('{product_nbr_stock}', $product_data['product_nbr_stock'], $form_tpl);
$form_tpl = str_replace('{product_cnt_sales}', $product_data['product_cnt_sales'], $form_tpl);

$form_tpl = str_replace('{snippet_select_pricelist}', $snippet_select_pricelist, $form_tpl);
$form_tpl = str_replace('{snippet_select_text}', $snippet_select_text, $form_tpl);

$form_tpl = str_replace('{product_price_net_purchasing}', $product_price_net_purchasing, $form_tpl);
$form_tpl = str_replace('{product_price_addition}', $product_data['product_price_addition'], $form_tpl);

$form_tpl = str_replace('{product_features_label}', $product_data['product_features_label'], $form_tpl);
$form_tpl = str_replace('{checkboxes_features}', $checkbox_features, $form_tpl);

$form_tpl = str_replace('{select_product_cart_mode}', $select_cart_mode, $form_tpl);
$form_tpl = str_replace('{select_product_pricetag_mode}', $select_pricetag_mode, $form_tpl);
$form_tpl = str_replace('{select_delivery_time}', $snippet_select_delivery_time, $form_tpl);


/* form modes */

$form_tpl = str_replace('{form_header_message}', $form_header_message, $form_tpl);
$form_tpl = str_replace('{form_header_mode}', $form_header_mode, $form_tpl);

$form_tpl = str_replace('{type}', $product_data['type'], $form_tpl);
$form_tpl = str_replace('{id}', $product_data['id'], $form_tpl);
$form_tpl = str_replace('{parent_id}', $product_data['parent_id'], $form_tpl);
$form_tpl = str_replace('{date}', $product_data['date'], $form_tpl);
$form_tpl = str_replace('{year}', date('Y',$product_data['date']), $form_tpl);
$form_tpl = str_replace('{modus}', $modus, $form_tpl);
$form_tpl = str_replace('{token}', $_SESSION['token'], $form_tpl);
$form_tpl = str_replace('{formaction}', '?tn=shop&sub=edit', $form_tpl);
$form_tpl = str_replace('{submit_button}', $submit_btn, $form_tpl);
$form_tpl = str_replace('{submit_variant_button}', $submit_variant_btn, $form_tpl);
$form_tpl = str_replace('{submit_delete_button}', $submit_delete_btn, $form_tpl);


echo $form_tpl;