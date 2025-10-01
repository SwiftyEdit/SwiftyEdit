<?php

//error_reporting(E_ALL ^E_NOTICE);


/**
 * Get products with filtering support
 *
 * @param int $start Starting offset for pagination
 * @param int|string $limit Number of products or 'all'
 * @param array $filter Filter configuration:
 *   - languages: string (language filter)
 *   - status: string (status filter)
 *   - categories: string (category filter)
 *   - labels: string (label filter)
 *   - text_search: string (search query)
 *   - sort_by: string (name|pasc|pdesc|ts)
 *   - custom_filter: array (filter item IDs for AND logic)
 *   - custom_range_filter: array (filter item IDs for OR logic)
 *
 * @return array Products with match statistics
 */
function se_get_products($start, $limit, $filter)
{
    global $db_posts, $time_string_start, $time_string_end, $time_string_now, $se_labels;

    if (SE_SECTION == 'frontend') {
        global $se_prefs;
    }

    if (empty($start)) {
        $start = 0;
    }
    if (empty($limit)) {
        $limit = 10;
    }

    // Build limit clause
    $limit_str = 'LIMIT ' . (int)$start;
    if ($limit == 'all') {
        $limit_str = '';
    } else {
        $limit_str .= ', ' . (int)$limit;
    }

    // Default order and direction
    $order = "ORDER BY fixed ASC, priority DESC, id DESC";

    // Custom order rules
    if (!empty($filter['sort_by'])) {
        switch ($filter['sort_by']) {
            case 'name':
                $order = "ORDER BY fixed ASC, title ASC, priority DESC";
                break;
            case 'pasc':
                $order = "ORDER BY fixed ASC, product_price_net*1 ASC, priority DESC";
                break;
            case 'pdesc':
                $order = "ORDER BY fixed ASC, product_price_net*1 DESC, priority DESC";
                break;
            case 'ts':
                $order = "ORDER BY fixed ASC, product_cnt_sales DESC, priority DESC";
                break;
        }
    }

    // Initialize filter defaults
    if (!isset($filter['labels'])) {
        $filter['labels'] = '';
    }
    if (!isset($filter['text_search'])) {
        $filter['text_search'] = '';
    }

    // Base filter: products and variants
    $sql_filter_start = "WHERE (type LIKE '%p%' OR type LIKE '%v%') ";

    // Language filter
    $sql_lang_filter = '';
    if (!empty($filter['languages'])) {
        $sql_lang_filter = "product_lang IS NULL OR ";
        $lang = explode('-', $filter['languages']);
        foreach ($lang as $l) {
            if ($l != '') {
                $l = addslashes(trim($l));
                $sql_lang_filter .= "(product_lang LIKE '%$l%') OR ";
            }
        }
        $sql_lang_filter = rtrim($sql_lang_filter, ' OR ');
    }

    // Custom product filter with proper OR/AND logic
    $sql_product_filter = '';
    if (!empty($filter['custom_filter_groups']) && is_array($filter['custom_filter_groups'])) {

        $group_conditions = [];

        // Iterate through filter groups
        foreach ($filter['custom_filter_groups'] as $group_id => $item_ids) {
            $item_conditions = [];

            // Within a group: OR logic (Rot OR Gelb)
            foreach ($item_ids as $item_id) {
                $item_id = (int)$item_id;
                $item_conditions[] = "filter LIKE '%:\"$item_id\"%'";
            }

            if (!empty($item_conditions)) {
                // Combine items within group with OR
                $group_conditions[] = '(' . implode(' OR ', $item_conditions) . ')';
            }
        }

        if (!empty($group_conditions)) {
            $sql_product_filter = implode(' AND ', $group_conditions);
        }
    }


    // Custom range filter (OR logic for range)
    $sql_product_range_filter = '';
    if (!empty($filter['custom_range_filter']) && is_array($filter['custom_range_filter'])) {
        foreach ($filter['custom_range_filter'] as $custom_range_filter) {
            if ($custom_range_filter != '') {
                $custom_range_filter = (int)$custom_range_filter;
                $sql_product_range_filter .= "(filter LIKE '%:\"$custom_range_filter\"%') OR ";
            }
        }
        $sql_product_range_filter = rtrim($sql_product_range_filter, ' OR ');
    }

    // Text search
    $sql_text_filter = '';
    if (!empty($filter['text_search'])) {
        $all_filter = explode(" ", $filter['text_search']);
        foreach ($all_filter as $f) {
            if ($f == "") {
                continue;
            }
            // Escape for LIKE - use real_escape_string equivalent
            $f = addslashes($f);
            $sql_text_filter .= "(tags LIKE '%$f%' OR title LIKE '%$f%' OR teaser LIKE '%$f%' OR text LIKE '%$f%') AND ";
        }
        $sql_text_filter = rtrim($sql_text_filter, ' AND ');
    }

    // Status filter
    $sql_status_filter = '';
    if (!empty($filter['status'])) {
        $sql_status_filter = "status IS NULL OR ";
        // Replace 4 (global invisible) with 3 (product invisible)
        $filter['status'] = str_replace("4", "3", $filter['status']);
        $status = explode('-', $filter['status']);
        foreach ($status as $s) {
            if ($s != '') {
                $s = addslashes($s);
                $sql_status_filter .= "(status LIKE '%$s%') OR ";
            }
        }
        $sql_status_filter = rtrim($sql_status_filter, ' OR ');
    }

    // Category filter
    $sql_cat_filter = '';
    if (!empty($filter['categories']) && $filter['categories'] != 'all') {
        $cats = explode(',', $filter['categories']);
        foreach ($cats as $c) {
            if ($c != '') {
                $c = addslashes($c);
                $sql_cat_filter .= "(categories LIKE '%$c%') OR ";
            }
        }
        $sql_cat_filter = rtrim($sql_cat_filter, ' OR ');
    }

    // Label filter
    $sql_label_filter = '';
    if (!empty($filter['labels']) && $filter['labels'] != 'all') {
        $checked_labels_array = explode('-', $filter['labels']);
        foreach ($se_labels as $label_data) {
            $label = $label_data['label_id'];
            if (in_array($label, $checked_labels_array)) {
                $label = addslashes($label);
                $sql_label_filter .= "labels LIKE '%,$label,%' OR labels LIKE '%,$label' OR labels LIKE '$label,%' OR labels = '$label' OR ";
            }
        }
        $sql_label_filter = rtrim($sql_label_filter, ' OR ');
    }

    // Build complete filter for subquery
    $sql_filter = $sql_filter_start;

    if ($sql_lang_filter != "") {
        $sql_filter .= " AND ($sql_lang_filter) ";
    }
    if ($sql_product_filter != "") {
        $sql_filter .= " AND ($sql_product_filter) ";
    }
    if ($sql_product_range_filter != '') {
        $sql_filter .= " AND ($sql_product_range_filter) ";
    }
    if ($sql_status_filter != "") {
        $sql_filter .= " AND ($sql_status_filter) ";
    }
    if ($sql_cat_filter != "") {
        $sql_filter .= " AND ($sql_cat_filter) ";
    }
    if ($sql_label_filter != "") {
        $sql_filter .= " AND ($sql_label_filter) ";
    }
    if ($sql_text_filter != "") {
        $sql_filter .= " AND ($sql_text_filter) ";
    }

    // Frontend: only show released products
    if (SE_SECTION == 'frontend') {
        $sql_filter .= "AND releasedate <= '$time_string_now' ";
    }

    // Time range filter
    if (!empty($time_string_start)) {
        $sql_filter .= "AND releasedate >= '$time_string_start' AND releasedate <= '$time_string_end' AND releasedate < '$time_string_now' ";
    }

    // Subquery: Find parent product IDs
    // For variants: take parent_id, for products: take id
    $subquery = "
        SELECT DISTINCT 
            CASE 
                WHEN type LIKE '%v%' THEN parent_id 
                ELSE id 
            END as product_id
        FROM se_products 
        $sql_filter
    ";

    // Main query: Only return parent products
    $sql = "SELECT * FROM se_products 
            WHERE id IN ($subquery) 
            AND type LIKE '%p%'
            $order $limit_str";

    $entries = $db_posts->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Count query for statistics
    $sql_cnt = "SELECT 
        count(*) AS 'P', 
        (SELECT count(*) FROM se_products WHERE type LIKE '%p%') AS 'A',
        (SELECT count(DISTINCT CASE WHEN type LIKE '%v%' THEN parent_id ELSE id END) 
         FROM se_products $sql_filter) AS 'F' ";

    $stat = $db_posts->query("$sql_cnt")->fetch(PDO::FETCH_ASSOC);

    // Add statistics to results
    if (count($entries) > 0) {
        $entries[0]['cnt_products_match'] = $stat['F'];
        $entries[0]['cnt_products_all'] = $stat['A'];
    }

    return $entries;
}



