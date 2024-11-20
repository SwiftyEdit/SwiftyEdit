<?php

//error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);

$writer_uri = '/admin/categories/edit/';
$delete_uri = '/admin/categories/delete/';
$reader_uri = '/admin/categories/read/';

print_r($_REQUEST);

/*
if(is_numeric($q['filename'])){
    $cat_id = (int) $q['filename'];
    $cat_name = 'hoooooooooooooooooo';

}
*/


if(isset($_POST['delete'])) {
    echo '<hr>DELETE<hr>';
    print_r($_POST);
    exit;
}


if($_REQUEST['action'] == 'list') {

    $all_categories = se_get_categories();
    $cnt_categories = count($all_categories);
    $redeclare_array = array();

    echo '<table class="table">';

    foreach ($all_categories as $cats) {


        $redeclare_array += [
            $cats['cat_id'] => $cats['cat_hash']
        ];

        $flag = '<img src="/assets/lang/' . $cats['cat_lang'] . '/flag.png" width="15">';

        $show_thumb = '';

        if ($cats['cat_thumbnail'] != '') {
            $cat_thumb = '/'.$cats['cat_thumbnail'];
            $show_thumb = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\'' . $cat_thumb . '\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url(' . $cat_thumb . ');">';
            $show_thumb .= '</div>';
        } else {
            $show_thumb .= '<div class="show-thumb" style="background-image: url(\'/assets/themes/administration/images/no-image.png\');">';
        }

        $delete_btn = '<button name="delete" class="btn btn-default text-danger" hx-post="'.$delete_uri.$cats['cat_id'].'/">'.$icon['trash_alt'].'</button>';

        echo '<tr id="id_'.$cats['cat_hash'].'">';
        echo '<td>#'.$cats['cat_id'].'</td>';
        echo '<td width="50">' . $show_thumb . '</td>';
        echo '<td>';
        echo '<h5 class="card-title">' . $flag . ' <small>' . $cats['cat_sort'] . '</small> | ' . $cats['cat_name'] . '</h5>';
        echo $cats['cat_description'];
        echo '</td>';
        echo '<td class="text-end">';
        echo $delete_btn;
        echo '<a class="btn btn-default text-success" href="'.$writer_uri.$cats['cat_id'].'/">'.$icon['edit'].'</a>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';
}