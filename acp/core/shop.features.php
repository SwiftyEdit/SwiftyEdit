<?php

/**
 * SwiftyEdit - manage product features and options
 *
 * backend global variables
 * @var $hidden_csrf_token
 * @var $db_content
 * @var $icon
 * @var $lang
 * @var $lang_codes
 */

error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
//prohibit unauthorized access
require __DIR__.'/access.php';

if(isset($_POST['switch_show']) && $_POST['switch_show'] == 'show_features') {
    $_SESSION['switch_product_features'] = 'show_features';
}

if(isset($_POST['switch_show']) && $_POST['switch_show'] == 'show_options') {
    $_SESSION['switch_product_features'] = 'show_options';
}

if(!isset($_SESSION['switch_product_features'])) {
    $_SESSION['switch_product_features'] = 'show_features';
}

if($_SESSION['switch_product_features'] == 'show_features') {
    $sel_features = 'active';
    $sel_options = '';
} else {
    $sel_features = '';
    $sel_options = 'active';
}

echo '<div class="subHeader d-flex">';
echo '<form action="?tn=shop&sub=shop-features" method="POST">';
echo '<div class="btn-group" role="group">';
echo '<button type="submit" class="btn btn-default '.$sel_features.'" name="switch_show" value="show_features">Features</button>';
echo '<button type="submit" class="btn btn-default '.$sel_options.'" name="switch_show" value="show_options">Options</button>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';

/**
 * write data - features
 * update or new entry
 */
if(isset($_POST['send_features_data'])) {

	$lastedit = time();
	$feature_title = se_return_clean_value($_POST['feature_title']);
	$feature_text = $_POST['feature_text'];
	$feature_priority = (int) $_POST['feature_priority'];
	$feature_lang = $_POST['select_lang'];

	if(is_numeric($_POST['send_features_data'])) {
		$edit_id = (int) $_POST['send_features_data'];
		
		$db_content->update("se_snippets",[
			"snippet_title" => $feature_title,
			"snippet_content" => $feature_text,
			"snippet_priority" => $feature_priority,
			"snippet_lastedit" => $lastedit,
			"snippet_lang" => $feature_lang
			],[
			"AND" => [
				"snippet_type" => "post_feature",
				"snippet_id" => "$edit_id"
			]
		]);		
	} else {
		
		$db_content->insert("se_snippets", [
			"snippet_title" => $feature_title,
			"snippet_content" => $feature_text,
			"snippet_priority" => $feature_priority,
			"snippet_lastedit" => $lastedit,
			"snippet_lang" => $feature_lang,
			"snippet_type" => 'post_feature'
		]);
		
		$edit_id = $db_content->id();
		
	}
	
}

/**
 * write data - features
 * update or new entry
 */
if(isset($_POST['send_options_data'])) {
    $lastedit = time();
    $option_title = se_return_clean_value($_POST['option_title']);
    $option_text = array_filter($_POST['option_text']);
    $option_text = json_encode($option_text,JSON_FORCE_OBJECT);
    $option_priority = (int) $_POST['option_priority'];
    $option_lang = $_POST['select_lang'];



    if(is_numeric($_POST['send_options_data'])) {
        $edit_id = (int) $_POST['send_options_data'];

        $db_content->update("se_snippets",[
            "snippet_title" => $option_title,
            "snippet_content" => $option_text,
            "snippet_priority" => $option_priority,
            "snippet_lastedit" => $lastedit,
            "snippet_lang" => $option_lang
        ],[
            "AND" => [
                "snippet_type" => "post_option",
                "snippet_id" => "$edit_id"
            ]
        ]);
    } else {

        $db_content->insert("se_snippets", [
            "snippet_title" => $option_title,
            "snippet_content" => $option_text,
            "snippet_priority" => $option_priority,
            "snippet_lastedit" => $lastedit,
            "snippet_lang" => $option_lang,
            "snippet_type" => 'post_option'
        ]);

        $edit_id = $db_content->id();

    }
}


/* show or hide the form */
$show_form = false;

if(isset($_GET) && $_GET['edit'] == 'new-feature') {
	$show_form = 'edit-features';
	$mode = 'new';
	$btn_send = '<button class="btn btn-success" name="send_features_data" value="new">'.$lang['save'].'</button>';
}

if(isset($_GET) && $_GET['edit'] == 'new-option') {
    $show_form = 'edit-options';
    $mode = 'new';
    $btn_send = '<button class="btn btn-success" name="send_options_data" value="new">'.$lang['save'].'</button>';
}


if(isset($_POST['edit']) OR is_numeric($edit_id)) {

	$mode = 'edit';
	
	if($edit_id == '') {
		$edit_id = (int) $_POST['edit'];
	}

    if($_SESSION['switch_product_features'] == 'show_features') {
        $type = 'post_feature';
        $show_form = 'edit-features';
    } else {
        $type = 'post_option';
        $show_form = 'edit-options';
    }
	
	$snippet_data = $db_content->get("se_snippets","*",[

		"AND" => [
			"snippet_type" => "$type",
			"snippet_id" => $edit_id
		]
	]);
	
	$feature_title = html_entity_decode($snippet_data['snippet_title']);
	$feature_text = $snippet_data['snippet_content'];
	$feature_priority = $snippet_data['snippet_priority'];
    $feature_lang = $snippet_data['snippet_lang'];

    if($type == 'post_option') {
        $option_text_array = json_decode($snippet_data['snippet_content']);
    }

}