/**
 * SwiftyEdit Shop Filter Helper Functions
 * Handles URL-based product filtering without sessions
 */

/**
 * Parse URL parameters and convert slugs to filter IDs
 *
 * @param array $product_filter Array from se_get_product_filter()
 * @param array $get_params $_GET parameters (default: $_GET)
 * @return array ['custom_filter' => [ids], 'custom_range_filter' => [ids], 'active_filters' => [...]]
 */
function se_parse_shop_url_filters($product_filter, $get_params = null)
{
    if ($get_params === null) {
        $get_params = $_GET;
    }

    $custom_filter_groups = [];
    $custom_range_filter = [];
    $active_filters = [];

    // Loop through all available filter groups
    foreach ($product_filter as $filter_group) {
        $filter_slug = $filter_group['slug'];
        $filter_type = $filter_group['input_type'];
        $filter_group_id = $filter_group['id'];

        // Check if this filter is present in URL
        if (!isset($get_params[$filter_slug]) || $get_params[$filter_slug] === '') {
            continue;
        }

        $url_value = $get_params[$filter_slug];

        // Handle different filter types
        if ($filter_type == 3) {
            // Range filter: preis=100-750
            $range_parts = explode('-', $url_value);
            if (count($range_parts) === 2) {
                $min_value = trim($range_parts[0]);
                $max_value = trim($range_parts[1]);

                // Collect all item IDs between min and max
                $range_ids = se_get_range_filter_ids($filter_group['items'], $min_value, $max_value);
                $custom_range_filter = array_merge($custom_range_filter, $range_ids);

                // Store for active filter display
                $active_filters[$filter_slug] = [
                    'type' => 'range',
                    'title' => $filter_group['title'],
                    'display' => $min_value . ' - ' . $max_value,
                    'min' => $min_value,
                    'max' => $max_value
                ];
            }
        } else if ($filter_type == 2) {
            // Checkbox: color=red,blue,green
            $values = explode(',', $url_value);
            $selected_items = [];
            $group_item_ids = [];

            foreach ($values as $slug_value) {
                $slug_value = trim($slug_value);
                if ($slug_value === '') continue;

                // Find item by slug
                foreach ($filter_group['items'] as $item) {
                    if ($item['slug'] === $slug_value) {
                        $group_item_ids[] = (int)$item['id'];
                        $selected_items[] = [
                            'id' => $item['id'],
                            'slug' => $item['slug'],
                            'title' => $item['title']
                        ];
                        break;
                    }
                }
            }

            if (!empty($selected_items)) {
                $custom_filter_groups[$filter_group_id] = $group_item_ids;
                $active_filters[$filter_slug] = [
                    'type' => 'checkbox',
                    'title' => $filter_group['title'],
                    'items' => $selected_items
                ];
            }
        } else if ($filter_type == 1) {
            // Radio: color=red
            $slug_value = trim($url_value);

            // Find item by slug
            foreach ($filter_group['items'] as $item) {
                if ($item['slug'] === $slug_value) {
                    $custom_filter_groups[$filter_group_id] = [(int)$item['id']];

                    $active_filters[$filter_slug] = [
                        'type' => 'radio',
                        'title' => $filter_group['title'],
                        'item' => [
                            'id' => $item['id'],
                            'slug' => $item['slug'],
                            'title' => $item['title']
                        ]
                    ];
                    break;
                }
            }
        }
    }

    return [
        'custom_filter_groups' => $custom_filter_groups,
        'custom_range_filter' => array_unique($custom_range_filter),
        'active_filters' => $active_filters
    ];
}

