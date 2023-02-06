<?php

//prohibit unauthorized access
require "core/access.php";

/* save upload preferences */
if(isset($_POST['update_shop'])) {

	foreach($_POST as $key => $val) {
		$data[htmlentities($key)] = htmlentities($val);
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

echo '<div class="subHeader">'.$icon['store'].' '.$lang['tn_shop'].'</div>';

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
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-general" ? 'active' :'').'" href="?tn=system&sub=shop&file=general">'.$lang['nav_general'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-payment-shipping" ? 'active' :'').'" href="?tn=system&sub=shop&file=payment-shipping">'.$lang['nav_payment_shipping'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-delivery" ? 'active' :'').'" href="?tn=system&sub=shop&file=shop-delivery">'.$lang['nav_delivery_areas'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.($file == "shop-business-details" ? 'active' :'').'" href="?tn=system&sub=shop&file=shop-business-details">'.$lang['nav_business_details'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '<div class="card-body">';

include 'core/preferences/'.$file.'.php';

echo '</div>';
echo '</div>';