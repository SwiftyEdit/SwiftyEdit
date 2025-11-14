<?php
/**
 * Account Confirmation
 * Change/Confirm E-Mail Address
 *
 * @var object $smarty
 * @var array $lang
 * @var object $db_user
 * @var string $languagePack
 */

if(isset($_GET['user']) && isset($_GET['al'])) {
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

    if ($cnt_changes > 0) {
        $account_msg = se_get_snippet("account_confirm", $languagePack, 'content');
        $account_msg = str_replace("{USERNAME}", "$user", $account_msg);
        record_log("$user", "user activated via mail - $user", "5");
    } else {
        $account_msg = "";
    }

    $smarty->assign('page_content', $account_msg, true);
}

// find user by user_activationkey
// copy address from user_mail_temp to user_mail
if(isset($_GET['change_mail']) && ($_GET['change_mail'] !== '')) {

    $user_data = $db_user->get("se_user", ["user_id","user_nick","user_mail_temp"],[
        "user_activationkey" => $_GET['change_mail']
    ]);

    if(is_array($user_data)) {

        $user_new_mail = $user_data['user_mail_temp'];
        $user_nick = sanitizeUserInputs($user_data['user_nick']);
        $user_id = $user_data['user_id'];

        $change_mail = $db_user->update("se_user", [
            "user_mail" => $user_new_mail,
            "user_mail_temp" => "",
            "user_activationkey" => ""
        ], [
            "user_id" => (int) $user_id
        ]);

        if($change_mail->rowCount() == 1) {
            $account_msg = $lang['msg_update_profile_mail_success'];
            record_log("$user_nick", "user changed mail - $user_new_mail", "5");
        } else {
            $account_msg = "";
        }

        $smarty->assign('page_content', $account_msg, true);

    }

}