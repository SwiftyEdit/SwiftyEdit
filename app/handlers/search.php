<?php

/**
 * global variables
 * @var string $languagePack
 * @var array $lang
 * @var array $se_prefs
 * @var object $smarty
 */

$start_search = "true";

$s = sanitizeUserInputs($_REQUEST['s']);

if($s != '' && strlen($s) < 3) {
    $start_search = "false";
    $search_msg = $lang['msg_search_undersized'];
}

$msg_no_search_results = se_get_snippet('no_search_results',$languagePack,'content');
if($msg_no_search_results == '') {
    $msg_no_search_results = $lang['msg_search_no_results'];
}

if($s != '' && $start_search == "true") {


    // Every section (pages/products/posts/events) pages independently -
    // "?pages_page=2&products_page=1&..." - so paging through one doesn't
    // reset the others. Each section's Prev/Next buttons (see
    // searchresults.tpl) submit their own "{section}_page" and carry the
    // other three sections' current page along as hidden fields.
    $pagesLimit = 10;
    $resultsLimit = 5;

    $pagesPage = max(1, (int) ($_REQUEST['pages_page'] ?? 1));
    $productsPage = max(1, (int) ($_REQUEST['products_page'] ?? 1));
    $postsPage = max(1, (int) ($_REQUEST['posts_page'] ?? 1));
    $eventsPage = max(1, (int) ($_REQUEST['events_page'] ?? 1));

    $get_pages = se_search_pages("$s", "$languagePack", $pagesPage, $pagesLimit);
    $get_products = se_search_products("$s", "$languagePack", $productsPage, $resultsLimit);
    $get_posts = se_search_posts("$s", "$languagePack", $postsPage, $resultsLimit);
    $get_events = se_search_events("$s", "$languagePack", $eventsPage, $resultsLimit);

    // assigns "{prefix}_page" (clamped to the last actual page),
    // "show_{prefix}_pagination", and "{prefix}_prev_page"/"{prefix}_next_page"
    $assign_section_pagination = function (string $prefix, int $currentPage, int $totalResults, int $itemsPerPage) use ($smarty) {
        $lastPage = max(1, (int) ceil($totalResults / $itemsPerPage));
        $currentPage = min($currentPage, $lastPage);

        $smarty->assign("{$prefix}_page", $currentPage, true);
        $smarty->assign("show_{$prefix}_pagination", $totalResults > $itemsPerPage, true);
        $smarty->assign("{$prefix}_prev_page", max(1, $currentPage - 1), true);
        $smarty->assign("{$prefix}_next_page", min($lastPage, $currentPage + 1), true);
    };

    $assign_section_pagination('pages', $pagesPage, $get_pages['totalResults'], $pagesLimit);
    $assign_section_pagination('products', $productsPage, $get_products['totalResults'], $resultsLimit);
    $assign_section_pagination('posts', $postsPage, $get_posts['totalResults'], $resultsLimit);
    $assign_section_pagination('events', $eventsPage, $get_events['totalResults'], $resultsLimit);

    $x = 0;
    foreach($get_pages['pages'] as $page) {

        $thumbs = [];
        $thumbs = explode('&lt;-&gt;',$page['page_thumbnail']);
        if($thumbs[0] != '') {
            $pages[$x]['thumbnail_src'] = $thumbs[0];
        }

        $pages[$x]['title'] = $page['page_title'];
        $pages[$x]['description'] = $page['page_meta_description'];
        $pages[$x]['href'] = '/'.$page['page_permalink'];
        $pages[$x]['url'] = $page['page_permalink'];
        $x++;
    }
    $smarty->assign('pages', $pages, true);

    $x=0;

    foreach($get_products['products'] as $product) {

        $url = $product['main_catalog_slug'].$product['slug'];

        $thumbs = [];
        $thumbs = explode('<->',$product['images']);
        if($thumbs[1] != '') {
            $products[$x]['thumbnail_src'] = $thumbs[1];
        }

        $products[$x]['id'] = $product['id'];
        $products[$x]['number'] = $product['product_number'];
        $products[$x]['href'] = '/'.$url;
        $products[$x]['url'] = $url;
        $products[$x]['description'] = $product['meta_description'];
        $products[$x]['title'] = $product['title'];
        $products[$x]['meta_title'] = $product['meta_title'];
        $products[$x]['product_currency'] = $product['product_currency'];
        $products[$x] = array_merge($products[$x], se_get_product_price_tag($product));

        $x++;
    }
    $smarty->assign('products', $products, true);

    // posts (blog) and events - prefer the stored canonical url (auto-built
    // at save time from the post's/event's own detail page + slug, see
    // acp/core/blog/data-writer.php / acp/core/events/data-writer.php -
    // respects a per-event main_category_slug override, which the fallback
    // below can't). Older rows saved before that feature existed can still
    // have an empty canonical url (no backfill migration for posts/events
    // yet, unlike products' install/migrations/..._product_canonical_url_
    // backfill.php) - fall back to the old {display page permalink}
    // + {slug}-{id}.html construction for those.
    $posts_target_page = se_get_type_of_use_pages('display_post');
    $events_target_page = se_get_type_of_use_pages('display_event');

    $x = 0;
    foreach ($get_posts['posts'] as $post) {

        $thumbs = explode('<->', $post['post_images']);
        if (($thumbs[1] ?? '') != '') {
            $posts[$x]['thumbnail_src'] = $thumbs[1];
        }

        $posts[$x]['title'] = $post['post_title'];
        $posts[$x]['description'] = $post['post_meta_description'] ?: $post['post_teaser'];
        $posts[$x]['href'] = $post['post_canonical_url'] ?: ('/' . $posts_target_page['page_permalink'] . basename($post['post_slug']) . '-' . $post['post_id'] . '.html');
        $x++;
    }
    $smarty->assign('posts', $posts, true);

    $x = 0;
    foreach ($get_events['events'] as $event) {

        $thumbs = explode('<->', $event['images']);
        if (($thumbs[1] ?? '') != '') {
            $events[$x]['thumbnail_src'] = $thumbs[1];
        }

        $events[$x]['title'] = $event['title'];
        $events[$x]['description'] = $event['meta_description'] ?: $event['teaser'];
        $events[$x]['href'] = $event['canonical_url'] ?: ('/' . $events_target_page['page_permalink'] . basename($event['slug']) . '-' . $event['id'] . '.html');
        $x++;
    }
    $smarty->assign('events', $events, true);

    $smarty->assign('pages_total', $get_pages['totalResults'], true);
    $smarty->assign('products_total', $get_products['totalResults'], true);
    $smarty->assign('posts_total', $get_posts['totalResults'], true);
    $smarty->assign('events_total', $get_events['totalResults'], true);

    // only set once an actual search ran (not on the bare/undersized-query
    // page load) - searchresults.tpl uses this to show $msg_no_search_results
    // instead of four empty "(0)" cards.
    $smarty->assign('msg_no_search_results', $msg_no_search_results, true);
    $smarty->assign('no_results_at_all', (
        $get_pages['totalResults'] == 0 &&
        $get_products['totalResults'] == 0 &&
        $get_posts['totalResults'] == 0 &&
        $get_events['totalResults'] == 0
    ), true);

}

$smarty->assign('search_string', $s, true);
$search_tpl = $smarty->fetch("search.tpl");
$output = $smarty->fetch("searchresults.tpl");
$smarty->assign('page_content', "$search_tpl $output", true);