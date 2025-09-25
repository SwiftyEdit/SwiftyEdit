<?php
/**
 * Account Confirmation Handler
 * SwiftyEdit CMS
 */

$user = se_return_clean_value($_GET['user']);
$al = se_return_clean_value($_GET['al']);

$verify = $db_user->update("se_user", [
    "user_verified" => 'verified'
], [
    "AND" => [
        "user_nick" => $user,
        "user_activationkey" => $al
    ]
]);

$cnt_changes = $verify->rowCount();

if($cnt_changes > 0){
    $account_msg = se_get_textlib("account_confirm", $languagePack,'content');
    $account_msg = str_replace("{USERNAME}","$user",$account_msg);
    record_log("switch","user activated via mail - $user","5");
} else {
    $account_msg = "";
}

$smarty->assign('page_content', $account_msg, true);