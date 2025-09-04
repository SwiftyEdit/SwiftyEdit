<?php

/**
 * show the login form
 * or the user navigation
 *
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 * @var string $se_base_url
 */


if(isset($_SESSION['user_nick']) AND $_SESSION['user_nick'] != "") {

    $link_logout = '/logout';

    $typeof_profile = se_get_type_of_use_pages('profile');

    if($typeof_profile === NULL) {
        $link_profile = SE_INCLUDE_PATH . "/profile/";
    } else {
        $link_profile = SE_INCLUDE_PATH .'/'. $typeof_profile['page_permalink'];
    }
    /* user == administrator */
    $link_acp = '';
    if($_SESSION['user_class'] == "administrator"){
        $link_acp = SE_INCLUDE_PATH . "/" . SE_ACP . "/dashboard/";
    }

    /* get permalink for orders page */
    $orders_page = se_get_type_of_use_pages('orders');
    if($orders_page == NULL OR $orders_page['page_permalink'] == '') {
        $orders_uri = '/orders/';
    } else {
        $orders_uri = '/'.$orders_page['page_permalink'];
    }

    $smarty->assign('orders_uri', $orders_uri);
    $smarty->assign('link_profile', $link_profile);
    $smarty->assign('href_profile', $link_profile);
    $smarty->assign("link_acp","$link_acp");
    $smarty->assign('lang_button_profile', $lang['button_profile']);
    $smarty->assign('lang_button_orders', $lang['button_orders']);
    $smarty->assign("link_logout","$link_logout");
    $smarty->assign('lang_button_logout', $lang['button_logout']);
    $smarty->assign('lang_button_acp', $lang['button_acp']);
    $smarty->assign('lang_button_edit_page', $lang['button_acp_edit_page']);

    $smarty->display('statusbox.tpl');

} else {

    if(!isset($status_msg)) {
        $status_msg = '';
    }

    $login_error = '';
    if(isset($_GET['error']) && $_GET['error'] === 'login_failed') {
        $smarty->assign('login_error', $lang['msg_login_false']);
    }

    if(isset($se_settings['userregistration']) AND $se_settings['userregistration'] == "yes") {
        $href_register = SE_INCLUDE_PATH . "/register/";
        $register_page = se_get_type_of_use_pages('register');
        if ($register_page['page_permalink'] != '') {
            $href_register = $se_base_url . $register_page['page_permalink'];
        }

        $show_register_link = '<a href="' . $href_register . '">' . $lang['link_register'] . '</a>';
        $smarty->assign("show_register_link", "$show_register_link");
    }

    // reset password link
    $href_reset_psw = SE_INCLUDE_PATH . "/password/";
    $reset_psw_page = se_get_type_of_use_pages('password');
    if($reset_psw_page['page_permalink'] != '') {
        $href_reset_psw = $se_base_url . $reset_psw_page['page_permalink'];
    }

    $show_forgotten_psw_link = '<a href="'.$href_reset_psw.'">'.$lang['forgotten_psw'].'</a>';

    $smarty->assign("show_forgotten_psw_link","$show_forgotten_psw_link");
    $smarty->assign("href_reset_psw","$href_reset_psw");
    $smarty->assign("msg_register",$lang['msg_register']);

    if($se_settings['showloginform'] == 'yes') {
        $smarty->assign("legend_login",$lang['legend_login']);
        $smarty->assign("label_login",$lang['label_login']);
        $smarty->assign("label_username",$lang['label_username']);
        $smarty->assign("label_psw",$lang['label_psw']);
        $smarty->assign("button_login",$lang['button_login']);
        $smarty->assign('status_msg', $status_msg);
        $smarty->assign('label_remember_me', $lang['label_remember_me']);

        $smarty->display('loginbox.tpl');
    }
}