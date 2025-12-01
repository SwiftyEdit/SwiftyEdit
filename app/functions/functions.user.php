<?php
use Medoo\Medoo;
/**
 * @param string $type all public groups
 * @return mixed
 */
function se_get_usergroups($type='all') {

    global $db_user;
    $type = sanitizeUserInputs($type);

    if($type == 'all') {
        $user_groups = $db_user->select("se_groups", "*");
    } else {
        $user_groups = $db_user->select("se_groups","*",[
            "group_type" => 'p'
        ]);
    }

    return $user_groups;
}

/**
 * @param integer $id
 * @return array data from user group
 */
function se_get_usergroup_by_id($id) {

    global $db_user;
    $id = (int) $id;

    $data = $db_user->get("se_groups","*",[
        "group_id" => $id
    ]);

    return $data;
}

/**
 * @param integer $id
 * @return array user data
 */
function se_get_userdata_by_id($id) {

    global $db_user;
    $id = (int) $id;

    $user_data = $db_user->get("se_user", "*", [
        "user_id" => $id
    ]);

    return $user_data;
}

/**
 * @param integer $user
 * @param integer $group
 * @return void
 */
function se_add_user_to_group(int $user, int $group): void {

    global $db_user;

    // get data from group
    $group_data = $db_user->select("se_groups","*",[
        "group_id" => $group
    ]);


    $users = explode(" ", $group_data['group_user']);
    if(in_array($user, $users)) {
        return;
    } else {
        $users[] = $user;
        $db_user->update("se_groups",[
            "group_user" => implode(" ", $users)
        ],[
            "group_id" => $group
        ]);
    }
}

/**
 * @param string $user username or e-mail
 * @param string $psw
 * @param $acp
 * @param $remember
 * @return string|void
 */
