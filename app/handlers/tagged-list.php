<?php

/**
 * global variables
 * @var object $db_content see database.php
 * @var object $smarty
 * @var array $lang
 * @var array $page_contents
 * @var string $swifty_slug
 * @var string $cache_id
 * @var string $tag_name_clean set in tagged.php
 */

$tag = null;
if ($tag_name_clean !== '') {
    foreach (se_get_tags() as $t) {
        if ($t['tag_name_clean'] === $tag_name_clean) {
            $tag = $t;
            break;
        }
    }
}

/* bare /tags/ (no slug in the URL) - show an index of every tag instead of
   one tag's content, each linking to this same page + its own slug.
   Most-used first (a tag cloud reads by weight, not alphabet); same-usage
   tags fall back to alphabetical so the order stays stable. */
$tag_index = [];
if ($tag_name_clean === '') {
    foreach (se_get_tags_with_usage() as $t) {
        if ($t['tag_usage'] < 1) {
            continue;
        }
        $tag_index[] = [
            "tag_href" => '/' . $swifty_slug . $t['tag_name_clean'] . '/',
            "tag_title" => $t['tag_name'],
            "tag_usage" => $t['tag_usage']
        ];
    }
    usort($tag_index, function ($a, $b) {
        return $b['tag_usage'] <=> $a['tag_usage'] ?: $a['tag_title'] <=> $b['tag_title'];
    });

    // tag_weight: usage normalized to a 1-5 scale (classic tag-cloud
    // convention), so themes can key font-size/emphasis off a small class
    // like "tag-weight-{n}" instead of having to normalize the raw count
    // (which has an arbitrary, site-specific range) themselves.
    $usage_values = array_column($tag_index, 'tag_usage');
    $min_usage = min($usage_values);
    $max_usage = max($usage_values);
    $weight_levels = 5;

    foreach ($tag_index as $k => $item) {
        if ($max_usage > $min_usage) {
            $ratio = ($item['tag_usage'] - $min_usage) / ($max_usage - $min_usage);
        } else {
            // every tag has the same usage count - give them all top weight
            $ratio = 1;
        }
        $tag_index[$k]['tag_weight'] = max(1, (int) ceil($ratio * $weight_levels));
    }
}

$tagged_content = [
    'pages' => [],
    'posts' => [],
    'products' => [],
    'events' => []
];

if ($tag !== null) {
    $tagged_content = se_get_content_by_tag($tag_name_clean, $page_contents['page_language']);
}

/* pages already carry their own permalink */
foreach ($tagged_content['pages'] as $k => $page) {
    $tagged_content['pages'][$k]['page_href'] = '/' . $page['page_permalink'];
}

/* posts, products and events are shown on their own dedicated listing page -
   build hrefs the same way posts-list.php/products-list.php/events-list.php
   do. Unlike those files, there's no sensible "current page" fallback here
   (we're on the tag archive page, not the blog/shop/events page), so if no
   listing page can be found for a language, fall back to root-relative
   instead of nesting under /tags/. */
$posts_target_page = se_get_type_of_use_page_permalink('display_post', $page_contents['page_language']) ?? '';
foreach ($tagged_content['posts'] as $k => $post) {
    $post_filename = basename($post['post_slug']);
    $tagged_content['posts'][$k]['post_href'] = SE_INCLUDE_PATH . "/" . $posts_target_page . "$post_filename-" . $post['post_id'] . ".html";
}

$products_target_page = se_get_type_of_use_page_permalink('display_product', $page_contents['page_language']) ?? '';
foreach ($tagged_content['products'] as $k => $product) {
    $post_filename = basename($product['slug']);
    $product_href = SE_INCLUDE_PATH . "/" . $products_target_page . "$post_filename-" . $product['id'] . ".html";
    if ($product['slug'] != '') {
        $product_href = SE_INCLUDE_PATH . "/" . $products_target_page . $product['slug'];
    }
    $tagged_content['products'][$k]['product_href'] = $product_href;
}

$events_target_page = se_get_type_of_use_page_permalink('display_event', $page_contents['page_language']) ?? '';
foreach ($tagged_content['events'] as $k => $event) {
    $post_filename = basename($event['slug']);
    $tagged_content['events'][$k]['event_href'] = SE_INCLUDE_PATH . "/" . $events_target_page . "$post_filename-" . $event['id'] . ".html";
}

/* display dates, formatted like the type-specific list pages already do */
foreach ($tagged_content['pages'] as $k => $page) {
    $tagged_content['pages'][$k]['tagged_date'] = $page['page_lastedit'] != '' ? date($se_settings['dateformat'], $page['page_lastedit']) : '';
}
foreach ($tagged_content['posts'] as $k => $post) {
    $tagged_content['posts'][$k]['tagged_date'] = date($se_settings['dateformat'], $post['post_releasedate']);
}
foreach ($tagged_content['products'] as $k => $product) {
    $tagged_content['products'][$k]['tagged_date'] = date($se_settings['dateformat'], $product['releasedate']);
}
foreach ($tagged_content['events'] as $k => $event) {
    $tagged_content['events'][$k]['tagged_date'] = date($se_settings['dateformat'], $event['event_startdate']);
}

$smarty->assign('tag_slug', $tag_name_clean);
$smarty->assign('tag_name', $tag['tag_name'] ?? $tag_name_clean);
$smarty->assign('tag_index', $tag_index);
$smarty->assign('tagged_pages', $tagged_content['pages']);
$smarty->assign('tagged_posts', $tagged_content['posts']);
$smarty->assign('tagged_products', $tagged_content['products']);
$smarty->assign('tagged_events', $tagged_content['events']);

if ($tag !== null) {
    $smarty->assign('page_title', $tag['tag_name']);
}

$tagged_page_content = $smarty->fetch("tagged-list.tpl", $cache_id);
$smarty->assign('page_content', $tagged_page_content, true);
