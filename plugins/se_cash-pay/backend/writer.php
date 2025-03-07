<?php

$pm_prefs_file = $plugin_root.'/pm_config.php';

if(isset($_POST['save_cash_prefs'])) {

    $pm_prefs_content_file = file_get_contents($plugin_root.'/pm_config.tpl');

    $cash_pay_additional_costs = sanitizeUserInputs($_POST['cash_pay_additional_costs']);
    $cash_pay_snippet_cart = sanitizeUserInputs($_POST['cash_pay_snippet']);

    $pm_prefs_content = str_replace("{addon_name}","se_cash-pay",$pm_prefs_content_file);
    $pm_prefs_content = str_replace("{addon_additional_costs}","$cash_pay_additional_costs",$pm_prefs_content);
    $pm_prefs_content = str_replace("{addon_snippet_cart}","$cash_pay_snippet_cart",$pm_prefs_content);

    if(file_put_contents($pm_prefs_file,$pm_prefs_content, LOCK_EX)) {
        show_toast("Saved Preferences","success");
    }

}