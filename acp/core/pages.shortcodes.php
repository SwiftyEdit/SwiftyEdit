<?php
//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require 'core/access.php';

$show_form = 'false';

/*save or update shortcode */
if(isset($_POST['write_shortcode'])) {
	
	foreach($_POST as $key => $val) {
		$$key = se_return_clean_value($val);
	}
	
	/* labels */
	$arr_labels = $_POST['shortcode_labels'];
	if(is_array($arr_labels)) {
		sort($arr_labels);
		$string_labels = implode(",", $arr_labels);
	} else {
		$string_labels = "";
	}	
	
	
	if($_POST['shortcode_id'] != '') {
		$db_mode = 'update';
		$shortcode_id = (int) $_POST['shortcode_id'];
	} else {
		$db_mode = 'save';
	}
	
	
	if($db_mode == 'update') {
		// update shorcode
		$data = $db_content->update("se_snippets", [
			"snippet_content" =>  $_POST['longcode'],
			"snippet_shortcode" => $shortcode,
			"snippet_labels" => $string_labels,
			"snippet_type" => "shortcode"
		],[
			"snippet_id" => $shortcode_id
		]);
		
	} else {
		// new shortcode

		$data = $db_content->insert("se_snippets", [
			"snippet_content" =>  $_POST['longcode'],
			"snippet_shortcode" => $shortcode,
			"snippet_labels" => $string_labels,
			"snippet_type" => "shortcode"
		]);
		
		$last_insert_id = $db_content->id();
		
	}
	
	$show_form = 'true';
	
}


/* get data from shortcode by id */

if(isset($_GET['edit'])) {
	$shortcode_id = (int) $_GET['edit'];
	$show_form = 'true';
}

if(isset($_POST['edit_shortcode'])) {
	$get_shortcode_by_name = filter_var($_POST['edit_shortcode'],FILTER_SANITIZE_STRING);
	if($get_shortcode_by_name != '') {
		$get_shortcode = $db_content->get("se_snippets", "*", [
			"snippet_shortcode" => $get_shortcode_by_name
		]);
		$show_form = 'true';
	}
}



if(is_numeric($last_insert_id)) {
	$shortcode_id = (int) $last_insert_id;
	$show_form = 'true';	
}
	
if($shortcode_id != '' && is_numeric($shortcode_id)) {
		$get_shortcode = $db_content->get("se_snippets", "*", [
			"snippet_id" => $shortcode_id
	]);
}

/* delete by id */

if(isset($_POST['delete'])) {
	$del_id = (int) $_POST['delete'];
	$delete = $db_content->delete("se_snippets", [
		"AND" => [
			"snippet_id" => $del_id,
			"snippet_type" => "shortcode"
		]
	]);
}


echo '<div class="subHeader d-flex align-items-center">';
echo '<h3>Shortcodes</h3>';
if($show_form !== 'true') {
    echo '<a href="?tn=pages&sub=shortcodes&edit=new" class="btn btn-default text-success ms-auto">' . $icon['plus'] . ' ' . $lang['new'] . '</a><hr>';
}
echo '</div>';


/* print the form */

if($show_form == 'true') {
	echo '<div class="card p-3 mb-3">';
	echo '<form action="?tn=pages&sub=shortcodes" method="POST">';
	
	echo '<div class="row">';
	echo '<div class="col-md-9">';
	
	echo '<div class="form-group">';
	echo '<label for="elements">'.$lang['shortcode'].'</label>';
	echo '<input type="text" class="form-control" name="shortcode" value="'.$get_shortcode['snippet_shortcode'].'">';
	echo '</div>';
	
	echo '<div class="form-group">';
	echo '<label for="elements">'.$lang['shortcode_replacement'].'</label>';
	echo '<textarea name="longcode" rows="8" class="form-control">'.$get_shortcode['snippet_content'].'</textarea>';
	echo '</div>';
	
	
	
	$cnt_labels = count($se_labels);
	$arr_checked_labels = explode(",", $get_shortcode['snippet_labels']);
	
	for($i=0;$i<$cnt_labels;$i++) {
		$label_title = $se_labels[$i]['label_title'];
		$label_id = $se_labels[$i]['label_id'];
		$label_color = $se_labels[$i]['label_color'];
		
	  if(in_array("$label_id", $arr_checked_labels)) {
			$checked_label = "checked";
		} else {
			$checked_label = "";
		}
		
		$checkbox_set_labels .= '<div class="form-check form-check-inline">';
	 	$checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="shortcode_labels[]" value="'.$label_id.'">';
	 	$checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
		$checkbox_set_labels .= '</div>';
	}
	
	echo '</div>';
	echo '<div class="col-md-3">';
	
	echo '<div class="form-group">';
	echo '<p>'.$lang['labels'].'</p>';
	echo $checkbox_set_labels;
	echo '</div>';
	
	echo '<hr>';
	
	echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
	
	if($get_shortcode['snippet_id'] != '') {
		echo '<input type="hidden" name="shortcode_id" value="'.$get_shortcode['snippet_id'].'">';
		echo '<input type="submit" name="write_shortcode" value="'.$lang['update'].'" class="btn btn-success w-100">';
	} else {
		echo '<input type="submit" name="write_shortcode" value="'.$lang['save'].'" class="btn btn-success w-100">';
	}
	
	echo '</div>';
	echo '</div>';
	
	echo '</form>';
	echo '</div>';
	echo '<hr class="shadow-line">';
}



