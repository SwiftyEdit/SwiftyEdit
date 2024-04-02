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

if($prefs_product_sorting == 1 OR $prefs_product_sorting == '') {
    $sel_sorting1 = 'selected';
} else if($prefs_product_sorting == 2) {
    $sel_sorting2 = 'selected';
} else if($prefs_product_sorting == 3) {
    $sel_sorting3 = 'selected';
} else if($prefs_product_sorting == 4) {
    $sel_sorting4 = 'selected';
} else if($prefs_product_sorting == 5) {
    $sel_sorting5 = 'selected';
}

echo '<form action="?tn=system&sub=shop&file=general" method="POST">';

echo '<div class="row">';
echo '<div class="col-md-4">';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_entries_per_page'] . '</label>';
echo '<input type="text" class="form-control" name="prefs_products_per_page" value="'.$prefs_products_per_page.'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-8">';
echo '<label>' . $lang['label_default_sorting'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_product_sorting">';
echo '<option value="1" '.$sel_sorting1.'>'.$lang['label_product_sorting_default'].'</option>';
echo '<option value="2" '.$sel_sorting2.'>'.$lang['label_product_sorting_topseller'].'</option>';
echo '<option value="3" '.$sel_sorting3.'>'.$lang['label_product_sorting_name'].'</option>';
echo '<option value="4" '.$sel_sorting4.'>'.$lang['label_product_sorting_price'].' '.$lang['ascending'].'</option>';
echo '<option value="5" '.$sel_sorting5.'>'.$lang['label_product_sorting_price'].' '.$lang['descending'].'</option>';
echo '</select>';
echo '</div>';
echo '</div>';

echo '<h5 class="heading-line">' . $lang['label_product_cart_mode'] . '</h5>';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_carts'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_products_cart">';
echo '<option value="1" '.$sel_carts1.'>'.$lang['product_cart_mode_off'].'</option>';
echo '<option value="2" '.$sel_carts2.'>'.$lang['product_cart_mode_registered'].'</option>';
echo '<option value="3" '.$sel_carts3.'>'.$lang['product_cart_mode_all_user'].'</option>';
echo '</select>';
echo '</div>';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_orders'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_order_mode">';
echo '<option value="1" '.$sel_order_mode1.'>'.$lang['product_order_mode_on'].'</option>';
echo '<option value="2" '.$sel_order_mode2.'>'.$lang['product_order_mode_request'].'</option>';
echo '<option value="3" '.$sel_order_mode3.'>'.$lang['product_order_mode_both'].'</option>';
echo '</select>';
echo '</div>';

echo '<h5 class="heading-line">'.$lang['label_product_tax'].' / '.$lang['label_product_currency'].'</h5>';


echo '<div class="row">';
echo '<div class="col">';
echo '<div class="form-group">
				<label>'.$lang['label_product_tax'].' #1</label>
				<input type="text" class="form-control" name="prefs_posts_products_default_tax" value="'.$prefs_posts_products_default_tax.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_product_tax'] . ' #2</label>
				<input type="text" class="form-control" name="prefs_posts_products_tax_alt1" value="'.$prefs_posts_products_tax_alt1.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_product_tax'] . ' #3</label>
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

$sel_price_visibility_all = '';
$sel_price_visibility_registered = '';
if($prefs_posts_price_visibility == 1 OR $prefs_posts_price_visibility == '') {
    $sel_price_visibility_all = 'selected';
} else {
    $sel_price_visibility_registered = 'selected';
}


echo '<div class="row">';
echo '<div class="col">';

echo '<div class="form-group">
				<label>' . $lang['label_product_currency'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_default_currency" value="'.$prefs_posts_products_default_currency.'">
			</div>';

echo '</div>';
echo '<div class="col">';

echo '<div class="mb-3">';
echo '<label>' . $lang['label_product_price'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_price_mode">';
echo '<option value="1" '.$sel_price_mode1.'>'.$lang['product_show_price_gross'].'</option>';
echo '<option value="2" '.$sel_price_mode2.'>'.$lang['product_show_price_both'].'</option>';
echo '<option value="3" '.$sel_price_mode3.'>'.$lang['product_show_price_net'].'</option>';
echo '</select>';
echo '</div>';

echo '</div>';
echo '<div class="col">';
echo '<label>' . $lang['label_visibility'] . '</label>';
echo '<select class="form-control custom-select" name="prefs_posts_price_visibility">';
echo '<option value="1" '.$sel_price_visibility_all.'>'.$lang['product_show_prices_to_all'].'</option>';
echo '<option value="2" '.$sel_price_visibility_registered.'>'.$lang['product_show_prices_to_registered'].'</option>';
echo '</select>';
echo '</div>';
echo '</div>';

echo '<h5 class="heading-line">'.$lang['images'].'</h5>';

echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_prefix'].'</label>
			<input type="text" class="form-control" name="prefs_shop_images_prefix" value="'.$prefs_shop_images_prefix.'">
			</div>';
$all_images = se_get_all_images();
echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_default_image'].'</label>';

echo '<select class="form-control custom-select" name="prefs_shop_default_banner">';
echo '<option value="use_standard">'.$lang['label_use_default'].'</option>';

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