/**
 * Get all range filter IDs between min and max values
 *
 * @param array $items Range filter items
 * @param string|int $min_value Minimum value
 * @param string|int $max_value Maximum value
 * @return array Item IDs
 */
function se_get_range_filter_ids($items, $min_value, $max_value)
{
    $min = (float)$min_value;
    $max = (float)$max_value;
    $range_ids = [];

    foreach ($items as $item) {
        $item_value = (float)$item['title'];

        // Include all values between min and max (inclusive)
        if ($item_value >= $min && $item_value <= $max) {
            $range_ids[] = (int)$item['id'];
        }
    }

    return $range_ids;
}

/**
 * Set checked status in product_filter array based on active filters
 *
 * @param array $product_filter Array from se_get_product_filter()
 * @param array $active_filters Active filters from se_parse_shop_url_filters()
 * @return array Modified product_filter with checked status
 */
function se_set_filter_checked_status($product_filter, $active_filters)
{
    foreach ($product_filter as $group_key => $filter_group) {
        $filter_slug = $filter_group['slug'];

        // Check if this filter group is active
        if (!isset($active_filters[$filter_slug])) {
            continue;
        }

        $active_data = $active_filters[$filter_slug];

        // Set checked status for items
        foreach ($product_filter[$group_key]['items'] as $item_key => $item) {
            $is_checked = false;

            if ($active_data['type'] === 'checkbox') {
                // Check if item slug is in active items
                foreach ($active_data['items'] as $active_item) {
                    if ($active_item['slug'] === $item['slug']) {
                        $is_checked = true;
                        break;
                    }
                }
            } else if ($active_data['type'] === 'radio') {
                // Check if this is the active radio item
                if ($active_data['item']['slug'] === $item['slug']) {
                    $is_checked = true;
                }
            } else if ($active_data['type'] === 'range') {
                // For range, check if item value is within range
                $item_value = (float)$item['title'];
                if ($item_value >= (float)$active_data['min'] &&
                    $item_value <= (float)$active_data['max']) {
                    $is_checked = true;
                }
            }

            $product_filter[$group_key]['items'][$item_key]['checked'] = $is_checked ? 'checked' : '';
        }
    }

    return $product_filter;
}

/**
 * Build filter URL with toggle logic
 */
function se_build_filter_url($filter_slug, $item_slug, $filter_type, $current_get = null) {
    if ($current_get === null) {
        $current_get = $_GET;
    }

    // Remove 'query' parameter (from .htaccess rewrite)
    $params = $current_get;
    unset($params['query']);
    unset($params['page']);

    if ($filter_type == 1) {
        // Radio: Replace value
        $params[$filter_slug] = $item_slug;

    } else if ($filter_type == 2) {
        // Checkbox: Toggle value
        $current_values = [];

        if (isset($params[$filter_slug]) && $params[$filter_slug] !== '') {
            $current_values = explode(',', $params[$filter_slug]);
        }

        $key = array_search($item_slug, $current_values);

        if ($key !== false) {
            unset($current_values[$key]);
        } else {
            $current_values[] = $item_slug;
        }

        $current_values = array_filter($current_values);

        if (empty($current_values)) {
            unset($params[$filter_slug]);
        } else {
            $params[$filter_slug] = implode(',', $current_values);
        }
    }

    $query_string = http_build_query($params);
    return $query_string ? '?' . $query_string : '';
}


/**
 * Remove a specific filter from URL
 */
function se_remove_filter_from_url($filter_slug, $item_slug = null, $current_get = null) {
    if ($current_get === null) {
        $current_get = $_GET;
    }

    // Remove 'query' parameter
    $params = $current_get;
    unset($params['query']);
    unset($params['page']);

    if ($item_slug === null) {
        unset($params[$filter_slug]);
    } else {
        if (isset($params[$filter_slug])) {
            $current_values = explode(',', $params[$filter_slug]);
            $key = array_search($item_slug, $current_values);

            if ($key !== false) {
                unset($current_values[$key]);
            }

            $current_values = array_filter($current_values);

            if (empty($current_values)) {
                unset($params[$filter_slug]);
            } else {
                $params[$filter_slug] = implode(',', $current_values);
            }
        }
    }

    $query_string = http_build_query($params);
    return $query_string ? '?' . $query_string : '';
}


/**
 * Build shop pagination URL with all current filters
 */
