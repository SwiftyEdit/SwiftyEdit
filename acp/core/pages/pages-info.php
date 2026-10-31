<?php

/**
 * info modal of a page in /admin/pages/, included by the data-reader (?page_info=id),
 * markup in acp/templates/pages/info-modal.twig
 *
 * global variables
 * @var object $db_content
 * @var object $twig
 */

$get_page_id = (int) $_GET['page_info'];
$page_data = $db_content->get("se_pages", "*", [
    "page_id" => $get_page_id
]);

if(!is_array($page_data)) {
    exit;
}

// most text fields are stored html-encoded (se_sanitize_page_inputs), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$thumb_src = '';
if($page_data['page_thumbnail'] != '') {
    $thumbs = explode('<->', $decode($page_data['page_thumbnail']));
    $thumb_src = str_replace('../content/images/', '/images/', $thumbs[0]);
}

$funnels = [];
if($page_data['page_funnel_uri'] != '') {
    $funnels = array_map($decode, explode(',', $page_data['page_funnel_uri']));
}

echo $twig->render('pages/info-modal.twig', [
    'page' => [
        'id' => (int) $page_data['page_id'],
        'hash' => $decode($page_data['page_hash']),
        'classes' => $decode($page_data['page_classes']),
        'hits' => (int) $page_data['page_hits'],
        'title' => $decode($page_data['page_title']),
        'description' => $decode($page_data['page_meta_description']),
        'keywords' => $decode($page_data['page_meta_keywords']),
        'robots' => $decode($page_data['page_meta_robots']),
        'status' => $decode($page_data['page_status']),
        'linkname' => $decode($page_data['page_linkname']),
        'permalink' => $decode($page_data['page_permalink']),
        'permalink_short' => $decode($page_data['page_permalink_short']),
        'short_link_hits' => (int) $page_data['page_permalink_short_cnt'],
        'redirect' => $decode($page_data['page_redirect']),
        'redirect_code' => (int) $page_data['page_redirect_code'],
        'funnels' => $funnels,
        // page_content is stored as html, the excerpt shows the source
        'content' => first_words((string) $page_data['page_content'], 50),
        'thumb_src' => $thumb_src
    ],
    'can_edit' => se_hasPermission('drm_acp_editpages')
]);
