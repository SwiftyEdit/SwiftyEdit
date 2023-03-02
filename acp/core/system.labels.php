<?php

//prohibit unauthorized access
require 'core/access.php';

foreach($_POST as $key => $val) {
	$$key = sanitizeUserInputs($val);
}

/* update labels */

if (isset($_POST['update_label'])) {

    if($label_custom_id == '') {
        $label_custom_id = clean_filename($label_title);
    }

    $data = $db_content->update("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ], [
        "label_id" => $label_id
    ]);
}


/* new label */

if (isset($_POST['new_label'])) {

    $label_custom_id = clean_filename($label_title);

    $data = $db_content->insert("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ]);

}

/* delete label */

if(isset($_POST['delete_label'])) {

	$label_id = (int) $_POST['label_id'];

	$data = $db_content->delete("se_labels", [
		"label_id" => $label_id
		]);
	
}


echo '<div class="subHeader">';
echo '<h3>'.$lang['labels'].'</h3>';
echo '</div>';

$se_labels = se_get_labels();
$cnt_labels = count($se_labels);

echo '<div class="card p-3">';

for($i=0;$i<$cnt_labels;$i++) {
	echo '<form action="acp.php?tn=system&sub=labels" method="POST" class="clearfix" id="labels">';
	echo '<div class="row mb-1">';
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
	echo '</div>';
	echo '<div class="col-2">';
	echo '<input type="hidden" name="label_id" value="'.$se_labels[$i]['label_id'].'">';
	echo '<div class="btn-group d-flex" role="group">';
	echo '<button type="submit" name="update_label" class="btn btn-default w-100 text-success">'.$icon['sync_alt'].'</button>';
	echo '<button type="submit" name="delete_label" class="btn btn-default w-100 text-danger">' .$icon['trash_alt'].'</button>';
	echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

	echo '</div>';
	echo '</div>';
	
	echo '</div>';
	echo '</form>';
	
	if($i == $cnt_labels-1) {
		echo '<hr>';
	}
	
}


echo '<form action="acp.php?tn=system&sub=labels" method="POST" class="form-horizontal">';
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
echo '<button type="submit" name="new_label" class="btn btn-success w-100">'.$lang['save'].'</button>';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</form>';

echo '</div>';