<?php

//error_reporting(E_ALL ^E_NOTICE);

/**
 * global variables
 * @var array $se_prefs preferences
 * @var array $lang from language files
 * @var array $icon
 * @var object $db_posts
 * @var string $hidden_csrf_token
 */

//prohibit unauthorized access
require __DIR__.'/access.php';

echo '<div class="subHeader">';
echo '<h3>'.$lang['nav_btn_price_groups'] .'</h3>';
echo '</div>';

/* update / new label */

if (isset($_POST['send'])) {

    $group_title = sanitizeUserInputs($_POST['title']);
    $unit = sanitizeUserInputs($_POST['unit']);
    $price_net = sanitizeUserInputs($_POST['price_net']);
    $price_net = str_replace('.', '', $price_net);

    $amount = (int) $_POST['amount'];
    $tax = (int) $_POST['tax'];
    $hash = md5(time());

    $product_price_volume_discount = '';
    if(isset($_POST['product_vd_amount'])) {
        $cnt_vd_prices = count($_POST['product_vd_amount']);
        for($i=0;$i<$cnt_vd_prices;$i++) {

            if($_POST['product_vd_amount'][$i] == '') {
                continue;
            }

            $amount = (int) $_POST['product_vd_amount'][$i];
            $price = sanitizeUserInputs($_POST['product_vd_price'][$i]);
            $price = str_replace('.', '', $price);

            $vd_price[] = [
                'amount' => $amount,
                'price' => $price
            ];

        }
        $product_price_volume_discount = json_encode($vd_price,JSON_FORCE_OBJECT);
    }

    // new data
    if($_POST['id'] == 'new') {
        $data = $db_posts->insert("se_prices", [
            "title" => $group_title,
            "hash" => $hash,
            "amount" => $amount,
            "unit" => $unit,
            "tax" => $tax,
            "price_net" => $price_net,
            "price_volume_discount" => $product_price_volume_discount
        ]);
        $edit_id = $db_posts->id();
        record_log($_SESSION['user_nick'], "create new price group", "1");
    }
    // update data
    if(is_numeric($_POST['id'])) {
        $id = (int) $_POST['id'];
        $data = $db_posts->update("se_prices", [
            "title" => $group_title,
            "amount" => $amount,
            "unit" => $unit,
            "tax" => $tax,
            "price_net" => $price_net,
            "price_volume_discount" => $product_price_volume_discount
        ],[
            "id" => $id
        ]);
        $edit_id = $id;
    }

}

if(isset($_POST['edit'])) {
    $edit_id = (int) $_POST['edit'];
}

// get data from se_prices by id
if(is_numeric($edit_id)) {
    $price_group = $db_posts->get("se_prices","*", [
        "id" => (int) $edit_id
    ]);
}


// buid select for tax
$get_tax = 0;
if($price_group['tax'] == '2') {
    $sel_tax_2 = 'selected';
    $sel_tax_1 = '';
    $sel_tax_3 = '';
    $get_tax = $se_prefs['prefs_posts_products_tax_alt1'];
} else if($price_group['tax'] == '3') {
    $sel_tax_3 = 'selected';
    $sel_tax_2 = '';
    $sel_tax_1 = '';
    $get_tax = $se_prefs['prefs_posts_products_tax_alt2'];
} else {
    $sel_tax_1 = 'selected';
    $sel_tax_2 = '';
    $sel_tax_3 = '';
    $get_tax = $se_prefs['prefs_posts_products_default_tax'];
}

$select_tax = "<select name='tax' class='form-control custom-select' id='tax'>";
$select_tax .= '<option value="1" '.$sel_tax_1.'>'.$se_prefs['prefs_posts_products_default_tax'].'</option>';
$select_tax .= '<option value="2" '.$sel_tax_2.'>'.$se_prefs['prefs_posts_products_tax_alt1'].'</option>';
$select_tax .= '<option value="3" '.$sel_tax_3.'>'.$se_prefs['prefs_posts_products_tax_alt2'].'</option>';
$select_tax .= '</select>';


// volume discounts

$volume_discounts = json_decode($price_group['price_volume_discount'],true);
$cnt_volume_discounts = 1;
if(is_array($volume_discounts)) {
    $cnt_volume_discounts = count($volume_discounts);
}


