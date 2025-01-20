<?php

function compare_versions() {

    global $lang;
    global $remote_versions_array;
    global $se_base_url;
    global $icon;
    global $se_environment;

    /**
     * from versions.php
     * @var string $se_version_date fe: 2025-01-20
     * @var string $se_version_title fe: beta 2.0
     * @var string $se_version_build fe: 2500001
     */

    $this_version = __DIR__.'/versions.php';

    if(is_file($this_version)){
        include $this_version;
    } else {
        $se_version_build = '';
    }

    echo '<div class="row">';
    echo '<div class="col-6">';
    /* installed version */
    echo '<div class="card h-100">';
    echo '<div class="card-header">'.$icon['database'].'  '. $se_base_url .'</div>';
    echo '<div class="card-body">';
    echo '<p>Version: '.$se_version_title.' (Build '.$se_version_build.')</p>';
    echo '<p>from: '.$se_version_date.'</p>';
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '<div class="col-6">';
    /* available versions */

    echo '<div class="card h-100">';
    echo '<div class="card-header">'.$icon['server'].'  SwiftyEdit Server</div>';
    echo '<ul class="list-group list-group-flush">';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['stable']['title'] .' ';
    echo 'Build: '.$remote_versions_array['version']['stable']['build'] .' ';
    echo $remote_versions_array['version']['stable']['date'];
    $update_stable = '';
    if($se_version_build < $remote_versions_array['version']['stable']['build']) {
        echo '<button class="btn btn-primary">'.$lang['btn_choose_this_update'].'</button>';
        $update_stable = $lang['update_msg_stable'];
    } else {
        echo '<button class="btn btn-primary" disabled>'.$lang['btn_choose_this_update'].'</button>';
    }
    echo '</li>';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['beta']['title'] .' ';
    echo 'Build: '.$remote_versions_array['version']['beta']['build'] .' ';
    echo $remote_versions_array['version']['beta']['date'];
    $update_beta = '';
    if($se_version_build < $remote_versions_array['version']['beta']['build']) {
        echo '<button class="btn btn-primary">'.$lang['btn_choose_this_update'].'</button>';
        $update_beta = $lang['update_msg_beta'];
    } else {
        echo '<button class="btn btn-primary" disabled>'.$lang['btn_choose_this_update'].'</button>';
    }
    echo '</li>';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['alpha']['title'] .' ';
    echo 'Build: '.$remote_versions_array['version']['alpha']['build'] .' ';
    echo $remote_versions_array['version']['alpha']['date'];
    $update_alpha = '';
    echo '<div class="w-50">';
    if($se_version_build < $remote_versions_array['version']['alpha']['build']) {
        echo '<button class="btn btn-primary btn-sm w-100">'.$lang['btn_choose_this_update'].'</button>';
        $update_alpha = $lang['update_msg_alpha'];
    } else {
        echo '<button class="btn btn-primary btn-sm w-100" disabled>'.$lang['btn_choose_this_update'].'</button>';
    }
    if($se_environment == 'd') {
        echo '<button class="btn btn-default btn-sm w-100">'.$lang['btn_choose_this_update'].' '.$icon['arrow_clockwise'].'</button>';
    }
    echo '</div>';

    echo '</li>';
    echo '</ul>';
    echo '</div>';


    echo '</div>';
    echo '</div>';
    echo '<hr class="shadow-line">';

    if($update_stable == '') {
        echo '<div class="alert alert-success">';
        echo $icon['check_circle'].' '.$lang['update_msg_no_update_available'];
        echo '</div>';
    } else {
        echo '<div class="alert alert-success">';
        echo $icon['info_circle'].' '.$lang['update_msg_update_available'];
        echo '</div>';
    }

    if($update_beta != '') {
        echo '<div class="alert alert-info">';
        echo $icon['info_circle'].' '.$update_beta;
        echo '</div>';
    }
    if($update_alpha != '') {
        echo '<div class="alert alert-danger">';
        echo $icon['info_circle'].' '.$update_alpha;
        echo '</div>';
    }

}

