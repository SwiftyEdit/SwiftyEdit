<?php
error_reporting(E_ALL);

if(!isset($languagePack)) {
    $languagePack = $_SESSION['lang'] ?? 'en';
}

// give the plugins the possibility to read via xhr
$path = explode('/', $_REQUEST['query']);
$plugin = basename($path[2]);
$plugin_base = '/admin/addons/plugin/' . $plugin . '/';
$plugin_root = SE_ROOT.'plugins/'.$plugin.'/';
$plugin_reader_file = $plugin_root.'backend/reader.php';
if(is_file("$plugin_reader_file")) {
    include_once "$plugin_reader_file";
    exit;
}



// list all plugins
if($_REQUEST['action'] == 'list_plugins') {

    $get_all_addons = se_get_all_addons();

    // for showing help text
    $modal_template_file = file_get_contents("../acp/templates/bs-modal.tpl");

    foreach($get_all_addons as $k => $v) {

        $get_image = base64_encode(file_get_contents(SE_PUBLIC.'/assets/themes/administration/images/poster-addons.png'));

        $addon_name = $v['addon']['name'];
        $addon_version = $v['addon']['version'];
        $addon_description = $v['addon']['description'];
        $addon_author = $v['addon']['author'];
        $addon_image_src = SE_ROOT.'plugins/'.$k.'/poster.png';
        if(is_file($addon_image_src)) {
            $get_image = base64_encode(file_get_contents($addon_image_src));
        }

        $addon_lang = se_return_addon_translations($k);

        $btn_help_text = '';
        $modal = '';
        $help_file_md = SE_ROOT.'/plugins/'.$k.'/readme.md';
        if(is_file($help_file_md)) {
            $addon_id = 'addonID'.$k;
            $btn_help_text = '<button type="button" class="btn btn-sm btn-default" data-bs-toggle="modal" data-bs-target="#'.$addon_id.'">'.$icon['question'].'</button>';

            $modal_body_text = file_get_contents($help_file_md);
            $Parsedown = new Parsedown();
            $modal_body = $Parsedown->text($modal_body_text);

            $modal = $modal_template_file;
            $modal = str_replace('{modalID}', $addon_id, $modal);
            $modal = str_replace('{modalTitle}', $addon_name, $modal);
            $modal = str_replace('{modalBody}', $modal_body, $modal);
            echo $modal;
        }

        $vals = ['csrf_token' => $_SESSION['token']];

        $delete_btn = '<button name="delete_addon" value="'.$k.'" class="btn btn-sm btn-default text-danger" 
                            hx-post="/admin/addons/write/"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-vals=\''.json_encode($vals).'\'
                            hx-swap="none"
                            >'.$icon['trash_alt'].'</button>';

        $activate_btn = '<button name="activate_addon" value="'.$k.'" class="btn btn-sm btn-default text-success"
                                hx-post="/admin/addons/write/"
                                hx-vals=\''.json_encode($vals).'\'
                                hx-swap="none"
                                >'.$lang['btn_addon_enable'].'</button>';


        echo '<div class="card mb-1 border-bottom">';
        echo '<div class="card-body">';
        echo '<div class="row">';
        echo '<div class="col-md-2">';
        echo '<img src="data:image/png;base64,'.$get_image.'" class="img-fluid rounded-circle">';
        echo '</div>';
        echo '<div class="col-md-10">';
        echo '<h5 class="card-title">'.$addon_name.' <span class="badge badge-se">'.$addon_version.'</span></h5>';
        echo $addon_description;
        echo '</div>';
        echo '</div>';

        echo '<div class="btn-toolbar mt-1">';
        echo '<div class="btn-group">';
        if(array_key_exists('navigation',$v)) {
            foreach ($v['navigation'] as $nav) {

                $nav_text = $nav['text'];
                if (array_key_exists($nav['text'], $addon_lang)) {
                    $nav_text = $addon_lang[$nav['text']];
                }

                echo '<a href="/admin/addons/plugin/' . $k . '/' . $nav['file'] . '/" class="btn btn-sm btn-default">' . $nav_text . '</a>';
            }
        }
        echo '</div>';
        echo '<div class="btn-group ms-auto">';
        echo $btn_help_text;
        echo $delete_btn;
        echo $activate_btn;
        echo '</div>';
        echo '</div>';



        echo '</div>';
        echo '</div>';
    }

    exit;
}

// list all themes
if($_REQUEST['action'] == 'list_themes') {

    $all_themes = get_all_templates();

    foreach($all_themes as $template) {

        if($template == 'administration') {
            continue;
        }

        $class = '';
        $active = '';
        if($template == $se_settings['template']) {
            $class = 'border-success border-2 border-opacity-50';
            $active = '(active)';
        }

        // get all layout templates from this theme
        $arr_layout_tpls = glob(SE_PUBLIC."/assets/themes/".$template."/templates/layout*.tpl");

        echo '<div class="card mb-3 '.$class.'">';
        echo '<div class="card-header">'.$template.' '.$active.'</div>';
        echo '<div class="card-body">';

        echo '<div class="row">';
        echo '<div class="col-md-8">';

        echo '<form hx-post="/admin/addons/write/" hx-swap="none">';

        echo '<label>Layout</label>';
        echo '<select name="select_template" class="form-control image-picker">';
        foreach($arr_layout_tpls as $layout_tpl) {
            $active = '';
            if($se_settings['template_layout'] == basename($layout_tpl) && ($template == $se_settings['template'])) {
                $active = 'selected';
            }
            $tpl_name = basename($layout_tpl,'.tpl');
            $value = "$template<|-|>$tpl_name".'.tpl';
            echo '<option value="'.$value.'" '.$active.'>'.$tpl_name.'</option>';
        }
        echo '</select>';

        $stylesheets = glob(SE_PUBLIC."/assets/themes/".$template."/css/theme_*.css");
        echo '<label>Stylesheet</label>';
        echo '<select name="select_template_sytlesheet" class="form-control">';
        echo '<option value=""></option>'; //blank
        foreach($stylesheets as $css) {

            $active = '';
            if($css == $se_settings['template_stylesheet']) {
                $active = 'selected';
            }

            echo '<option value="'.$css.'" '.$active.'>'.$css.'</option>';
        }

        echo '</select><hr>';

        echo '<input type="submit" class="btn btn-success btn-md" name="save_default_layout" value="Layout '.$lang['save'].'">';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

        echo '</form>';

        echo '</div>';
        echo '<div class="col-md-4">';
        $theme_poster = '/themes/'.$template.'/images/preview.png';
        echo '<img src="'.$theme_poster.'" class="img-fluid rounded">';
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</div>';


    }

    exit;
}
