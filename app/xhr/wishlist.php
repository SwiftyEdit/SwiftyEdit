<?php

/**
 * xhr endpoints for wishlists
 *
 * CSRF is already validated globally by bootstrap.php for every POST
 * request, no manual se_validate_token() call needed here.
 *
 * @var object $db_content medoo database object
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 */

if(($se_settings['wishlist_enabled'] ?? 0) != 1) {
    http_response_code(404);
    exit;
}

foreach($lang as $key => $val) {
    $smarty->assign("lang_$key", $val);
}

$logged_in = (($_SESSION['user_nick'] ?? '') != "");
$current_user_id = (int) ($_SESSION['user_id'] ?? 0);

// ----- add a product from the public wishlist view to the visitor's own
// cart (no login required - guests can add to cart from a shared link,
// same as se_add_to_cart() itself already supports for guests) -----
if(isset($_POST['add_to_cart'])) {

    se_add_to_cart();

    header("HX-Trigger: update_user_status");
    echo '<div class="text-success small mt-auto"><i class="bi bi-check2-circle"></i> ' . $lang['msg_wishlist_added_to_cart'] . '</div>';
    exit;
}

// ----- picker: list of the user's wishlists + inline "new list" -----
if(($_REQUEST['form'] ?? '') == 'picker') {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    $smarty->assign('picker_product_id', (int) ($_GET['product_id'] ?? 0), true);
    $smarty->assign('picker_product_href', htmlspecialchars($_GET['product_href'] ?? '', ENT_QUOTES, 'UTF-8'), true);
    $smarty->assign('picker_lists', se_get_user_wishlists($current_user_id), true);
    $smarty->display('wishlist_picker.tpl', $cache_id);
    exit;
}

// ----- list column: the user's own wishlists ("my wishlists" overview) -----
if(($_REQUEST['form'] ?? '') == 'lists') {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    $smarty->assign('wishlists', se_get_user_wishlists($current_user_id), true);
    $smarty->display('wishlist_list_column.tpl', $cache_id);
    exit;
}

// ----- detail partial (owned list) -----
if(($_REQUEST['form'] ?? '') == 'detail') {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    $wishlist = se_get_wishlist_by_id((int) ($_GET['id'] ?? 0), $current_user_id);
    if(!is_array($wishlist)) {
        http_response_code(404);
        exit;
    }

    global $se_base_url;
    $wishlist_page = se_get_type_of_use_pages('wishlist');
    $wishlist_page_uri = ($wishlist_page['page_permalink'] ?? '') !== '' ? $wishlist_page['page_permalink'] : 'wishlist/';

    $smarty->assign('wishlist', $wishlist, true);
    $smarty->assign('wishlist_items', se_get_wishlist_items((int) $wishlist['id']), true);
    $smarty->assign('wishlist_share_url', rtrim($se_base_url, '/') . '/' . $wishlist_page_uri . $wishlist['slug'] . '/', true);
    $smarty->display('wishlist_detail.tpl', $cache_id);
    exit;
}

// ----- create a new list (optionally add a product right away, from the picker) -----
if(isset($_POST['create_wishlist'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    $product_id = (int) ($_POST['product_id'] ?? 0);
    $product_href = $_POST['product_href'] ?? '';
    $new_id = se_create_wishlist($current_user_id, trim($_POST['wishlist_name'] ?? ''));

    if($new_id > 0 && $product_id > 0) {
        se_add_wishlist_item($new_id, $current_user_id, $product_id, $product_href);
    }

    header("HX-Trigger: update_wishlists");
    $smarty->assign('picker_product_id', $product_id, true);
    $smarty->assign('picker_product_href', htmlspecialchars($product_href, ENT_QUOTES, 'UTF-8'), true);
    $smarty->assign('picker_lists', se_get_user_wishlists($current_user_id), true);
    $smarty->assign('picker_message', $lang['msg_wishlist_item_added'], true);
    $smarty->display('wishlist_picker.tpl', $cache_id);
    exit;
}

// ----- add item to an existing list -----
if(isset($_POST['add_wishlist_item'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    $product_id = (int) $_POST['add_wishlist_item'];
    $product_href = $_POST['product_href'] ?? '';
    se_add_wishlist_item((int) ($_POST['wishlist_id'] ?? 0), $current_user_id, $product_id, $product_href);

    header("HX-Trigger: update_wishlists");
    $smarty->assign('picker_product_id', $product_id, true);
    $smarty->assign('picker_product_href', htmlspecialchars($product_href, ENT_QUOTES, 'UTF-8'), true);
    $smarty->assign('picker_lists', se_get_user_wishlists($current_user_id), true);
    $smarty->assign('picker_message', $lang['msg_wishlist_item_added'], true);
    $smarty->display('wishlist_picker.tpl', $cache_id);
    exit;
}

// ----- remove an item -----
if(isset($_POST['remove_wishlist_item'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    se_remove_wishlist_item((int) $_POST['remove_wishlist_item'], $current_user_id);

    // detail panel needs the item removed, list column needs its item_count updated
    header("HX-Trigger: update_wishlist_detail, update_wishlists");
    exit;
}

// ----- rename a list -----
if(isset($_POST['rename_wishlist'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    se_rename_wishlist((int) ($_POST['wishlist_id'] ?? 0), $current_user_id, trim($_POST['wishlist_name'] ?? ''));

    // list column shows the new name, detail panel re-confirms the saved value
    header("HX-Trigger: update_wishlists, update_wishlist_detail");
    exit;
}

// ----- toggle public/private -----
if(isset($_POST['toggle_wishlist_public'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    se_set_wishlist_visibility((int) ($_POST['wishlist_id'] ?? 0), $current_user_id, ($_POST['is_public'] ?? '') == '1');

    // detail panel shows/hides the share-link section, list column shows/hides the public badge
    header("HX-Trigger: update_wishlist_detail, update_wishlists");
    exit;
}

// ----- delete a list -----
if(isset($_POST['delete_wishlist'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    se_delete_wishlist((int) $_POST['delete_wishlist'], $current_user_id);

    header("HX-Trigger: update_wishlists");
    exit;
}

// ----- reorder (SortableJS drag end) -----
if(isset($_POST['reorder_wishlist'])) {

    if(!$logged_in) {
        http_response_code(403);
        exit;
    }

    $ordered_ids = array_map('intval', (array) ($_POST['item_ids'] ?? []));
    se_reorder_wishlist_items((int) $_POST['reorder_wishlist'], $current_user_id, $ordered_ids);
    exit;
}
