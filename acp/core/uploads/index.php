<?php

$reader_uri = '/admin/uploads/read/';
$writer_uri = '/admin/uploads/write/';

include 'functions.php';

$q = pathinfo($_REQUEST['query']);

echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>'.$lang['uploads'].'</h3>';
echo '<a href="/admin/categories/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

echo '<div id="response"></div>';

if($q['dirname'] == 'uploads/edit' && is_numeric($q['filename'])) {
    $get_media_id = (int) $q['filename'];
    include 'form.php';
}


include 'select_directory.php';


// show existing uploads

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getUploads" class="card p-3" hx-post="'.$reader_uri.'?action=list" hx-trigger="load, changed, updated_global_filter from:body, update_uploads_list from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo'</div>';

echo '</div>';
echo '<div class="col-md-3">';

// sidebar

echo '<div class="card p-3">';

echo '<form hx-post="'.$writer_uri.'" hx-swap="none" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="uploads_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

if(isset($_SESSION['uploads_text_filter']) AND $_SESSION['uploads_text_filter'] != "") {
    unset($all_filter);
    $all_filter = explode(" ", $_SESSION['uploads_text_filter']);

    foreach($all_filter as $f) {
        if($_REQUEST['rm_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="'.$writer_uri.'" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
    }
}

if(isset($btn_remove_keyword)) {
    echo '<div class="d-inline">';
    echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
    echo '</div><hr>';
}

echo '</div>';

echo '<div class="card my-1">';
echo '<div class="card-header d-flex justify-content-between">';
echo $lang['sorting'];
//echo '<a class="btn btn-sm btn-default" href="acp.php?tn='.$tn.'&sub=browse&d='.$disk.'&sort_direction=1">'. show_sort_arrow() .'</a>';
echo '<button class="btn btn-sm btn-default" hx-post="'.$writer_uri.'" hx-swap="none" name="sorting" value="direction" hx-include="[name=\'csrf_token\']">'.show_sort_arrow() .'</button>';
echo '</div>';

echo '<div class="list-group list-group-flush">';
echo '<button class="list-group-item list-group-item-action" hx-post="'.$writer_uri.'" hx-swap="none" name="sorting" value="date" hx-include="[name=\'csrf_token\']">'.$lang['date_of_change'].'</button>';
echo '<button class="list-group-item list-group-item-action" hx-post="'.$writer_uri.'" hx-swap="none" name="sorting" value="name" hx-include="[name=\'csrf_token\']">'.$lang['filename'].'</button>';
echo '<button class="list-group-item list-group-item-action" hx-post="'.$writer_uri.'" hx-swap="none" name="sorting" value="size" hx-include="[name=\'csrf_token\']">'.$lang['filesize'].'</button>';

echo '</div>';

echo '</div>';

echo '<div class="card mt-2">';
echo '<div class="card-header">Database</div>';
echo '<div class="card-body">';
echo '<div id="responseRebase"></div>';

echo '<button class="btn btn-default w-100" name="rebase" value="files_to_database" hx-post="'.$writer_uri.'" hx-target="#responseRebase" hx-swap="innerHTML" hx-include="[name=\'csrf_token\']">Rebase DB</button> ';

echo '</div>';
echo '</div>';


echo '</div>';
echo '</div>';