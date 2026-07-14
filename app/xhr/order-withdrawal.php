<?php

/**
 * validate order number + e-mail and forward the withdrawal request to the admin
 *
 * @var object $db_content
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 * @var string $languagePack
 */

$order_nbr = sanitizeUserInputs($_POST['order_nbr'] ?? '');
$mail = sanitizeUserInputs($_POST['mail'] ?? '');
$reason = clean_visitors_input($_POST['reason'] ?? '');

$alert_text = '';
$alert_type = 'danger';

if($order_nbr === '' || $mail === '') {
    $alert_text = $lang['msg_order_withdrawal_missing_fields'];
} else if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $alert_text = $lang['msg_invalid_mail_format'];
} else {

    $this_order = se_get_order_by_nbr_and_mail($order_nbr, $mail);

    if($this_order === null) {
        $alert_text = $lang['msg_order_withdrawal_not_found'];
    } else if((int) $this_order['order_status'] === 3) {
        $alert_text = $lang['msg_order_withdrawal_already_canceled'];
    } else {

        $subject = $lang['mail_subject_order_withdrawal'].' (#'.$this_order['order_nbr'].')';

        $salutation = se_get_snippet("mail_salutation_order_withdrawal", "$languagePack", 'content');
        if($salutation == '') {
            $salutation = $lang['mail_salutation_order_withdrawal'];
        }

        $mail_data['body_tpl'] = 'order-withdrawal-request.tpl';
        $mail_data['subject'] = $subject;
        $mail_data['salutation'] = str_replace("{order_nbr}", $this_order['order_nbr'], $salutation);

        $build_html_mail = se_build_html_file($mail_data);
        $build_html_mail = str_replace("{order_nbr}", $this_order['order_nbr'], $build_html_mail);
        $build_html_mail = str_replace("{order_mail}", $mail, $build_html_mail);
        $build_html_mail = str_replace("{order_reason}", nl2br($reason), $build_html_mail);

        foreach($lang as $key => $val) {
            $build_html_mail = str_replace("{lang_$key}", $val, $build_html_mail);
        }

        $recipient['name'] = $se_settings['mailer_name'];
        $recipient['mail'] = $se_settings['notify_mail'] != '' ? $se_settings['notify_mail'] : $se_settings['mailer_adr'];

        $send_mail = se_send_mail($recipient, $subject, $build_html_mail);

        if($send_mail == 1) {
            $db_content->update("se_orders", [
                "order_withdrawal_requested" => time()
            ], [
                "id" => $this_order['id']
            ]);
            $alert_text = $lang['msg_order_withdrawal_sent'];
            $alert_type = 'success';
        } else {
            $alert_text = $lang['msg_order_withdrawal_mail_error'];
        }
    }
}

$smarty->assign("alert_text", "$alert_text");
$smarty->display("alert/alert-$alert_type.tpl");
