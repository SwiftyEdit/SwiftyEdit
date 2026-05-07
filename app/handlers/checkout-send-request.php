<?php
$send_request = false;

/* build table from cart items */
$table = '<table cellpadding="5">';
for($i=0;$i<$cnt_cart_items;$i++) {
    $table .= '<tr>';
    $table .= '<td valign="top">'.$lang['label_product_info'].'</td>';
    $table .= '<td valign="top"><h5>'.$cart_item[$i]['title'].'</h5>'.$cart_item[$i]['options'].'</td>';
    $table .= '</tr>';
    $table .= '<tr>';
    $table .= '<td valign="top">'.$lang['label_price'].'</td>';
    $table .= '<td valign="top">'.$cart_item[$i]['amount'].' x '.$cart_item[$i]['price_gross_single_format'].' ('.$lang['label_gross'].')</td>';
    $table .= '</tr>';
}
$table .= '</table>';

$recipient['name'] = sanitizeUserInputs($_POST['buyer_name']);
$recipient['mail'] = sanitizeUserInputs($_POST['buyer_mail']);
$comment = sanitizeUserInputs($_POST['buyer_comment']);
$subject = 'Order request / '.$se_settings['pagename'];

$mail_content = '<p>'.$subject.'</p>';
$mail_content .= '<p>'.$recipient['name'].' '.$recipient['mail'].'</p>';
$mail_content .= '<hr>';
$mail_content .= $table;
$mail_content .= '<hr>';
$mail_content .= $comment;

if($client_data != '') {
    $mail_content .= '<hr>';
    $mail_content .= '<p>'.$lang['label_invoice_address'].'</p>';
    $mail_content .= '<p>'.$client_data.'</p>';
}

if($client_shipping_address != '') {
    $mail_content .= '<hr>';
    $mail_content .= '<p>'.$lang['label_shipping_address'].'</p>';
    $mail_content .= '<p>'.$client_shipping_address.'</p>';
}

if($recipient['name'] != '' AND $recipient['mail'] != '') {
    $send_request = true;
} else {
    $send_request = false;
    $send_request_msg = 'Name and E-Mail';
    $smarty->assign('send_request_msg', $send_request_msg);
    $smarty->assign('request_msg_class', 'danger');
}

if($send_request === true) {
    $send = se_send_mail($recipient,$subject,$mail_content,true);
    if($send == 1) {
        $send_request_msg = $lang['msg_request_send'];
        $smarty->assign('send_request_msg', $send_request_msg);
        $smarty->assign('request_msg_class', 'success');
        /* remove items from se_carts */
        se_clear_cart($get_cd['user_id']);
        $cnt_cart_items = 0;
    }
}