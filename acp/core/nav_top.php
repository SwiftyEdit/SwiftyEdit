<?php

//prohibit unauthorized access
require __DIR__.'/access.php';

echo '<nav class="navbar navbar-custom">';
echo '<div class="container-fluid pe-4">';
echo '<a href="#" id="toggleNav" class="me-auto" title="Dashboard"><span class="caret_left">'.$icon['caret_left'].'</span> <span class="caret_right">'.$icon['caret_right'].'</span></a>';


/**
 * Filter modal
 */

echo '<button id="globalFilter" class="btn btn-default me-1" data-bs-toggle="offcanvas" data-bs-target="#globalFilter">';
echo 'Filter ';
echo '<span class="badge bg-secondary">'.$cnt_global_filters.'</span>';
echo '</button>';


/**
 * choose language
 */

$lang_key = array_search($_SESSION['lang'],$all_langs);
$selected_lang_flag = '<img src="../core/lang/'.$_SESSION['lang'].'/flag.png" style="vertical-align: baseline; width:18px; height:auto;">';

echo '<div class="dropstart me-1">';

echo '<a class="btn btn-default" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">'.$selected_lang_flag.'</a>';

echo '<ul class="dropdown-menu">';
foreach($active_lang as $k => $v) {
    $lang_icon = '<img src="' . $v['flag'] . '" style="vertical-align: baseline; width:18px; height:auto;">';
    echo '<li><a class="dropdown-item" href="acp.php?set_lang=' . $v['sign'] . '">' . $lang_icon . ' ' . $v['name'] . '</a></li>';
}
echo '</ul>';

echo '</div>';

/**
 * user menu
 */

$user_avatar = '<img src="images/avatar.png" class="rounded-circle avatar" width="22" height="22">';
$my_avatar_path = '../content/avatars/' . md5($_SESSION['user_nick']) . '.png';
if(is_file("$my_avatar_path")) {
    $user_avatar = '<img src="'.$my_avatar_path.'" class="rounded-circle border img-responsive align-top me-1" width="22" height="22">';
}

echo '<div class="dropstart me-1">';
echo '<a class="btn btn-default" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">'.$user_avatar.$_SESSION['user_nick'].'</a>';
echo '<ul class="dropdown-menu">';
echo '<li><a class="dropdown-item" href="/profile/">Profil</a></li>';
echo '<li><a class="dropdown-item" href="/index.php?goto=logout">'.$lang['btn_logout'].'</a></li>';
echo '</ul>';
echo '</div>';


echo '<a class="btn btn-default" href="#" id="toggleSupport">';
echo $icon['question'];
echo '</a>';

echo '<button class="btn btn-default ms-1" type="button" onclick="toggleTheme()">
<span id="toggle-dark"><i class="bi bi-moon"></i></span>
<span id="toggle-light"><i class="bi bi-sun"></i></span>
</button>';

echo '</div>';
echo '</nav>';
