<?php

$pm_prefs_file = $this_addon_root.'/pm_config.php';

if(is_file($pm_prefs_file)) {
    include $pm_prefs_file;
}

echo '<div id="response"></div>';
echo '<div class="card p-3">';
echo '<form hx-post="/admin/xhr/addons/plugin/se_invoice-pay/write/" hx-target="#response" method="POST">';
echo '<div class="mb-3">';
echo '<label class="form-label">Additional Costs</label>';
echo '<input class="form-control" type="text" name="invoice_pay_additional_costs" value="'.$addon_payment_prefs['addon_additional_costs'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Snippet for Shopping Cart</label>';
echo '<select class="form-control" name="invoice_pay_snippet">';
echo '<option>No, nothing</option>';
$get_payment_snippets = se_get_all_snippets();
foreach($get_payment_snippets as $snippet) {
    if(str_starts_with($snippet['snippet_name'],'cart_pm_')) {

        $sel = '';
        if($addon_payment_prefs['addon_snippet_cart'] == $snippet['snippet_name']) {
            $sel = 'selected';
        }

        echo '<option value="'.$snippet['snippet_name'].'" '.$sel.'>'.$snippet['snippet_name'].'</option>';
    }
}
echo '</select>';
echo '</div>';

echo '<button type="submit" class="btn btn-default" name="save_settings">Save</button>';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';

echo '<div class="card p-3">';

echo '</div>';