<?php

/**
 * show support offcanvas
 * check if is installed/activated support.mod
 * if not, show open source docs
 */
//error_reporting(E_ALL ^E_NOTICE);
$support_addon = SE_CONTENT.'/modules/support.mod';
$show_open_source_docs = true;

if(is_dir($support_addon)) {
    /* support.mod is installed */
    $show_open_source_docs = false;
    $get_se_addons = se_get_addons($t='module');

    if(is_array($get_se_addons)) {
        $search_key = array_column($get_se_addons, 'addon_dir');
        $found_key = array_search('support.mod', $search_key);

        if ($found_key !== false) {
            include $support_addon . '/index.php';
        } else {
            /* support.mod is not activated */
            echo '<div class="alert alert-info">';
            echo $lang['msg_support_addon_not_activated'];
            echo '</div>';
            $show_open_source_docs = true;
        }
    }
}


if($show_open_source_docs == true) {
    echo '<div class="card">';
    echo '<div class="card-body">';

    /* get the support contents from swiftyedit.net */

    echo $lang['msg_community_edition'];

    echo '<ul>';
    echo '<li>SwiftyEdit.com</li>';
    echo '<li>GitHub.com</li>';
    echo '</ul>';

    echo '</div>';
    echo '</div>';
}
