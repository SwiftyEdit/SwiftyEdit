<?php

//prohibit unauthorized access
require 'core/access.php';


$sel_carts1 = '';
$sel_carts2 = '';
$sel_carts3 = '';

if($prefs_posts_products_cart == 1 OR $prefs_posts_products_cart == '') {
    $sel_carts1 = 'selected';
} else if($prefs_posts_products_cart == 2) {
    $sel_carts2 = 'selected';
} else if($prefs_posts_products_cart == 3) {
    $sel_carts3 = 'selected';
}

$sel_order_mode1 = '';
$sel_order_mode2 = '';
$sel_order_mode3 = '';

if($prefs_posts_order_mode == 1 OR $prefs_posts_order_mode == '') {
    $sel_order_mode1 = 'selected';
} else if($prefs_posts_order_mode == 2) {
    $sel_order_mode2 = 'selected';
} else if($prefs_posts_order_mode == 3) {
    $sel_order_mode3 = 'selected';
}

echo '<form action="?tn=system&sub=shop&file=general" method="POST">';

echo '<h3>' . $lang['label_carts'] . '</h3>';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_carts'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_products_cart">';
echo '<option value="1" '.$sel_carts1.'>'.$lang['carts_deactivated'].'</option>';
echo '<option value="2" '.$sel_carts2.'>'.$lang['carts_for_registered'].'</option>';
echo '<option value="3" '.$sel_carts3.'>'.$lang['carts_for_all'].'</option>';
echo '</select>';
echo '</div>';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_orders'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_order_mode">';
echo '<option value="1" '.$sel_order_mode1.'>'.$lang['order_mode_active'].'</option>';
echo '<option value="2" '.$sel_order_mode2.'>'.$lang['order_mode_request'].'</option>';
echo '<option value="3" '.$sel_order_mode3.'>'.$lang['order_mode_both'].'</option>';
echo '</select>';
echo '</div>';

echo '<hr><h3>Taxes and Currency</h3>';


echo '<div class="row">';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['products_default_tax'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_default_tax" value="'.$prefs_posts_products_default_tax.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_product_tax_alt1'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_tax_alt1" value="'.$prefs_posts_products_tax_alt1.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_product_tax_alt2'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_tax_alt2" value="'.$prefs_posts_products_tax_alt2.'">
			</div>';
echo '</div>';
echo '</div>';

$sel_price_mode1 = '';
$sel_price_mode2 = '';
$sel_price_mode3 = '';

if($prefs_posts_price_mode == 1 OR $prefs_posts_price_mode == '') {
    $sel_price_mode1 = 'selected';
} else if($prefs_posts_price_mode == 2) {
    $sel_price_mode2 = 'selected';
} else if($prefs_posts_price_mode == 3) {
    $sel_price_mode3 = 'selected';
}

echo '<div class="row">';
echo '<div class="col">';

echo '<div class="form-group">
				<label>' . $lang['products_default_currency'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_default_currency" value="'.$prefs_posts_products_default_currency.'">
			</div>';

echo '</div>';
echo '<div class="col">';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_product_price'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_price_mode">';
echo '<option value="1" '.$sel_price_mode1.'>'.$lang['show_price_in_gross'].'</option>';
echo '<option value="2" '.$sel_price_mode2.'>'.$lang['show_price_both'].'</option>';
echo '<option value="3" '.$sel_price_mode3.'>'.$lang['show_price_in_net'].'</option>';
echo '</select>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '<hr><h3>'.$lang['label_images'].'</h3>';

echo '<div class="form-group">';
echo '<label>'.$lang['label_images_prefix'].'</label>
			<input type="text" class="form-control" name="prefs_shop_images_prefix" value="'.$prefs_shop_images_prefix.'">
			</div>';
$all_images = se_get_all_images();
echo '<div class="form-group">';
echo '<label>'.$lang['label_default_image'].'</label>';

echo '<select class="form-control custom-select" name="prefs_shop_default_banner">';
echo '<option value="use_standard">'.$lang['use_standard'].'</option>';

if($prefs_shop_default_banner == 'without_image') { $sel_without_image = 'selected'; }
echo '<option value="without_image" '.$sel_without_image.'>'.$lang['dont_use_an_image'].'</option>';
foreach ($all_images as $img) {
    unset($sel);
    if($prefs_shop_default_banner == $img) {
        $sel = "selected";
    }
    echo "<option $sel value='$img'>$img</option>";
}

echo '</select>';

echo '</div>';



echo '<input type="submit" class="btn btn-success" name="update_shop" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';