<?php

use Twig\Environment;

/**
 * SwiftyEdit /admin-xhr/widgets/read/
 * image picker (acp/templates/widgets/select-img.twig) and product picker
 * (acp/templates/widgets/select-products.twig) with search, sorting and pagination
 *
 * global variables
 * @var object $db_content
 * @var object $db_posts
 * @var array $lang
 * @var Environment $twig
 */

// search filters are stored sanitized (sanitizeUserInputs), Twig escapes on output
$decode = static fn($value): string => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

if (isset($_POST['pagination_img_widget'])) {
    $_SESSION['pagination_image_widget'] = (int)$_POST['pagination_img_widget'];
    header("HX-Trigger: update_image_widget");
    exit;
}

if (isset($_POST['pagination_products'])) {
    $_SESSION['pagination_product_widget'] = (int)$_POST['pagination_products'];
    header("HX-Trigger: update_product_widget");
    exit;
}


if (isset($_REQUEST['change_filter'])) {

    if (isset($_POST['media_widget_text_filter'])) {
        $_SESSION['media_widget_text_filter'] = sanitizeUserInputs($_POST['media_widget_text_filter']);
        $_SESSION['pagination_image_widget'] = 0; // reset pagination
        header("HX-Trigger: update_image_widget");
        exit;
    }

    if (isset($_POST['product_widget_text_filter'])) {
        $_SESSION['product_widget_text_filter'] = sanitizeUserInputs($_POST['product_widget_text_filter']);
        $_SESSION['pagination_product_widget'] = 0; // reset pagination
        header("HX-Trigger: update_product_widget");
        exit;
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
        $_SESSION['pagination_image_widget'] = 0; // reset pagination
        header("HX-Trigger: update_image_widget");
        exit;
    }
}

/**
 * drag and drop image widget
 * select and sort images
 */
