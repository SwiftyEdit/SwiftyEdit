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
$products_str  = '<table role="presentation" border="0" cellpadding="5" cellspacing="0">';
$products_str .= '<tr>';
$products_str .= '<td valign="top">'.$lang['label_product_info'].'</td>';
$products_str .= '<td valign="top">'.$lang['label_product_amount'].'</td>';
$products_str .= '<td valign="top">'.$lang['label_price'].'</td>';
$products_str .= '</tr>';
for($i=0;$i<$cnt_cart_items;$i++) {

    $product_info  = '#'.$cart_item[$i]['product_number'].'<br>';
    $product_info .= '<strong>'.$cart_item[$i]['title'].'</strong><br>';
    $product_info .= $cart_item[$i]['options'].'<br>';
    $product_info .= $cart_item[$i]['slug'].'<br>';

    $products_str .= '<tr>';
    $products_str .= '<td valign="top">'.$product_info.'</td>';
    $products_str .= '<td valign="top">'.$cart_item[$i]['amount'].'</td>';
    $products_str .= '<td valign="top">'.$cart_item[$i]['price_net_single_format'].' ('.$lang['label_net'].')</td>';
    $products_str .= '</tr>';
}
$products_str .= '</table>';


$mail_data['body_tpl'] = 'send-order-request.tpl';
$mail_data['subject'] = 'Order Request / '.$se_settings['pagename'];

$order_request_time = date("H:i",time());
$order_request_date = date("d.m.Y",time());

$recipient['name'] = sanitizeUserInputs($_POST['buyer_name']);
$recipient['mail'] = sanitizeUserInputs($_POST['buyer_mail']);
$recipient['tel'] = sanitizeUserInputs($_POST['buyer_tel']);
$comment = sanitizeUserInputs($_POST['buyer_comment']);

if(isset($_POST['buyer_invoice_address']) && $_POST['buyer_invoice_address'] != '') {
    $client_data = sanitizeUserInputs($_POST['buyer_invoice_address']);
}

if(isset($_POST['buyer_delivery_address']) && $_POST['buyer_delivery_address'] != '') {
    $client_shipping_address = sanitizeUserInputs($_POST['buyer_delivery_address']);
}

$subject = $lang['mail_subject_order_request'].' / '.$se_settings['pagename'];
$salutation = se_get_snippet("mail_salutation_order_request","$lang","content");

$mail_data['salutation'] = $subject;
if($salutation != "") {
    $mail_data['salutation'] = $salutation;
}

$mail_data['salutation'] = str_replace("{order_time}",$order_request_time,$mail_data['salutation']);
$mail_data['salutation'] = str_replace("{order_date}",$order_request_date,$mail_data['salutation']);

$build_html_mail = se_build_html_file($mail_data);

$build_html_mail = str_replace("{order_products}",$products_str,$build_html_mail);
$build_html_mail = str_replace("{order_user_comment}",$comment,$build_html_mail);

$build_html_mail = str_replace("{invoice_address}",$client_data,$build_html_mail);
$build_html_mail = str_replace("{shipping_address}",$client_shipping_address,$build_html_mail);

$build_html_mail = str_replace("{recipient_name}",$recipient['name'],$build_html_mail);
$build_html_mail = str_replace("{recipient_mail}",$recipient['mail'],$build_html_mail);
$build_html_mail = str_replace("{recipient_tel}",$recipient['tel'],$build_html_mail);

foreach($lang as $key => $val) {
    $search = '{lang_'.$key.'}';
    $build_html_mail = str_replace("$search","$val",$build_html_mail);
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
    //$send = se_send_mail($recipient,$subject,$mail_content,true);
    $send_mail = se_send_mail($recipient, $subject, $build_html_mail,true);
    if($send_mail == 1) {
        $send_request_msg = $lang['msg_request_send'];
        $smarty->assign('send_request_msg', $send_request_msg);
        $smarty->assign('request_msg_class', 'success');
        /* remove items from se_carts */
        if(isset($_SESSION['user_id'])) {
            se_clear_cart($_SESSION['user_id']);
        } else if(isset($_SESSION['token'])) {
            se_clear_cart($_SESSION['token']);
        }
        $cnt_cart_items = 0;
    }
}