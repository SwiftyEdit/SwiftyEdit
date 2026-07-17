<?php
/**
 * Wishlist Handler
 * SwiftyEdit CMS
 *
 * Two cases on the same page permalink:
 * - /wishlist/              -> authenticated "my wishlists" overview (own lists)
 * - /wishlist/{uuid-slug}/  -> public read-only view of one list (if is_public)
 *
 * @var string $mod_slug from routing.php
 * @var array $se_settings
 * @var array $lang
 */

if(($se_settings['wishlist_enabled'] ?? 0) != 1) {
    $error_code = 404;
    include __DIR__.'/../error.php';
    exit;
}

/* get permalink for the wishlist page (for cross-links from other templates) */
$wishlist_page = se_get_type_of_use_pages('wishlist');
if($wishlist_page['page_permalink'] == '') {
    $wishlist_page_uri = '/wishlist/';
} else {
    $wishlist_page_uri = '/'.$wishlist_page['page_permalink'];
}
$smarty->assign('wishlist_page_uri', $wishlist_page_uri);

$wishlist_slug = trim((string) $mod_slug, '/');

if($wishlist_slug !== '') {

    // public view by slug
    $public_list = se_get_wishlist_by_slug(se_getLastSlug($wishlist_slug));

    if(!is_array($public_list) || (int) $public_list['is_public'] !== 1) {
        $error_code = 404;
        include __DIR__.'/../error.php';
        exit;
    }

    // "add to cart" from the public view reuses the normal cart logic
    if(isset($_POST['add_to_cart'])) {
        se_add_to_cart();
    }

    $smarty->assign('wishlist', $public_list, true);
    $smarty->assign('wishlist_items', se_get_wishlist_items((int) $public_list['id']), true);
    $smarty->assign('wishlist_is_owner', false, true);
    $smarty->assign('form_action', $wishlist_page_uri . $public_list['slug'] . '/', true);

    $output = $smarty->fetch("wishlist_public.tpl", $cache_id);
    $smarty->assign('page_content', $output, true);
    return;
}

// "my wishlists" - requires login, same gate as profile.php
if(($_SESSION['user_nick'] ?? '') == "") {
    $text = se_get_snippet("no_access", $languagePack, 'all');
    $smarty->assign('page_content', $text, true);
    return;
}

$current_user_id = (int) $_SESSION['user_id'];

$smarty->assign('wishlists', se_get_user_wishlists($current_user_id), true);
$smarty->assign('wishlist_is_owner', true, true);

$output = $smarty->fetch("wishlist_overview.tpl", $cache_id);
$smarty->assign('page_content', $output, true);
