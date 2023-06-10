<?php

//error_reporting(E_ALL ^E_NOTICE);


/**
 * @param int $start
 * @param int $limit
 * @param array $filter
 * @return array
 */

function se_get_products($start,$limit,$filter) {

    global $db_posts;
    global $time_string_start;
    global $time_string_end;
    global $time_string_now;
    global $se_labels;

    if(SE_SECTION == 'frontend') {
        global $se_prefs;
    }

    if(empty($start)) {
        $start = 0;
    }
    if(empty($limit)) {
        $limit = 10;
    }


    $limit_str = 'LIMIT '. (int) $start;

    if($limit == 'all') {
        $limit_str = '';
    } else {
        $limit_str .= ', '. (int) $limit;
    }


    /**
     * default order and direction
     */

    $order = "ORDER BY fixed ASC, priority DESC, id DESC";

    if($direction == 'ASC') {
        $direction = 'ASC';
    } else {
        $direction = 'DESC';
    }

    /* we have a custom order rule */
    if($filter['sort_by'] != '') {
        if($filter['sort_by'] == 'name') {
            $order = "ORDER BY fixed ASC, title ASC, priority DESC";
        }
        if($filter['sort_by'] == 'pasc') {
            $order = "ORDER BY fixed ASC, product_price_net*1 ASC, priority DESC";
        }
        if($filter['sort_by'] == 'pdesc') {
            $order = "ORDER BY fixed ASC, product_price_net*1 DESC, priority DESC";
        }
        if($filter['sort_by'] == 'ts') {
            $order = "ORDER BY fixed ASC, product_cnt_sales DESC, priority DESC";
        }

    }


    /* set filters */
    $sql_filter_start = "WHERE type LIKE '%p%' ";

    /* language filter */
    $sql_lang_filter = "product_lang IS NULL OR ";
    $lang = explode('-', $filter['languages']);
    foreach($lang as $l) {
        if($l != '') {
            $sql_lang_filter .= "(product_lang LIKE '%$l%') OR ";
        }
    }
    $sql_lang_filter = substr("$sql_lang_filter", 0, -3); // cut the last ' OR'

    /* custom product filter - stored in $_SESSION['custom_filter'] */
    $nbr_of_filter = is_array($_SESSION['custom_filter']) ? count($_SESSION['custom_filter']) : 0;

    if(SE_SECTION == 'backend') {
        // reset the custom filter
        // we do not use the filter in the backend
        $nbr_of_filter = 0;
    }

    if ($nbr_of_filter > 0) {
        $sql_product_filter = "filter IS NULL OR ";
        foreach ($_SESSION['custom_filter'] as $custom_filter) {
            if ($custom_filter != '') {
                $sql_product_filter .= "(filter LIKE '%:\"$custom_filter\"%') AND ";
            }
        }
        $sql_product_filter = substr("$sql_product_filter", 0, -4); // cut the last ' AND'
    } else {
        $sql_product_filter = '';
    }

    /* text search */
    if($filter['text_search'] == '') {
        $sql_text_filter = '';
    } else {
        $tf = $filter['text_search'];
        $sql_text_filter = "(title LIKE '%$tf%') OR 
        (teaser LIKE '%$tf%') OR 
        (text LIKE '%$tf%') OR 
        (product_number LIKE '%$tf%') OR
        (text_additional1 LIKE '%$tf%') OR 
        (text_additional2 LIKE '%$tf%') OR 
        (text_additional3 LIKE '%$tf%') OR 
        (text_additional4 LIKE '%$tf%') OR 
        (text_additional5 LIKE '%$tf%')";
    }

    /* status filter */
    $sql_status_filter = "status IS NULL OR ";
    $status = explode('-', $filter['status']);
    foreach($status as $s) {
        if($s != '') {
            $sql_status_filter .= "(status LIKE '%$s%') OR ";
        }
    }
    $sql_status_filter = substr("$sql_status_filter", 0, -3); // cut the last ' OR'


    /* category filter */
    if($filter['categories'] == 'all' OR $filter['categories'] == '') {
        $sql_cat_filter = '';
    } else {

        $cats = explode(',', $filter['categories']);
        foreach($cats as $c) {
            if($c != '') {
                $sql_cat_filter .= "(categories LIKE '%$c%') OR ";
            }
        }
        $sql_cat_filter = substr("$sql_cat_filter", 0, -3); // cut the last ' OR'
    }

    /* label filter */
    if($filter['labels'] == 'all' OR $filter['labels'] == '') {
        $sql_label_filter = '';
    } else {

        $checked_labels_array = explode('-', $filter['labels']);

        for($i=0;$i<count($se_labels);$i++) {
            $label = $se_labels[$i]['label_id'];
            if(in_array($label, $checked_labels_array)) {
                $sql_label_filter .= "labels LIKE '%,$label,%' OR labels LIKE '%,$label' OR labels LIKE '$label,%' OR labels = '$label' OR ";
            }
        }
        $sql_label_filter = substr("$sql_label_filter", 0, -3); // cut the last ' OR'
    }

    $sql_filter = $sql_filter_start;

    if($sql_lang_filter != "") {
        $sql_filter .= " AND ($sql_lang_filter) ";
    }
    if($sql_product_filter != "") {
        $sql_filter .= " AND ($sql_product_filter) ";
    }
    if($sql_status_filter != "") {
        $sql_filter .= " AND ($sql_status_filter) ";
    }
    if($sql_cat_filter != "") {
        $sql_filter .= " AND ($sql_cat_filter) ";
    }
    if($sql_label_filter != "") {
        $sql_filter .= " AND ($sql_label_filter) ";
    }
    if($sql_text_filter != "") {
        $sql_filter .= " AND ($sql_text_filter) ";
    }

    if(SE_SECTION == 'frontend') {
        $sql_filter .= "AND releasedate <= '$time_string_now' ";
    }

    if($time_string_start != '') {
        $sql_filter .= "AND releasedate >= '$time_string_start' AND releasedate <= '$time_string_end' AND releasedate < '$time_string_now' ";
    }

    $sql = "SELECT * FROM se_products $sql_filter $order $limit_str";
    $entries = $db_posts->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $sql_cnt = "SELECT count(*) AS 'P', (SELECT count(*) FROM se_products WHERE type LIKE '%p%') AS 'A' ,(SELECT count(*) FROM se_products $sql_filter) AS 'F' ";
    $stat = $db_posts->query("$sql_cnt")->fetch(PDO::FETCH_ASSOC);

    /* number of posts that match the filter */
    $entries[0]['cnt_products_match'] = $stat['F'];
    $entries[0]['cnt_products_all'] = $stat['A'];
    return $entries;
}