if ($_REQUEST['widget'] == 'img-select') {

    if (!isset($_SESSION['image_picker_id'])) {
        $_SESSION['image_picker_id'] = uniqid();
    }
    $image_picker_id = $_SESSION['image_picker_id'];

    $order_by = 'media_id';
    $order_direction = 'ASC';
    $limit_start = (int) ($_SESSION['pagination_image_widget'] ?? 0);
    $nbr_show_items = 25;

    $match_str = $_SESSION['media_widget_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_media_widget'] ?? $order_by;
    $order_direction = $_SESSION['sorting_media_widget_direction'] ?? $order_direction;

    if ($limit_start > 0) {
        $limit_start = ($limit_start * $nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "media_id[>]" => 0,
            "media_type[~]" => "image"
        ]
    ];

    // every keyword has to match one of the fields
    $filter_by_str = [];
    foreach (array_values(array_filter(explode(" ", $match_str))) as $i => $f) {
        $filter_by_str["OR #search$i"] = [
            "media_file[~]" => "%$f%",
            "media_title[~]" => "%$f%",
            "media_description[~]" => "%$f%",
            "media_keywords[~]" => "%$f%",
            "media_credit[~]" => "%$f%"
        ];
    }


    $db_where = [
        "AND" => $filter_base + $filter_by_str
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

    $media_data = $db_content->select("se_media", "*",
        $db_where + $db_order + $db_limit
    );

    $nbr_pages = ceil($media_data_cnt / $nbr_show_items);

    // Sorting
    $sorting_selected = strtolower($order_key . '_' . $order_direction);


    foreach ($media_data as &$image) {
        $img_filename = se_filter_filepath(basename($image['media_file']));
        // plain (autoescaped) text + flag; the ellipsis markup lives in the template
        $image['img_filename_short'] = se_return_first_chars($img_filename, 20, false);
        $image['img_filename_truncated'] = strlen($img_filename) > 20;

        $image_src = se_filter_filepath($image['media_file']);
        $image['image_src'] = str_replace("../", "/", $image_src);

        // stored html-encoded, Twig escapes on output
        $image['image_title'] = $decode($image['media_title']);
        $image['image_upload_time'] = se_format_datetime($image['media_upload_time']);

        // the thumbnail as preview, stored as "../images_tmb/..." - the file is in public/assets/
        $image_tmb_name = se_filter_filepath($image['media_thumb']);
        if (str_starts_with($image_tmb_name, '../images_tmb/') && is_file(SE_PUBLIC.'/assets/'.substr($image_tmb_name, 3))) {
            $image['preview'] = substr($image_tmb_name, 2);
        } else {
            $image['preview'] = $image['image_src'];
        }
    }

    // Pagination
    $pagination_classes = [
        'class_pagination' => 'pagination-sm justify-content-center mb-0'
    ];
    $pagination = se_print_pagination(
        '/admin-xhr/widgets/read/?widget=img-select',
        $nbr_pages,
        (int) ($_SESSION['pagination_image_widget'] ?? 0),
        '6',
        $pagination_classes,
        'pagination_img_widget'
    );


    echo $twig->render('widgets/select-img.twig', [
        'media_widget_text_filter' => $decode($match_str),
        'sorting_selected' => $sorting_selected,
        'media_data' => $media_data,
        'pagination' => $pagination,
        'lang_sort_start_newest' => $lang['sort_start_newest'],
        'lang_sort_start_oldest' => $lang['sort_start_oldest']
    ]);

    exit;
}

/**
 * select products
 * used for accessories and similar products
 */
if ($_REQUEST['widget'] == 'product-select') {

    if (!isset($_SESSION['prod_picker_id'])) {
        $_SESSION['prod_picker_id'] = uniqid();
    }
    $prod_picker_id = $_SESSION['prod_picker_id'];

    $order_by = 'id';
    $order_direction = 'ASC';
    $limit_start = (int) ($_SESSION['pagination_product_widget'] ?? 0);
    $nbr_show_items = 25;

    $match_str = $_SESSION['product_widget_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_product_widget'] ?? $order_by;
    $order_direction = $_SESSION['sorting_product_widget_direction'] ?? $order_direction;

    if ($limit_start > 0) {
        $limit_start = ($limit_start * $nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "id[>]" => 0,
            "type" => ["p", "v"]
        ]
    ];

    // every keyword has to match one of the fields
    $filter_by_str = [];
    foreach (array_values(array_filter(explode(" ", $match_str))) as $i => $f) {
        $filter_by_str["OR #search$i"] = [
            "id[~]" => "%$f%",
            "title[~]" => "%$f%",
            "teaser[~]" => "%$f%",
            "text[~]" => "%$f%",
            "text_additional1[~]" => "%$f%",
            "text_additional2[~]" => "%$f%",
            "text_additional3[~]" => "%$f%",
            "text_additional4[~]" => "%$f%",
            "text_additional5[~]" => "%$f%",
            "product_number[~]" => "%$f%"
        ];
    }

    $db_where = [
        "AND" => $filter_base + $filter_by_str
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

    $products_data = $db_posts->select("se_products", ["id", "product_lang", "title", "type", "product_number"],
        $db_where + $db_order + $db_limit
    );

    $nbr_pages = ceil($products_data_cnt / $nbr_show_items);

    foreach ($products_data as &$product) {
        $product['product_id'] = (int)$product['id'];
        $product['flag_src'] = return_language_flag_src($product['product_lang']);
        // stored html-encoded, Twig escapes on output
        $product['title'] = $decode($product['title']);
        $product['product_number'] = $decode($product['product_number']);
    }

    $pagination_classes = [
        'class_pagination' => 'pagination-sm justify-content-center mb-0'
    ];
    $pagination = se_print_pagination(
        '/admin-xhr/widgets/read/?widget=product-select',
        $nbr_pages,
        (int) ($_SESSION['pagination_product_widget'] ?? 0),
        '6',
        $pagination_classes,
        'pagination_products'
    );

    echo $twig->render('widgets/select-products.twig', [
        'product_widget_text_filter' => $decode($match_str),
        'products_data' => $products_data,
        'pagination' => $pagination
    ]);
    exit;
}