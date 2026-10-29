<?php

/**
 * top navigation of the ACP, markup in acp/templates/layout/nav-top.twig
 *
 * global variables
 * @var array $active_lang
 * @var object $twig
 */

// uploaded avatar, otherwise an identicon from the mail address
$avatar_src = '/assets/avatars/' . md5($_SESSION['user_nick']) . '.png';
if(!is_file(SE_PUBLIC.$avatar_src)) {
    $avatar_src = se_identicon_data_url($_SESSION['user_mail']);
}

$languages = [];
foreach($active_lang as $v) {
    $languages[] = [
        'sign' => $v['sign'],
        'name' => $v['name'],
        'flag' => $v['flag']
    ];
}

echo $twig->render('layout/nav-top.twig', [
    'server_name' => $_SERVER['SERVER_NAME'],
    'current_lang_flag' => $active_lang[$_SESSION['lang']]['flag'],
    'languages' => $languages,
    'avatar_src' => $avatar_src,
    'user_nick' => $_SESSION['user_nick']
]);