/**
 * @param $id
 * @return mixed
 */

function se_get_product_data($id) {

    global $db_posts;

    $data = $db_posts->get("se_products","*", [
        "id" => $id
    ]);

    return $data;
}

/**
 * @param $id integer id of the main product
 * @return array
 */

function se_get_product_variants($id) {
    global $db_posts;

    $get_columns = ["id","title","teaser","images","slug"];

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

    global $db_posts;

    if(!is_numeric($product_id)) {
        return false;
    }

    $product_data_hits = $db_posts->get("se_products","hits", [
        "id" => $product_id
    ]);

    $product_data_hits = ((int) $product_data_hits)+1;

    $update = $db_posts->update("se_products", [
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
	global $se_prefs;

	$cart_product_id = (int) $_POST['add_to_cart'];
	$cart_product_amount = 1;
	$cart_time = time();
	
	/* check if user or visitor */
	if(is_numeric($_SESSION['user_id'])) {
		$cart_user_id = $_SESSION['user_id'];
		$cart_user_hash = '';
	} else {
		$cart_user_id = '';
		$cart_user_hash = $_SESSION['visitor_csrf_token'];
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
	
	if($this_item['product_tax'] == '1') {
		$cart_product_tax = $se_prefs['prefs_posts_products_default_tax'];
	} else if($this_item['product_tax'] == '2') {
		$cart_product_tax = $se_prefs['prefs_posts_products_tax_alt1'];
	} else {
		$cart_product_tax = $se_prefs['prefs_posts_products_tax_alt2'];
	}
	
	$db_content->insert("se_carts", [
		"cart_time" =>  $cart_time,
		"cart_user_hash" =>  $cart_user_hash,
		"cart_user_id" =>  $cart_user_id,
		"cart_product_id" =>  $cart_product_id,
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

        $cart_user_hash = $_SESSION['visitor_csrf_token'];
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
	if(is_numeric($_SESSION['user_id'])) {
		$cart_user_id = $_SESSION['user_id'];
		
		$items = $db_content->select("se_carts", ["cart_id"], [
			"AND" => [
				"cart_user_id" => $cart_user_id,
				"cart_status" => "progress"
			]
		]);
		
	} else {
		$cart_user_hash = $_SESSION['visitor_csrf_token'];
		$items = $db_content->select("se_carts", ["cart_id"], [
			"AND" => [
				"cart_user_hash" => $cart_user_hash,
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
		
		$items = $db_content->select("se_carts", "*", [
			"AND" => [
				"cart_user_id" => $cart_user_id,
				"cart_status" => "progress"
			]
		]);
		
	} else {
		$cart_user_hash = $_SESSION['visitor_csrf_token'];
		$items = $db_content->select("se_carts", "*", [
			"AND" => [
				"cart_user_hash" => $cart_user_hash,
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
		$cart_user_hash = $_SESSION['visitor_csrf_token'];
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
 * at the moment we have no third party payment methods
 * just check if payment method is active
 */
 
function se_get_payment_methods() {
	
	global $se_prefs;
	global $lang;
	$payment_methods = array();
	
	if($se_prefs['prefs_pm_bank_transfer'] == 1) {
		$payment_methods[0]['key'] = 'prefs_pm_bank_transfer';
		$payment_methods[0]['cost'] = $se_prefs['prefs_payment_costs_bt'];
		$payment_methods[0]['title'] = $lang['label_pm_bank_transfer'];
		$payment_methods[0]['checked'] = '';
	}
	if($se_prefs['prefs_pm_invoice'] == 1) {
		$payment_methods[1]['key'] = 'prefs_pm_invoice';
		$payment_methods[1]['cost'] = $se_prefs['prefs_payment_costs_invoice'];
		$payment_methods[1]['title'] = $lang['label_pm_invoice'];
		$payment_methods[1]['checked'] = '';
	}
	if($se_prefs['prefs_pm_cash'] == 1) {
		$payment_methods[2]['key'] = 'prefs_pm_cash';
		$payment_methods[2]['cost'] = $se_prefs['prefs_payment_costs_cash'];
		$payment_methods[2]['title'] = $lang['label_pm_cash'];
		$payment_methods[2]['checked'] = '';
	}
	if($se_prefs['prefs_pm_paypal'] == 1) {
		$payment_methods[3]['key'] = 'prefs_pm_paypal';
		$payment_methods[3]['cost'] = $se_prefs['prefs_payment_costs_paypal'];
		$payment_methods[3]['title'] = $lang['label_pm_paypal'];
		$payment_methods[3]['checked'] = '';
	}
	
	return $payment_methods;
}

/**
 * get payment costs from array
 * key (string)
 */
 
function se_get_payment_costs($key) {
	
	$payment_methods = se_get_payment_methods();
	
	$id = array_search($key, array_column($payment_methods, 'key'));
	$pm_costs = $payment_methods[$id]['cost'];
	$pm_costs = str_replace(',', '.', $pm_costs);

    if($pm_costs == '') {
        $pm_costs = '0.00';
    }

	return $pm_costs;
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
	$order_nbr = $user_id.'-'.uniqid();
	$order_time = time();
	$order_status = 1;
	$order_status_shipping = 1;
	$order_status_payment = 1;
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
    $cnt_items = count($items);

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
			]
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
			]
		]);

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
function se_get_product_filter_groups($lang) {

    global $db_content, $languagePack, $lang_codes;
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

function se_get_product_filter_values($pid) {

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


function se_get_product_filter($lang) {

    global $languagePack;

    if($lang == '') {
        $lang = $languagePack;
    }

    $filter_groups = se_get_product_filter_groups($lang);

    // loop through groups
    foreach($filter_groups as $k => $v) {

        $filter[$k] = [
            "title" => $v['filter_title'],
            "id" => $v['filter_id'],
            "input_type" => $v['filter_input_type']
        ];

        $get_filter_items = se_get_product_filter_values($v['filter_id']);
        // loop through items
        foreach($get_filter_items as $filter_item) {

            if(in_array($filter_item['filter_id'],$_SESSION['custom_filter'])) {
                $class = 'active';
                $checked = 'checked';
            } else {
                $class = '';
                $checked = '';
            }

            $filter[$k]['items'][] = [
                "id" => $filter_item['filter_id'],
                "title" => $filter_item['filter_title'],
                "class" => $class,
                "checked" => $checked
            ];

        }


    }


    return $filter;
}