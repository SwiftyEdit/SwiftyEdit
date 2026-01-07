<?php

/**
 * @var array $lang
 * @var object $db_posts
 * @var string $query - the current url
 */

$writer_uri = '/admin-xhr/shop/write/';
$form_header_mode = $lang['btn_new'];
$my_user_presets = se_get_my_presets();

$btn_save = '<button type="submit" hx-trigger="click" hx-post="'.$writer_uri.'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_product" value="new">'.$lang['save'].'</button>';
$btn_update = '';
$btn_delete = '';
$submit_variant_btn = '';

// check if last part of url is an id
$path = parse_url($query, PHP_URL_PATH);
$segments = explode('/', rtrim($path, '/'));
$lastSegment = end($segments);
if(is_numeric($lastSegment)) {
    $get_product_id = (int) $lastSegment;
    $form_mode = $get_product_id;
    $btn_submit_text = $lang['update'];
    $form_header_mode = $lang['edit'].' #'.$get_product_id;
    $btn_save = '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100 my-1" name="save_product" value="'.$form_mode.'">'.$btn_submit_text.'</button>';
    $btn_delete = '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-target="#formResponse" hx-confirm="'.$lang['msg_confirm_delete'].'" hx-swap="innerHTML" class="btn btn-danger w-50" name="delete_product" value="'.$get_product_id.'">'.$lang['btn_delete'].'</button>';
}

if(isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
    $get_product_id = (int) $_POST['product_id'];
    $form_mode = $get_product_id;
    $btn_submit_text = $lang['update'];
    $form_header_mode = $lang['edit'].' #'.$get_product_id;
    $btn_save = '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100 my-1" name="save_product" value="'.$form_mode.'">'.$btn_submit_text.'</button>';
    $btn_delete = '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-target="#formResponse" hx-confirm="'.$lang['msg_confirm_delete'].'" hx-swap="innerHTML" class="btn btn-danger w-50" name="delete_product" value="'.$get_product_id.'">'.$lang['btn_delete'].'</button>';
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_product_id = (int) $_POST['duplicate_id'];
    $form_mode = 'new';
    $btn_submit_text = $lang['duplicate'];
    $form_header_mode = $lang['duplicate'].' #'.$get_product_id;
    $submit_variant_btn = '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-default w-100 my-1" name="save_variant" value="'.$get_product_id.'">'.$lang['submit_variant'].'</button>';
}

if(is_int($get_product_id)) {

    $product_data = $db_posts->get("se_products","*",[
        "id" => "$get_product_id"
    ]);

    foreach($product_data as $k => $v) {
        if($v == '') {
            continue;
        }
        $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
    }

} else {
    $btn_submit_text = $lang['save'];
    $form_mode = 'new';
}

if($product_data['type'] == 'v') {
    // hide the submit as variant button
    $submit_variant_btn = '';
    $form_header_mode .= ' ('.$lang['product_type_variant'].')';
} else {
    $form_header_mode .= ' ('.$lang['product_type_main'].')';
}


if(!is_array($product_data)) {
    $product_data = [];
    $product_data['product_amount'] = 1;
}

// select main catalog page
$all_catalog_pages = [];
$all_catalog_pages = $db_content->select("se_pages","page_permalink",[
    "page_posts_types" => "p"
]);
array_unshift($all_catalog_pages, "default");

$product_main_catalog_slug = '';
if(isset($product_data['main_catalog_slug'])) {
    $product_main_catalog_slug = $product_data['main_catalog_slug'];
}

