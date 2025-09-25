<?php

/**
 * @var object $db_user
 * @var object $smarty
 * @var array $lang
 * @var string $cache_id
 */

if(isset($_GET['code']) && $_GET['code'] != "") {

    $unlock_code = htmlspecialchars($_GET['code']);

    // reset unlock code and reset failed logins
    $unlock_data = $db_user->update("se_user", [
        "user_failed_logins" => 0,
        "user_unlock_code" => ''
    ], [
        "user_unlock_code" => $unlock_code
    ]);

    if ($unlock_data->rowCount() > 0) {
        $smarty->assign('page_content', $lang['account_unlocked']);
    }

}