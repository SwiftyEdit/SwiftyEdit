<?php

//prohibit unauthorized access
require "core/access.php";

/* save upload preferences */
if(isset($_POST['update_shop'])) {

	foreach($_POST as $key => $val) {
        if(is_string($key)) {
            $data[htmlentities($key)] = htmlentities((string) $val);
        }
	}

	se_write_option($data,'se');
}

if(isset($_POST['update_pm_shipping'])) {

    foreach($_POST as $key => $val) {
        if(is_string($key)) {
            $data[htmlentities($key)] = htmlentities((string) $val);
        }
    }

    if($_POST['prefs_pm_bank_transfer'] != 1) {
        $data['prefs_pm_bank_transfer'] = 0;
    }
    if($_POST['prefs_pm_invoice'] != 1) {
        $data['prefs_pm_invoice'] = 0;
    }
    if($_POST['prefs_pm_cash'] != 1) {
        $data['prefs_pm_cash'] = 0;
    }

    $data['prefs_payment_addons'] = '';
    if(isset($_POST['payment_addons'])) {
        $addon_str = json_encode($_POST['payment_addons'],JSON_FORCE_OBJECT);
        $data['prefs_payment_addons'] = $addon_str;
    }


    se_write_option($data,'se');
}

if(isset($_POST)) {
	/* read the preferences again */
	$se_get_preferences = se_get_preferences();
	
	foreach($se_get_preferences as $k => $v) {
		$key = $se_get_preferences[$k]['option_key'];
		$value = $se_get_preferences[$k]['option_value'];
		$se_prefs[$key] = $value;
	}
	
	foreach($se_prefs as $k => $v) {
	   $$k = stripslashes($v);
	}
}

echo '<div class="subHeader">'.$icon['store'].' '.$lang['nav_btn_settings'].' / '.$lang['nav_btn_shop'].'</div>';

$file = 'shop-general';

if(isset($_REQUEST['file'])) {
    if($_REQUEST['file'] == 'general') {
        $file = 'shop-general';
    }
    if($_REQUEST['file'] == 'payment-shipping') {
        $file = 'shop-payment-shipping';
    }
    if($_REQUEST['file'] == 'shop-delivery') {
        $file = 'shop-delivery';
    }
    if($_REQUEST['file'] == 'shop-business-details') {
        $file = 'shop-business-details';
    }
}


echo '<div class="card">';
echo '<div class="card-header">';
echo '<ul class="nav nav-tabs card-header-tabs">';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-general" ? 'active' :'').'" href="?tn=system&sub=shop&file=general">'.$lang['nav_btn_general'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-payment-shipping" ? 'active' :'').'" href="?tn=system&sub=shop&file=payment-shipping">'.$lang['nav_btn_payment_shipping'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-delivery" ? 'active' :'').'" href="?tn=system&sub=shop&file=shop-delivery">'.$lang['nav_btn_delivery_areas'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-business-details" ? 'active' :'').'" href="?tn=system&sub=shop&file=shop-business-details">'.$lang['nav_btn_business_details'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '<div class="card-body">';

include 'core/preferences/'.$file.'.php';

echo '</div>';
echo '</div>';