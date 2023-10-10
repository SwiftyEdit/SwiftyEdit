<?php

/**
 * SwiftyEdit backend
 *
 * global variables
 * @var object $db_posts medoo database object
 * @var array $icon icons set in acp/core/icons.php
 * @var array $lang language
 * @var array $lang_codes language
 * @var string $languagePack
 * @var string $hidden_csrf_token
 * @var array $se_labels
 * @var array $se_prefs
 */

//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require __DIR__.'/access.php';

/* delete product */

if((isset($_POST['delete_id'])) && is_numeric($_POST['delete_id'])) {
    $delete_product_id = (int) $_POST['delete_id'];
    $cnt_delete_product = se_delete_product($delete_product_id);
    if($cnt_delete_product > 0) {
        echo '<div class="alert alert-success">'.$lang['msg_post_deleted'].' ('.$cnt_delete_product.')</div>';
        record_log($_SESSION['user_nick'],"delete product id: $delete_product_id","8");
    }
}

/* change priority */

if(isset($_POST['priority'])) {
    $change_id = (int) $_POST['prio_id'];
    $db_posts->update("se_products", [
        "priority" => (int) $_POST['priority']
    ],[
        "id" => $change_id
    ]);
}

/* remove fixed */

if(is_numeric($_POST['rfixed'])) {

    $change_id = (int) $_POST['rfixed'];
    $db_posts->update("se_products", [
        "fixed" => "2"
    ],[
        "id" => $change_id
    ]);
}

/* set fixed */

if(is_numeric($_POST['sfixed'])) {

    $change_id = (int) $_POST['sfixed'];
    $db_posts->update("se_products", [
        "fixed" => "1"
    ],[
        "id" => $change_id
    ]);
}


// search
if(isset($_POST['product_text_search'])) {
    $_SESSION['product_text_search'] = $_SESSION['product_text_search'] . ' ' . clean_filename($_POST['product_text_search']);
}