function se_set_shop_pagination_query($page_number, $current_get = null) {
    if ($current_get === null) {
        $current_get = $_GET;
    }

    // Remove 'query' parameter
    $params = $current_get;
    unset($params['query']);

    if ($page_number > 1) {
        $params['page'] = $page_number;
    } else {
        unset($params['page']);
    }

    $query_string = http_build_query($params);
    return $query_string ? '?' . $query_string : '';
}

/**
 * Build category URL with current filters preserved
 */
function se_build_category_url($category_slug, $base_slug, $current_get = null) {
    if ($current_get === null) {
        $current_get = $_GET;
    }

    // Remove 'query' parameter
    $params = $current_get;
    unset($params['query']);
    unset($params['page']);

    $url = '/' . $base_slug . '/' . $category_slug . '/';
    $query_string = http_build_query($params);

    if ($query_string) {
        $url .= '?' . $query_string;
    }

    return $url;
}

/**
 * Generate active filter tags for display
 *
 * @param array $active_filters Active filters from se_parse_shop_url_filters()
 * @param string $base_url Base URL (e.g., '/shop/' or '/shop/category/')
 * @return array Filter tags with remove URLs
 */
function se_get_active_filter_tags($active_filters, $base_url = '')
{
    $tags = [];

    foreach ($active_filters as $filter_slug => $filter_data) {
        if ($filter_data['type'] === 'checkbox') {
            // Create one tag per checkbox item
            foreach ($filter_data['items'] as $item) {
                $remove_url = se_remove_filter_from_url($filter_slug, $item['slug']);
                $tags[] = [
                    'filter_slug' => $filter_slug,
                    'filter_title' => $filter_data['title'],
                    'display' => $item['title'],
                    'remove_url' => $base_url . $remove_url
                ];
            }
        } else if ($filter_data['type'] === 'radio') {
            // Create one tag for radio
            $remove_url = se_remove_filter_from_url($filter_slug);
            $tags[] = [
                'filter_slug' => $filter_slug,
                'filter_title' => $filter_data['title'],
                'display' => $filter_data['item']['title'],
                'remove_url' => $base_url . $remove_url
            ];
        } else if ($filter_data['type'] === 'range') {
            // Create one tag for range
            $remove_url = se_remove_filter_from_url($filter_slug);
            $tags[] = [
                'filter_slug' => $filter_slug,
                'filter_title' => $filter_data['title'],
                'display' => $filter_data['display'],
                'remove_url' => $base_url . $remove_url
            ];
        }
    }

    return $tags;
}


/**
 * Build sort URL with current filters preserved
 *
 * @param string $sort_value Sort value (name|ts|pasc|pdesc)
 * @param array $current_get Current $_GET parameters
 * @return string URL with query parameters
 */
function se_build_sort_url($sort_value, $current_get = null) {
    if ($current_get === null) {
        $current_get = $_GET;
    }

    $params = $current_get;
    unset($params['query']);
    unset($params['page']); // Reset page on sort change

    if (!empty($sort_value)) {
        $params['sort'] = $sort_value;
    } else {
        unset($params['sort']);
    }

    $query_string = http_build_query($params);
    return $query_string ? '?' . $query_string : '';
}



function se_getProductCachePath($id, $lang) {
    $baseDir = SE_CONTENT . '/cache/products';
    $range   = floor($id / 1000) * 1000; // 1542 → 1000

    $dir = $baseDir . '/' . str_pad($range, 3, '0', STR_PAD_LEFT);

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir . '/' . $id . '-' . $lang . '.json';
}

/**
 * @param $id
 * @return mixed
 */

function se_get_product_data($id, $lang = null) {

    global $db_posts,$se_settings,$languagePack;

    $lang = $lang ?: $languagePack;

    // try cache first
    if($se_settings['products_cache'] == 1) {

        $cacheFile = se_getProductCachePath($id, $lang);
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data) {
                $data['data_source'] = 'cache';
                return $data;
            }
        }

    }

    // get data from database
    $data = $db_posts->get("se_products","*", [
        "id" => $id
    ]);

    if ($data) {
        $data['data_source'] = 'database';
        return $data;
    }

    return null; // Product doesn't exists
}

/**
 * @param $slug
 * @param $lang
 * @param $variantId
 * @return mixed|null
 */
function se_get_product_data_by_slug($slug, $lang = null, $variantId = null): mixed
{
    global $languagePack, $db_posts, $se_settings;

    $lang = $lang ?: $languagePack;

    // normalize slug, add slash if needed
    $slug = rtrim($slug, "/") . "/";

    if($se_settings['products_cache'] == 1) {

        $mapFile = SE_CONTENT . '/cache/products/slug-map-' . $lang . '.json';
        if (file_exists($mapFile)) {
            $map = json_decode(file_get_contents($mapFile), true);
            if ($map && isset($map[$slug])) {
                $productIds = (array)$map[$slug];

                // if $variantId exists in map
                if ($variantId !== null && in_array($variantId, $productIds)) {
                    $productId = $variantId;
                } else {
                    // Fallback: first ID
                    $productId = $productIds[0];
                }

                // 2. check product cache
                $cacheFile = se_getProductCachePath($productId, $lang);
                if (file_exists($cacheFile)) {
                    $data = json_decode(file_get_contents($cacheFile), true);
                    $data['data_source'] = 'cache';
                    return $data;
                }
            }
        }

    }

    // get product from database
    $where = [
        "AND" => [
            "slug" => $slug,
            "product_lang" => $lang
        ]
    ];
    if ($variantId !== null) {
        $where["AND"]["id"] = $variantId;
    }

    $data = $db_posts->get("se_products", "*", $where);

    if ($data) {
        $data['data_source'] = 'database';
        return $data;
    }

    return null; // Product doesn't exists
}


