<?php

//prohibit unauthorized access
require 'core/access.php';

$arr_lang = get_all_languages();

foreach($_POST as $key => $val) {
    if(is_string($val)) {
        $$key = @htmlspecialchars($val, ENT_QUOTES);
    }
}



$submit_button = '<input type="submit" class="btn btn-success" name="new_category" value="'.$lang['save'].'">';
$delete_button = "";
$show_form = false;

if(isset( $_GET['cat'] ) && ( $_GET['cat'] == 'n') ) {
    $show_form = true;
}

/* update category */

if(isset($_POST['update_category'])) {

    $cat_name_clean = clean_filename($cat_name);

    $data = $db_content->update("se_categories", [
        "cat_name" =>  $cat_name,
        "cat_lang" =>  $cat_lang,
        "cat_name_clean" =>  $cat_name_clean,
        "cat_sort" =>  $cat_sort,
        "cat_description" =>  $cat_description,
        "cat_thumbnail" =>  $cat_thumbnail
    ], [
        "cat_id" => $editcat
    ]);

    $show_form = true;

}

if(isset($_POST['new_category'])) {

    $cat_name_clean = clean_filename($cat_name);

    $data = $db_content->insert("se_categories", [
        "cat_name" =>  $cat_name,
        "cat_lang" =>  $cat_lang,
        "cat_name_clean" =>  $cat_name_clean,
        "cat_sort" =>  $cat_sort,
        "cat_description" =>  $cat_description,
        "cat_thumbnail" =>  $cat_thumbnail,
    ]);

    $editcat = $db_content->id();

    $show_form = true;
    $submit_button = '<input type="submit" class="btn btn-success order-2 mx-1" name="update_category" value="'.$lang['update'].'">';
    $hidden_field = "<input type='hidden' name='editcat' value='$editcat'>";
}

/* delete category */

if(isset($_POST['delete_category'])) {

    $delete_id = (int) $_POST['editcat'];

    $data = $db_content->delete("se_categories", [
        "cat_id" => $delete_id
    ]);

    unset($_REQUEST['editcat'],$cat_name,$cat_sort,$cat_description,$cat_thumbnail);
}


if(isset($_POST['cat']) && ($_POST['cat'] != '')) {

    // open category

    $editcat = (int) $_POST['cat'];

    $submit_button = '<input type="submit" class="btn btn-success order-2 mx-1" name="update_category" value="'.$lang['update'].'">';
    $delete_button = "<input type='submit' class='btn btn-danger order-1' name='delete_category' value='$lang[delete]' onclick=\"return confirm('$lang[confirm_delete_data]')\">";
    $hidden_field = "<input type='hidden' name='editcat' value='$editcat'>";

    $get_category = $db_content->get("se_categories","*",[
        "AND" => [
            "cat_id" => "$editcat"
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

    echo '<div class="card p-3">';

    echo '<form action="?tn=categories" method="POST">';

    echo '<div class="row">';
    echo '<div class="col-md-9">';

    echo '<div class="form-group">';
    echo '<label>'.$lang['category_name'].'</label>';
    echo '<input type="text" class="form-control" name="cat_name" value="'.$cat_name.'">';
    echo '</div>';

    echo '</div>';
    echo '<div class="col-md-3">';

    echo '<div class="form-group">';
    echo '<label>'.$lang['category_priority'].'</label>';
    echo '<input type="text" class="form-control" name="cat_sort" value="'.$cat_sort.'">';
    echo '</div>';

    echo '</div>';
    echo '</div>';


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

    echo '<div class="row">';
    echo '<div class="col-md-9">';

    echo '<div class="form-group">';
    echo '<label>'.$lang['category_thumbnail'].'</label>';
    echo $choose_tmb;
    echo '</div>';

    echo '</div>';
    echo '<div class="col-md-3">';

    if($cat_lang == '' && $default_lang_code != '') {
        $cat_lang = $default_lang_code;
    }

    $select_cat_language  = '<select name="cat_lang" class="custom-select form-control">';
    foreach($lang_codes as $lang_code) {
        $select_cat_language .= "<option value='$lang_code'".($cat_lang == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";
    }
    $select_cat_language .= '</select>';


    echo '<div class="form-group">';
    echo '<label>'.$lang['f_page_language'].'</label>';
    echo $select_cat_language;
    echo '</div>';

    echo '</div>';
    echo '</div>';


    echo '<div class="form-group">';
    echo '<label>'.$lang['category_description'].'</label>';
    echo "<textarea class='form-control' rows='8' name='cat_description'>$cat_description</textarea>";
    echo '</div>';



    echo '<div class="formfooter d-flex">';
    echo '<a href="?tn=categories" class="btn btn-default me-auto">'.$lang['nav_overview'].'</a>';
    echo $submit_button;
    echo $delete_button;
    echo $hidden_field;
    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '</div>';

    echo '</form>';
    echo '</div>';


} else {


    /* list categories */

    echo '<div class="card p-3">';

    $all_categories = se_get_categories();
    $cnt_categories = count($all_categories);

    echo '<table class="table">';

    foreach ($all_categories as $cats) {

        $show_thumb = '';
        $flag = '<img src="/lib/lang/' . $cats['cat_lang'] . '/flag.png" width="15">';

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
        echo '<button name="cat" value=' . $cats['cat_id'] . '" class="btn btn-sm btn-default">' . $icon['edit'] . ' ' . $lang['edit'] . '</button>';
        echo $hidden_csrf_token;
        echo '</form>';


        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';

    echo '</div>';
}