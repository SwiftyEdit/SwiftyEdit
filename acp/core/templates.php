<?php

/**
 * SwiftyEdit form templates
 * print text input, textarea, checkbox, radio, radios ...
 * from arrays, the markup lives in acp/templates/components/form/*.twig
 */

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
    } else if($input['type'] === 'content_editor') {
        $block = tpl_form_input_content_editor($input);
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

    global $twig;

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    if((!isset($data['inputid'])) OR $data['inputid'] == '') {
        $data['inputid'] = uniqid();
    }

    if((!isset($data['type'])) OR $data['type'] == '') {
        $data['type'] = 'text';
    }

    if(($data['label'] ?? '') == '') {
        $data['label'] = '&nbsp;';
    }

    // password inputs get autocomplete="new-password" in the template to keep
    // password managers from filling in the login of the current administrator
    // (e.g. new password of another user, SMTP password)
    return $twig->render('components/form/input-text.twig', [
        'container_class' => $data['container_class'],
        'inputid' => $data['inputid'],
        'label' => $data['label'],
        'type' => $data['type'],
        'input_name' => $data['input_name'],
        'input_value' => $data['input_value'] ?? '',
        'input_classes' => ($data['input_classes'] ?? '') == '' ? 'form-control' : $data['input_classes'],
        'group_start_text' => $data['input_group_start_text'] ?? '',
        'group_end_text' => $data['input_group_end_text'] ?? '',
        'form_text' => $data['form_text'] ?? ''
    ]);
}

/**
 * Build the "switch content format" <select> shared by the legacy textarea
 * and the content_editor field type. $options is ['legacy' => label, '<editor_key>' => label, ...].
 * Changing it reloads the page with ?set_content_format=<key> (handled in
 * acp/core/pages/pages-edit.php); nothing is persisted until Save.
 */
function tpl_content_format_switch(array $options, string $current, $page_id): string {

    if (count($options) <= 1) {
        // no registered content-format editors installed - nothing to switch to
        return '';
    }

    global $lang;

    $select = '<div class="float-end pb-1 ms-1">';
    $select .= '<select class="form-select form-select-sm" name="content_format_switch" style="width:auto;display:inline-block;">';
    foreach ($options as $key => $label) {
        $selected = ($key === $current) ? ' selected' : '';
        $select .= '<option value="'.htmlspecialchars($key).'"'.$selected.'>'.htmlspecialchars($label).'</option>';
    }
    $select .= '</select>';
    $select .= '</div>';
    $select .= '<script>document.currentScript.previousElementSibling.querySelector("select[name=content_format_switch]").addEventListener("change", function(e){';
    $select .= 'var current='.json_encode($current).';';
    $select .= 'var pageId='.json_encode((string) $page_id).';';
    /*
     * Switching TO "legacy" is not destructive - page_content already holds
     * the last-saved rendered HTML at all times (se_freeze_editor_content()
     * in app/functions/functions.editors.php runs on every save), so the
     * plain-textarea view just shows it as-is. Switching to any other format
     * still starts an empty tree/string (formats aren't auto-converted into
     * each other), so that case keeps the discard warning.
     */
    $select .= 'var discardMsg='.json_encode($lang['label_content_format_confirm_switch'] ?? 'Switching the format discards the current content. Continue?').';';
    $select .= 'var convertMsg='.json_encode($lang['label_content_format_confirm_convert'] ?? 'The current content will be converted to static HTML and can no longer be edited with its current editor afterwards. Continue?').';';
    $select .= 'var isConvertToLegacy=(e.target.value==="legacy"&&current!=="legacy");';
    $select .= 'if(confirm(isConvertToLegacy?convertMsg:discardMsg)){';
    /*
     * HTMX partial swap instead of a full page reload - a reload would
     * discard any unsaved edits in the rest of the pages-edit form (title,
     * meta, ...). #pageContentField wraps the field in both
     * acp/core/pages/pages-edit.php (initial load) and this swap's target
     * endpoint, acp/core/pages/data-reader.php.
     */
    $select .= 'htmx.ajax("GET","/admin-xhr/pages/read/?content_field="+encodeURIComponent(pageId)+"&set_content_format="+encodeURIComponent(e.target.value),{target:"#pageContentField",swap:"outerHTML"});';
    $select .= '}else{e.target.value=current;}';
    $select .= '});</script>';

    return $select;
}

