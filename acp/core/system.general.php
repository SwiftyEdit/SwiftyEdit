<?php

//prohibit unauthorized access
require 'core/access.php';


echo '<div class="subHeader">'.$icon['gear'].' '.$lang['nav_preferences'].'</div>';

$file = 'general';

if(isset($_REQUEST['file'])) {
    if($_REQUEST['file'] == 'general') {
        $file = 'general';
    }
    if($_REQUEST['file'] == 'general-system') {
        $file = 'general-system';
    }
    if($_REQUEST['file'] == 'general-email') {
        $file = 'general-email';
    }
    if($_REQUEST['file'] == 'general-user') {
        $file = 'general-user';
    }
}


echo '<div class="card">';
echo '<div class="card-header">';
echo '<ul class="nav nav-tabs card-header-tabs">';
echo '<li class="nav-item"><a class="nav-link '.($file == "general" ? 'active' :'').'" href="?tn=system&sub=general&file=general">'.$lang['nav_general'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "general-system" ? 'active' :'').'" href="?tn=system&sub=general&file=general-system">'.$lang['nav_system'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "general-email" ? 'active' :'').'" href="?tn=system&sub=general&file=general-email">E-Mail</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "general-user" ? 'active' :'').'" href="?tn=system&sub=general&file=general-user">'.$lang['nav_user'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '<div class="card-body">';

include 'core/preferences/'.$file.'.php';

echo '</div>';
echo '</div>';

