<?php

//prohibit unauthorized access
require 'core/access.php';


echo '<form action="?tn=system&sub=shop&file=payment-shipping" method="POST">';


echo '<h5 class="heading-line">'.$lang['label_shipping'].'</h5>';


if($prefs_shipping_costs_mode == 1 OR $prefs_shipping_costs_mode == '') {
    $sel_shipping_costs_mode1 = 'selected';
} else if($prefs_shipping_costs_mode == 2) {
    $sel_shipping_costs_mode2 = 'selected';
}

echo '<div class="form-group">';
echo '<label>' . $lang['label_shipping_mode'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_shipping_costs_mode">';
echo '<option value="1" '.$sel_shipping_costs_mode1.'>'.$lang['label_shipping_mode_flat'].'</option>';
echo '<option value="2" '.$sel_shipping_costs_mode2.'>'.$lang['label_shipping_mode_cats'].'</option>';
echo '</select>';
echo '</div>';

echo '<div class="form-group">
				<label>' . $lang['label_shipping_costs_flat'] . '</label>
				<input type="text" class="form-control" name="prefs_shipping_costs_flat" value="'.$prefs_shipping_costs_flat.'">
			</div>';

echo '<div class="row">';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_shipping_costs_cat1'] . '</label>
				<input type="text" class="form-control" name="prefs_shipping_costs_cat1" value="'.$prefs_shipping_costs_cat1.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_shipping_costs_cat2'] . '</label>
				<input type="text" class="form-control" name="prefs_shipping_costs_cat2" value="'.$prefs_shipping_costs_cat2.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_shipping_costs_cat3'] . '</label>
				<input type="text" class="form-control" name="prefs_shipping_costs_cat3" value="'.$prefs_shipping_costs_cat3.'">
			</div>';
echo '</div>';
echo '</div>';


echo '<h5 class="heading-line">'.$lang['label_payment_methods'].'</h5>';

echo '<table class="table">';
echo '<tr>';
echo '<td>'.$lang['label_status'].'</td>';
echo '<td>'.$lang['label_type'].'</td>';
echo '<td>'.$lang['label_description'].'</td>';
echo '</tr>';

// list payment addons
$get_payment_addons = se_get_payment_addons();
$cnt_payment_addons = count($get_payment_addons);

// get stored payment addons from $prefs_payment_addons (json)
$active_payment_addons = json_decode($prefs_payment_addons,true);
if(!is_array($active_payment_addons)) {
    $active_payment_addons = array();
}

if($cnt_payment_addons > 0) {

    foreach ($get_payment_addons as $payment_addon) {
        echo '<tr>';

        $addon_dir = SE_CONTENT . '/modules/' . $payment_addon;
        $addon_info_file = $addon_dir . '/info.inc.php';
        $mod = array();
        if (is_file("$addon_info_file")) {
            include $addon_info_file;
        }

        $addon_link = '?tn=addons&sub='.$payment_addon.'&a=start';
        $addon_id = basename($payment_addon,".pay");

        $check = '';
        if(in_array("$payment_addon",$active_payment_addons)) {
            $check = 'checked';
        }

        echo '<td>';
        echo '<input class="form-check-input" type="checkbox" name="payment_addons[]" value="'.$payment_addon.'" id="payment_'.$addon_id.'" '.$check.'>';
        echo '</td>';
        echo '<td><a href="'.$addon_link.'">' . $addon_id . '</a></td>';
        echo '<td>' . $mod['description'] . '</td>';
        echo '</tr>';
    }

}

echo '</table>';

echo '<input type="submit" class="btn btn-success" name="update_pm_shipping" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';