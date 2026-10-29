<?php

/**
 * global filter offcanvas, markup in acp/templates/layout/nav-top-filter.twig
 * the filters are stored as json in the session (see acp/core/widgets)
 *
 * global variables
 * @var array $lang_codes
 * @var object $twig
 */

foreach(['global_filter_label', 'global_filter_languages', 'global_filter_status'] as $filter) {
    if(!isset($_SESSION[$filter])) {
        $_SESSION[$filter] = '';
    }
}

$decode_filter = static fn(string $json): array => (array) (json_decode($json, true) ?? []);

echo $twig->render('layout/nav-top-filter.twig', [
    'filter_status' => $decode_filter($_SESSION['global_filter_status']),
    'filter_languages' => $decode_filter($_SESSION['global_filter_languages']),
    'labels' => se_get_label_checklist($decode_filter($_SESSION['global_filter_label'])),
    'lang_codes' => $lang_codes
]);
