<?php


if($_REQUEST['action'] == 'list') {

    $all_categories = se_get_categories();
    $cnt_categories = count($all_categories);
    $redeclare_array = array();

    echo '<table class="table">';

    foreach ($all_categories as $cats) {


        $redeclare_array += [
            $cats['cat_id'] => $cats['cat_hash']
        ];

        $flag = '<img src="'.return_language_flag_src($cats['cat_lang']).'" width="15">';

        $show_thumb = '';

        if ($cats['cat_thumbnail'] != '') {
            $cat_thumb = '/'.$cats['cat_thumbnail'];
            $show_thumb = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\'' . $cat_thumb . '\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url(' . $cat_thumb . ');">';
            $show_thumb .= '</div>';
        } else {
            $show_thumb .= '<div class="show-thumb" style="background-image: url(\'/assets/themes/administration/images/no-image.png\');">';
        }

        $delete_btn = '<button name="delete" value="'.$cats['cat_id'].'" class="btn btn-sm btn-default text-danger" 
                            hx-post="/admin/categories/write/"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-swap="none"
                            >'.$icon['trash_alt'].'</button>';

        echo '<tr id="id_'.$cats['cat_hash'].'">';
        echo '<td>#'.$cats['cat_id'].'</td>';
        echo '<td width="50">' . $show_thumb . '</td>';
        echo '<td>';
        echo '<h5 class="card-title">' . $flag . ' <small>' . $cats['cat_sort'] . '</small> | ' . $cats['cat_name'] . '</h5>';
        echo $cats['cat_description'];
        echo '</td>';
        echo '<td class="text-end">';
        echo $delete_btn;
        echo '<button hx-post="/admin/categories/read/" hx-swap="innerHTML" hx-target="#categoryForm" class="btn btn-default btn-sm text-success" name="open_category" value="'.$cats['cat_id'].'">'.$icon['edit'].'</button> ';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';
}

if($_REQUEST['action'] == 'show_category_form') {
    $show_form = true;
}

if(isset($_REQUEST['open_category'])) {

    $get_cat_id = (int) $_REQUEST['open_category'];
    $get_category = $db_content->get("se_categories","*",[
        "cat_id" => "$get_cat_id"
    ]);

    $show_form = true;
}

if($show_form) {
    include 'form.php';
}