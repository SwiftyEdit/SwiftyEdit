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
echo '<li><a class="dropdown-item" href="/index.php?goto=logout">'.$lang['logout'].'</a></li>';
echo '</ul>';
echo '</div>';


echo '<a class="btn btn-default" href="#" id="toggleSupport">';
echo $icon['question'];
echo '</a>';


echo '<div class="dropstart ms-1">';
echo '            <button class="btn btn-default nav-link py-2 px-0 px-lg-2 d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Toggle theme (dark)">
              <i class="bi theme-icon-active"></i>
              <span class="d-lg-none ms-2" id="bd-theme-text">Toggle theme</span>
            </button>';
echo '



            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text">
              <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                  <i class="bi bi-sun-fill"></i>
                  Light
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="dark" aria-pressed="true">
                  <i class="bi bi-moon-stars-fill"></i>
                  Dark
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false">
                  <i class="bi bi-circle-half"></i>
                  Auto
                </button>
              </li>
            </ul>

';
echo '</div>';

echo '</div>';
echo '</nav>';