$show_price_volume_discount = '<div class="card my-2">';
$show_price_volume_discount .= '<div class="card-header">';
$show_price_volume_discount .= '<span>'.$lang['label_product_scaling_prices'].'</span>';
$show_price_volume_discount .= '<button class="btn btn-default btn-sm float-end" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVDP" aria-expanded="false" aria-controls="collapseExample">+</button>';
$show_price_volume_discount .= '</div>';
$show_price_volume_discount .= '<div class="card-body collapse" id="collapseVDP">';


for($i=0;$i<($cnt_volume_discounts+5);$i++) {

    $this_ammount = $volume_discounts[$i]['amount'];
    $price_net = $volume_discounts[$i]['price'];

    $show_price_volume_discount .= '<div class="calculate_price">';
    $show_price_volume_discount .= '<div class="row">';
    $show_price_volume_discount .= '<div class="col-md-4">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_amount'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control" name="product_vd_amount[]" type="number" value="'.$this_ammount.'">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '<div class="col-md-4">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_price'] . ' ' . $lang['label_product_net'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control prod_price_net" name="product_vd_price[]" type="text" value="'.$price_net.'">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '<div class="col-md-4">';
    $show_price_volume_discount .= '<label>' . $lang['label_product_price'] . ' ' . $lang['label_product_gross'] . '</label>';
    $show_price_volume_discount .= '<input class="form-control prod_price_gross" name="product_vd_price_gross[]" type="text" value="">';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';
}

$show_price_volume_discount .= '</div>';
$show_price_volume_discount .= '</div>';


echo '<div class="row">';
echo '<div class="col-md-6">';

// listing
$get_all_price_groups = se_get_price_groups();
$cnt_price_groups = count($get_all_price_groups);

echo '<div class="card p-2">';

if($cnt_price_groups < 1) {
    echo '<div class="alert alert-info">'.$lang['msg_info_no_entries_so_far'].'</div>';
} else {

    echo '<table class="table table-sm">';
    echo '<tr>';
    echo '<td>'.$lang['label_title'].'</td>';
    echo '<td>'.$lang['label_price'].'</td>';
    echo '<td>'.$lang['label_scaling_prices'].'</td>';
    echo '<td></td>';
    echo '<td>';
    foreach ($get_all_price_groups as $group) {

        $status_volume_discounts = $icon['check'];
        if($group['price_volume_discount'] == '' OR $group['price_volume_discount'] == 'null') {
            $status_volume_discounts = $icon['x'];
        }

        echo '<tr>';
        echo '<td>'.$group['title'].'</td>';
        echo '<td>'.$group['price_net'].'</td>';
        echo '<td>'.$status_volume_discounts.'</td>';
        echo '<td class="text-end">';
        echo '<form action="" method="POST">';
        echo '<button class="btn btn-default btn-sm text-success" name="edit" value="'.$group['id'].'">'.$icon['edit'].'</button> ';
        echo '<button class="btn btn-default btn-sm text-danger" name="delete" value="'.$group['id'].'">'.$icon['trash_alt'].'</button>';
        echo $hidden_csrf_token;
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';

}

echo '</div>';


echo '</div>';
echo '<div class="col-md-6">';

// sidebar

echo '<div class="card p-3">';

$form_tpl = file_get_contents('templates/form-edit-price-groups.tpl');

/* replace all entries from $lang */
foreach($lang as $k => $v) {
    $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
}


$form_tpl = str_replace('{title}', $price_group['title'], $form_tpl);
$form_tpl = str_replace('{amount}', $price_group['amount'], $form_tpl);
$form_tpl = str_replace('{unit}', $price_group['unit'], $form_tpl);
$form_tpl = str_replace('{price_net}', $price_group['price_net'], $form_tpl);
$form_tpl = str_replace('{select_tax}', $select_tax, $form_tpl);
$form_tpl = str_replace('{show_price_volume_discount}', $show_price_volume_discount, $form_tpl);

if(isset($price_group['id'])) {
    $form_tpl = str_replace('{btn_send}', $lang['button_update'], $form_tpl);
    $form_tpl = str_replace('{id}', $price_group['id'], $form_tpl);
} else {
    $form_tpl = str_replace('{btn_send}', $lang['button_save'], $form_tpl);
    $form_tpl = str_replace('{id}', 'new', $form_tpl);
}



echo '<form action="acp.php?tn=shop&sub=shop-prices" method="POST" class="form-horizontal">';

echo $form_tpl;
echo $hidden_csrf_token;

echo '</form>';
echo '</div>';

echo '</div>';
echo '</div>';