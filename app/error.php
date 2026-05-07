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

if($page_contents['page_permalink'] == '') {
    $smarty->assign('page_title', "404 Page Not Found");
    $output = $smarty->fetch("404.tpl");
    $smarty->assign('page_content', $output);
}

$smarty->display('index.tpl',$cache_id);
include_once __DIR__.'/tracker.php';
exit;