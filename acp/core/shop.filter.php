<?php

/**
 * SwiftyEdit - manage product filter
 *
 * backend global variables
 * @var $hidden_csrf_token
 * @var $db_content
 * @var $icon
 * @var $lang
 * @var $lang_codes
 */


//error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
//prohibit unauthorized access
require __DIR__.'/access.php';

$show_form = false;

if(isset($_GET['new'])) {
    if($_GET['new'] == 'group') {
        $mode = 'new_group';
        $show_form = 'edit_group';
    }
    if($_GET['new'] == 'value') {
        $mode = 'new_value';
        $show_form = 'edit_value';
    }

    $btn_submit_text = $lang['save'];
}

if(isset($_GET['edit_group'])) {
    $mode = 'edit_group';
    $show_form = 'edit_group';
    $get_data_id = (int) $_GET['edit_group'];

    $btn_submit_text = $lang['update'];

    $group_data = $db_content->get("se_filter","*", [
        "filter_id" => $get_data_id
    ]);
}


if(isset($_GET['edit_value'])) {
    $mode = 'edit_value';
    $show_form = 'edit_value';
    $get_data_id = (int) $_GET['edit_value'];
    $btn_submit_text = $lang['update'];
    $value_data = $db_content->get("se_filter","*", [
        "filter_id" => $get_data_id
    ]);
}


echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>'.$icon['filter'] .' Filter</h3>';
echo '<div class="btn-group ms-auto">';
echo '<a href="?tn=shop&sub=shop-filter&new=group" class="btn btn-default">'.$icon['plus'].' '.$lang['btn_new_group'].'</a>';
echo '<a href="?tn=shop&sub=shop-filter&new=value" class="btn btn-default">'.$icon['plus'].' '.$lang['btn_new_value'].'</a>';
echo '</div>';
echo '</div>';

$all_filters = se_get_product_filter_groups('all');
$get_all_categories = se_get_categories();

