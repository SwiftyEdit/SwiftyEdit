<?php

//prohibit unauthorized access
require 'core/access.php';

if(isset($_POST['save_delivery_data'])) {

    $get_countries = json_decode($se_prefs['prefs_delivery_countries'],JSON_OBJECT_AS_ARRAY);
    $get_countries[] = $_POST['delivery_country'];
    $add_country = array_filter($get_countries);
    sort($add_country);
    $prefs_countries_json = json_encode($add_country, JSON_FORCE_OBJECT);

    $data['prefs_delivery_countries'] = $prefs_countries_json;

    se_write_option($data,'se');
}

if(isset($_POST['delete_country']) AND is_numeric($_POST['delete_country'])) {
    $delete_id = (int) $_POST['delete_country'];
    $get_countries = json_decode($se_prefs['prefs_delivery_countries'],JSON_OBJECT_AS_ARRAY);

    unset($get_countries[$delete_id]);
    $add_countries = array_values($get_countries);
    $prefs_countries_json = json_encode($add_countries, JSON_FORCE_OBJECT);
    $data['prefs_delivery_countries'] = $prefs_countries_json;
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




echo '<div class="row">';
echo '<div class="col-9">';

$get_countries = json_decode($se_prefs['prefs_delivery_countries'],JSON_OBJECT_AS_ARRAY);
if(is_array($get_countries)) {

    echo '<form action="?tn=system&sub=shop&file=shop-delivery" method="POST" class="form-horizontal">';

    sort($get_countries);
    $cnt_countries = count($get_countries);
    echo '<table class="table">';
    for($i=0;$i<$cnt_countries;$i++) {
        echo '<tr>';
        echo '<td><input class="form-control" type="text" name="delivery_country['.$i.']" value="'.$get_countries[$i].'"></input>';
        echo '<td><button class="btn btn-danger" name="delete_country" value="'.$i.'">'.$icon['trash'].'</button></td>';
        echo '</tr>';
    }
    echo '</table>';

    echo $hidden_csrf_token;
    echo '</form>';
}



echo '</div>';
echo '<div class="col-3">';

echo '<form action="?tn=system&sub=shop&file=shop-delivery" method="POST" class="form-horizontal">';

$input_delivery_country = [
    "input_name" => "delivery_country",
    "input_value" => "",
    "label" => $lang['label_add_delivery_country']
];

echo tpl_form_input_text($input_delivery_country);

echo '<input type="submit" class="btn btn-success" name="save_delivery_data" value="'.$lang['save'].'">';
echo $hidden_csrf_token;
echo '</form>';

echo '</div>';
echo '</div>';



