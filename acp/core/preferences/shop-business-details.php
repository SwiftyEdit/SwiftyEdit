<?php

//prohibit unauthorized access
require 'core/access.php';

if(isset($_POST['save_business_data'])) {
    foreach($_POST as $key => $val) {
        if(is_string($val)){
            $data[htmlentities($key)] = htmlentities($val);
        }
    }

    se_write_option($data,'se');
}


if(isset($_POST)) {
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


echo '<form action="?tn=system&sub=shop&file=shop-business-details" method="POST" class="form-horizontal">';

$input_bd_address = [
    "input_name" => "prefs_business_address",
    "input_value" => $prefs_business_address,
    "label" => $lang['label_business_address']
];

$input_bd_taxnumber = [
    "input_name" => "prefs_business_taxnumber",
    "input_value" => $prefs_business_taxnumber,
    "label" => $lang['label_tax_number']
];

echo tpl_form_input_textarea($input_bd_address);
echo tpl_form_input_text($input_bd_taxnumber);


echo '<input type="submit" class="btn btn-success" name="save_business_data" value="'.$lang['save'].'">';
echo $hidden_csrf_token;

echo '</form>';