if($show_form !== false)  {

    if($show_form == 'edit_group') {
        $form_tpl = file_get_contents('templates/form-edit-filter-groups.tpl');
    } else {
        $form_tpl = file_get_contents('templates/form-edit-filter-values.tpl');
    }

    /* replace labels, if there is the same label in language */
    foreach($lang as $k => $v) {
        $s = '{'.$k.'}';
        $form_tpl = str_replace("$s",$v,$form_tpl);
    }

    /* select for group language */
    $select_group_language  = '<select name="filter_group_lang" class="custom-select form-control">';
    foreach($lang_codes as $lang_code) {
        $select_group_language .= "<option value='$lang_code'".($group_data['filter_lang'] == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";
    }
    $select_group_language .= '</select>';

    /* select for parent id */
    $select_parent_id  = '<select name="filter_parent_id" class="custom-select form-control">';
    foreach($all_filters as $k => $v) {
        $title = $v['filter_title'];
        $id = $v['filter_id'];
        if(isset($_GET['parent'])) {
            $value_data['filter_parent_id'] = (int) $_GET['parent'];
        }
        $select_parent_id .= "<option value='$id'".($value_data['filter_parent_id'] == "$id" ? 'selected="selected"' :'').">$title</option>";
    }
    $select_parent_id .= '</select>';

    /* select for input type - (1) radio or (2) checkbox */
    $sel_checkbox = '';
    $sel_radio = '';
    if(isset($group_data['filter_input_type']) && ($group_data['filter_input_type'] == 2)) {
        $sel_checkbox = 'selected';
    } else {
        $sel_radio = 'selected';
    }
    $select_input_type  = '<select name="filter_input_type" class="custom-select form-control">';
    $select_input_type .= '<option value="2" '.$sel_checkbox.'>Checkbox</option>';
    $select_input_type .= '<option value="1" '.$sel_radio.'>Radio</option>';
    $select_input_type .= '</select>';

    /* select categories */
    if(isset($group_data['filter_categories'])) {
        $get_categories = explode(",",$group_data['filter_categories']);
        $checked_all = '';
        if($get_categories[0] == 'all') {
            $checked_all = 'checked';
        }
    }

    $cats = '<div class="form-check">';
    $cats .= '<input class="form-check-input" type="checkbox" name="filter_cats[]" value="all" id="cat_id_all" '.$checked_all.'>';
    $cats .= '<label class="form-check-label" for="cat_id_all">'.$lang['label_all_categories'].'</label>';
    $cats .= '</div><hr>';
    foreach($get_all_categories as $k => $v) {

        $check_this = '';
        if(is_array($get_categories)) {
            if (in_array($v['cat_hash'], $get_categories)) {
                $check_this = 'checked';
            }
        }

        $cats .= '<div class="form-check">';
        $cats .= '<input class="form-check-input" type="checkbox" name="filter_cats[]" value="'.$v['cat_hash'].'" id="cat_id'.$k.'" '.$check_this.'>';
        $cats .= '<label class="form-check-label" for="cat_id'.$k.'">'.$v['cat_name'].'</label>';
        $cats .= '</div>';
    }


    if($mode == 'new_group') {
        $form_tpl = str_replace('{val_group_name}','',$form_tpl);
        $form_tpl = str_replace('{val_group_description}','',$form_tpl);
        $form_tpl = str_replace('{val_group_priority}','',$form_tpl);
        $form_tpl = str_replace('{select_language}',"$select_group_language",$form_tpl);
        $form_tpl = str_replace('{select_input_type}',"$select_input_type",$form_tpl);
        $form_tpl = str_replace('{select_categories}',"$cats",$form_tpl);
        $form_tpl = str_replace('{id}',"",$form_tpl);
        $form_tpl = str_replace('{btn_delete_class}',"d-none",$form_tpl);
    }
    if($mode == 'edit_group') {
        $form_tpl = str_replace('{val_group_name}',$group_data['filter_title'],$form_tpl);
        $form_tpl = str_replace('{val_group_description}',$group_data['filter_description'],$form_tpl);
        $form_tpl = str_replace('{val_group_priority}',$group_data['filter_priority'],$form_tpl);
        $form_tpl = str_replace('{select_language}',"$select_group_language",$form_tpl);
        $form_tpl = str_replace('{select_input_type}',"$select_input_type",$form_tpl);
        $form_tpl = str_replace('{select_categories}',"$cats",$form_tpl);
        $form_tpl = str_replace('{id}',$get_data_id,$form_tpl);
        $form_tpl = str_replace('{btn_delete_class}',"",$form_tpl);
    }
    if($mode == 'new_value') {
        $form_tpl = str_replace('{select_parent_group}',"$select_parent_id",$form_tpl);
        $form_tpl = str_replace('{value_priority}','',$form_tpl);
        $form_tpl = str_replace('{value_name}','',$form_tpl);
        $form_tpl = str_replace('{value_description}','',$form_tpl);
        $form_tpl = str_replace('{id}',"",$form_tpl);
        $form_tpl = str_replace('{btn_delete_class}',"d-none",$form_tpl);
    }
    if($mode == 'edit_value') {
        $form_tpl = str_replace('{value_name}',$value_data['filter_title'],$form_tpl);
        $form_tpl = str_replace('{value_description}',$value_data['filter_description'],$form_tpl);
        $form_tpl = str_replace('{value_priority}',$value_data['filter_priority'],$form_tpl);
        $form_tpl = str_replace('{select_parent_group}',"$select_parent_id",$form_tpl);
        $form_tpl = str_replace('{id}',$get_data_id,$form_tpl);
        $form_tpl = str_replace('{btn_delete_class}',"",$form_tpl);
    }

    $form_tpl = str_replace('{mode}',"$mode",$form_tpl);
    $form_tpl = str_replace('{csrf_token}',$_SESSION['token'],$form_tpl);
    $form_tpl = str_replace('{btn_submit_text}',"$btn_submit_text",$form_tpl);
    $form_tpl = str_replace('{btn_delete_text}',$lang['btn_delete'],$form_tpl);
    $form_tpl = str_replace('{btn_close}',$lang['btn_close'],$form_tpl);

    echo $form_tpl;

} else {

    // list all filter

    echo '<div class="card p-3">';
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>'.$icon['translate'].'</th>';
    echo '<th>'.$icon['bars'].'</th>';
    echo '<th>Type</th>';
    echo '<th>Group</th>';
    echo '<th>Values</th>';
    echo '</tr>';
    echo '</thead>';
    foreach($all_filters as $k => $v) {

        $group_title = $v['filter_title'];
        $group_id = $v['filter_id'];
        $group_prio = $v['filter_priority'];
        $group_categories = explode(",",$v['filter_categories']);

        $type = '';
        if($v['filter_input_type'] == '1') {
            $type = $icon['ui_radios'];
        } else {
            $type = $icon['ui_checks'];
        }

        $flag = '<img src="/core/lang/' . $v['filter_lang'] . '/flag.png" width="15">';

        $get_filter_items = se_get_product_filter_values($group_id);

        echo '<tr>';
        echo '<td>'.$flag.'</td>';
        echo '<td>'.$group_prio.'</td>';
        echo '<td>'.$type.'</td>';
        echo '<td>';
        echo '<a href="?tn=shop&sub=shop-filter&edit_group='.$group_id.'" class="btn btn-default">'.$group_title.'</a>';
        // show categories
        echo '<br>';
        foreach($get_all_categories as $k => $v) {
            if (in_array($v['cat_hash'], $group_categories)) {
                echo '<span class="badge text-bg-secondary opacity-50">'.$v['cat_name'].'</span> ';
            }
        }
        echo '</td>';
        echo '<td>';
        foreach($get_filter_items as $item) {
            echo '<a href="?tn=shop&sub=shop-filter&edit_value='.$item['filter_id'].'" class="btn btn-sm btn-default">';
            echo '<span class="badge text-bg-secondary rounded-pill opacity-50">'.$item['filter_priority'].'</span> '.$item['filter_title'];
            echo '</a> ';
        }
        echo '<a href="?tn=shop&sub=shop-filter&new=value&parent='.$group_id.'" class="btn btn-sm btn-default text-success">+</a>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';
    echo '</div>';
}