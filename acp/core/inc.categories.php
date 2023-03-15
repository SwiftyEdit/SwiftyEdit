<?php

//prohibit unauthorized access
require 'core/access.php';

$arr_lang = get_all_languages();

$show_form = false;

if(isset( $_GET['cat'] ) && ( $_GET['cat'] == 'n') ) {
    $mode = 'new';
    $cat_id = '';
    $show_form = true;
    $btn_submit_text = $lang['save'];
}


/* delete category */

if(isset($_POST['delete_category'])) {

    $delete_id = (int) $_POST['delete_category'];

    $data = $db_content->delete("se_categories", [
        "cat_id" => $delete_id
    ]);

    unset($_REQUEST['editcat'],$cat_name,$cat_sort,$cat_description,$cat_thumbnail);
}


if(isset($_POST['cat']) && ($_POST['cat'] != '')) {

    // open category

    $cat_id = (int) $_POST['cat'];
    $mode = 'update';
    $btn_submit_text = $lang['update'];

    $get_category = $db_content->get("se_categories","*",[
        "AND" => [
            "cat_id" => "$cat_id"
        ]
    ]);

    $cat_name = $get_category['cat_name'];
    $cat_sort = $get_category['cat_sort'];
    $cat_lang = $get_category['cat_lang'];
    $cat_thumbnail = $get_category['cat_thumbnail'];
    $cat_description = $get_category['cat_description'];

    $show_form = true;
}


echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>'.$lang['categories'].'</h3>';
echo '<a href="?tn=categories&cat=n" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

if($show_form == true)  {
 /* print the form */

    $form_tpl = file_get_contents('templates/form-edit-categories.tpl');

    foreach($lang as $k => $v) {
        $s = '{'.$k.'}';
        $form_tpl = str_replace("$s",$v,$form_tpl);
    }

    $images = se_scandir_rec('../content/images');

    $choose_tmb = '<select class="form-control choose-thumb custom-select" name="cat_thumbnail">';
    $choose_tmb .= '<option value="">'.$lang['no_image'].'</option>';
    foreach($images as $img) {
        $img = str_replace('../content/', '/content/', $img);
        $selected = '';
        if($cat_thumbnail == $img) {$selected = 'selected';}
        $choose_tmb .= '<option '.$selected.' value='.$img.'>'.$img.'</option>';
    }
    $choose_tmb .= '</select>';

    if($cat_lang == '' && $default_lang_code != '') {
        $cat_lang = $default_lang_code;
    }

    $select_cat_language  = '<select name="cat_lang" class="custom-select form-control">';
    foreach($lang_codes as $lang_code) {
        $select_cat_language .= "<option value='$lang_code'".($cat_lang == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";
    }
    $select_cat_language .= '</select>';

    $form_tpl = str_replace('{csrf_token}',$_SESSION['token'],$form_tpl);
    $form_tpl = str_replace('{btn_submit_text}',$btn_submit_text,$form_tpl);
    $form_tpl = str_replace('{mode}',$mode,$form_tpl);
    $form_tpl = str_replace('{val_cat_name}',$cat_name,$form_tpl);
    $form_tpl = str_replace('{val_cat_priority}',$cat_sort,$form_tpl);
    $form_tpl = str_replace('{val_cat_description}',$cat_description,$form_tpl);
    $form_tpl = str_replace('{select_thumbnail}',$choose_tmb,$form_tpl);
    $form_tpl = str_replace('{select_language}',$select_cat_language,$form_tpl);
    $form_tpl = str_replace('{id}',$cat_id,$form_tpl);

    echo $form_tpl;


} else {


    /* list categories */
    $all_categories = se_get_categories();
    $cnt_categories = count($all_categories);

    echo '<div class="card p-3">';
    echo '<table class="table">';

    foreach ($all_categories as $cats) {

        $show_thumb = '';
        $flag = '<img src="/core/lang/' . $cats['cat_lang'] . '/flag.png" width="15">';

        if ($cats['cat_thumbnail'] != '') {
            $cat_thumb = $cats['cat_thumbnail'];
            $show_thumb = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\'' . $cat_thumb . '\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url(' . $cat_thumb . ');">';
            $show_thumb .= '</div>';
        } else {
            $show_thumb .= '<div class="show-thumb" style="background-image: url(\'images/no-image.png\');">';
        }

        echo '<tr>';
        echo '<td width="50">' . $show_thumb . '</td>';
        echo '<td>';
        echo '<h5 class="card-title">' . $flag . ' ' . $cats['cat_name'] . '</h5>';
        echo $cats['cat_description'];
        echo '</td>';
        echo '<td class="text-end">';
        echo '<form action="?tn=categories" method="POST">';
        echo '<button type="submit" class="btn btn-sm btn-default text-danger me-1" name="delete_category" value="'.$cats['cat_id'].'" onclick="return confirm(\''.$lang['confirm_delete_data'].'\')">'.$icon['trash'].'</button>';
        echo '<button name="cat" value='.$cats['cat_id'].'" class="btn btn-sm btn-default">'.$icon['edit'].' '.$lang['edit'].'</button>';
        echo $hidden_csrf_token;
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';
}