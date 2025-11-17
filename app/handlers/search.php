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


    $prodLimit = 5;
    $currentPage = 1;

    if(isset($_REQUEST['next_page'])) {
        $currentPage = (int) $_REQUEST['next_page'];
    }
    if(isset($_REQUEST['prev_page'])) {
        $currentPage = (int) $_REQUEST['prev_page'];
    }

    if($currentPage < 1) {
        $currentPage = 1;
    }

    $nextPage = $currentPage + 1;
    $prevPage = $currentPage - 1;

    $get_pages = se_search_pages("$s", "$languagePack");
    $get_products = se_search_products("$s","$languagePack",$currentPage,$prodLimit);

    $cnt_pages = ceil($get_products['totalResults'] / $prodLimit);
    if($currentPage >= $cnt_pages) {
        $currentPage = $cnt_pages;
        $nextPage = $currentPage;
    }

    if($get_products['totalResults'] > $prodLimit) {
        // show products pagination
        $smarty->assign('show_prod_pagination', "true");
        $smarty->assign('next_page_nbr', "$nextPage");
        $smarty->assign('prev_page_nbr', "$prevPage");
    }

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
        $products[$x]['title'] = $product['title'];
        $products[$x]['description'] = $product['meta_description'];
        $products[$x]['number'] = $product['product_number'];
        $products[$x]['href'] = '/'.$url;
        $products[$x]['url'] = $url;
        $x++;
    }
    $smarty->assign('products', $products, true);

    $smarty->assign('pages_total', $get_pages['totalResults'], true);
    $smarty->assign('products_total', $get_products['totalResults'], true);

}

$smarty->assign('search_string', $s, true);
$search_tpl = $smarty->fetch("search.tpl");
$output = $smarty->fetch("searchresults.tpl");
$smarty->assign('page_content', "$search_tpl $output", true);