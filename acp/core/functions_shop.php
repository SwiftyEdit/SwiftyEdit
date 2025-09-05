<?php
/**
 * prohibit unauthorized access
 */
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){ 
	die ('<h2>Direct File Access Prohibited</h2>');
}


/**
 * update order
 * $id (int) id of the order
 * $data (array) order data
 * returns the number of rows affected 1 = success
 */

function se_update_order($data) {
	
	global $db_content;
	
	$id = (int) $data['open_order'];
	
	$status_order = (int) $data['status_order'];
	$status_payment = (int) $data['status_payment'];
	$status_shipping = (int) $data['status_shipping'];
	
	$update = $db_content->update("se_orders", [
		"order_status" => $status_order,
		"order_status_shipping" => $status_shipping,
		"order_status_payment" => $status_payment
	],[
		"id" => $id
	]);


	return $update->rowCount();
}

/**
 * get all products
 * @return mixed
 */
function se_get_all_products() {

    global $db_posts;

    $products = $db_posts->select("se_products",["id","title","product_lang"]);
    return $products;
}

function se_prepareProductData($data, $id = null) {

    global $languagePack,$se_base_url,$db_content;

    if(!isset($data['product_lang'])) {
        $data['product_lang'] = $languagePack;
    }

    $type = 'p';
    $releasedate = time();
    $lastedit = time();

    if (!empty($data['context']) && $data['context'] === 'cache') {
        // generate cache files, copy data from database
        foreach ($data as $key => $val) {
            $$key = $val;
        }
    } else {
        // data from backend
        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $$key = @htmlspecialchars($val, ENT_QUOTES);
            }
        }

        // images
        $images = '';
        if(isset($data['picker_0'])) {
            $product_images_string = implode("<->", $data['picker_0']);
            $product_images_string = "<->$product_images_string<->";
            $images = $product_images_string;
        }

        $product_accessories = '';
        if(isset($data['picker_1'])) {
            $product_accessories = json_encode($data['picker_1'],JSON_FORCE_OBJECT);
        }
        $product_related = '';
        if(isset($data['picker_2'])) {
            $product_related = json_encode($data['picker_2'], JSON_FORCE_OBJECT);
        }

        $product_options = json_encode($data['option_keys'],JSON_FORCE_OBJECT);
        $filter = json_encode($data['product_filter'],JSON_FORCE_OBJECT);

        if(isset($data['type'])) {
            $type = clean_filename($data['type']);
        }

        $priority = (int) $data['priority'];
        $product_variant_type = (int) $data['product_variant_type'];

        // translation url
        $translation_urls = '';
        if(is_array($data['translation_url'])) {
            foreach($data['translation_url'] as $k => $v) {
                $t_urls[$k] = se_clean_permalink($v);
            }
            $translation_urls = json_encode($t_urls,JSON_UNESCAPED_UNICODE);
        }

        $clean_title = clean_filename($data['title']);

        if($data['slug'] == "") {
            $slug = $clean_title.'/';
        } else {
            $slug = se_clean_permalink($data['slug']);
        }

        $categories = '';
        if(isset($data['categories'])) {
            $categories = implode("<->", (array) $data['categories']);
        }

        // prices
        $product_price_net = se_sanitize_price($data['product_price_net']);
        $product_price_manufacturer = se_sanitize_price($data['product_price_manufacturer']);

        // labels
        $product_labels = '';
        if(isset($data['labels'])) {
            $labels = implode(",", (array) $data['labels']);
        }

        // fixed?
        $fixed = 2;
        if(isset($data['fixed']) AND $data['fixed'] == 'fixed') {
            $fixed = 1;
        }

        $priority = (int) $data['priority'];

        // stock mode
        $product_stock_mode = 2;
        if(isset($data['product_ignore_stock']) AND $data['product_ignore_stock'] == 1) {
            // ignore stock
            $product_stock_mode = 1;
        }

        $product_order_quantity_min = (int) $data['product_order_quantity_min'];
        $product_order_quantity_max = (int) $data['product_order_quantity_max'];

        // metas
        $meta_title = $data['meta_title'] ?: $data['title'];
        $meta_description = $data['meta_description'] ?: strip_tags($data['teaser']);
        $lastedit_from = $_SESSION['user_nick'];
    }



    if(isset($data['save_variant'])) {
        $type = 'v';
        $modus = 'save_variant';
        $parent_id = (int) $data['save_variant'];
    }

    if (isset($data['file_attachment_user']) && $data['file_attachment_user'] == '2'){
        $file_attachment_user = 2;
    } else {
        $file_attachment_user = 1;
    }


    if($data['date'] != "") {
        if (ctype_digit($data['date'])) {
            $date = (int) $data['date'];
        } else {
            $date = strtotime($data['date']);
        }
    }

    if($data['releasedate'] != "") {
        if (ctype_digit($data['releasedate'])) {
            $releasedate = (int) $data['releasedate'];
        } else {
            $releasedate = strtotime($data['releasedate']);
        }
    }



    if($data['main_catalog_slug'] == "default") {
        //get a target page by page_type_of_use and language
        $main_catalog_slug = $db_content->get("se_pages", "page_permalink", [
            "AND" => [
                "page_type_of_use" => "display_product",
                "page_language" => $data['product_lang']
            ]
        ]);
        // if we have no page for display_products, find another catalog page
        $main_catalog_slug = $db_content->get("se_pages", "page_permalink", [
            "AND" => [
                "page_posts_types[~]" => "p",
                "page_language" => $data['product_lang']
            ]
        ]);
    } else {
        $main_catalog_slug = se_clean_permalink($data['main_catalog_slug']);
    }

    if(is_numeric($id)) {
        $filename = str_replace("/", "", $slug) . '-' . $id . '.html';
        // rss url
        if ($data['rss_url'] == "") {
            $rss_url = $se_base_url . $main_catalog_slug . $filename;
        }
    }



    $meta_title = se_return_clean_value($meta_title);
    $meta_description = se_return_clean_value($meta_description);

    // variants title and description
    if($data['product_variant_title'] == '') {
        $product_variant_title = $data['title'];
    }
    if($data['product_variant_description'] == '') {
        $product_variant_description = $meta_description;
    }

    // volume discounts
    if(isset($data['product_vd_amount'])) {
        $cnt_vd_prices = count($data['product_vd_amount']);
        for($i=0;$i<$cnt_vd_prices;$i++) {

            if($data['product_vd_amount'][$i] == '') {
                continue;
            }

            $vd_price[] = [
                'amount' => (int) $data['product_vd_amount'][$i],
                'price' => se_sanitize_price($data['product_vd_price'][$i])
            ];

        }
        $product_price_volume_discount = json_encode($vd_price,JSON_FORCE_OBJECT);
    }

    // get all columns from the installer template
    require SE_ROOT.'install/contents/se_products.php';
    // build SQL string -> f.e. "releasedate" => $releasedate,
    foreach($cols as $k => $v) {
        if($k == 'id') {continue;}
        $value = $$k;
        $inputs[$k] = "$value";
    }

    return $inputs;

}


