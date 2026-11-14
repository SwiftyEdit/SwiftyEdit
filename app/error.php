<?php

/**
 * @var int|null $error_code
 * @var object $smarty
 * @var array $se_settings
 * @var string $themes_path
 * @var string $cache_id
 * @var array $page_contents data of the page matched by routing (replaced on 404)
 * @var string $swifty_slug
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

    // smarty.php and template-setup.php already selected the theme and layout
    // of the page matched by routing - the error page has to use its own.
    // Without a 404 page or on use_standard this is the site-wide theme/layout.
    $error_page_theme = se_resolve_page_theme($page_contents, $se_settings, $themes_path);

    $smart_template_dirs = [];
    $smart_template_dirs[] = $themes_path.'/'.$error_page_theme['template'].'/templates/';
    $smart_template_dirs[] = $themes_path.'/default/templates/';
    $smarty->setTemplateDir($smart_template_dirs);

    // a layout file that no longer exists must not end in a fatal error,
    // the default theme always has layout_default.tpl
    $error_page_layout = $error_page_theme['layout'];
    if(!$smarty->templateExists($error_page_layout)) {
        $error_page_layout = 'layout_default.tpl';
    }

    $smarty->assign('se_template_layout', $error_page_layout);
    $smarty->assign('body_template', $error_page_layout);

    // CSS and JS in head.tpl must come from the theme of the error page, too.
    // The stylesheet is always assigned: an empty value has to replace the
    // stylesheet of the page matched by routing.
    $smarty->assign('se_template', $error_page_theme['template']);
    $smarty->assign('se_template_stylesheet', $error_page_theme['stylesheet']);
    $smarty->assign('se_assets_version', se_get_theme_assets_version($themes_path, $error_page_theme['template']));
}

// this file can be include()'d mid-request from inside a handler
// (products.php, products-list.php, ...) that already ran
// template-setup.php for the page that was actually matched by routing -
// title, meta tags, canonical url, breadcrumbs and page_content were
// already assigned to Smarty for THAT page above and need to be redone
// here for the real error page, or the visitor gets a 404 status with the
// previous page's content still on screen
if(($page_contents['page_permalink'] ?? '') != '') {
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

// error pages must never be indexed - keep the error page's own robots
// value only if it already contains noindex (e.g. "noindex, nofollow"),
// otherwise the robots value of the page matched by routing would leak
$page_meta_robots = $page_contents['page_meta_robots'] ?? '';

if(stripos($page_meta_robots, 'noindex') === false) {
    $page_meta_robots = 'noindex';
}

$smarty->assign('page_meta_robots', $page_meta_robots);

$smarty->display('index.tpl',$cache_id);
include_once __DIR__.'/tracker.php';
exit;