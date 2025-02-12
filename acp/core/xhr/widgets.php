<?php
//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

if(isset($_POST['pagination_img_widget'])) {
    $_SESSION['pagination_image_widget'] = (int) $_POST['pagination_img_widget'];
    header( "HX-Trigger: update_image_widget");
    exit;
}

if(isset($_POST['pagination_products'])) {
    $_SESSION['pagination_product_widget'] = (int) $_POST['pagination_products'];
    header( "HX-Trigger: update_product_widget");
    exit;
}


if(isset($_REQUEST['change_filter'])) {

    if (isset($_POST['media_widget_text_filter'])) {
        $_SESSION['media_widget_text_filter'] = sanitizeUserInputs($_POST['media_widget_text_filter']);
    }

    if (isset($_POST['product_widget_text_filter'])) {
        $_SESSION['product_widget_text_filter'] = sanitizeUserInputs($_POST['product_widget_text_filter']);
        header( "HX-Trigger: update_product_widget");
    }

    if (isset($_POST['sorting_media_widget'])) {
        if ($_POST['sorting_media_widget'] == 'media_id_asc') {
            $_SESSION['sorting_media_widget'] = 'media_id';
            $_SESSION['sorting_media_widget_direction'] = 'ASC';
        } else if ($_POST['sorting_media_widget'] == 'media_id_desc') {
            $_SESSION['sorting_media_widget'] = 'media_id';
            $_SESSION['sorting_media_widget_direction'] = 'DESC';
        } else if ($_POST['sorting_media_widget'] == 'media_file_asc') {
            $_SESSION['sorting_media_widget'] = 'media_file';
            $_SESSION['sorting_media_widget_direction'] = 'ASC';
        } else if ($_POST['sorting_media_widget'] == 'media_file_desc') {
            $_SESSION['sorting_media_widget'] = 'media_file';
            $_SESSION['sorting_media_widget_direction'] = 'DESC';
        }
        header("HX-Trigger: update_image_widget");
        exit;
    }
}

/**
 * drag and drop image widget
 * select and sort images
 */