$select_main_catalog_page  = '<select name="main_catalog_slug" class="custom-select form-control">';
foreach($all_catalog_pages as $permalink) {
    $label = $permalink;
    if($permalink == 'default') {
        $label = $lang['label_use_default'];
    }
    $select_main_catalog_page .= "<option value='$permalink'".($product_main_catalog_slug == "$permalink" ? 'selected="selected"' :'').">$label</option>";
}
$select_main_catalog_page .= '</select>';



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
    $releasedate = date('Y-m-d H:i', $product_data['releasedate']);
} else {
    $releasedate = date('Y-m-d H:i', time());
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
$draggable = '';
if(is_array($array_images)) {
    $array_images = array_filter($array_images);
    foreach($array_images as $image) {
        $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
        $image_src = str_replace('../images/','/images/',$image_src);
        $draggable .= '<div class="list-group-item d-flex align-items-start draggable" data-id="'.$image.'">';
        $draggable .= '<div class="d-flex gap-2">';
        $draggable .= '<div class="rounded-circle flex-shrink-0" style="width:40px;height:40px;background-image:url('.$image_src.');background-size:cover;"></div>';
        $draggable .= '<div class="text-muted small">'.basename($image).'</div>';
        $draggable .= '</div>';
        $draggable .= '</div>';
    }
}

$choose_images = '<div id="imgdropper" class="sortable_target list-group mb-3">'.$draggable.'</div>';
$choose_images .= '<div id="imgWidget" hx-post="/admin-xhr/widgets/read/?widget=img-select" hx-include="[name=\'csrf_token\']" hx-trigger="load, update_image_widget from:body">';
$choose_images .= 'Loading Images ...</div>';

/* status | draft or published */
$sel_status_draft = '';
$sel_status_published = '';
$sel_status_ghost = '';

if(!isset($product_data['status'])) {
    // new product, check if we have user presets
    if($my_user_presets['status'] == 'p') {
        $product_data['status'] = 1;
    } else if($my_user_presets['status'] == 'd') {
        $product_data['status'] = 2;
    }

}

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
$select_votings .= '<option value="1" '.$sel_votings_1.'>'.$lang['label_votings_status_off'].'</option>';
$select_votings .= '<option value="2" '.$sel_votings_2.'>'.$lang['label_votings_status_registered'].'</option>';
$select_votings .= '<option value="3" '.$sel_votings_3.'>'.$lang['label_votings_status_global'].'</option>';
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

$translation_inputs = '<div class="card">';
$translation_inputs .= '<div class="card-header d-flex justify-content-between">';
$translation_inputs .= $lang['label_translations'].' (URLs)';
$translation_inputs .= ' <button class="btn btn-sm btn-default" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTranslationURL" aria-expanded="false">'.$icon['plus'].'</button>';
$translation_inputs .= '</div>';
$translation_inputs .= '<div class="card-body collapse" id="collapseTranslationURL">';
foreach($active_lang as $k => $v) {
    $ls = $v['sign'];
    $translation_inputs .= '<div class="input-group mb-3">';
    $translation_inputs .= '<span class="input-group-text"><i class="bi bi-translate me-1"></i> '.$ls.'</span>';
    $translation_inputs .= '<input class="form-control" type="text" autocomplete="off" name="translation_url['.$ls.']" id="set_canonical_url_'.$ls.'" value="'.$translation_urls_array[$ls].'">';
    $translation_inputs .= '</div>';
}
$translation_inputs .= '</div>';
$translation_inputs .= '</div>';

// hooks

$productHooks    = se_get_backend_hooks('product.');
$productHookMeta = se_get_backend_hook_meta('product.');

if (!empty($productHookMeta)) {
    $x = 0;
    $list_product_update_hooks .= '<div class="card">';
    $list_product_update_hooks .= '<div class="card-header">Hooks</div>';
    $list_product_update_hooks .= '<ul class="list-group list-group-flush">';

    foreach ($productHookMeta as $hookName => $entries) {
        foreach ($entries as $entryIndex => $meta) {
            if (!is_array($meta)) {
                continue;
            }

            $x++;
            $id = 'hookid' . $x;

            // Name: hooks[hookName][entryIndex]
            $inputName = 'hooks['
                . htmlspecialchars($hookName, ENT_QUOTES) . ']['
                . (int)$entryIndex . ']';

            $list_product_update_hooks .= '<li class="list-group-item">';
            $list_product_update_hooks .= '<div class="mb-1 form-check">';
            $list_product_update_hooks .= '<input type="checkbox" class="form-check-input"'
                . ' name="' . $inputName . '"'
                . ' value="1"'
                . ' id="' . $id . '"> ';
            $list_product_update_hooks .= '<label class="form-check-label" for="' . $id . '">';
            $list_product_update_hooks .= '<code>' . htmlspecialchars($meta['plugin']) . '</code>';
            $list_product_update_hooks .= '<span class="p-1">'
                . htmlspecialchars($meta['label'] ?? '')
                . '</span>';
            $list_product_update_hooks .= '</label>';
            $list_product_update_hooks .= '</div>';
            $list_product_update_hooks .= '</li>';
        }
    }

    $list_product_update_hooks .= '</ul>';
    $list_product_update_hooks .= '</div>';
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

if(!isset($product_data['product_shipping_mode'])) {
    if($my_user_presets['product_type'] == 'deliver') {
        $product_data['product_shipping_mode'] = 2;
    }
}

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

$filter_btns = '<button type="button" id="toggle-all" class="btn-toggle btn btn-sm btn-default">'.$lang['btn_all'].'</button>';
foreach($cats as $key => $value) {
    $filter_btns .= '<button type="button" class="btn-toggle btn btn-sm btn-default" data-target="toggle-'.$value['cat_hash'].'">'.$value['cat_name'].'</button>';
}

$filter_list .= $filter_btns;

foreach($all_filters as $k => $v) {

    $group_categories = explode(",",$v['filter_categories']);
    $filter_cats = '';
    $toggle_class = 'd-none ';
    foreach($cats as $key => $value) {
        if (in_array($value['cat_hash'], $group_categories)) {
            $filter_cats .= '<span class="badge badge-se text-opacity-50">'.$value['cat_name'].'</span>';
            $toggle_class .= 'toggle-'.$value['cat_hash'].' ';
        }
    }

    $flag = '<img src="'.return_language_flag_src($v['filter_lang']).'" width="15">';

    $filter_list .= '<div class="toggle-item '.$toggle_class.' card mb-1">';
    $filter_list .= '<div class="card-header">'.$flag.' '.$v['filter_title'].' <div class="float-end">'.$filter_cats.'</div></div>';
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

// temporary array for showing title and lang by product id
foreach($all_products as $prod) {
    $temp_prod[$prod['id']] = [
      "title" => $prod['title'],
      "lang" => $prod['product_lang'],
      "number" => $prod['product_number']
    ];
}

$get_prod_related = json_decode($product_data['product_related'],true);
$draggable_related = '';
if(is_array($get_prod_related)) {
    $get_prod_related = array_filter($get_prod_related);
    foreach($get_prod_related as $id) {

        $show_title = $temp_prod[$id]['title'];
        $show_lang = return_language_flag_src($temp_prod[$id]['lang']);

        $draggable_related .= '<div class="list-group-item draggable" data-id="'.$id.'">';
        $draggable_related .= '<div class="d-flex flex-row gap-2">';
        $draggable_related .= '<div class="text-muted small"><img src="'.$show_lang.'" width="15"> '.$show_title;
        $draggable_related .= '[#'.$id.'] '.$temp_prod[$id]['number'];
        $draggable_related .= '</div>';
        $draggable_related .= '</div>';
        $draggable_related .= '</div>';
    }
}

$get_prod_accessories = json_decode($product_data['product_accessories'],true);
$draggable_accessories = '';
if(is_array($get_prod_accessories)) {
    $get_prod_accessories = array_filter($get_prod_accessories);
    foreach($get_prod_accessories as $id) {

        $show_title = $temp_prod[$id]['title'];
        $show_lang = return_language_flag_src($temp_prod[$id]['lang']);

        $draggable_accessories .= '<div class="list-group-item draggable" data-id="'.$id.'">';
        $draggable_accessories .= '<div class="d-flex flex-row gap-2">';
        $draggable_accessories .= '<div class="text-muted small"><img src="'.$show_lang.'" width="15"> '.$show_title;
        $draggable_accessories .= '[#'.$id.'] '.$temp_prod[$id]['number'];
        $draggable_accessories .= '</div>';
        $draggable_accessories .= '</div>';
        $draggable_accessories .= '</div>';
    }
}

$prod_related_dropper = '<div id="prodDropper_r" class="sortable_target target_products list-group mb-3">'.$draggable_related.'</div>';
$prod_accessories_dropper = '<div id="prodDropper_a" class="sortable_target target_products list-group mb-3">'.$draggable_accessories.'</div>';
$prod_sel_widget = '<div id="prodWidget" hx-post="/admin-xhr/widgets/read/?widget=product-select" hx-include="[name=\'csrf_token\']" hx-trigger="load, update_product_widget from:body"></div>';
$prod_sel_widget .= '<div id="fake" class="sortable_source"><div></div></div>';
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

$variant_controls = '';
if($cnt_variants > 1) {
    // $product_data['id'] is a main product, inject variant controls
    $variant_controls = '<div class="col-md-4">';

    $checked_var_type_parameter = ($product_data['product_variant_type'] == 2) ? 'checked' : '';
    $checked_var_type_page      = ($product_data['product_variant_type'] == 2) ? '' : 'checked';

    // create input radios for selecting variant handling (type page or parameter)
    $variant_controls .= '<p>'.$lang['label_product_variant_type'].'</p>';
    $variant_controls .= '<div class="form-check">';
    $variant_controls .= '<input class="form-check-input" type="radio" name="product_variant_type" value="1" id="variantPage" '.$checked_var_type_page.'>';
    $variant_controls .= '<label class="form-check-label" for="variantPage">'.$lang['label_product_variant_type_page'].' <span class="ms-3 form-text">product-5.html</span></label>';
    $variant_controls .= '</div>';
    $variant_controls .= '<div class="form-check">';
    $variant_controls .= '<input class="form-check-input" type="radio" name="product_variant_type" value="2" id="variantParameter" '.$checked_var_type_parameter.'>';
    $variant_controls .= '<label class="form-check-label" for="variantParameter">'.$lang['label_product_variant_type_parameter'].' <span class="ms-3 form-text">product/?c=black</span></label>';
    $variant_controls .= '</div><hr>';

    // create buttons to edit variants
    $variant_controls .= '<p>'.$lang['label_product_variants'].'<p>';
    foreach($variants as $variant) {
        if($variant['id'] == $product_data['id']) {continue;} // skip the product which is itself
        $variant_controls .= '<a href="/admin/shop/edit/'.$variant['id'].'/" class="btn btn-default btn-sm mb-1" ">'.$icon['edit'].' '.$variant['title'].' (#'.$variant['id'].')</a> ';
    }

    $variant_controls .= '</div>';
}


if($product_data['type'] == 'v') {
    $variant_controls = '<div class="col-md-4">';
    $variant_controls .= '<p>'.$lang['product_type_main'].'<p>';
    $variant_controls .= '<a href="/admin/shop/edit/'.$product_data['parent_id'].'/" class="btn btn-default btn-sm" ">'.$icon['edit'].' '.$lang['btn_edit'].' (#'.$product_data['parent_id'].')</a> ';
    $variant_controls .= '</div>';
}


$product_price_manufacturer = $product_data['product_price_manufacturer'];
if($product_price_manufacturer == '') {
    $product_price_net = '';
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
$select_price_groups .= '<option value="null">'.$lang['label_product_price_group_no_selection'].'</option>';
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
$show_price_volume_discount .= '<span>'.$lang['label_product_scaling_prices'].'</span>';
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
    $show_price_volume_discount .= '<label>' . $lang['label_product_price'] . ' ' . $lang['label_product_net'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control prod_price_net" name="product_vd_price[]" type="text" value="'.$price_net.'">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '<div class="col-md-3">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_price'] . ' ' . $lang['label_product_gross'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control prod_price_gross" name="product_vd_price_gross[]" type="text" value="">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';
}

$show_price_volume_discount .= '</div>';
$show_price_volume_discount .= '</div>';


/* select delivery time */
// get all snippets where name starts with 'shop_delivery_time'
$snippets_delivery_time = $db_content->select("se_snippets", "*", [
    "snippet_name[~]" => "shop_delivery_time%"
]);

$snippet_select_delivery_time = '<select class="form-control custom-select" name="product_delivery_time">';
$snippet_select_delivery_time .= '<option value="no_specification">'.$lang['label_product_no_delivery_time'].'</option>';
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
$snippet_select_pricelist .= '<option value="no_snippet">'.$lang['no_snippet_selected'].'</option>';

$snippets_price_list = $db_content->select("se_snippets", "*", [
    "snippet_name[~]" => "%post_price%"
]);

foreach($snippets_price_list as $snippet) {
    $selected = "";
    if($snippet['snippet_name'] == $product_data['product_textlib_price']) {
        $selected = 'selected';
    }
    $snippet_select_pricelist .= '<option '.$selected.' value='.$snippet['snippet_name'].'>'.$snippet['snippet_name']. ' - ' .$snippet['snippet_title'].'</option>';
}
$snippet_select_pricelist .= '</select>';


/* add text snippet to text */

$snippet_select_text = '<select class="form-control custom-select" name="product_textlib_content" id="snippet_tex">';
$snippet_select_text .= '<option value="no_snippet">'.$lang['no_snippet_selected'].'</option>';
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

$files_directory = SE_PUBLIC.'/assets/files';
$all_files = se_scandir_rec($files_directory);

/* pre-sale files */
$select_file = '<select class="form-control custom-select" name="file_attachment">';
$select_file .= '<option value="">-- '.$lang['label_no_file_selected'].' --</option>';

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
$select_file_as .= '<option value="">-- '.$lang['label_no_file_selected'].' --</option>';

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


// shopping cart mode
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


// price tag mode
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

// stock
$product_data['product_nbr_stock'] = (int) $product_data['product_nbr_stock'];
$product_data['product_cnt_sales'] = (int) $product_data['product_cnt_sales'];

if($product_data['product_stock_mode'] == 1) {
    $checkIgnoreStock = 'checked';
} else {
    $checkIgnoreStock = '';
}

$product_order_quantity_min = (int) $product_data['product_order_quantity_min'];
$product_order_quantity_max = (int) $product_data['product_order_quantity_max'];
if($product_order_quantity_min < 2) {
    $product_order_quantity_min = 1;
}
if($product_order_quantity_max == 0) {
    $product_order_quantity_max = '';
}

// labels
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


// print the form

$form_tpl = file_get_contents('../acp/templates/form-edit-products.tpl');

if(!isset($product_data['type']) OR $product_data['type'] == '') {
    $product_data['type'] = 'p';
}


/* replace all entries from $lang */
foreach($lang as $k => $v) {
    $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
}

$form_tpl = str_replace('{product_labels}', $checkbox_set_labels, $form_tpl);
$form_tpl = str_replace('{show_price_volume_discount}', $show_price_volume_discount, $form_tpl);
$form_tpl = str_replace('{checkIgnoreStock}', $checkIgnoreStock, $form_tpl);

$form_tpl = str_replace('{variant_tooltip}', se_print_docs_link("05-00-shop.md"), $form_tpl);

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
$form_tpl = str_replace('{label_scope_of_delivery}', $lang['label_scope_of_delivery'], $form_tpl);
$form_tpl = str_replace('{text_scope_of_delivery}', $product_data['text_scope_of_delivery'], $form_tpl);
$form_tpl = str_replace('{author}', $product_data['author'], $form_tpl);
$form_tpl = str_replace('{slug}', $product_data['slug'], $form_tpl);
$form_tpl = str_replace('{translation_inputs}', $translation_inputs, $form_tpl);

$form_tpl = str_replace('{se_base_url}', $se_base_url, $form_tpl);
$form_tpl = str_replace('{select_main_catalog_page}', $select_main_catalog_page, $form_tpl);

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
$form_tpl = str_replace('{variant_controls}', $variant_controls, $form_tpl);
$form_tpl = str_replace('{product_variant_title}', $product_data['product_variant_title'], $form_tpl);
$form_tpl = str_replace('{product_variant_description}', $product_data['product_variant_description'], $form_tpl);
$form_tpl = str_replace('{product_variant_color}', $product_data['product_variant_color'], $form_tpl);
$form_tpl = str_replace('{options_input}', $options_input, $form_tpl);
$form_tpl = str_replace('{product_options_comment_label}', $product_data['product_options_comment_label'], $form_tpl);

$form_tpl = str_replace('{prod_sel_widget}', $prod_sel_widget, $form_tpl);
$form_tpl = str_replace('{prod_accessories_dropper}', $prod_accessories_dropper, $form_tpl);
$form_tpl = str_replace('{prod_related_dropper}', $prod_related_dropper, $form_tpl);

$form_tpl = str_replace('{product_list_related}', $checkbox_related_prod, $form_tpl);
$form_tpl = str_replace('{product_list_accessories}', $checkbox_accessories_prod, $form_tpl);

/* links */
$form_tpl = str_replace('{link}', $product_data['link'], $form_tpl);

$form_tpl = str_replace('{product_number}', $product_data['product_number'], $form_tpl);
$form_tpl = str_replace('{product_ean}', $product_data['product_ean'], $form_tpl);
$form_tpl = str_replace('{product_mpn}', $product_data['product_mpn'], $form_tpl);
$form_tpl = str_replace('{product_manufacturer}', $product_data['product_manufacturer'], $form_tpl);
$form_tpl = str_replace('{product_price_manufacturer}', $product_data['product_price_manufacturer'], $form_tpl);

$form_tpl = str_replace('{product_url}', $product_data['product_url'], $form_tpl);
$form_tpl = str_replace('{product_supplier}', $product_data['product_supplier'], $form_tpl);
$form_tpl = str_replace('{product_currency}', $product_currency, $form_tpl);
$form_tpl = str_replace('{product_price_label}', $product_data['product_price_label'], $form_tpl);
$form_tpl = str_replace('{product_amount}', $product_data['product_amount'], $form_tpl);
$form_tpl = str_replace('{product_unit}', $product_data['product_unit'], $form_tpl);
$form_tpl = str_replace('{product_unit_content}', $product_data['product_unit_content'], $form_tpl);
$form_tpl = str_replace('{product_price_net}', $product_price_net, $form_tpl);
$form_tpl = str_replace('{select_tax}', $select_tax, $form_tpl);
$form_tpl = str_replace('{select_shipping_mode}', $select_shipping_mode, $form_tpl);
$form_tpl = str_replace('{select_shipping_category}', $select_shipping_category, $form_tpl);
$form_tpl = str_replace('{select_price_group}', $select_price_groups, $form_tpl);

$form_tpl = str_replace('{product_nbr_stock}', $product_data['product_nbr_stock'], $form_tpl);
$form_tpl = str_replace('{product_cnt_sales}', $product_data['product_cnt_sales'], $form_tpl);

$form_tpl = str_replace('{product_order_quantity_min}', $product_order_quantity_min, $form_tpl);
$form_tpl = str_replace('{product_order_quantity_max}', $product_order_quantity_max, $form_tpl);

$form_tpl = str_replace('{snippet_select_pricelist}', $snippet_select_pricelist, $form_tpl);
$form_tpl = str_replace('{snippet_select_text}', $snippet_select_text, $form_tpl);

$form_tpl = str_replace('{product_price_net_purchasing}', $product_price_net_purchasing, $form_tpl);
$form_tpl = str_replace('{product_price_addition}', $product_data['product_price_addition'], $form_tpl);

$form_tpl = str_replace('{product_features_label}', $product_data['product_features_label'], $form_tpl);
$form_tpl = str_replace('{checkboxes_features}', $checkbox_features, $form_tpl);

$form_tpl = str_replace('{select_product_cart_mode}', $select_cart_mode, $form_tpl);
$form_tpl = str_replace('{select_product_pricetag_mode}', $select_pricetag_mode, $form_tpl);
$form_tpl = str_replace('{select_delivery_time}', $snippet_select_delivery_time, $form_tpl);

$form_tpl = str_replace('{list_product_update_hooks}', $list_product_update_hooks, $form_tpl);


/* files */
$form_tpl = str_replace('{file_attachment_external}', $product_data['file_attachment_external'], $form_tpl);
$form_tpl = str_replace('{file_license}', $product_data['file_license'], $form_tpl);
$form_tpl = str_replace('{file_version}', $product_data['file_version'], $form_tpl);
$form_tpl = str_replace('{select_file}', $select_file, $form_tpl);
$form_tpl = str_replace('{select_file_as}', $select_file_as, $form_tpl);
$form_tpl = str_replace('{cnt_attachment_as_hits}', $product_data['file_attachment_as_hits'], $form_tpl);
$form_tpl = str_replace('{cnt_attachment_hits}', $product_data['file_attachment_hits'], $form_tpl);
$form_tpl = str_replace('{checked_user_uploads}', $checked_user_uploads, $form_tpl);

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
$form_tpl = str_replace('{submit_button}', $btn_save, $form_tpl);
$form_tpl = str_replace('{submit_variant_button}', $submit_variant_btn, $form_tpl);
$form_tpl = str_replace('{submit_delete_button}', $btn_delete, $form_tpl);



echo $form_tpl;