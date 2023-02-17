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
echo '<td>Active</td>';
echo '<td>Type</td>';
echo '<td>'.$lang['label_payment_costs'].'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>';
$check_bt = ($prefs_pm_bank_transfer == 1) ? 'checked' : '';
echo '<input class="form-check-input" type="checkbox" name="prefs_pm_bank_transfer" value="1" id="checkBankTransfer" '.$check_bt.'>';
echo '</td>';
echo '<td>';
echo '<label class="form-check-label" for="checkBankTransfer">'.$lang['label_payment_bank_transfer'].'</label>';
echo '</td>';
echo '<td>';
echo '<input type="text" class="form-control" name="prefs_payment_costs_bt" value="'.$prefs_payment_costs_bt.'">';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>';
$check_invoice = ($prefs_pm_invoice == 1) ? 'checked' : '';
echo '<input class="form-check-input" type="checkbox" name="prefs_pm_invoice" value="1" id="checkInvoice" '.$check_invoice.'>';
echo '</td>';
echo '<td>';
echo '<label class="form-check-label" for="checkInvoice">'.$lang['label_payment_invoice'].'</label>';
echo '</td>';
echo '<td>';
echo '<input type="text" class="form-control" name="prefs_payment_costs_invoice" value="'.$prefs_payment_costs_invoice.'">';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>';
$check_cash = ($prefs_pm_cash == 1) ? 'checked' : '';
echo '<input class="form-check-input" type="checkbox" name="prefs_pm_cash" value="1" id="checkCash" '.$check_cash.'>';
echo '</td>';
echo '<td>';
echo '<label class="form-check-label" for="checkCash">'.$lang['label_payment_cash'].'</label>';
echo '</td>';
echo '<td>';
echo '<input type="text" class="form-control" name="prefs_payment_costs_cash" value="'.$prefs_payment_costs_cash.'">';
echo '</td>';
echo '</tr>';

echo '</table>';


echo '<input type="submit" class="btn btn-success" name="update_pm_shipping" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';