if(isset($_POST['delete']) AND is_numeric($_POST['delete'])) {
    $delete_id = (int) $_POST['delete'];
    $snippet_delete = $db_content->delete("se_snippets",[
        "AND" => [
            "snippet_type" => "post_feature",
            "snippet_id" => $delete_id
        ]
    ]);
}

if($show_form !== false) {
    
    if($show_form == 'edit-features'){
        $form_edit = file_get_contents('templates/post_features_form.tpl');
        $value_lang = $feature_lang;
        $btn_send = '<button class="btn btn-success" name="send_features_data" value="'.$edit_id.'">'.$lang['update'].'</button>';
    } else {
        $form_edit = file_get_contents('templates/post_options_form.tpl');
        $value_lang = $option_lang;
        $btn_send = '<button class="btn btn-success" name="send_options_data" value="'.$edit_id.'">'.$lang['update'].'</button>';

        $inputs_tpl = '';
        foreach($option_text_array as $option_value) {
            $inputs_tpl .= '<input type="text" name="option_text[]" value="'.$option_value.'" class="form-control">';
        }
        $form_edit = str_replace('{option_text_inputs}',$inputs_tpl, $form_edit);


    }

	$select_lang  = '<select name="select_lang" class="custom-select form-control">';
	foreach($lang_codes as $lang_code) {
		$select_lang .= "<option value='$lang_code'".($value_lang == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";
	}
	$select_lang .= '</select>';
	
	$form_edit = str_replace('{feature_title}',$feature_title, $form_edit);
	$form_edit = str_replace('{feature_text}',$feature_text, $form_edit);
	$form_edit = str_replace('{feature_priority}',$feature_priority, $form_edit);
	$form_edit = str_replace('{select_lang}',$select_lang, $form_edit);
	
	$form_edit = str_replace('{label_language}',$lang['label_language'], $form_edit);
	$form_edit = str_replace('{label_title}',$lang['label_title'], $form_edit);
	$form_edit = str_replace('{label_text}',$lang['label_text'], $form_edit);
    $form_edit = str_replace('{label_value}',$lang['label_value'], $form_edit);
	$form_edit = str_replace('{label_priority}',$lang['label_priority'], $form_edit);
	
	$form_edit = str_replace('{hidden_csrf}',$hidden_csrf_token, $form_edit);
	$form_edit = str_replace('{btn_send_form}',$btn_send, $form_edit);	
	echo $form_edit;
}



/**
 * list entries
 */

if($_SESSION['switch_product_features'] == 'show_features') {
    $show_data = se_get_posts_features();
} else {
    $show_data = se_get_posts_options();
}
$cnt_data = count($show_data);

echo '<div class="app-container">';
echo '<div class="max-height-container">';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="scroll-box">';

echo '<div class="card p-3">';

echo '<table class="table table-sm">';
echo '<tr>';
echo '<td>#</td>';
echo '<td>'.$lang['label_priority'].'</td>';
echo '<td>'.$lang['label_language'].'</td>';
echo '<td>'.$lang['label_text'].'</td>';
echo '<td></td>';
echo '</tr>';
for($i=0;$i<$cnt_data;$i++) {



    if($_SESSION['switch_product_features'] == 'show_options') {
        $get_show_values = json_decode($show_data[$i]['snippet_content']);
        $show_values = '';
        foreach($get_show_values as $value) {
            $show_values .= '<span class="badge badge-secondary">'.$value.'</span> ';
        }
    } else {
        $show_values = $show_data[$i]['snippet_content'];
    }

	echo '<tr>';
    echo '<td>'.$show_data[$i]['snippet_id'].'</td>';
	echo '<td>'.$show_data[$i]['snippet_priority'].'</td>';
	echo '<td>'.$show_data[$i]['snippet_lang'].'</td>';
	echo '<td><strong>'.$show_data[$i]['snippet_title'].'</strong><br>'.$show_values.'</td>';
	echo '<td class="text-end" style="width:120px;">';
	echo '<form action="?tn=shop&sub=shop-features" method="POST">';
	echo '<button type="submit" class="btn btn-default text-success" name="edit" value="'.$show_data[$i]['snippet_id'].'">'.$icon['edit'].'</button> ';
	echo '<button type="submit" class="btn btn-default text-danger" name="delete" value="'.$show_data[$i]['snippet_id'].'">'.$icon['trash_alt'].'</button>';
	echo $hidden_csrf_token;
	echo '</form>';
	echo '</td>';
	echo '</tr>';
}

echo '</table>';

echo '</div>'; // card
echo '</div>'; // scroll-box

echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card p-2">'; // sidebar
if($sel_features == 'active') {
    echo '<a href="?tn=shop&sub=shop-features&edit=new-feature" class="btn btn-success w-100 mb-1">' . $lang['btn_new_feature'] . '</a>';
} else {
    echo '<a href="?tn=shop&sub=shop-features&edit=new-option" class="btn btn-success w-100">' . $lang['btn_new_option'] . '</a>';
}
echo '</div>'; // card

echo '</div>'; // col
echo '</div>'; //row


echo '</div>'; // .max-height-container
echo '</div>'; // .app-container