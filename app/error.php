<?php

/**
 * @var int|null $error_code
 * @var object $smarty
 * @var array $se_settings
 * @var string $themes_path
 * @var string $cache_id
 */

http_response_code($error_code);

$error_messages = [
    403 => ['title' => 'Zugriff verweigert', 'text' => 'Du hast keine Berechtigung...'],
    404 => ['title' => 'Seite nicht gefunden', 'text' => 'Die Seite existiert nicht...'],
    500 => ['title' => 'Serverfehler', 'text' => 'Es ist ein Fehler aufgetreten...'],
];

$error = $error_messages[$error_code] ?? ['title' => 'Fehler', 'text' => 'Unbekannter Fehler.'];

if($error_code == 404) {
    list($page_contents,$se_nav) = se_get_content('404','type_of_use');
}


if($page_contents['page_template'] != 'use_standard') {
    $smart_template_dirs = [];
    $smart_template_dirs[] = $themes_path.'/'.$page_contents['page_template'].'/templates/';
    $smart_template_dirs[] = $themes_path.'/default/templates/';
    $smarty->setTemplateDir($smart_template_dirs);
}

// this file can be include()'d mid-request from inside a handler
// (products.php, products-list.php, ...) that already ran
// template-setup.php for the page that was actually matched by routing -
// title, meta tags, canonical url, breadcrumbs and page_content were
// already assigned to Smarty for THAT page above and need to be redone
// here for the real error page, or the visitor gets a 404 status with the
// previous page's content still on screen
if($page_contents['page_permalink'] != '') {
    $smarty->assign('page_title', html_entity_decode($page_contents['page_title'] ?: $error['title']));
    $smarty->assign('page_meta_description', html_entity_decode($page_contents['page_meta_description'] ?? ''));
    $smarty->assign('page_meta_keywords', html_entity_decode($page_contents['page_meta_keywords'] ?? ''));
    $smarty->assign('page_canonical_url', $page_contents['page_canonical_url'] ?? '');
    $smarty->assign('arr_bcmenue', null);

    $error_content_editor = se_decode_editor_content($page_contents['page_content'] ?? '');
    if ($error_content_editor !== null) {
        $smarty->assign('page_content', se_render_editor_content_frontend($error_content_editor['editor'], $error_content_editor['content']), true);
    } else {
        $smarty->assign('page_content', text_parser(stripslashes($page_contents['page_content'] ?? '')), true);
    }

    $error_content_tags = array();
    foreach (se_get_content_tags('page', (int) $page_contents['page_id']) as $tag) {
        $tag_href = se_get_tagged_page_url($tag['tag_name_clean'], $page_contents['page_language'])
            ?? ('/' . $swifty_slug . '?tag=' . urlencode($tag['tag_name_clean']));
        $error_content_tags[] = array(
            "tag_href" => $tag_href,
            "tag_title" => $tag['tag_name']
        );
    }
    $smarty->assign('content_tags', $error_content_tags);
} else {
    $smarty->assign('page_title', "404 Page Not Found");
    $output = $smarty->fetch("404.tpl");
    $smarty->assign('page_content', $output);
    $smarty->assign('content_tags', array());
}

$smarty->display('index.tpl',$cache_id);
include_once __DIR__.'/tracker.php';
exit;