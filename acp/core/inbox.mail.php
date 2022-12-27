<?php
//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require 'core/access.php';
$section_url = '?tn=inbox&sub=mailbox';
$show_form = false;


echo '<div class="subHeader d-flex">';
echo '<div class="d-flex">E-Mails</div>';
echo '<form action="'.$section_url.'" method="post" class="d-inline ms-auto">';
echo '<button class="btn btn-default text-success" name="new_mail">'.$lang['label_new_post'].'</button>';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';


if(isset($_POST['new_mail']) OR isset($_POST['save_draft']) OR isset($_POST['send_mail'])) {
    $show_form = true;
}

if(isset($_POST['form_close'])) {
    $show_form = false;
}

if($show_form == true) {

    $btn_save = '<button class="btn btn-default" name="save_draft">SAVE</button>';
    $btn_send = '<button class="btn btn-primary" name="send_mail">SEND</button>';
    $btn_close = '<button class="btn btn-default" name="form_close">X</button>';

    $mail_form_tpl = file_get_contents('templates/mail-form.tpl');

    $mail_form_tpl = str_replace('{mail_subject}','',$mail_form_tpl);
    $mail_form_tpl = str_replace('{mail_text}','',$mail_form_tpl);
    $mail_form_tpl = str_replace('{hidden_csrf}',$hidden_csrf_token,$mail_form_tpl);
    $mail_form_tpl = str_replace('{btn_save_draft}',$btn_save,$mail_form_tpl);
    $mail_form_tpl = str_replace('{btn_send}',$btn_send,$mail_form_tpl);
    $mail_form_tpl = str_replace('{btn_close}',$btn_close,$mail_form_tpl);
    $mail_form_tpl = str_replace('{formaction}',$section_url,$mail_form_tpl);

    echo $mail_form_tpl;
}