/**
 * @param $id integer id of the main product
 * @return array
 */

function se_get_product_variants($id) {
    global $db_posts;

    $get_columns = ["id","type","title","teaser","images","slug","product_variant_title","product_variant_description"];

    $main_product = $db_posts->select("se_products", $get_columns, [
        "id" => $id
    ]);

    $variants = $db_posts->select("se_products", $get_columns, [
        "parent_id" => $id
    ]);

    $products = array_merge($main_product, $variants);

    return $products;
}

/**
 * get the lowest price for a product
 * check volume discount, also
 *
 * @param integer $id
 * @return string|void
 */
function se_get_product_lowest_price(int $id) {
    global $db_posts;

    $variants = se_get_product_variants($id);
    if (count($variants) < 1) {
        return null; // kein Produkt gefunden
    }

    $allPrices = [];

    foreach ($variants as $variant) {
        $product = $db_posts->get("se_products", ["product_price_net", "product_price_volume_discount"], [
            "AND" => [
                "id" => $variant['id'],
                "status" => 1
            ]
        ]);

        if (!$product || !isset($product['product_price_net'])) {
            continue; // invalid data
        }

        // Add base price
        $basePrice = floatval(str_replace(',', '.', $product['product_price_net']));
        if ($basePrice > 0) {
            $allPrices[] = $basePrice;
        }

        // Add volume discounts
        if (!empty($product['product_price_volume_discount'])) {
            $discounts = json_decode($product['product_price_volume_discount'], true);
            if (is_array($discounts)) {
                foreach ($discounts as $entry) {
                    $discountPrice = floatval(str_replace(',', '.', $entry['price']));
                    if ($discountPrice > 0) {
                        $allPrices[] = $discountPrice;
                    }
                }
            }
        }
    }

    if (empty($allPrices)) {
        return null; // No prices found
    }

    $lowestPrice = min($allPrices);
    return str_replace('.', ',', $lowestPrice);
}



/**
 * @param $id
 * @return int|mixed
 */
function se_delete_product($id) {
    global $db_posts;
    $del_id = (int) $id;
    $cnt_delete = 0;

    $delete = $db_posts->delete("se_products", ["id" => $del_id]);
    $cnt_delete = $delete->rowCount();

    /* get product it's variants and delete them also */
    $variants = se_get_product_variants($del_id);
    $cnt_variants = count($variants);
    if($cnt_variants > 0) {
        foreach($variants as $variant) {
            $delete_variant_id = $variant['id'];
            $delete_variant = $db_posts->delete("se_products", ["id" => $delete_variant_id]);
            $cnt_delete_variant = $delete_variant->rowCount();
            $cnt_delete += $cnt_delete_variant;
        }
    }


    if($cnt_delete > 0) {
        return $cnt_delete;
    }
    return 0;
}




/**
 * increase the downloads counter
 */

function se_increase_downloads_hits($product_id) {

    global $db_posts;

    $product_file_hits = $db_posts->get("se_products","file_attachment_hits", [
        "id" => $product_id
    ]);

    $product_file_hits = ((int) $product_file_hits)+1;

    $update = $db_posts->update("se_products", [
        "file_attachment_hits" => $product_file_hits
    ],[
        "id" => $product_id
    ]);

}

/**
 * increase the hits counter
 */

function se_increase_product_hits($product_id) {

    global $db_posts,$se_bot_list;

    if(!is_numeric($product_id)) {
        return;
    }

    // User-Agent
    if (se_is_bot()) {
        return;
    }

    $product_data_hits = $db_posts->get("se_products","hits", [
        "id" => $product_id
    ]);

    $product_data_hits = ((int) $product_data_hits)+1;

    $db_posts->update("se_products", [
        "hits" => $product_data_hits
    ],[
        "id" => $product_id
    ]);

}


/**
 * add a product to cart
 * 
 */

function se_add_to_cart() {
	
	global $db_content;
	global $se_settings;

	$cart_product_id = (int) $_POST['add_to_cart'];
    $cart_product_amount = max(1, (int) ($_POST['amount'] ?? 0));
    $cart_time = time();
	
	/* check if user or visitor */
	if(is_numeric($_SESSION['user_id'])) {
		$cart_user_id = $_SESSION['user_id'];
		$cart_user_hash = '';
	} else {
		$cart_user_id = '';
		$cart_user_hash = $_SESSION['token'];
	}

    $option_string = '';
    if(is_array($_POST['product_options'])) {
        foreach($_POST['product_options'] as $option) {
            $option_string .= '<span>'.$option.'</span>';
        }
    }

    $cart_product_options_comment = clean_visitors_input($_POST['customer_options_comment']);

	
	$cart_status = 'progress';
	
	/* we store tax and price_net from item */
	$this_item = se_get_product_data($cart_product_id);
	$cart_product_price_net = $this_item['product_price_net'];
	$cart_product_title = $this_item['title'];
	$cart_product_number = $this_item['product_number'];

    $cart_product_slug = '#';
    if(isset($_POST['product_href'])) {
        $cart_product_slug = htmlspecialchars($_POST['product_href'], ENT_QUOTES, 'UTF-8');
    }

	
	if($this_item['product_tax'] == '1') {
		$cart_product_tax = $se_settings['posts_products_default_tax'];
	} else if($this_item['product_tax'] == '2') {
		$cart_product_tax = $se_settings['posts_products_tax_alt1'];
	} else {
		$cart_product_tax = $se_settings['posts_products_tax_alt2'];
	}
	
	$db_content->insert("se_carts", [
		"cart_time" =>  $cart_time,
		"cart_user_hash" =>  $cart_user_hash,
		"cart_user_id" =>  $cart_user_id,
		"cart_product_id" =>  $cart_product_id,
        "cart_product_slug" =>  $cart_product_slug,
		"cart_product_amount" =>  $cart_product_amount,
		"cart_product_price_net" =>  $cart_product_price_net,
		"cart_product_tax" =>  $cart_product_tax,
		"cart_product_title" =>  $cart_product_title,
        "cart_product_options" =>  $option_string,
        "cart_product_options_comment" =>  $cart_product_options_comment,
		"cart_product_number" =>  $cart_product_number,
		"cart_status" =>  $cart_status
	]);
			
	$insert_id = $db_content->id();
	return $insert_id;
}


