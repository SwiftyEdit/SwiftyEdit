<?php

/**
 * SwiftyEdit
 *
 * global variables
 * @var array $lang from language files
 * @var string $languagePack
 * @var string $lang_sign
 * @var array $icon from icons.php
 * @var array $all_mods
 * @var $hidden_csrf_token
 *
 * infos from info.inc.php
 * @var array $mod
 * @var array $modnav
 */

//prohibit unauthorized access
require __DIR__.'/access.php';

$cnt_mods = count($all_mods);

if($cnt_mods > 0) {
    echo '<hr class="shadow-line">';

    echo '<div class="row row-cols-1 row-cols-md-3 row-cols-xl-5">';

    for ($i = 0; $i < $cnt_mods; $i++) {
        $modFolder = $all_mods[$i]['folder'];

        $mod_info_file = SE_CONTENT . "/modules/$modFolder/info.inc.php";

        $poster_img = '';
        if (is_file(SE_CONTENT . "/modules/$modFolder/poster.png")) {
            $poster_img = '<img src="/content/modules/' . $modFolder . '/poster.png" class="card-img-top">';
        } else {
            $poster_img = '<img src="images/poster-addons.png" class="card-img-top">';
        }

        if (is_file("$mod_info_file")) {

            unset($mod, $modnav);
            include $mod_info_file;

            $mod_id = 'id_' . clean_filename($mod['name']);

            echo '<div class="col mb-4">';
            echo '<div class="card" style="max-width:450px;">';
            echo '<div class="card-header p-1"><strong>' . $mod['name'] . '</strong> <span class="badge badge-dark float-end">v' . $mod['version'] . '</span></div>';
            echo $poster_img;
            echo '<div class="card-img-overlay fade-menu">';
            echo '<div class="list-group list-group-flush">';

            foreach ($modnav as $nav) {
                echo '<a class="list-group-item list-group-item-ghost p-1 px-2" href="acp.php?tn=moduls&sub=' . $modFolder . '&a=' . $nav['file'] . '">' . $icon['caret_right'] . ' ' . $nav['link'] . '</a>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

        }


    }
    echo '</div>';

}

if (isset($_POST['send_hook'])) {
    if (is_array($_POST['send_hook'])) {
        se_run_hooks($_POST['send_hook'],$_POST);
    }
}

$dashboard_hooks = se_get_hook('dashboard_listed_all_addons');

if (count($dashboard_hooks) > 0) {

    echo '<div class="card p-3">';
    echo '<form action="acp.php" method="POST">';
    foreach ($dashboard_hooks as $hook) {
        echo $hook;
    }
    echo $hidden_csrf_token;
    echo '<button type="submit" class="btn btn-default mt-3">'.$lang['btn_run_commands'].'</button>';
    echo '</form>';
    echo '</div>';
}