function se_updateProductCache($id, $data, $updateSlugMap = true) {
    if($id == '' OR $data['product_lang'] == '') {
        return; // we need the id and lang
    }
    if($data['status'] == '2' OR $data['product_lang'] == '') {
        return; // no cache for drafts
    }
    $file = se_getProductCachePath($id, $data['product_lang']);
    $data['id'] = $id;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($updateSlugMap) {
        se_updateSlugMap($data['product_lang']);
    }
}

function se_updateSlugMap($lang) {
    global $db_posts;

    if (!is_dir(SE_CONTENT . '/cache/products')) {
        mkdir(SE_CONTENT . '/cache/products', 0777, true);
    }

    $products = $db_posts->select("se_products", ["id","slug"], ["product_lang" => $lang]);

    $data = [];

    foreach($products as $product) {
        $url = $product['slug'];
        if (!isset($data[$url])) {
            $data[$url] = [];
        }
        $data[$url][] = $product['id']; // IDs sammeln
    }

    $file = SE_CONTENT . '/cache/products/slug-map-'.$lang.'.json';
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function se_clearProductCache() {

    $cacheDir = SE_CONTENT . '/cache/products/';
    if (!is_dir($cacheDir)) {
        return 0;
    }

    $count = 0;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'json') {
            unlink($file->getPathname());
            $count++;
        }
    }

    return $count; // Anzahl gelöschter Dateien
}