/**
 * @param $item
 * @param $amount
 * @return void
 */

function se_update_cart_item_amount($item,$amount){
    global $db_content;

    $item = (int) $item;
    $amount = (int) $amount;

    /* check if user or visitor */
    if(is_numeric($_SESSION['user_id'])) {
        $cart_user_id = $_SESSION['user_id'];

        $db_content->update("se_carts", [
            "cart_product_amount" => $amount
        ], [
            "AND" => [
                "cart_id" => $item,
                "cart_user_id" => $cart_user_id,
                "cart_status" => "progress"
            ]
        ]);

    } else {

        $cart_user_hash = $_SESSION['token'];
        $db_content->update("se_carts", [
            "cart_product_amount" => $amount
        ], [
            "AND" => [
                "cart_id" => $item,
                "cart_user_hash" => $cart_user_hash,
                "cart_status" => "progress"
            ]
        ]);

    }
}


function se_return_cart_amount() {
	
	global $db_content;

	/* check if user or visitor */
	if(isset($_SESSION['user_id']) AND is_numeric($_SESSION['user_id'])) {
		$cart_user_id = $_SESSION['user_id'];
		
		$items = $db_content->select("se_carts", ["cart_id"], [
            "AND" => [
                "OR" => [
                    "cart_user_id" => $cart_user_id,
                    "cart_user_hash" => $_SESSION['token'],
                ],
                "cart_status" => "progress"
            ]
		]);
		
	} else {
		$cart_user_hash = $_SESSION['token'];
		$items = $db_content->select("se_carts", ["cart_id"], [
			"AND" => [
				"cart_user_hash" => $_SESSION['token'],
				"cart_status" => "progress"
			]
		]);
	}
	
	$cnt_items = count($items);
	
	return $cnt_items;
	
}


function se_return_my_cart() {
	
	global $db_content;
	
	/* check if user or visitor */
	if(is_numeric($_SESSION['user_id'])) {
		$cart_user_id = $_SESSION['user_id'];

        // check if we have products with hash
        $items_from_hash = $db_content->select("se_carts", "*", [
            "AND" => [
                "cart_user_hash" => $_SESSION['token'],
                "cart_status" => "progress"
            ]
        ]);

        foreach($items_from_hash as $item) {
            $db_content->update("se_carts", [
                "cart_user_id" => $cart_user_id
            ],[
                "AND" => [
                    "cart_user_hash" => $_SESSION['token'],
                    "cart_status" => "progress"
                ]
            ]);
        }
		
		$items = $db_content->select("se_carts", "*", [
			"AND" => [
                "OR" => [
                    "cart_user_id" => $cart_user_id,
                    "cart_user_hash" => $_SESSION['token'],
                ],
				"cart_status" => "progress"
			]
		]);
		
	} else {

		$items = $db_content->select("se_carts", "*", [
			"AND" => [
				"cart_user_hash" => $_SESSION['token'],
				"cart_status" => "progress"
			]
		]);
	}

	return $items;
}

/**
 * remove items by id (int)
 */

function se_remove_from_cart($id) {
	
	global $db_content;
	
	$id = (int) $id;
	
	/* check if user or visitor */
	if(is_numeric($_SESSION['user_id'])) {
		$cart_user_id = $_SESSION['user_id'];
		$data = $db_content->delete("se_carts", [
			"AND" => [
				"cart_user_id" => $cart_user_id,
				"cart_status" => "progress",
				"cart_id" => $id
			]
		]);
		
	} else {
		$cart_user_hash = $_SESSION['token'];
		$data = $db_content->delete("se_carts", [
			"AND" => [
				"cart_user_hash" => $cart_user_hash,
				"cart_status" => "progress",
				"cart_id" => $id
			]
		]);		
		
	}
}


/**
 * @param $user user id for clients or hash for guest
 * @return void
 */
function se_clear_cart($user) {

    global $db_content;

    if(is_numeric($user)) {
        $data = $db_content->delete("se_carts", [
            "AND" => [
                "cart_user_id" => $user,
                "cart_status" => "progress"
            ]
        ]);
    } else {
        $data = $db_content->delete("se_carts", [
            "AND" => [
                "cart_user_hash" => $user,
                "cart_status" => "progress"
            ]
        ]);
    }
}

/**
 * get payment methods
 */
 
