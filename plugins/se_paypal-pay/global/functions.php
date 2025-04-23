<?php

function pp_get_settings(): array {
    $addon_payment_prefs = [];
    $pm_prefs_file = SE_ROOT.'plugins/se_paypal-pay/pm_config.php';
    if(is_file($pm_prefs_file)) {
        include $pm_prefs_file;
    }
    return $addon_payment_prefs;
}