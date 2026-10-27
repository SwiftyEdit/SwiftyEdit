<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var array $se_settings
 * @var array $lang_codes
 * @var string $hidden_csrf_token
 */


if(isset($_POST['delete'])) {
    $delete_id = (int) $_POST['delete'];
    $data = $db_content->delete("se_categories", [
        "cat_id" => $delete_id
    ]);
    if($data->rowCount() > 0) {
        record_log($_SESSION['user_nick'],"delete category id: $delete_id","8");
        se_updateCategoriesCache();
        header( "HX-Trigger: updated_categories");
        exit;
    }
}

if (isset($_POST['save_category'])) {

    if($_POST['cat_name_clean'] == '') {
        $cat_name_clean = clean_filename($_POST['cat_name']);
    } else {
        $cat_name_clean = clean_filename($_POST['cat_name_clean']);
    }
    $cat_name = sanitizeUserInputs($_POST['cat_name']);
    $cat_lang = sanitizeUserInputs($_POST['cat_lang']);
    $cat_title = sanitizeUserInputs($_POST['cat_title']);
    $cat_description = sanitizeUserInputs($_POST['cat_description']);
    $cat_keywords = sanitizeUserInputs($_POST['cat_keywords']);
    $cat_sort = (int) $_POST['cat_sort'];
    $cat_template = sanitizeUserInputs($_POST['cat_template']);

    // check for theme values from themes/$cat_template/php/category-values.php
    $cat_template_values = '';
    $theme_values = [];
    if (isset($_POST['theme_values']) && is_array($_POST['theme_values'])) {
        foreach($_POST['theme_values'] as $k => $v) {
            $theme_values[$k] = htmlentities(stripslashes($v), ENT_QUOTES);
        }
        $cat_template_values = json_encode($theme_values,JSON_UNESCAPED_UNICODE);
    }

    // image paths end up in attributes and url() - only allow path characters
    $cat_thumbnail = '';
    if(is_array($_POST['picker_0'] ?? null)) {
        $thumbnails = array_map('se_filter_filepath', $_POST['picker_0']);
        $thumbnails = array_unique(array_filter($thumbnails));
        if(count($thumbnails) > 1) {
            $cat_thumbnail = implode("<->", $thumbnails);
        } elseif(count($thumbnails) == 1) {
            $cat_thumbnail = reset($thumbnails).'<->';
        }
    }

    // products and filters reference categories by hash - an update keeps the
    // stored hash, only a new category gets one (never taken from the form)
    $cat_hash = '';
    if(is_numeric($_POST['save_category'])) {
        $cat_hash = (string) $db_content->get("se_categories", "cat_hash", [
            "cat_id" => (int) $_POST['save_category']
        ]);
    }
    if($cat_hash == '') {
        $cat_hash = md5(uniqid('', true));
    }


    $insert_data = [
        "cat_name" =>  $cat_name,
        "cat_name_clean" =>  $cat_name_clean,
        "cat_hash" =>  $cat_hash,
        "cat_lang" =>  $cat_lang,
        "cat_sort" => $cat_sort,
        "cat_title" => $cat_title,
        "cat_description" => $cat_description,
        "cat_keywords" => $cat_keywords,
        "cat_teaser" => se_purify_html($_POST['cat_teaser'] ?? ''),
        "cat_text" => se_purify_html($_POST['cat_text'] ?? ''),
        "cat_thumbnail" => $cat_thumbnail,
        "cat_template" => $cat_template,
        "cat_template_values" => $cat_template_values
    ];

    // create a new category - the uuid is only set here, updates keep it
    if($_POST['save_category'] == 'new') {
        $insert_data['uuid'] = se_generate_uuid();
        $data = $db_content->insert("se_categories", $insert_data);
        $new_id = $db_content->id();
        header( 'HX-REDIRECT: /admin/categories/edit/'.$new_id.'/');
    }

    // updated category
    if(is_numeric($_POST['save_category'])) {
        $cat_id = (int) $_POST['save_category'];
        $data = $db_content->update("se_categories", $insert_data,[
            "cat_id" => $cat_id
        ]);
    }

    se_updateCategoriesCache();
    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: updated_categories");
}