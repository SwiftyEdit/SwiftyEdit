<?php

/**
 * ACP Templates
 * 
 */


$bs_form_control_group = file_get_contents('templates/bs-form-control-group.tpl');
$bs_form_checkbox = file_get_contents('templates/bs-form-checkbox.tpl');
$bs_form_radio = file_get_contents('templates/bs-form-radio.tpl');
$bs_form_input_text = file_get_contents('templates/bs-form-input-text.tpl');
$bs_form_input_textarea = file_get_contents('templates/bs-form-input-textarea.tpl');

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
        $data['inputid'] = md5($data['label']);
    }

    if((!isset($data['type'])) OR $data['type'] == '') {
        $data['type'] = 'text';
    }

    $tpl = str_replace('{container_class}', $data['container_class'], $bs_form_input_text);
    $tpl = str_replace('{inputid}', $data['inputid'], $tpl);
    $tpl = str_replace('{label}', $data['label'], $tpl);
    $tpl = str_replace('{input_name}', $data['input_name'], $tpl);
    $tpl = str_replace('{input_value}', $data['input_value'], $tpl);
    $tpl = str_replace('{type}', $data['type'], $tpl);

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
        $data['inputid'] = md5($data['label']);
    }

    if((!isset($data['type'])) OR $data['type'] == '') {
        $data['type'] = 'text';
    }

    global $bs_form_input_textarea;

    $tpl = str_replace('{container_class}', $data['container_class'], $bs_form_input_textarea);
    $tpl = str_replace('{inputid}', $data['inputid'], $tpl);
    $tpl = str_replace('{label}', $data['label'], $tpl);
    $tpl = str_replace('{input_name}', $data['input_name'], $tpl);
    $tpl = str_replace('{input_value}', $data['input_value'], $tpl);
    $tpl = str_replace('{type}', $data['type'], $tpl);

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