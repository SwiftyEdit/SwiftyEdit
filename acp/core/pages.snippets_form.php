<?php

//prohibit unauthorized access
require 'core/access.php';

if($_POST['snip_id'] == 'n') {
	$modus = 'new';
} else {
	$modus = 'update';
}

if(!empty($_POST['snip_id_duplicate'])) {
	$snip_id = (int) $_POST['snip_id_duplicate'];
	$modus = "duplicate";
}

/**
 * open snippet
 */

if(!isset($snip_id)) {
	$snip_id = (int) $_POST['snip_id'];
}

$result = $db_content->get("se_snippets", "*", [ "snippet_id" => $snip_id ]);

if(is_array($result)) {
	foreach($result as $k => $v) {
				$$k = htmlspecialchars(stripslashes($v));
	}
}


echo '<div class="subHeader">'.$lang['nav_btn_snippets'].' <span class="badge badge-default">'.$modus.'</span></div>';

echo "<form action='acp.php?tn=pages&sub=snippets' method='POST'>";

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="card">';
echo '<div class="card-header">';

echo '<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">';
echo '<li class="nav-item"><a class="nav-link active" href="#content" data-bs-toggle="tab">'.$lang['label_content'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#images" data-bs-toggle="tab">'.$lang['images'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#link" data-bs-toggle="tab">'.$lang['label_url'].'</a></li>';
echo '</ul>';

echo '</div>';
echo '<div class="card-body">';

echo '<div class="tab-content">';

echo '<div class="tab-pane fade show active" id="content">';

echo '<div class="row">';
echo '<div class="col-md-4">';

echo '<div class="mb-3">';
echo '<label class="form-label">'.$lang['filename'].' <small>(a-z,0-9)</small></label>';
echo '<input class="form-control" type="text" name="snippet_name" value="'.$snippet_name.'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-8">';

echo '<div class="mb-3">';
echo '<label class="form-label">'.$lang['label_title'].'</label>';
echo '<input class="form-control" type="text" name="snippet_title" value="'.html_entity_decode($snippet_title).'">';
echo '</div>';

echo '</div>';
echo '</div>';



echo '<div class="form-group">';
echo '<div class="btn-group float-end pb-1" role="group">';
echo '<label class="btn btn-sm btn-default"><input type="radio" class="btn-check" name="optEditor" value="optE1"> WYSIWYG</label>';
echo '<label class="btn btn-sm btn-default"><input type="radio" class="btn-check" name="optEditor" value="optE2"> Text</label>';
echo '<label class="btn btn-sm btn-default"><input type="radio" class="btn-check" name="optEditor" value="optE3"> Code</label>';
echo '</div>';
echo '</div>';

echo '<textarea class="form-control mceEditor switchEditor" id="textEditor" name="snippet_content">'.$snippet_content.'</textarea>';
echo '<input type="hidden" name="text" value="'.$text.'">';



echo '<div class="row mt-3">';
echo '<div class="col-md-4">';

echo '<div class="form-group">';
echo '<label>'.$lang['label_keywords'].'</label>';
echo '<input class="form-control tags" type="text" name="snippet_keywords" value="'.html_entity_decode($snippet_keywords).'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-4">';

echo '<div class="form-group">';
echo '<label>'.$lang['label_classes'].'</label>';
echo '<input class="form-control" type="text" name="snippet_classes" value="'.$snippet_classes.'" />';
echo '</div>';

echo '</div>';
echo '<div class="col-md-4">';

echo '<div class="form-group">';
echo '<label>Label</label>';
echo '<input class="form-control" type="text" name="snippet_label" value="'.html_entity_decode($snippet_label).'" />';
echo '</div>';

echo '</div>';
echo '</div>';



echo '</div>';
echo '<div class="tab-pane fade" id="images">';


$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');

$snippet_thumbnail_array = explode("&lt;-&gt;", $snippet_images);

echo '<input class="filter-images form-control" name="filter-images" placeholder="Filter ..." type="text">';


$choose_images = se_select_img_widget($images,$snippet_thumbnail_array,$prefs_pagethumbnail_prefix,1);
// picker1_images[]
echo $choose_images;

echo '</div>'; // images
echo '<div class="tab-pane fade" id="link">';

echo '<div class="form-group mt-2">';
echo '<label>'.$lang['label_url'].'</label>';
echo '<input class="form-control" type="text" name="snippet_permalink" value="'.$snippet_permalink.'" />';
echo '</div>';

echo '<div class="form-group mt-2">';
echo '<label>'.$lang['label_link_name'].'</label>';
echo '<input class="form-control" type="text" name="snippet_permalink_name" value="'.html_entity_decode($snippet_permalink_name).'" />';
echo '</div>';

echo '<div class="form-group mt-2">';
echo '<label>'.$lang['label_title'].'</label>';
echo '<input class="form-control" type="text" name="snippet_permalink_title" value="'.html_entity_decode($snippet_permalink_title).'" />';
echo '</div>';

echo '<div class="form-group mt-2">';
echo '<label>'.$lang['label_classes'].'</label>';
echo '<input class="form-control" type="text" name="snippet_permalink_classes" value="'.$snippet_permalink_classes.'" />';
echo '</div>';

echo '</div>'; // link





echo '</div>';
echo '</div>';
echo '</div>';



if($snippet_name != '') {
	$get_snip_name_editor = '[snippet]'.$snippet_name.'[/snippet]';
	echo '<hr><label>Snippet</label>';
	echo '<div class="input-group">';
	echo '<input type="text" class="form-control" id="copy_snip" placeholder="[snippet]...[/snippet]" value="'.$get_snip_name_editor.'" readonly>';
	echo '<button type="button" class="btn btn-default copy-btn" data-clipboard-target="#copy_snip">'.$icon['clipboard'].'</button>';
	echo '</div>';
}

echo '</div>';
echo '<div class="col-md-3">';


echo '<div class="card">';
echo '<div class="card-header">'.$lang['label_settings'].'</div>';
echo '<div class="card-body">';

if($snippet_lang == '' && $default_lang_code != '') {
	$snippet_lang = $default_lang_code;
}

$select_snippet_language  = '<select name="sel_language" class="custom-select form-control">';
foreach($lang_codes as $lang_code) {
	$select_snippet_language .= "<option value='$lang_code'".($snippet_lang == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";	
}
$select_snippet_language .= '</select>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo '<div class="form-group">';
echo '<label>'.$lang['label_language'].'</label>';
echo $select_snippet_language;
echo '</div>';

echo '</div>';
echo '<div class="col-md-6">';

echo '<div class="form-group">';
echo '<label>'.$lang['label_priority'].'</label>';
echo '<input class="form-control" type="text" name="snippet_priority" value="'.$snippet_priority.'">';
echo '</div>';

echo '</div>';
echo '</div>';

/* Select Template */

$arr_Styles = get_all_templates();

$select_select_template = '<select id="select_template" name="select_template"  class="custom-select form-control">';

if($snippet_template == '') {
	$selected_standard = 'selected';
}

$select_select_template .= "<option value='use_standard<|-|>use_standard' $selected_standard>$lang[label_use_default]</option>";

/* templates list */
foreach($arr_Styles as $template) {

	$arr_layout_tpl = glob("../styles/$template/templates/snippet*.tpl");
	
	$select_select_template .= "<optgroup label='$template'>";
	
	foreach($arr_layout_tpl as $layout_tpl) {
		$layout_tpl = basename($layout_tpl);
	
		$selected = '';
		if($template == "$snippet_theme" && $layout_tpl == "$snippet_template") {
			$selected = 'selected';
		}
		
		$select_select_template .=  "<option $selected value='$template<|-|>$layout_tpl'>$template » $layout_tpl</option>";
	}
	
	$select_select_template .= '</optgroup>';

}

$select_select_template .= '</select>';

echo '<div class="mb-3">';
echo '<label class="form-label">'.$lang['label_template'].'</label>';
echo $select_select_template;
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">'.$lang['label_groups'].'</label>';
echo '<input class="form-control" type="text" name="snippet_groups" value="'.$snippet_groups.'" />';
echo '</div>';


$cnt_labels = count($se_labels);
$arr_checked_labels = explode(",", $snippet_labels);

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
 	$checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="snippet_labels[]" value="'.$label_id.'">';
 	$checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
	$checkbox_set_labels .= '</div>';
}

echo '<div class="form-group">';
echo '<p>'.$lang['labels'].'</p>';
echo $checkbox_set_labels;
echo '</div>';


echo '<div class="alert alert-dark" style="padding:2px 3px;">';
echo '<strong>'.$icon['info_circle'].' '.$lang['label_notes'].':</strong>';
echo '<textarea class="masked-textarea" name="snippet_notes" rows="5">'.html_entity_decode($snippet_notes).'</textarea>';
echo '</div>';

echo '<div class="well well-sm">';
if($modus == 'new') {
	echo '<input type="submit" name="save_snippet" class="btn btn-success w-100" value="'.$lang['save'].'">';
} else if($modus == 'duplicate') {
	echo '<input type="submit" name="save_snippet" class="btn btn-success w-100" value="'.$lang['duplicate'].'">';
} else {
	echo '<input type="hidden" name="snip_id" value="'.$snip_id.'">';
	echo '<input type="submit" name="save_snippet" class="btn btn-success w-100" value="'.$lang['update'].'"> ';
	echo '<div class="mt-1 d-flex">';
	echo '<a class="btn btn-default w-100 mr-1" href="acp.php?tn=pages&sub=snippets">'.$lang['btn_discard'].'</a> ';
	echo '<input type="submit" name="delete_snippet" class="btn btn-default text-danger" value="'.$lang['delete'].'" onclick="return confirm(\''.$lang['confirm_delete_data'].'\')">';
	echo '</div>';
}
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '<input type="hidden" name="modus" value="'.$modus.'">';
echo '</div>';


echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';


echo '</form>';

?>