$label_filter_box  = '<div class="card mt-2">';
$label_filter_box .= '<div class="card-header p-1 px-2">'.$lang['labels'].'</div>';
$label_filter_box .= '<div class="card-body">';
$this_btn_status = '';
foreach($se_labels as $label) {

	if(in_array($label['label_id'], $a_checked_labels)) {
		$this_btn_status = 'active';
	} else {
		$this_btn_status = '';
	}

	$label_title = '<span class="label-dot" style="background-color:'.$label['label_color'].';"></span> '.$label['label_title'];
	$label_filter_box .= '<a href="acp.php?tn=pages&sub='.$sub.'&switchLabel='.$label['label_id'].'" class="btn btn-default btn-sm m-1 '.$this_btn_status.'">'.$label_title.'</a>';

}
$label_filter_box .= '</div>';
$label_filter_box .= '</div>'; // card

$label_filter['labels'] = $_SESSION['checked_label_str'];


/* get all shortcodes */

$shortcodes = se_get_shortcodes($label_filter);
$cnt_shortcodes = count($shortcodes);

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="card p-3">';

echo '<table class="table table-sm">';
echo '<thead>';
echo '<tr>';
echo '<th>'.$lang['shortcode'].'</th>';
echo '<th>'.$lang['shortcode_replacement'].'</th>';
echo '<th>Label</th>';
echo '<th></th>';
echo '</tr>';
echo '</thead>';


for($i=0;$i<$cnt_shortcodes;$i++) {
	
	$btn_edit = '<a href="?tn=pages&sub=shortcodes&edit='.$shortcodes[$i]['snippet_id'].'" class="btn btn-default text-success btn-sm">'.$icon['edit'].'</a>';
	
	$btn_delete  = '<form action="?tn=pages&sub=shortcodes" method="POST" class="d-inline">';
	$btn_delete .= '<button type="submit" name="delete" value="'.$shortcodes[$i]['snippet_id'].'" class="btn btn-default text-danger btn-sm">'.$icon['trash_alt'].'</button>';
	$btn_delete .= $hidden_csrf_token;
	$btn_delete .= '</form>';
	
	$get_sc_labels = explode(',',$shortcodes[$i]['snippet_labels']);
	
	
	$label = '';
	if($shortcodes[$i]['snippet_labels'] != '') {
		foreach($get_sc_labels as $sc_label) {
			
			foreach($se_labels as $l) {
				if($sc_label == $l['label_id']) {
					$label_color = $l['label_color'];
					$label_title = $l['label_title'];
				}
			}
			
			$label .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
		}
	}
	
	$longcode = htmlentities($shortcodes[$i]['snippet_content']);
	if(strlen($longcode) > 75) {
		$longcode = substr($longcode, 0,75). ' <em><small>(...)</small></em>';
	}
	
	$copy_shortcode  = '<div class="input-group">';
	$copy_shortcode .= '<input type="text" class="form-control" id="copy_sc_'.$i.'" value="'.$shortcodes[$i]['snippet_shortcode'].'" readonly>';
	$copy_shortcode .= '<button type="button" class="btn btn-default copy-btn" data-clipboard-target="#copy_sc_'.$i.'">'.$icon['clipboard'].'</button>';
	$copy_shortcode .= '</div>';
		
	echo '<tr>';
	echo '<td>'.$copy_shortcode.'</td>';
	echo '<td><code>'.$longcode.'</code></td>';
	echo '<td>'.$label.'</td>';
	echo '<td class="text-right">'.$btn_edit.' '.$btn_delete.'</td>';
	echo '</tr>';	
}


echo '</table>';

echo '</div>'; // card






echo '</div>';
echo '<div class="col-md-3">';

/* sidebar */

echo $label_filter_box;

/* sidebar end */

echo '</div>';
echo '</div>';


?>