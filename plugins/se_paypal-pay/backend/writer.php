<?php

$pm_prefs_file = $plugin_root.'/pm_config.php';

if(isset($_POST['save_paypal_prefs'])) {

    $pm_prefs_content_file = file_get_contents($plugin_root.'/pm_config.tpl');

    $paypal_additional_costs = sanitizeUserInputs($_POST['paypal_additional_costs']);
    $paypal_snippet = sanitizeUserInputs($_POST['paypal_snippet']);
    $paypal_client_id = sanitizeUserInputs($_POST['paypal_client_id']);
    $paypal_client_secret = sanitizeUserInputs($_POST['paypal_client_secret']);
    $paypal_cancel_url = sanitizeUserInputs($_POST['paypal_cancel_url']);
    $paypal_return_url = sanitizeUserInputs($_POST['paypal_return_url']);
    $paypal_sb_client_id = sanitizeUserInputs($_POST['paypal_sb_client_id']);
    $paypal_sb_client_secret = sanitizeUserInputs($_POST['paypal_sb_client_secret']);
    $paypal_sb_cancel_url = sanitizeUserInputs($_POST['paypal_sb_cancel_url']);
    $paypal_sb_return_url = sanitizeUserInputs($_POST['paypal_sb_return_url']);

    $paypal_mode = $_POST['paypal_mode'] == 'live' ? 'live' : 'sandbox';

    $pm_prefs_content = str_replace("{addon_name}","se_cash-pay",$pm_prefs_content_file);
    $pm_prefs_content = str_replace("{addon_additional_costs}","$paypal_additional_costs",$pm_prefs_content);
    $pm_prefs_content = str_replace("{addon_snippet_cart}","$paypal_snippet",$pm_prefs_content);

    $pm_prefs_content = str_replace("{paypal_mode}","$paypal_mode",$pm_prefs_content);

    $pm_prefs_content = str_replace("{paypal_client_id}","$paypal_client_id",$pm_prefs_content);
    $pm_prefs_content = str_replace("{paypal_client_secret}","$paypal_client_secret",$pm_prefs_content);
    $pm_prefs_content = str_replace("{paypal_cancel_url}","$paypal_cancel_url",$pm_prefs_content);
    $pm_prefs_content = str_replace("{paypal_return_url}","$paypal_return_url",$pm_prefs_content);

    $pm_prefs_content = str_replace("{paypal_sb_client_id}","$paypal_sb_client_id",$pm_prefs_content);
    $pm_prefs_content = str_replace("{paypal_sb_client_secret}","$paypal_sb_client_secret",$pm_prefs_content);
    $pm_prefs_content = str_replace("{paypal_sb_cancel_url}","$paypal_sb_cancel_url",$pm_prefs_content);
    $pm_prefs_content = str_replace("{paypal_sb_return_url}","$paypal_sb_return_url",$pm_prefs_content);

    if(file_put_contents($pm_prefs_file,$pm_prefs_content, LOCK_EX)) {
        show_toast("Saved Preferences","success");
    }

}