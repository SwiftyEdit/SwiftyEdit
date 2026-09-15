<?php

/**
 * mini shopping cart preview, shown in the header offcanvas sidebar
 * - GET: render the current cart preview
 * - POST (remove_from_cart): remove one line item, then render the
 *   updated preview
 *
 * CSRF is already validated globally by bootstrap.php for every POST
 * request, no manual se_validate_token() call needed here.
 *
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 */

// Read-only w.r.t. $_SESSION (only read below, and by se_remove_from_cart() /
// se_return_my_cart() - never assigned) - safe to release the session lock
// immediately. This fires alongside other hx-trigger="load" widgets on the
// same page, all sharing one PHPSESSID; without an early close here, the
// default file session handler would force them to queue up and run one
// at a time instead of concurrently. Do not add $_SESSION writes below
// without removing this first.
session_write_close();

// check if it's an ajax request
$isHtmxRequest = se_isAjaxRequest();

if (!$isHtmxRequest) {
    // redirect
    header('Location: /');
    exit;
}

if (!($se_settings['posts_products_cart'] == 2 || $se_settings['posts_products_cart'] == 3)) {
    http_response_code(404);
    exit;
}

if (isset($_POST['remove_from_cart'])) {
    se_remove_from_cart((int) $_POST['remove_from_cart']);
    // let the header cart counter know the item count has changed
    header('HX-Trigger: update_user_status');
}

// permalink of the cart / checkout page (same lookup as template-setup.php,
// which isn't loaded for xhr requests)
$checkout_page = se_get_type_of_use_pages('checkout');
if (($checkout_page['page_permalink'] ?? '') == '') {
    $shopping_cart_uri = '/checkout/';
} else {
    $shopping_cart_uri = '/' . $checkout_page['page_permalink'];
}

$price_mode = $se_settings['posts_price_mode'];
$currency = $se_settings['posts_products_default_currency'];

$cart_rows = se_return_my_cart();
$cart_items = [];
$subtotal_net = 0;
$subtotal_gross = 0;

foreach ($cart_rows as $row) {
    $amount = (int) $row['cart_product_amount'];
    $line_price = se_posts_calc_price($row['cart_product_price_net'], $row['cart_product_tax'], $amount);

    $cart_items[] = [
        'cart_id' => $row['cart_id'],
        'parent_id' => (int) $row['cart_parent_id'],
        'title' => $row['cart_product_title'],
        'options' => $row['cart_product_options'],
        'amount' => $amount,
        'price_net_format' => $line_price['net'],
        'price_gross_format' => $line_price['gross'],
    ];

    $subtotal_net += $line_price['net_raw'];
    $subtotal_gross += $line_price['gross_raw'];
}

$smarty->assign('cart_items', $cart_items);
$smarty->assign('cnt_items', count($cart_items));
$smarty->assign('price_mode', $price_mode);
$smarty->assign('currency', $currency);
$smarty->assign('subtotal_net_format', se_post_print_currency($subtotal_net));
$smarty->assign('subtotal_gross_format', se_post_print_currency($subtotal_gross));
$smarty->assign('shopping_cart_uri', $shopping_cart_uri);

$smarty->assign('lang_label_cart_empty', $lang['label_cart_empty']);
$smarty->assign('lang_label_net', $lang['label_net']);
$smarty->assign('lang_label_gross', $lang['label_gross']);
$smarty->assign('lang_price_subtotal', $lang['price_subtotal']);
$smarty->assign('lang_button_view_cart', $lang['button_view_cart']);
$smarty->assign('lang_close', $lang['close']);
$smarty->assign('lang_button_remove_item', $lang['button_remove_item']);

$smarty->display('cart_preview.tpl');
