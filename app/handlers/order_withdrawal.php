<?php

/**
 * order withdrawal form
 * prefill order number / e-mail when opened from "my orders"
 * the actual submit + validation happens via xhr/order-withdrawal.php
 */

$prefill_order_nbr = sanitizeUserInputs($_GET['order_nbr'] ?? '');
$prefill_mail = sanitizeUserInputs($_GET['mail'] ?? '');

$smarty->assign("prefill_order_nbr", "$prefill_order_nbr");
$smarty->assign("prefill_mail", "$prefill_mail");

$smarty->assign("heading_order_withdrawal", "$lang[heading_order_withdrawal]");
$smarty->assign("label_order_nbr", "$lang[label_order_nbr]");
$smarty->assign("label_mail", "$lang[label_mail]");
$smarty->assign("label_order_withdrawal_reason", "$lang[label_order_withdrawal_reason]");
$smarty->assign("button_order_withdrawal", "$lang[button_order_withdrawal]");

$intro_text = se_get_snippet("order_withdrawal_intro", "$languagePack", 'content');
if($intro_text == '') {
    $intro_text = $lang['text_order_withdrawal_intro'];
}
$smarty->assign("text_order_withdrawal_intro", text_parser($intro_text));

$output = $smarty->fetch("order-withdrawal.tpl");
$smarty->assign('page_content', $output);