function se_get_payment_methods(): array {
	
	global $se_settings,$languagePack;
	global $lang;
	$payment_methods = array();

    // get payment addons
    $active_payment_addons = json_decode($se_settings['payment_addons'],true);
    if(!is_array($active_payment_addons)) {
        $active_payment_addons = array();
    }

    if(count($active_payment_addons) > 0) {
        foreach ($active_payment_addons as $payment_addon) {

            $key = clean_filename($payment_addon);
            $addon_data = se_get_payment_method_data($payment_addon);

            if (!is_array($addon_data)) {
                error_log("se_get_payment_method_data returned non-array for: " . $payment_addon);
                continue;
            }

            $costs = se_reformat_payment_costs($addon_data['addon_additional_costs']);
            $snippet_data = se_get_snippet($addon_data['addon_snippet_cart'],$languagePack,'all');

            $payment_methods[$key] = [
                "addon" => $payment_addon,
                "key" => $key,
                "cost" => $costs,
                "title" => $snippet_data['snippet_title'] ?? '',
                "snippet" => $snippet_data['snippet_content'] ?? '',
                "checked" => ""
            ];

        }
    }
	return $payment_methods;
}

/**
 * find payment addons from /plugins/
 * payment addons has the suffix -pay
 * @return array basename of addons
 */
function se_get_payment_addons() {
    $addons = array();
    $get_addons = glob(SE_ROOT.'/plugins/*-pay');

    if(is_array($get_addons)) {
        foreach($get_addons as $addon) {
            $addons[] = basename($addon);
        }
    }

    return $addons;
}

/**
 * find delivery addons from /plugins/
 * delivery addons has the suffix -delivery
 * @return array basename of addons
 */
function se_get_delivery_addons() {
    $addons = array();
    $get_addons = glob(SE_ROOT.'/plugins/*-delivery');

    if(is_array($get_addons)) {
        foreach($get_addons as $addon) {
            $addons[] = basename($addon);
        }
    }

    return $addons;
}

function se_get_payment_method_data($addon) {

    $addon_payment_prefs = array();

    $addon_config = SE_ROOT.'/plugins/'.$addon.'/pm_config.php';
    if(is_file($addon_config)) {
        require $addon_config;
    }

    return $addon_payment_prefs;
}


function se_reformat_payment_costs($amount) {

    $format = str_replace(',', '.', $amount);
    if($format == '') {
        $format = '0.00';
    }
    return $format;
}


/**
 * client send an order
 * $data (array)
 * return row_id
 */

function se_send_order($data) {
	
	global $db_content;
	global $se_prefs;
	
	$user_id = $data['user_id'];
	$order_nbr = $data['order_nbr'];
	$order_time = time();
	$order_status = 1;
	$order_status_shipping = 1;
	$order_status_payment = 1;
	$order_shipping_address = $data['order_shipping_address'];
    $order_invoice_address = $data['order_invoice_address'];
    $order_invoice_mail = $data['user_mail'];
	$order_products = $data['order_products'];
	$order_price_total = $data['order_price_total'];
	$order_shipping_type = $data['order_shipping_type'];
	$order_shipping_costs = $data['order_shipping_costs'];
	$order_payment_type = $data['order_payment_type'];
	$order_payment_costs = $data['order_payment_costs'];
    $order_comment = clean_visitors_input($data['order_comment']);
	
	$db_content->insert("se_orders", [
		"user_id" => "$user_id",
		"order_nbr" => "$order_nbr",
		"order_time" => "$order_time",
		"order_status" => "$order_status",
		"order_status_shipping" => "$order_status_shipping",
		"order_status_payment" => "$order_status_payment",
        "order_shipping_address" => "$order_shipping_address",
		"order_invoice_address" => "$order_invoice_address",
        "order_invoice_mail" => "$order_invoice_mail",
		"order_products" => "$order_products",
		"order_price_total" => $order_price_total,
		"order_shipping_type" => "$order_shipping_type",
		"order_shipping_costs" => "$order_shipping_costs",
		"order_payment_type" => "$order_payment_type",
		"order_payment_costs" => "$order_payment_costs",
		"order_currency" => $se_prefs['prefs_posts_products_default_currency'],
        "order_user_comment" => "$order_comment"
		
	]);

	$order_id = $db_content->id();

	return $order_id;
}

/**
 * @param array $items amount and item
 * @return void
 *
 * if an order was sent, increse sales and if necessary, reduce stock
 */

function se_recalculate_stock_sales($items) {
    global $db_posts;
    $cnt_items = 0;
    if(is_array($items)) {
        $cnt_items = count($items);
    }


    for($i=0;$i<$cnt_items;$i++) {

        $post_id = (int) $items[$i]['post_id'];
        $amount = (int) $items[$i]['amount'];

        $stock_mode = $db_posts->get("se_products", "product_stock_mode", [
            "id" => $post_id
        ]);

        if($stock_mode == 1) {
            /* ignore stock counter */
            $db_posts->update("se_products", [
                "product_cnt_sales[+]" => $amount
            ], [
                "id" => $post_id
            ]);
        } else {
            $db_posts->update("se_products", [
                "product_cnt_sales[+]" => $amount,
                "product_nbr_stock[-]" => $amount
            ], [
                "id" => $post_id
            ]);
        }
    }
}


/**
 * @param mixed $user if is numeric get orders by user id
 * @param array $filter status_payment, status_shipping, status_order
 * @param array $sort key and direction
 * @param integer $start start for pagination
 * @param integer $limit number of entries
 * @return void
 */

