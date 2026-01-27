<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 */

if($_REQUEST['action'] == 'list') {

    $all_categories = se_get_categories();
    $cnt_categories = count($all_categories);
    $redeclare_array = array();

    echo '<table class="table">';

    foreach ($all_categories as $cats) {

        $redeclare_array += [
            $cats['cat_id'] => $cats['cat_hash']
        ];

        $hx_vals = [
            "csrf_token"=> $_SESSION['token']
        ];

        $flag = '<img src="'.return_language_flag_src($cats['cat_lang']).'" width="15">';

        $category_images = explode('<->',$cats['cat_thumbnail']);
        $show_thumb = '';
        if(count($category_images) > 1) {
            $x=0;
            foreach($category_images as $img) {
                $img = str_replace('../content/','/',$img);
                if($img != '') {
                    $x++;
                    $show_thumb .= '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-title="'.$img.'" data-bs-content="<img src=\''.$img.'\'>">'.$icon['images'].'</a> ';
                }
                if($x>2) {
                    $show_thumb .= '<small>(...)</small>';
                    break;
                }
            }
        }

        $delete_btn = '<button name="delete" value="'.$cats['cat_id'].'" class="btn btn-sm btn-default text-danger" 
                            hx-post="/admin-xhr/categories/write/"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-swap="none"
                            hx-vals=\''.json_encode($hx_vals).'\'
                            >'.$icon['trash_alt'].'</button>';

        $edit_button  = '<form action="/admin/categories/edit/" method="post" class="d-inline">';
        $edit_button .= '<button class="btn btn-sm btn-default text-success" name="category_id" value="'.$cats['cat_id'].'">'.$icon['edit'].'</button>';
        $edit_button .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $edit_button .=  '</form>';

        echo '<tr id="id_'.$cats['cat_hash'].'">';
        echo '<td>#'.$cats['cat_id'].'</td>';
        echo '<td width="50">' . $show_thumb . '</td>';
        echo '<td>';
        echo '<h5 class="card-title">' . $flag . ' <small>' . $cats['cat_sort'] . '</small> | ' . $cats['cat_name'] . '</h5>';
        echo '<p>'.$cats['cat_description'].'</p>';
        echo '<code>'.$cats['cat_name_clean'].'</code>';
        echo '</td>';
        echo '<td class="text-end text-nowrap">';
        echo $delete_btn;
        echo $edit_button;
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';
    exit;
}