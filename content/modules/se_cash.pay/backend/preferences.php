<?php

echo '<div class="subHeader">';
echo 'se_cash.pay - preferences';
echo '</div>';

$pm_prefs_file = SE_CONTENT.'/modules/se_cash.pay/pm_config.php';

if(isset($_POST['save_cash_prefs'])) {

    $pm_prefs_content_file = file_get_contents(SE_CONTENT.'/modules/se_cash.pay/pm_config.tpl');

    $cash_pay_additional_costs = sanitizeUserInputs($_POST['cash_pay_additional_costs']);
    $cash_pay_snippet_cart = sanitizeUserInputs($_POST['cash_pay_snippet']);

    $pm_prefs_content = str_replace("{addon_name}","se_cash.pay",$pm_prefs_content_file);
    $pm_prefs_content = str_replace("{addon_additional_costs}","$cash_pay_additional_costs",$pm_prefs_content);
    $pm_prefs_content = str_replace("{addon_snippet_cart}","$cash_pay_snippet_cart",$pm_prefs_content);

    if(file_put_contents($pm_prefs_file,$pm_prefs_content, LOCK_EX)) {
        show_toast("Saved Preferences","success");
    }

}

if(is_file($pm_prefs_file)) {
    include $pm_prefs_file;
}


echo '<div class="card p-3">';

echo '<form action="?tn=addons&sub=se_cash.pay&a=preferences" method="POST">';

echo '<div class="mb-3">';
echo '<label class="form-label">Additional Costs</label>';
echo '<input class="form-control" type="text" name="cash_pay_additional_costs" value="'.$addon_payment_prefs['addon_additional_costs'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Snippet for Shopping Cart</label>';
echo '<select class="form-control" name="cash_pay_snippet">';
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

echo '<button type="submit" class="btn btn-default" name="save_cash_prefs">Save</button>';
echo $hidden_csrf_token;
echo '</form>';

echo '</div>';