function tpl_form_input_textarea(array $data) {

    global $twig;

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    if((!isset($data['inputid'])) OR $data['inputid'] == '') {
        $data['inputid'] = uniqid();
    }

    if(($data['label'] ?? '') == '') {
        $data['label'] = '&nbsp;';
    }

    $editors = null;
    $editor_classes = '';
    if(($data['mode'] ?? '') == 'wysiwyg') {

        global $se_editor_addons;

        /*
         * One button per installed editor plugin (labelled with the editor's
         * name and keyed by its id), plus the built-in plain-text option.
         * Installing another editor adds its button automatically; removing one
         * removes it. Editors are ordered by their numeric "order". Content-format
         * editors (mode "format") are a separate concept (see content_format_options
         * below) and are excluded here.
         */
        $editors = [];
        if(!empty($se_editor_addons) && is_array($se_editor_addons)) {
            foreach($se_editor_addons as $editor_addon) {
                if(($editor_addon['mode'] ?? '') === 'format') {
                    continue;
                }
                $id = $editor_addon['id'] ?? '';
                if($id === '') {
                    continue;
                }
                $editors[] = [
                    'id' => $id,
                    'label' => $editor_addon['label'] ?? $id
                ];
            }
        }

        $editor_classes = 'textEditor switchEditor';
    }

    $format_switch = '';
    if(!empty($data['content_format_options'])) {
        $format_switch = tpl_content_format_switch($data['content_format_options'], $data['content_format_value'] ?? 'legacy', $data['page_id'] ?? 'new');
    }

    if(($data['input_classes'] ?? '') != '') {
        $editor_classes .= ' '.$data['input_classes'];
    }

    return $twig->render('components/form/textarea.twig', [
        'container_class' => $data['container_class'],
        'inputid' => $data['inputid'],
        'label' => $data['label'],
        'input_name' => $data['input_name'],
        'input_value' => $data['input_value'] ?? '',
        'editors' => $editors,
        'editor_classes' => $editor_classes,
        'format_switch' => $format_switch
    ]);
}

/**
 * A content field whose value is a registered content-format editor's JSON
 * payload (see app/functions/functions.editors.php). Renders a mount point
 * that the editor plugin's own JS (window.seContentEditors[id]) attaches to,
 * plus a hidden textarea that carries the serialized value on submit - Core
 * never interprets the mounted content itself.
 */
function tpl_form_input_content_editor(array $data): string {

    global $twig;

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    if((!isset($data['inputid'])) OR $data['inputid'] == '') {
        $data['inputid'] = uniqid();
    }

    $label = $data['label'] ?? '';
    if($label == '') {
        $label = '&nbsp;';
    }

    return $twig->render('components/form/content-editor.twig', [
        'container_class' => $data['container_class'],
        'inputid' => $data['inputid'],
        'label' => $label,
        'input_name' => $data['input_name'],
        'format_switch' => tpl_content_format_switch($data['content_format_options'] ?? [], $data['content_format_value'] ?? '', $data['page_id'] ?? 'new'),
        // escaped by Twig, the editor reads it back with JSON.parse()
        'editor' => $data['editor'] ?? '',
        'content_json' => json_encode($data['backend_payload'] ?? null)
    ]);
}


/**
 * @param array $data
 * @return string
 */
function tpl_form_checkbox(array $data): string {

    global $twig;

    return $twig->render('components/form/checkbox.twig', [
        'inputid' => uniqid(),
        'input_name' => $data['input_name'],
        'input_value' => $data['input_value'],
        'label' => $data['label'],
        'checked' => (($data['status'] ?? '') != '')
    ]);
}

/**
 * @param array $data
 * @return string
 */
function tpl_form_radios(array $data): string {

    global $twig, $lang;

    $radios = [];
    foreach($data['radios'] as $k => $v) {
        $radios[] = [
            'id' => uniqid(),
            'value' => $v,
            'label' => array_key_exists($k, $lang) ? $lang[$k] : $k,
            'checked' => ($data['input_value'] == $v)
        ];
    }

    return $twig->render('components/form/radios.twig', [
        'input_name' => $data['input_name'],
        'radios' => $radios
    ]);
}


function tpl_form_select(array $data): string {

    global $twig, $lang;

    if((!isset($data['container_class'])) OR $data['container_class'] == '') {
        $data['container_class'] = 'mb-3';
    }

    $options = [];
    foreach($data['options'] as $k => $v) {
        $options[] = [
            'value' => $v,
            'label' => array_key_exists($k, $lang) ? $lang[$k] : $k,
            'selected' => ($data['input_value'] == $v)
        ];
    }

    return $twig->render('components/form/select-field.twig', [
        'container_class' => $data['container_class'],
        'inputid' => uniqid(),
        'label' => $data['label'],
        'input_name' => $data['input_name'],
        'options' => $options
    ]);
}

/**
 * print a bootstrap row
 *
 * @param array $cols html of the columns
 * @param array $classes column classes, default: equal widths (col-md-6, col-md-4, col-md-3 ...)
 * @return string
 */
function se_print_row(array $cols, array $classes = []): string {

    global $twig;

    $default_class = 'col-md-'.intdiv(12, max(count($cols), 1));
    $row = [];
    foreach(array_values($cols) as $i => $content) {
        $row[] = [
            'class' => $classes[$i] ?? $default_class,
            'content' => $content
        ];
    }

    return $twig->render('components/form/row.twig', ['cols' => $row]);
}
