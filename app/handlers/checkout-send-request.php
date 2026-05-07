<?php

/**
 * send order request
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 * @var array $cart_item
 * @var int $cnt_cart_items
 * @var string $client_data
 * @var string $client_shipping_address
 * @var array $get_cd
 */

$send_request = false;

/* build table from cart items */
$table = '<table role="presentation" border="0" cellpadding="5" cellspacing="0">';
$table .= '<tr>';
$table .= '<td valign="top">'.$lang['label_product_info'].'</td>';
$table .= '<td valign="top">'.$lang['label_product_amount'].'</td>';
$table .= '<td valign="top">'.$lang['label_price'].'</td>';
$table .= '</tr>';
for($i=0;$i<$cnt_cart_items;$i++) {

    $product_info = '#'.$cart_item[$i]['product_number'].'<br>';
    $product_info .= '<strong>'.$cart_item[$i]['title'].'</strong><br>';
    $product_info .= $cart_item[$i]['options'].'<br>';
    $product_info .= $cart_item[$i]['slug'].'<br>';

    $table .= '<tr>';
    $table .= '<td valign="top">'.$product_info.'</td>';
    $table .= '<td valign="top">'.$cart_item[$i]['amount'].'</td>';
    $table .= '<td valign="top">'.$cart_item[$i]['price_net_single_format'].' ('.$lang['label_net'].')</td>';
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
    $mail_content .= '<p>'.$lang['label_delivery_address'].'</p>';
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