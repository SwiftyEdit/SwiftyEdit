<?php

$reader_uri = '/admin/xhr/uploads/read/';
$writer_uri = '/admin/xhr/uploads/write/';

$q = pathinfo($_REQUEST['query']);

if(!isset($_SESSION['disk'])) {
    $_SESSION['disk'] = 'assets/images/';
}

echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>'.$lang['uploads'].'</h3>';
echo '<button type="button" class="btn btn-default btn-sm text-success ms-auto" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i>Upload</button>';
echo '</div>';

echo '<div id="response"></div>';

if($q['dirname'] == 'uploads/edit' && is_numeric($q['filename'])) {
    $get_media_id = (int) $q['filename'];
    include 'form.php';
}


echo '<div class="card p-3 mb-1">';
echo '<div class="row">';
echo '<div class="col-md-8">';
echo '<div id="selDirectory" hx-get="'.$reader_uri.'?action=select_directory" hx-trigger="load, changed, updated_global_filter from:body, update_uploads_list from:body"></div>';
echo '</div>';
echo '<div class="col-md-4">';
echo '<div id="newDirectory" hx-get="'.$reader_uri.'?action=input_new_directory" hx-trigger="load, changed, updated_global_filter from:body, update_uploads_list from:body"></div>';
echo '</div>';
echo '</div>';
echo '</div>';


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

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches" hx-trigger="load, changed, update_uploads_list from:body, updated_global_filter from:body"></div>';

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

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=show_stats" hx-trigger="load, changed, update_uploads_list from:body, updated_global_filter from:body"></div>';

echo '<div id="responseRebase"></div>';

echo '<button class="btn btn-default w-100" name="rebase" value="files_to_database" hx-post="'.$writer_uri.'" hx-target="#responseRebase" hx-swap="innerHTML" hx-include="[name=\'csrf_token\']">Rebase DB</button> ';

echo '</div>';
echo '</div>';


echo '</div>';
echo '</div>';