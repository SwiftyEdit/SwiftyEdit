<?php

//prohibit unauthorized access
require 'core/access.php';

if(isset($_POST['save_prefs_contacts'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
}


if(!empty($_POST)) {
    /* read the preferences again */
    $se_get_preferences = se_get_preferences();

    foreach($se_get_preferences as $k => $v) {
        $key = $se_get_preferences[$k]['option_key'];
        $value = $se_get_preferences[$k]['option_value'];
        $se_prefs[$key] = $value;
    }

    foreach($se_prefs as $k => $v) {
        $$k = stripslashes($v);
    }
}


echo '<form action="?tn=system&sub=general&file=general-email" method="POST" class="form-horizontal">';

if($se_prefs['prefs_mailer_type'] == '') {
    $se_prefs['prefs_mailer_type'] = 'mail';
}

$prefs_mail_name_input = '<input class="form-control" type="text" name="prefs_mailer_name" value="'.$se_prefs['prefs_mailer_name'].'">';
$prefs_mail_adr_input = '<input class="form-control" type="text" name="prefs_mailer_adr" value="'.$se_prefs['prefs_mailer_adr'].'">';
$prefs_mail_smtp_host_input = '<input class="form-control" type="text" name="prefs_smtp_host" value="'.$se_prefs['smtp_host'].'">';
$prefs_mail_smtp_port_input = '<input class="form-control" type="text" name="prefs_smtp_port" value="'.$se_prefs['smtp_port'].'">';
$prefs_mail_smtp_encryption_input = '<input class="form-control" type="text" name="prefs_smtp_encryption" value="'.$se_prefs['smtp_encryption'].'">';
$prefs_mail_smtp_username_input = '<input class="form-control" type="text" name="prefs_smtp_username" value="'.$se_prefs['smtp_username'].'">';
$prefs_mail_smtp_psw_input = '<pre>'.$smtp_psw.'</pre>';

$prefs_mail_type_input = '<div class="form-check">';
$prefs_mail_type_input .= '<input type="radio" class="form-check-input" id="mail" name="prefs_mailer_type" value="mail" '.($se_prefs['prefs_mailer_type'] == "mail" ? 'checked' :'').'>';
$prefs_mail_type_input .= '<label class="form-check-label" for="mail">'.$lang['label_settings_use_mail'].'</label>';
$prefs_mail_type_input .= '</div>';
$prefs_mail_type_input .= '<div class="form-check">';
$prefs_mail_type_input .= '<input type="radio" class="form-check-input" id="smtp" name="prefs_mailer_type" value="smtp" '.($se_prefs['prefs_mailer_type'] == "smtp" ? 'checked' :'').'>';
$prefs_mail_type_input .= '<label class="form-check-label" for="smtp">'.$lang['label_settings_use_smtp'].'</label>';
$prefs_mail_type_input .= '</div>';

echo tpl_form_control_group('',$lang['label_settings_mailer_name'],$prefs_mail_name_input);
echo tpl_form_control_group('',$lang['label_settings_mailer_mail'],$prefs_mail_adr_input);

echo $prefs_mail_type_input;

echo '<div class="alert alert-info my-2">'.$lang['msg_info_settings_use_smtp'].'</div>';

if(is_file(SE_CONTENT.'/config_smtp.php')) {

    echo '<fieldset class="mt-5">';
    echo '<legend>config_smtp.php</legend>';

    echo '<dl class="row">';
    echo '<dt class="col-sm-3"><code>$smtp_host</code></dt>';
    echo '<dd class="col-sm-9">' . $smtp_host . '</dd>';
    echo '<dt class="col-sm-3"><code>$smtp_port</code></dt>';
    echo '<dd class="col-sm-9">' . $smtp_port . '</dd>';
    echo '<dt class="col-sm-3"><code>$smtp_encryption</code></dt>';
    echo '<dd class="col-sm-9">' . $smtp_encryption . '</dd>';
    echo '<dt class="col-sm-3"><code>$smtp_username</code></dt>';
    echo '<dd class="col-sm-9">' . $smtp_username . '</dd>';
    echo '<dt class="col-sm-3"><code>$smtp_psw</code></dt>';
    echo '<dd class="col-sm-9">*****</dd>';
    echo '</dl>';
    echo '</fieldset>';
}


echo '<input type="submit" class="btn btn-success" name="save_prefs_contacts" value="'.$lang['save'].'">';
echo $hidden_csrf_token;
echo '</form>';

echo '<div class="mt-3">';
if($se_prefs['prefs_mailer_adr'] != '') {
    echo '<form action="?tn=system&sub=general&file=general-email" method="post">';
    echo '<button class="btn btn-primary btn-sm" name="sendtest">' .$lang['label_settings_mailer_send_test'].' '.$se_prefs['prefs_mailer_adr'].'</button>';
    echo $hidden_csrf_token;
    echo '</form>';
}


if(isset($_POST['sendtest'])) {

    $subject = 'SwiftyEdit Mail Test';
    $message = 'SwiftyEdit Test (via '.$se_prefs['prefs_mailer_type'].')';
    $recipient = array('name' => $se_prefs['prefs_mailer_name'], 'mail' => $se_prefs['prefs_mailer_adr']);
    $testmail = se_send_mail($recipient,$subject,$message);

    if($testmail == 1) {
        echo '<p class="alert alert-success mt-3">'.$icon['check'].' '.$lang['msg_success_mailer_sent_test'].'</p>';
    } else {
        echo '<div class="alert alert-danger mt-3">'.print_r($testmail).'</div>';;
    }

}

echo '</div>';


echo '</fieldset>';