function se_get_orders($user, $filter, $sort, $start=0, $limit=10) {
	
	global $db_content;

    if(isset($filter['status_shipping'])) {
        $set_filter_status_shipping = $filter['status_shipping'];
    }
    if(isset($filter['status_payment'])) {
        $set_filter_status_payment = $filter['status_payment'];
    }
    if(isset($filter['status_order'])) {
        $set_filter_status_order = $filter['status_order'];
    }

    if(empty($set_filter_status_payment)) {
        $set_filter_status_payment = [1,2,3];
    }
    if(empty($set_filter_status_shipping)) {
        $set_filter_status_shipping = [1,2,3];
    }
    if(empty($set_filter_status_order)) {
        $set_filter_status_order = [1,2,3];
    }

    if(empty($sort['key'])) {
        $sort['key'] = 'order_time';
    }
    if(empty($sort['direction'])) {
        $sort['direction'] = 'DESC';
    }

	/* check if user or visitor */
	if(is_numeric($user)) {
		$user_id = (int) $user;
		
		$orders = $db_content->select("se_orders", "*", [
			"AND" => [
				"user_id" => $user_id,
				"order_status" => $set_filter_status_order,
                "order_status_shipping" => $set_filter_status_shipping,
                "order_status_payment" => $set_filter_status_payment
			],
			"ORDER" => [
                $sort['key'] => $sort['direction']
			],
            "LIMIT" => [$start,$limit]
		]);
		
	} else if($user == 'all') {

		$orders = $db_content->select("se_orders", "*", [
			"AND" => [
                "order_status" => $set_filter_status_order,
                "order_status_shipping" => $set_filter_status_shipping,
                "order_status_payment" => $set_filter_status_payment
			],
			"ORDER" => [
                $sort['key'] => $sort['direction']
			],
            "LIMIT" => [$start,$limit]
		]);

        $orders_cnt = $db_content->count("se_orders",[
            "AND" => [
                "order_status" => $set_filter_status_order,
                "order_status_shipping" => $set_filter_status_shipping,
                "order_status_payment" => $set_filter_status_payment
            ]
        ]);

        // number of orders matching the filter
        $orders[0]['cnt_matching_orders'] = $orders_cnt;

	} else {
		return;
	}
		
	return $orders;
}

/**
 * get order details
 * $id (int)
 *	return array
 */
 
function se_get_order_details($id) {
	
	global $db_content;

	$order = $db_content->get("se_orders","*", [
		"id" => $id
	]);
	
	return $order;
}

/**
 * @param int $type 1 or 2
 * @param string $lang en, de ...
 * @return array
 */
function se_get_product_filter_groups(string $lang): array {

    global $db_content, $lang_codes;
    if($lang == 'all' OR $lang == '') {
        $lang_filter = $lang_codes;
    } else {
        $lang_filter = [$lang];
    }

    $filters = $db_content->select("se_filter", "*",[
        "AND" => [
            "filter_lang" => $lang_filter,
            "filter_type" => 1
        ],
        "ORDER" => [
                "filter_priority" => "DESC"
        ]
    ]);
    return $filters;
}

/**
 * get all filter values from se_filter
 * @param integer $pid id of the filter entry
 * @return mixed
 */
function se_get_product_filter_values($pid): mixed {

    global $db_content;
    $pid = (int) $pid;

    $items = $db_content->select("se_filter", "*",[
        "AND" => [
            "filter_parent_id" => $pid,
            "filter_type" => 2
        ],
        "ORDER" => [
            "filter_priority" => "DESC"
        ]
    ]);

    return $items;
}

/**
 * get products filter
 * @param string $lang
 * @return array
 */
function se_get_product_filter($lang = 'de') {
    global $db_content;

    // Load filter groups
    $filters = $db_content->select('se_filter', '*', [
        'filter_lang' => $lang,
        'filter_parent_id' => NULL,
        'ORDER' => ['filter_priority' => 'ASC']
    ]);

    $result = [];

    foreach ($filters as $filter_group) {
        // Load filter items for this group
        $items = $db_content->select('se_filter', '*', [
            'filter_parent_id' => $filter_group['filter_id'],
            'ORDER' => ['filter_priority' => 'ASC']
        ]);

        $filter_items = [];
        foreach ($items as $item) {
            $filter_items[] = [
                'id' => $item['filter_id'],
                'hash' => $item['filter_hash'],
                'title' => $item['filter_title'],
                'slug' => $item['filter_slug'],
                'description' => $item['filter_description'],
                'class' => '', // Will be set later if needed
                'checked' => '' // Will be set by se_set_filter_checked_status()
            ];
        }

        $result[] = [
            'title' => $filter_group['filter_title'],
            'slug' => $filter_group['filter_slug'],
            'id' => $filter_group['filter_id'],
            'input_type' => $filter_group['filter_input_type'],
            'categories' => $filter_group['filter_categories'],
            'description' => $filter_group['filter_description'],
            'items' => $filter_items
        ];
    }

    return $result;
}



/**
 * get all price groups
 * @return mixed
 */
function se_get_price_groups() {

    global $db_posts;

    $groups = $db_posts->select("se_prices", "*");

    return $groups;
}

/**
 * @param string $hash
 * @return mixed
 */
function se_get_price_group_data($hash) {
    global $db_posts;
    $data = $db_posts->get("se_prices", "*",[
        "hash" => $hash
    ]);
    return $data;
}

/**
 * @return array
 * get all keywords
 * key is the keyword, value the counter
 */
function se_get_products_keywords() {

    global $db_posts;

    $get_keywords = $db_posts->select("se_products", "tags",[
        "tags[!]" => ""
    ]);

    $get_keywords = array_filter( $get_keywords );
    foreach($get_keywords as $keys) {
        $keys_string .= trim($keys).',';
    }
    $keys_string = str_replace(', ', ',', $keys_string);
    $keys_string = str_replace(' ,', ',', $keys_string);
    $keys_array = explode(",",$keys_string);
    $keys_array = array_filter( $keys_array );
    $count_keywords = array_count_values($keys_array);

    return $count_keywords;
}