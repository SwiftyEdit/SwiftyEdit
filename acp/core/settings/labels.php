<?php

echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['labels'].'</div>';

$writer_uri = '/admin/xhr/settings/labels/write/';
$reader_uri = '/admin/xhr/settings/labels/read/';

echo '<div id="getLabels" class="card p-3" hx-post="'.$reader_uri.'" hx-trigger="load, changed, updated_labels from:body" hx-include="[name=\'csrf_token\']" hx-vals=\'{"load_labels": "labels"}\'>';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo'</div>';

echo '<hr>';

echo '<form id="create_label" name="create_label" hx-post="'.$writer_uri.'" hx-target="#getLabels" hx-on::after-request="this.reset()">';
echo '<div class="row">';
echo '<div class="col-md-4">';

echo '<div class="input-group">';
echo '<input type="color" class="form-control form-control-color" name="label_color" value="#3366cc" title="Choose your color">';
echo '<input class="form-control" type="text" name="label_title" value="" placeholder="'.$lang['label_title'].'" required="">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-6">';

echo '<input class="form-control" type="text" name="label_description" value="" placeholder="'.$lang['label_description'].'">';
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" id="new_label" name="post_label" value="new" class="btn btn-default w-100 text-success">'.$lang['save'].'</button>';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</form>';