/* remove keyword from filter list */
if(isset($_REQUEST['rm_keyword'])) {
    $all_products_text_filter = explode(" ", $_SESSION['product_text_search']);
    $_SESSION['product_text_search'] = '';
    foreach($all_products_text_filter as $f) {
        if($_REQUEST['rm_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['product_text_search'] .= "$f ";
    }
}

if(isset($_SESSION['product_text_search']) AND $_SESSION['product_text_search'] != "") {
    unset($all_products_text_filter);
    $all_products_text_filter = explode(" ", $_SESSION['product_text_search']);
    $btn_remove_keyword = '';
    foreach($all_products_text_filter as $f) {
        if($_REQUEST['rm_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $btn_remove_keyword .= '<a class="btn btn-sm btn-default" href="acp.php?tn=shop&sub='.$sub.'&rm_keyword='.$f.'">'.$icon['x'].' '.$f.'</a> ';
    }
}



// defaults
$sql_start_nbr = 0;
$sql_items_limit = 10;
$sql_default_order = 'id';
$sql_default_direction = 'DESC';
$products_filter = array();

$arr_status = array('2','1');
$arr_types = array('p');
$arr_lang = get_all_languages();
$arr_categories = se_get_categories();

/* items per page */
if(!isset($_SESSION['items_per_page'])) {
    $_SESSION['items_per_page'] = $sql_items_limit;
}
if(isset($_POST['items_per_page'])) {
    $_SESSION['items_per_page'] = (int) $_POST['items_per_page'];
}

/* default: check all categories */
if(!isset($_SESSION['checked_cat_string'])) {
    $_SESSION['checked_cat_string'] = 'all';
}
/* filter by categories */
if(isset($_GET['cat'])) {
    if($_GET['cat'] !== 'all') {
        $_SESSION['checked_cat_string'] = se_return_clean_value($_GET['cat']);
    } else {
        $_SESSION['checked_cat_string'] = 'all';
    }
}

$cat_all_active = '';
$icon_all_toggle = $icon['circle_alt'];
if($_SESSION['checked_cat_string'] == 'all') {
    $cat_all_active = 'active';
    $icon_all_toggle = $icon['check_circle'];
}

$cat_btn_group = '<div class="card">';
$cat_btn_group .= '<div class="list-group list-group-flush scroll-container">';
$cat_btn_group .= '<a href="acp.php?tn=shop&cat=all" class="list-group-item p-1 px-2 '.$cat_all_active.'">'.$icon_all_toggle.' '.$lang['btn_all_categories'].'</a>';
foreach($arr_categories as $c) {
    $cat_active = '';
    $icon_toggle = $icon['circle_alt'];
    if($_SESSION['checked_cat_string'] == $c['cat_hash']) {
        $icon_toggle = $icon['check_circle'];
        $cat_active = 'active';
    }

    $cat_lang_thumb = '<img src="/core/lang/'.$c['cat_lang'].'/flag.png" width="15" alt="'.$c['cat_lang'].'">';

    $cat_btn_group .= '<a href="acp.php?tn=shop&cat='.$c['cat_hash'].'" class="list-group-item p-1 px-2 '.$cat_active.'">';
    $cat_btn_group .= $icon_toggle.' '.$c['cat_name'].' <span class="float-end">'.$cat_lang_thumb.'</span>';
    $cat_btn_group .= '</a>';
}

$cat_btn_group .= '</div>';
$cat_btn_group .= '</div>';


if((isset($_GET['sql_start_nbr'])) && is_numeric($_GET['sql_start_nbr'])) {
    $sql_start_nbr = (int) $_GET['sql_start_nbr'];
}

if((isset($_POST['setPage'])) && is_numeric($_POST['setPage'])) {
    $sql_start_nbr = (int) $_POST['setPage'];
}


// sorting

$sort_products = 'priority';
$sort_products_direction = 'DESC';

if(isset($_POST['sorting_products_dir'])) {
    if($_POST['sorting_products_dir'] == 'desc') {
        $_SESSION['sorting_products_dir'] = 'DESC';
    } else {
        $_SESSION['sorting_products_dir'] = 'ASC';
    }
}

if(!isset($_SESSION['sorting_products_dir'])) {
    $_SESSION['sorting_products_dir'] = $sort_products_direction;
}

if(isset($_POST['sorting_products'])) {
    if($_POST['sorting_products'] == 'priority') {
        $_SESSION['sorting_products'] = 'priority';
    } else if($_POST['sorting_products'] == 'time_edit') {
        $_SESSION['sorting_products'] = 'time_edit';
    } else if($_POST['sorting_products'] == 'time_submited') {
        $_SESSION['sorting_products'] = 'time_submited';
    } else {
        $_SESSION['sorting_products'] = 'price';
    }
}

if(!isset($_SESSION['sorting_products'])) {
    $_SESSION['sorting_products'] = $sort_products;
}



$products_filter['languages'] = implode("-",$global_filter_languages);
$products_filter['status'] = implode("-",$global_filter_status);
$products_filter['labels'] = implode("-",$global_filter_label);
$products_filter['types'] = 'p';
$products_filter['categories'] = $_SESSION['checked_cat_string'];
$products_filter['text_search'] = $_SESSION['product_text_search'];
$products_filter['sort_by'] = $_SESSION['sorting_products'];
$products_filter['sort_direction'] = $_SESSION['sorting_products_dir'];


$get_products = se_get_products($sql_start_nbr,$_SESSION['items_per_page'],$products_filter);
$cnt_filter_posts = $get_products[0]['cnt_products_match'];
$cnt_get_posts = count($get_products);
$cnt_posts = $get_products[0]['cnt_products_all'];


$pagination_query = '?tn=shop&sql_start_nbr={page}';
$pagination = se_return_pagination($pagination_query,$cnt_filter_posts,$sql_start_nbr,$_SESSION['items_per_page'],10,3,2);

echo '<div class="subHeader d-flex flex-row align-items-center">';

echo '<h3 class="align-middle">' . sprintf($lang['label_show_products'], $cnt_filter_posts, $cnt_filter_posts) .'</h3>';

echo '<div class="ms-auto ps-3">';
echo '<a class="btn btn-default text-success w-100" href="?tn=shop&sub=edit&new=p">'.$icon['plus'].' '.$lang['post_type_product'].'</a>';
echo '</div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="card p-3">';

echo '<div class="d-flex flex-row-reverse">';
echo '<div class="ps-3">';
echo '<form action="?tn=shop&sub=shop-list" method="POST" data-bs-toggle="tooltip" data-bs-title="'.$lang['items_per_page'].'">';
echo '<input type="number" class="form-control" name="items_per_page" min="5" max="99" value="'.$_SESSION['items_per_page'].'" onchange="this.form.submit()">';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';
echo '<div class="p-0">';
echo $pagination;
echo '</div>';
echo '</div>';

if($cnt_filter_posts > 0) {

    echo '<table class="table table-sm table-hover">';

    echo '<thead><tr>';
    echo '<th>#</th>';
    echo '<th class="text-center">'.$icon['star'].'</th>';
    echo '<th>'.$lang['label_priority'].'</th>';
    echo '<th></th>';
    echo '<th>'.$lang['label_post_title'].'</th>';
    echo '<th>'.$lang['label_price'].'</th>';
    echo '<th></th>';
    echo '</tr></thead>';

    for($i=0;$i<$cnt_get_posts;$i++) {

        $type_class = 'label-type label-'.$get_products[$i]['type'];
        $icon_fixed = '';
        $add_row_class = '';
        $add_label = '';

        $variants = array();
        $variants = se_get_product_variants($get_products[$i]['id']);
        $cnt_variants = count($variants);

        $edit_variant_select = '';
        if($cnt_variants > 1) {
            $edit_variant_select = '<form class="mt-2" action="?tn=shop&sub=edit" method="POST">';
            $edit_variant_select .= '<div class="dropdown">';
            $edit_variant_select .= '<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'.$lang['label_product_variants'].' ('.$cnt_variants.')</button>';
            $edit_variant_select .= '<ul class="dropdown-menu">';
            foreach($variants as $variant) {
                $edit_variant_select .= '<li><button class="dropdown-item" name="edit_id" value="'.$variant['id'].'" type="submit">'.$variant['id'].' '.$variant['title'].'</button></li>';
            }
            $edit_variant_select .= '</ul>';
            $edit_variant_select .= $hidden_csrf_token;
            $edit_variant_select .= '</form>';
        }

        $icon_fixed_form = '<form action="?tn=shop" method="POST" class="form-inline">';
        if($get_products[$i]['fixed'] == '1') {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="rfixed" value="'.$get_products[$i]['id'].'">'.$icon['star'].'</button>';
        } else {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="sfixed" value="'.$get_products[$i]['id'].'">'.$icon['star_outline'].'</button>';
        }
        $icon_fixed_form .= $hidden_csrf_token;
        $icon_fixed_form .= '</form>';

        if($get_products[$i]['status'] == '2') {
            $add_row_class = 'item_is_draft';
            $add_label = '<span class="badge badge-se">'.$lang['status_draft'].'</span>';
        }
        if($get_products[$i]['status'] == '3') {
            $add_row_class = 'item_is_ghost';
            $add_label = '<span class="badge badge-se">'.$lang['status_ghost'].'</span>';
        }

        $product_lang_thumb = '<img src="/core/lang/'.$get_products[$i]['product_lang'].'/flag.png" width="15" title="'.$get_products[$i]['product_lang'].'" alt="'.$get_products[$i]['product_lang'].'">';

        /* trim teaser to $trim chars */
        $trimmed_teaser = se_return_first_chars($get_products[$i]['teaser'],100);

        $post_image = explode("<->", $get_products[$i]['images']);
        $show_thumb = '';
        if($post_image[1] != "") {
            $image_src = $post_image[1];
            $show_thumb  = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\''.$image_src.'\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url('.$image_src.');">';
            $show_thumb .= '</div>';
        } else {
            $show_thumb = '<div class="show-thumb" style="background-image: url(images/no-image.png);">';
        }

        /* labels */
        $get_labels = explode(',',$get_products[$i]['labels']);
        $label = '';
        if($get_products[$i]['labels'] != '') {
            $label = '<p>';
            foreach($get_labels as $labels) {

                foreach($se_labels as $l) {
                    if($labels == $l['label_id']) {
                        $label_color = $l['label_color'];
                        $label_title = $l['label_title'];
                    }
                }

                $label .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
            }
            $label .= '</p>';
        }

        /* categories */
        $get_post_categories = explode('<->',$get_products[$i]['categories']);
        $categories = '';
        if($get_products[$i]['categories'] != '') {
            foreach($get_post_categories as $cats) {

                foreach($arr_categories as $cat) {
                    if($cats == $cat['cat_hash']) {
                        $cat_title = $cat['cat_name'];
                        $cat_description = $cat['cat_description'];
                    }
                }
                $categories .= '<span class="text-muted small" title="'.$cat_description.'">'.$icon['tags'].' '.$cat_title.'</span> ';
            }
        }

        $select_priority = '<select name="priority" class="form-control custom-select" onchange="this.form.submit()">';
        for($x=1;$x<11;$x++) {
            $option_add = '';
            $sel_prio = '';
            if($get_products[$i]['priority'] == $x) {
                $sel_prio = 'selected';
            }
            $select_priority .= '<option value="'.$x.'" '.$sel_prio.'>'.$x.'</option>';
        }
        $select_priority .= '</select>';


        $prio_form  = '<form action="acp.php?tn=shop&sub=shop-list" method="POST">';
        $prio_form .= '<input type="number" name="priority" value="'.$get_products[$i]['priority'].'" class="form-control" style="max-width:150px" onchange="this.form.submit()">';
        $prio_form .= '<input type="hidden" name="prio_id" value="'.$get_products[$i]['id'].'">';
        $prio_form .= $hidden_csrf_token;
        $prio_form .= '</form>';


        $published_date = '<span title="'.$lang['label_data_submited'].'">'.$icon['save'].': '.se_format_datetime($get_products[$i]['date']).'</span>';
        $release_date = '<span title="'.$lang['label_data_releasedate'].'">'.$icon['calendar_check'].': '.se_format_datetime($get_products[$i]['releasedate']).'</span>';
        $lastedit_date = '';
        if($get_products[$i]['lastedit'] != '') {
            $lastedit_date = '<span title="'.$lang['label_data_lastedit'].'">'.$icon['edit'].': '.se_format_datetime($get_products[$i]['lastedit']).'</span>';
        }

        $show_items_dates = '<span class="text-muted small">'.$published_date.' | '.$lastedit_date.' | '.$release_date.'</span>';


        $show_items_price = '';

            if($get_products[$i]['product_tax'] == '1') {
                $tax = $se_prefs['prefs_posts_products_default_tax'];
            } else if($get_products[$i]['product_tax'] == '2') {
                $tax = $se_prefs['prefs_posts_products_tax_alt1'];
            } else {
                $tax = $se_prefs['prefs_posts_products_tax_alt2'];
            }

            if(empty($get_products[$i]['product_price_net'])) {
                $get_products[$i]['product_price_net'] = 0;
            }

            $post_price_net = str_replace('.', '', $get_products[$i]['product_price_net']);
            $post_price_net = str_replace(',', '.', $post_price_net);

            $post_price_gross = $post_price_net*($tax+100)/100;

            $post_price_net_format = se_post_print_currency($post_price_net);
            $post_price_gross_format = se_post_print_currency($post_price_gross);

            $show_items_price = '<div class="card p-2 text-nowrap">';
            $show_items_price .= '<span class="small">'.$get_products[$i]['product_currency'].' '.$post_price_net_format . '</span>';
            $show_items_price .= '<span class="small"> + '.$tax.'%</span>';
            $show_items_price .= '<span class="text-success">'.$get_products[$i]['product_currency'].' '.$post_price_gross_format.'</span>';
            $show_items_price .= '</div>';




        echo '<tr class="'.$add_row_class.'">';
        echo '<td>'.$get_products[$i]['id'].'</td>';
        echo '<td>'.$icon_fixed_form.'</td>';
        echo '<td>'.$prio_form.'</td>';
        echo '<td>'.$show_thumb.'</td>';
        echo '<td>';
        echo '<h5 class="mb-0">'.$product_lang_thumb.' '.$get_products[$i]['title'].$add_label.'</h5><small>'.$trimmed_teaser.'</small>';
        echo '<div>'.$show_items_dates.'</div>';
        echo '<div>'.$categories.'</div>';
        if($edit_variant_select != '') {
            echo $edit_variant_select;
        }
        echo '</td>';
        echo '<td>'.$show_items_price.'</td>';
        echo '<td style="min-width: 150px;">';
        echo '<nav class="nav justify-content-end">';
        echo '<form class="form-inline p-1" action="?tn=shop&sub=edit" method="POST">';
        echo '<button class="btn btn-default btn-sm text-success" type="submit" name="edit_id" value="'.$get_products[$i]['id'].'">'.$icon['edit'].'</button>';
        echo '<button class="btn btn-sm btn-default mx-1" name="duplicate" value="'.$get_products[$i]['id'].'" title="'.$lang['duplicate'].'">'.$icon['copy'].'</button>';

        echo $hidden_csrf_token;
        echo '</form> ';
        echo '<form class="form-inline p-1" action="acp.php?tn=shop&sub=shop-list" method="POST" onsubmit="return confirm(\''.$lang['confirm_delete_data'].'\');">';
        echo '<button class="btn btn-default text-danger btn-sm" type="submit" name="delete_id" value="'.$get_products[$i]['id'].'">'.$icon['trash_alt'].'</button>';
        echo $hidden_csrf_token;
        echo '</form>';
        echo '</nav>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';

} else {
    echo '<div class="alert alert-info">'.$lang['msg_no_posts_to_show'].'</div>';
}

echo $pagination;

echo '</div>'; // card


echo '</div>';
echo '<div class="col-md-3">';


/* sidebar */
echo '<div class="card">';
echo '<div class="card-header">'.$icon['filter'].' Filter</div>';
echo '<div class="card-body">';

echo '<form action="?tn=shop&sub=shop-list" method="POST" class="ms-auto">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="product_text_search" value="" placeholder="'.$lang['button_search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';


if(isset($btn_remove_keyword)) {
    echo '<div class="d-inline">';
    echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
    echo '</div><hr>';
}


if($_SESSION['sorting_products'] == 'priority') {
    $sel_sort_value['priority'] = 'selected';
} else if ($_SESSION['sorting_products'] == 'time_submited') {
    $sel_sort_value['time_submited'] = 'selected';
} else if ($_SESSION['sorting_products'] == 'time_edit') {
    $sel_sort_value['time_edit'] = 'selected';
} else {
    $sel_sort_value['price'] = 'selected';
}

if($_SESSION['sorting_products_dir'] == 'ASC') {
    $sel_sort_value['sort_asc'] = 'active';
} else {
    $sel_sort_value['sort_desc'] = 'active';
}

echo '<div class="my-3">';
echo '<label class="form-label">'.$lang['h_page_sort'].'</label>';
echo '<form action="?tn=shop&sub=shop-list" method="post" class="dirtyignore">';

echo '<div class="row g-1">';
echo '<div class="col-md-8">';

echo '<select class="form-control form-select-sm" name="sorting_products" onchange="this.form.submit()">';
echo '<option value="priority" '.$sel_sort_value['priority'].'>'.$lang['label_priority'].'</option>';
echo '<option value="time_submited" '.$sel_sort_value['time_submited'].'>'.$lang['label_data_submited'].'</option>';
echo '<option value="time_edit" '.$sel_sort_value['time_edit'].'>'.$lang['btn_sort_edit'].'</option>';
echo '<option value="price" '.$sel_sort_value['price'].'>'.$lang['label_price'].'</option>';
echo '</select>';

echo '</div>';
echo '<div class="col-md-4">';
echo '<div class="btn-group d-flex">';
echo '<button name="sorting_products_dir" value="asc" title="'.$lang['btn_sort_asc'].'" class="btn btn-sm btn-default w-100 '.$sel_sort_value['sort_asc'].'">'.$icon['arrow_up'].'</button> ';
echo '<button name="sorting_products_dir" value="desc" title="'.$lang['btn_sort_desc'].'" class="btn btn-sm btn-default w-100 '.$sel_sort_value['sort_desc'].'">'.$icon['arrow_down'].'</button>';
echo '</div>';
echo '</div>';
echo '</div>';

echo $hidden_csrf_token;
echo '</form>';
echo '</div>';


echo '<div class="card mt-2">';
echo '<div class="card-header p-1 px-2">'.$lang['label_categories'].'</div>';

echo $cat_btn_group;

echo '</div>';
echo '</div>'; // card-body
echo '</div>'; // card


echo '</div>';
echo '</div>';