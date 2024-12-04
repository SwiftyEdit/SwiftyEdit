<?php

/**
 * SwiftyEdit form templates
 * print text input, textarea, checkbox, radio, radios ...
 * from arrays
 */
//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
$tpl_dir = '../acp/templates/';

$bs_form_checkbox = file_get_contents($tpl_dir.'/bs-form-checkbox.tpl');
$bs_form_radio = file_get_contents($tpl_dir.'/bs-form-radio.tpl');
$bs_form_select = file_get_contents($tpl_dir.'/bs-form-select.tpl');
$bs_form_input_text = file_get_contents($tpl_dir.'bs-form-input-text.tpl');
$bs_form_input_textarea = file_get_contents($tpl_dir.'/bs-form-input-textarea.tpl');
$bs_form_control_group = file_get_contents($tpl_dir.'/bs-form-control-group.tpl');

$bs_row_col2 = file_get_contents($tpl_dir.'/bs-row-col2.tpl');
$bs_row_col3 = file_get_contents($tpl_dir.'/bs-row-col3.tpl');

$bs_row_2_cols = file_get_contents($tpl_dir.'/bs-row-2-cols.tpl');
$bs_row_3_cols = file_get_contents($tpl_dir.'/bs-row-3-cols.tpl');
$bs_row_4_cols = file_get_contents($tpl_dir.'/bs-row-4-cols.tpl');

$bs_modal = file_get_contents($tpl_dir.'/bs-modal.tpl');


/**
 * @param array $input
 * @return string
 */
function se_print_form_input(array $input): string {

    $block = '';

    if($input['type'] === 'text') {
        $block = tpl_form_input_text($input);
    } else if($input['type'] === 'password') {
        $block = tpl_form_input_text($input);
    } else if($input['type'] === 'textarea') {
        $block = tpl_form_input_textarea($input);
    } else if($input['type'] === 'checkbox') {
        $block = tpl_form_checkbox($input);
    } else if($input['type'] === 'radios') {
        $block = tpl_form_radios($input);
    } else if($input['type'] === 'select') {
        $block = tpl_form_select($input);
    } else if($input['type'] === 'code') {
        $block = $input['code'];
    }

    return $block;
}

/**
 *
 * @param $data array replacements for the template - container_class, inputid, label, type, input_name, input_value
 * @return string template
 */
function tpl_form_input_text(array $data) {

    global $bs_form_input_text;

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    if((!isset($data['inputid'])) OR $data['inputid'] == '') {
        $data['inputid'] = uniqid();
    }

    if((!isset($data['type'])) OR $data['type'] == '') {
        $data['type'] = 'text';
    }

    if($data['input_value'] === NULL) {
        $data['input_value'] = '';
    }

    if($data['label'] == '') {
        $data['label'] = '&nbsp;';
    }

    $tpl = str_replace('{container_class}', $data['container_class'], $bs_form_input_text);
    $tpl = str_replace('{inputid}', $data['inputid'], $tpl);
    $tpl = str_replace('{label}', $data['label'], $tpl);
    $tpl = str_replace('{input_name}', $data['input_name'], $tpl);
    $tpl = str_replace('{input_value}', $data['input_value'], $tpl);
    $tpl = str_replace('{type}', $data['type'], $tpl);

    if($data['input_classes'] == '') {
        $tpl = str_replace('{input_classes}', 'form-control', $tpl);
    } else {
        $tpl = str_replace('{input_classes}', $data['input_classes'], $tpl);
    }

    if($data['input_group_start_text'] != '') {
        $tpl = str_replace('{input_group_start_text}', '<span class="input-group-text">'.$data['input_group_start_text'].'</span>', $tpl);
    } else {
        $tpl = str_replace('{input_group_start_text}', '', $tpl);
    }
    if($data['input_group_end_text'] != '') {
        $tpl = str_replace('{input_group_end_text}', '<span class="input-group-text">'.$data['input_group_end_text'].'</span>', $tpl);
    } else {
        $tpl = str_replace('{input_group_end_text}', '', $tpl);
    }

    if($data['input_group_start_text'] != '' OR $data['input_group_end_text'] != '') {
        $tpl = str_replace('{input_group_start}', '<div class="input-group">', $tpl);
        $tpl = str_replace('{input_group_end}', '</div>', $tpl);
    } else {
        $tpl = str_replace('{input_group_start}', '', $tpl);
        $tpl = str_replace('{input_group_start_text}', '', $tpl);
        $tpl = str_replace('{input_group_end}', '', $tpl);
        $tpl = str_replace('{input_group_end_text}', '', $tpl);
    }

    if((!isset($data['form_text'])) OR $data['form_text'] == '') {
        $tpl = str_replace('{form_text}', '', $tpl);
    } else {
        $form_text = '<div class="form-text">'.$data['form_text'].'</div>';
        $tpl = str_replace('{form_text}', $form_text, $tpl);
    }

    return $tpl;
}