if($_REQUEST['widget'] == 'img-select') {

    if(!isset($_SESSION['image_picker_id'])) {
        $_SESSION['image_picker_id'] = uniqid();
    }
    $image_picker_id = $_SESSION['image_picker_id'];

    $order_by = 'media_id';
    $order_direction = 'ASC';
    $limit_start = $_SESSION['pagination_image_widget'] ?? 0;
    $nbr_show_items = 25;

    $match_str = $_SESSION['media_widget_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_media_widget'] ?? $order_by;
    $order_direction = $_SESSION['sorting_media_widget_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "media_id[>]" => 0,
            "media_type[~]" => "image"
        ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "media_file[~]" => "%$f%",
                    "media_title[~]" => "%$f%",
                    "media_description[~]" => "%$f%",
                    "media_keywords[~]" => "%$f%",
                    "media_credit[~]" => "%$f%"
                ]
            ];
        }
    }


    $db_where = [
        "AND" => $filter_base+$filter_by_str
    ];
    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];
    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $media_data_cnt = $db_content->count("se_media", $db_where);

    $media_data = $db_content->select("se_media","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($media_data_cnt/$nbr_show_items);


    echo '<div class="card">';
    echo '<div class="card-header">Uploads</div>';

    echo '<div class="card-body p-0">';

    echo '<div class="p-1">';
    echo '<div class="row g-2">';
    echo '<div class="col-md-6">';
    echo '<div class="input-group">';
    echo '<span class="input-group-text"><i class="bi bi-search"></i></span>';
    echo '<input type="text" class="form-control no-enter" hx-post="/admin/widgets/read/?change_filter" hx-params="media_widget_text_filter,csrf_token" hx-trigger="keyup changed delay:500ms" hx-swap="none" name="media_widget_text_filter" value="'.$_SESSION['media_widget_text_filter'].'">';
    echo '</div>';
    echo '</div>';
    echo '<div class="col-md-6">';


    $selected_media_id_asc = '';
    $selected_media_id_desc = '';
    $selected_media_file_asc = '';
    $selected_media_file_desc = '';

    if($_SESSION['sorting_media_widget'] == 'media_id') {
        if($_SESSION['sorting_media_widget_direction'] == 'ASC') {
            $selected_media_id_asc = 'selected';
        } else {
            $selected_media_id_desc = 'selected';
        }
    } else if($_SESSION['sorting_media_widget'] == 'media_file') {
        if($_SESSION['sorting_media_widget_direction'] == 'ASC') {
            $selected_media_file_asc = 'selected';
        } else {
            $selected_media_file_desc = 'selected';
        }
    }

    echo '<select class="form-control" hx-post="/admin/widgets/read/?change_filter" hx-params="sorting_media_widget,csrf_token" name="sorting_media_widget" hx-trigger="change" hx-swap="none">';
    echo '<option value="media_id_desc" '.$selected_media_id_desc.'>Newest first</option>';
    echo '<option value="media_id_asc" '.$selected_media_id_asc.'>Oldest first</option>';
    echo '<option value="media_file_asc" '.$selected_media_file_asc.'>A-Z</option>';
    echo '<option value="media_file_desc" '.$selected_media_file_desc.'>Z-A</option>';
    echo '</select>';

    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '<div class="scroll-container p-0">';
    echo '<div class="sortable_source list-group list-group-flush">';

    foreach ($media_data as $image) {

        $img_filename = basename($image['media_file']);
        $img_filename_short = se_return_first_chars($img_filename,20);
        $image_src = $image['media_file'];
        $image_src = str_replace("../","/",$image_src);
        $image_title = sanitizeUserInputs($image['media_title']);
        $image_tmb_name = $image['media_thumb'];
        $image_upload_time = se_format_datetime($image['media_upload_time']);

        if(file_exists($image_tmb_name)) {
            $preview = $image_tmb_name;
        } else {
            $preview = $image_src;
        }

        $preview = str_replace("../","/",$preview);


        echo '<div class="list-group-item draggable" data-id="'.$image_src.'">';
        echo '<div class="d-flex flex-row gap-2">';
        echo '<div class="rounded-circle flex-shrink-0" style="width:64px;height:64px;background-image:url('.$preview.');background-size:cover;"></div>';
        echo '<div class="text-muted small">'.$image_title.$img_filename_short.'<br>'.$image_upload_time.'</div>';
        echo '</div>';
        echo '</div>';


    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="card-footer">';
    $pagination_classes = [
        'class_pagination' => 'pagination-sm justify-content-center mb-0'
        ];
    echo se_print_pagination('/admin/widgets/read/?widget=img-select',$nbr_pages,$_SESSION['pagination_image_widget'],'6',$pagination_classes,'pagination_img_widget');
    echo '</div>';
    echo '</div>';
    exit;
}

/**
 * select products
 * used for accessories and similar products
 */
if($_REQUEST['widget'] == 'product-select') {

    echo '<div class="card">';
    echo '<div class="card-header">'.$lang['label_products'].'</div>';

    echo '<div class="card-body p-0">';

    $order_by = 'id';
    $order_direction = 'ASC';
    $limit_start = $_SESSION['pagination_product_widget'] ?? 0;
    $nbr_show_items = 25;

    $match_str = $_SESSION['product_widget_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_product_widget'] ?? $order_by;
    $order_direction = $_SESSION['sorting_product_widget_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "id[>]" => 0,
            "type[~]" => "p"
        ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "title[~]" => "%$f%",
                    "teaser[~]" => "%$f%",
                    "text[~]" => "%$f%",
                    "text_additional1[~]" => "%$f%",
                    "text_additional2[~]" => "%$f%",
                    "text_additional3[~]" => "%$f%",
                    "text_additional4[~]" => "%$f%",
                    "text_additional5[~]" => "%$f%"
                ]
            ];
        }
    }


    $db_where = [
        "AND" => $filter_base+$filter_by_str
    ];
    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];
    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $products_data_cnt = $db_posts->count("se_products", $db_where);

    $products_data = $db_posts->select("se_products",["id","product_lang","title"],
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($products_data_cnt/$nbr_show_items);


    echo '<div class="input-group my-1">';
    echo '<span class="input-group-text"><i class="bi bi-search"></i></span>';
    echo '<input type="text" class="form-control no-enter" hx-post="/admin/widgets/read/?change_filter" hx-params="product_widget_text_filter,csrf_token" hx-trigger="keyup changed delay:500ms" hx-swap="none" name="product_widget_text_filter" value="'.$_SESSION['product_widget_text_filter'].'">';
    echo '</div>';
    echo '<div class="scroll-container p-0 mb-2">';
    echo '<div class="sortable_source list-group list-group-flush">';

    foreach($products_data as $product) {

        $flag_src = return_language_flag_src($product['product_lang']);

        echo '<div class="list-group-item draggable" data-id="'.$product['id'].'">';
        echo '<img src="'.$flag_src.'" alt="'.$flag_src.'" width="15"> ';
        echo $product['title'];
        echo ' [#'.$product['id'].'] '.$product['product_number'];
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';

    $pagination_classes = [
        'class_pagination' => 'pagination-sm justify-content-center mb-0'
    ];
    echo se_print_pagination('/admin/widgets/read/?widget=product-select',$nbr_pages,$_SESSION['pagination_product_widget'],'6',$pagination_classes,'pagination_products');

    echo '</div>';
    exit;

}