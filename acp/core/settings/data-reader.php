<?php

if(isset($_POST['load_settings']) && ($_POST['load_settings'] == 'general')) {
    echo 'FOO';
}

if(isset($_POST['load_labels'])) {


    $writer_uri = '/admin/settings/labels/write/';
    $se_labels = se_get_labels();
    $cnt_labels = count($se_labels);


    for($i=0;$i<$cnt_labels;$i++) {
        echo '<form>';
        echo '<div class="row mb-1" id="row_'.$i.'">';
        echo '<div class="col-2">';
        echo '<div class="input-group">';
        echo '<span class="input-group-text" id="basic-addon1">#</span>';
        echo '<input class="form-control" type="text" name="label_id" value="'.$se_labels[$i]['label_id'].'" readonly>';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-2">';

        echo '<div class="input-group">';
        echo '<input type="color" class="form-control form-control-color" style="max-width:45px;" name="label_color" value="'.$se_labels[$i]['label_color'].'" title="Choose your color">';
        echo '<input class="form-control" type="text" name="label_title" value="'.$se_labels[$i]['label_title'].'">';
        echo '</div>';

        echo '</div>';
        echo '<div class="col">';
        echo '<input class="form-control" type="text" name="label_description" value="'.$se_labels[$i]['label_description'].'">';
        echo '<div class="update-response-'.$i.'"></div>';
        echo '</div>';
        echo '<div class="col-2">';
        echo '<input type="hidden" name="label_id" value="'.$se_labels[$i]['label_id'].'">';
        echo '<div class="btn-group d-flex" role="group">';
        echo '<button hx-post="'.$writer_uri.'" hx-target="#page-content" hx-swap="afterbegin" name="update_label" class="btn btn-default w-100 text-success">'.$icon['sync_alt'].'</button>';
        echo '<button hx-post="'.$writer_uri.'" hx-delete="'.$se_labels[$i]['label_id'].'" hx-target="#row_'.$i.'" hx-swap="outerHTML swap:0.1s" name="delete_label" class="btn btn-default w-100 text-danger">' .$icon['trash_alt'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</form>';

    }



}