function tpl_form_input_textarea(array $data) {

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    if((!isset($data['inputid'])) OR $data['inputid'] == '') {
        $data['inputid'] = uniqid();
    }

    if((!isset($data['type'])) OR $data['type'] == '') {
        $data['type'] = 'text';
    }

    if($data['input_value'] === NULL) {
        $data['input_value'] = '';
    }

    if($data['label'] == '') {
        $data['label'] = '&nbsp;';
    }

    $editor_switch = '';
    $editor_classes = '';
    if($data['mode'] == 'wysiwyg') {
        $editor_switch = '<div class="btn-group float-end pb-1" role="group">';
        $editor_switch .= '<label class="btn btn-sm btn-default"><input type="radio" class="btn-check" name="optEditor" value="optE1"> WYSIWYG</label>';
        $editor_switch .= '<label class="btn btn-sm btn-default"><input type="radio" class="btn-check" name="optEditor" value="optE2"> Text</label>';
        $editor_switch .= '<label class="btn btn-sm btn-default"><input type="radio" class="btn-check" name="optEditor" value="optE3"> Code</label>';
        $editor_switch .= '</div>';
        $editor_classes = 'mceEditor textEditor switchEditor';
    }

    global $bs_form_input_textarea;


    $tpl = str_replace('{container_classes}', $data['container_class'], $bs_form_input_textarea);
    $tpl = str_replace('{inputid}', $data['inputid'], $tpl);
    $tpl = str_replace('{editor_switch}', $editor_switch, $tpl);
    $tpl = str_replace('{editor_classes}', $editor_classes, $tpl);
    $tpl = str_replace('{label}', $data['label'], $tpl);
    $tpl = str_replace('{input_name}', $data['input_name'], $tpl);
    $tpl = str_replace('{input_value}', $data['input_value'], $tpl);
    $tpl = str_replace('{type}', $data['type'], $tpl);

    return $tpl;
}


/**
 * @param array $data
 * @return string
 */
function tpl_form_checkbox(array $data): string {

    global $bs_form_checkbox;
    $checkbox_id = uniqid();

    $tpl = str_replace('{checkbox_name}', $data['input_name'], $bs_form_checkbox);
    $tpl = str_replace('{checkbox_value}', $data['input_value'], $tpl);
    $tpl = str_replace('{checkbox_id}', $checkbox_id, $tpl);
    $tpl = str_replace('{checkbox_label}', $data['label'], $tpl);

    /* checked or not checked */
    if($data['status'] == '') {
        $tpl = str_replace('{checked}', '', $tpl);
    } else {
        $tpl = str_replace('{checked}', 'checked', $tpl);
    }

    return $tpl;
}

/**
 * @param array $data
 * @return string
 */
function tpl_form_radios(array $data): string {

    global $bs_form_radio,$lang;

    $tpl_radio = $bs_form_radio;
    $tpl = '';

    foreach($data['radios'] as $k => $v) {
        $radio_id = uniqid();
        $tpl = str_replace('{radio_label}', $lang[$k], $tpl_radio);
        $tpl = str_replace('{radio_value}', $v, $tpl);
        $tpl = str_replace('{radio_id}', $radio_id, $tpl);
        $tpl = str_replace('{radio_name}', $data['input_name'], $tpl);

        if($data['input_name'] == $v) {
            $tpl = str_replace('{checked}', 'checked', $tpl);
        } else {
            $tpl = str_replace('{checked}', '', $tpl);
        }

        $tpl_str .= $tpl;
    }

    //$tpl = str_replace('{radio_name}', $data['input_name'], $bs_form_radio);


    return $tpl_str;
}


function tpl_form_select(array $data): string {

    global $bs_form_select,$lang;
    $select_id = uniqid();
    $select_options = '';

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    $tpl = str_replace('{container_classes}', $data['container_class'], $bs_form_select);
    $tpl = str_replace('{select_id}', $select_id, $tpl);
    $tpl = str_replace('{label}', $data['label'], $tpl);
    $tpl = str_replace('{select_name}', $data['input_name'], $tpl);

    foreach($data['options'] as $k => $v) {

        if(array_key_exists($k, $lang)) {
            $option_name = $lang[$k];
        } else {
            $option_name = $k;
        }

        $selected = '';
        if($data['input_value'] == $v) {
            $selected = 'selected';
        }

        $select_options .= '<option value="'.$v.'" '.$selected.'>'.$option_name.'</option>';

    }

    $tpl = str_replace('{options}', $select_options, $tpl);


    return $tpl;
}



function tpl_form_control_group($labelFor,$labelText,$formControls) {
	global $bs_form_control_group;
	$tpl = str_replace('{labelText}', $labelText, $bs_form_control_group);
	$tpl = str_replace('{labelFor}', $labelText, $tpl);
	$tpl = str_replace('{formControls}', $formControls, $tpl);
	return $tpl;
}


function tpl_checkbox($checkbox_name,$checkbox_value,$checkbox_id,$checkbox_label,$checkbox_checked) {
	global $bs_form_checkbox;
	
	$tpl = str_replace('{checkbox_name}', $checkbox_name, $bs_form_checkbox);
	$tpl = str_replace('{checkbox_value}', $checkbox_value, $tpl);
	$tpl = str_replace('{checkbox_id}', $checkbox_id, $tpl);
	$tpl = str_replace('{checkbox_label}', $checkbox_label, $tpl);
	
	if($checkbox_checked == '') {
		$tpl = str_replace('{checked}', '', $tpl);
	} else {
		$tpl = str_replace('{checked}', 'checked', $tpl);
	}
	
	return $tpl;
}


function tpl_radio($radio_name,$radio_value,$radio_id,$radio_label,$radio_checked) {
	global $bs_form_radio;
	
	$tpl = str_replace('{radio_name}', $radio_name, $bs_form_radio);
	$tpl = str_replace('{radio_value}', $radio_value, $tpl);
	$tpl = str_replace('{radio_id}', $radio_id, $tpl);
	$tpl = str_replace('{radio_label}', $radio_label, $tpl);
	
	if($radio_checked == '') {
		$tpl = str_replace('{checked}', '', $tpl);
	} else {
		$tpl = str_replace('{checked}', 'checked', $tpl);
	}
	
	return $tpl;
}