function se_user_login(string $user, string $psw, $acp=NULL, $remember=NULL) {

    if($user == '') {
        return 'failed';
    }

    global $db_user, $se_settings, $se_failed_logins_limit;

    if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
        $user_data = $db_user->get("se_user", ["user_nick","user_psw_hash"], [
            "AND" => [
                "user_mail" => "$user",
                "user_verified" => "verified",
                'OR' => [
                    'user_unlock_code' => null,
                    'AND #Empty' => [
                        'user_unlock_code' => ''
                    ]
                ]
            ]
        ]);
        $user_nick = $user_data['user_nick'];
        $hash = $user_data['user_psw_hash'];
    } else {
        $user_data = $db_user->get("se_user", ["user_psw_hash"], [
            "AND" => [
                "user_nick" => "$user",
                "user_verified" => "verified",
                'OR' => [
                    'user_unlock_code' => null,
                    'AND #Empty' => [
                        'user_unlock_code' => ''
                    ]
                ]
            ]
        ]);
        $user_nick = $user;
        $hash = $user_data['user_psw_hash'];
    }

    if(password_verify($psw, $hash)) {
        /* valid psw */

        $result = $db_user->get("se_user", "*", [
            "AND" => [
                "user_nick" => "$user_nick",
                "user_verified" => "verified"
            ]
        ]);

    }

    if(is_array($result)) {

        se_start_user_session($result);

        /* set cookie to remember user */
        if($remember == TRUE) {
            $identifier = randpsw($length=24);
            $securitytoken = randpsw($length=24);
            $securitytoken_hashed = sha1($securitytoken);
            $time = time();

            $se_base_url = $se_settings['prefs_cms_ssl_domain'] ?? $se_settings['prefs_cms_domain'];

            $db_user->insert("se_tokens", [
                "user_id" => $result['user_id'],
                "identifier" => "$identifier",
                "securitytoken" => "$securitytoken_hashed",
                "time" => "$time"
            ]);

            setcookie("identifier", $identifier, [
                'expires' => time() + (3600 * 24 * 365),
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            setcookie("securitytoken", $securitytoken, [
                'expires' => time() + (3600 * 24 * 365),
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

        }

        // reset failed logins
        $db_user->update("se_user",[
            "user_failed_logins" => 0
        ],[
            "user_nick" => $user_nick
        ]);

        if($_SESSION['user_class'] == 'administrator') {
            record_log("$user_nick","admin logged in",1);
        }


        if(($acp == TRUE) AND ($_SESSION['user_class'] == "administrator")) {
            header("location:/admin/");
        }


    } else {

        if(is_numeric($se_failed_logins_limit)) {
            se_handle_failed_logins($user_nick);
        }
        session_destroy();
        return "failed";
    }
}


function se_handle_failed_logins($user) {

    global $db_user,$se_failed_logins_limit,$lang,$se_base_url;

    $failed_user_data = $db_user->get("se_user", "*", ["user_nick" => $user]);
    $failed_logins = $failed_user_data['user_failed_logins']+1;

    if($failed_logins >= $se_failed_logins_limit) {
        // generate and save unlock code
        $unlock_code = bin2hex(random_bytes(16));
        $db_user->update("se_user",[
            "user_unlock_code" => $unlock_code
        ],[
            "user_nick" => $user,
            "user_unlock_code" => ""
        ]);

        // send mail to user
        $unlock_link = $se_base_url."unlock/?code=$unlock_code";
        $email_msg = str_replace("{USERNAME}","$user",$lang['account_temporarily_locked']);
        $email_msg = str_replace("{RESET_LINK}","$unlock_link",$email_msg);

        $mail_data['tpl'] = 'mail.tpl';
        $mail_data['subject'] = 'Account / '.$se_base_url;
        $mail_data['preheader'] = 'Unlock your Account at '.$se_base_url;
        $mail_data['title'] = 'Unlock your Account at  '.$se_base_url;
        $mail_data['salutation'] = "Unlock your Account | $user";
        $mail_data['body'] = "$email_msg";

        $build_html_mail = se_build_html_file($mail_data);

        $recipient = array('name' => $user, 'mail' => $failed_user_data['user_mail']);
        $send_reset_mail = se_send_mail($recipient,$mail_data['subject'],$build_html_mail);


    } else {
        // update failed_logins
        $db_user->update("se_user",[
            "user_failed_logins" => $failed_logins
        ],[
            "user_nick" => $user
        ]);
    }





}



/**
 * @param array $ud
 * @return void
 */
function se_start_user_session($ud) {

    /* reset session id */
    session_regenerate_id(true);

    $_SESSION['user_id'] = $ud['user_id'];
    $_SESSION['user_nick'] = $ud['user_nick'];
    $_SESSION['user_mail'] = $ud['user_mail'];
    $_SESSION['user_class'] = $ud['user_class'];
    $_SESSION['user_psw'] = $ud['user_psw'];
    $_SESSION['user_firstname'] = $ud['user_firstname'];
    $_SESSION['user_lastname'] = $ud['user_lastname'];
    $_SESSION['user_hash'] = md5($ud['user_nick']);

    /* CSRF Protection */
    if(empty($_SESSION['token'])) {
        $token = md5(uniqid(rand(), TRUE));
        $_SESSION['token'] = $token;
        $_SESSION['token_time'] = time();
    }

    $arr_drm = explode("|", $ud['user_drm']);

    if($arr_drm[0] == "drm_acp_pages")	{  $_SESSION['acp_pages'] = "allowed";  }
    if($arr_drm[1] == "drm_acp_files")	{  $_SESSION['acp_files'] = "allowed";  }
    if($arr_drm[2] == "drm_acp_user")	{  $_SESSION['acp_user'] = "allowed";  }
    if($arr_drm[3] == "drm_acp_system")	{  $_SESSION['acp_system'] = "allowed";  }
    if($arr_drm[4] == "drm_acp_editpages")	{  $_SESSION['acp_editpages'] = "allowed";  }
    if($arr_drm[5] == "drm_acp_editownpages")	{  $_SESSION['acp_editownpages'] = "allowed";  }
    if($arr_drm[6] == "drm_moderator")	{  $_SESSION['drm_moderator'] = "allowed";  }
    if($arr_drm[7] == "drm_can_publish")	{  $_SESSION['drm_can_publish'] = "true";  }
    if($arr_drm[8] == "drm_acp_sensitive_files")	{  $_SESSION['drm_acp_sensitive_files'] = "allowed";  }

}

/**
 * @return string
 */
function se_end_user_session() {

    global $db_user;

    if(is_numeric($_SESSION['user_id'])) {
        // delete data from se_tokens
        $db_user->delete("se_tokens",[
            "AND" => [
                "user_id" => $_SESSION['user_id']
            ]
        ]);

        unset($_COOKIE['identifier']);
        unset($_COOKIE['securitytoken']);
        unset($_COOKIE['permit_cookies']);
        setcookie('identifier', '', 1);
        setcookie('securitytoken', '', 1);
        setcookie('permit_cookies', '', 1);
        $cookiesSet = array_keys($_COOKIE);
        for ($x=0;$x<count($cookiesSet);$x++) setcookie($cookiesSet[$x],"",1);
    }
    session_destroy();
    unset($_SESSION['user_nick']);
    setcookie("PHPSESSID", "", 1);

    return 'logout';

}

/**
 * @param integer $length
 * @return string
 */
function randpsw(int $length=8): string {

    $chars = '123456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

    $random_s = '';
    $cnt_chars = strlen($chars);
    for($i=0;$i<$length;$i++) {
        $random_s .= $chars[mt_rand(0, $cnt_chars - 1)];
    }
    return $random_s;
}

/**
 * @param $token
 * @return mixed
 */
function get_userdata_by_token($token) {

    global $db_user;

    $user_data = $db_user->get("se_user", "*", [
        "AND" => [
            "user_reset_psw" => "$token"
        ]
    ]);

    return $user_data;
}

/**
 * @param string $mail
 * @return mixed
 */
function get_userdata_by_mail(string $mail): mixed {

    global $db_user;

    $user_data = $db_user->get("se_user", "*", [
        "AND" => [
            "user_mail" => "$mail",
            "user_verified" => "verified"
        ]
    ]);

    return $user_data;
}

/**
 * @return mixed
 */
function get_my_userdata() {

    global $db_user;

    $user_data = $db_user->get("se_user", "*", [
        "AND" => [
            "user_id" => $_SESSION['user_id'],
            "user_verified" => "verified"
        ]
    ]);

    return $user_data;
}

function se_email_exists($email): bool {
    global $db_user;
    // check database for existing username
    $sql = 'SELECT COUNT(*) FROM se_user WHERE LOWER(user_mail) = LOWER(?)';
    $stmt = $db_user->pdo->prepare($sql);
    $stmt->execute([strtolower($email)]);
    $count = $stmt->fetchColumn();
    $exists = ($count > 0);

    if($exists) {
        return true;
    }

    return false;
}


/**
 * @param $username
 * @return bool
 */
function se_is_valid_username($username): bool
{
    if(!preg_match("/^[a-zA-Z0-9-_]{2,20}$/",$username)) {
        return false;
    }
    return true;
}

/**
 * @param $username
 * @return bool
 */
function se_username_exists($username): bool
{
    global $db_user, $se_settings;

    $check_usernames = [];

    // add blacklist usernames if configured
    if (!empty($se_settings['blacklist_usernames'])) {
        $check_usernames = array_map('trim', explode(',', $se_settings['blacklist_usernames'])); // comments in English
    }

    $needle = strtolower(trim($username));
    foreach ($check_usernames as $user) {
        if (strtolower(trim($user)) === $needle) {
            return true;
        }
    }

    // check database for existing username
    $sql = 'SELECT COUNT(*) FROM se_user WHERE LOWER(user_nick) = LOWER(?)';
    $stmt = $db_user->pdo->prepare($sql);
    $stmt->execute([strtolower($username)]);
    $count = $stmt->fetchColumn();
    $exists = ($count > 0);

    if($exists) {
        return true;
    }

    return false;
}


/**
 * @return mixed
 */
function get_all_usermail() {
    global $db_user;
    $user_mails = $db_user->select("se_user", ["user_mail"]);
    return $user_mails;
}

/**
 * @return mixed
 */
function get_all_usernames() {
    global $db_user;
    $user_nicks = $db_user->select("se_user", ["user_nick"]);
    return $user_nicks;
}