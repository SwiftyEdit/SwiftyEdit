<?php

$all_mods = se_get_all_addons();
$writer_uri = '/admin-xhr/pages/write/';

$q = pathinfo($_REQUEST['query']);

// check if last part of url is an id
$path = parse_url($_REQUEST['query'], PHP_URL_PATH);
$segments = explode('/', rtrim($path, '/'));
$lastSegment = end($segments);
if(is_numeric($lastSegment)) {
    $get_page_id = (int) $lastSegment;
    $form_mode = $get_page_id;
    $btn_submit_text = $lang['update'];
}

if(isset($_POST['page_id']) && is_numeric($_POST['page_id'])) {
    $get_page_id = (int) $_POST['page_id'];
    $form_mode = $get_page_id;
    $btn_submit_text = $lang['update'];
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_page_id = (int) $_POST['duplicate_id'];
    $form_mode = 'new';
    $btn_submit_text = $lang['duplicate'];
}

if(is_int($get_page_id)) {

    $get_page = $db_content->get("se_pages","*",[
        "page_id" => "$get_page_id"
    ]);

    foreach($get_page as $k => $v) {
        if($v == '') {
            continue;
        }
        $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
    }

} else {
    $btn_submit_text = $lang['save'];
    $form_mode = 'new';
}

if(isset($_POST['restore_id']) && is_numeric($_POST['restore_id'])) {
    $restore_id = (int) $_POST['restore_id'];
    $get_page = $db_content->get("se_pages_cache","*",[ "page_id" => $restore_id ]);
    foreach($get_page as $k => $v) {
        if($v == '') {
            continue;
        }
        $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
    }

    $form_mode = (int) $get_page['page_id_original'];
    $btn_submit_text = $lang['update'];
}

if(str_contains($page_sort, '.')) {
    $last_part_page_sort = substr($page_sort, strrpos($page_sort, '.') + 1);
} else {
    $last_part_page_sort = $page_sort;
}

$input_text_page_sort = [
    "input_name" => "page_order",
    "input_value" => $last_part_page_sort,
    "label" => $lang['label_pages_position'].' '.se_print_docs_tip("tip-page-position.md"),
    "type" => "text"
];

$input_text_page_linkname = [
    "input_name" => "page_linkname",
    "input_value" => $get_page['page_linkname'],
    "label" => $lang['label_pages_link_name'],
    "type" => "text"
];

$input_text_page_classes = [
    "input_name" => "page_classes",
    "input_value" => $page_classes,
    "label" => $lang['label_pages_classes'],
    "type" => "text"
];

$input_select_page_target = [
    "input_name" => "page_target",
    "input_value" => $ppage_target,
    "label" => "target",
    "options" => ["_self" => "_self", "_blank" => "_blank", "_parent" => "_parent", "_top" => "_top"],
    "type" => "select"
];

$input_text_page_hash = [
    "input_name" => "page_hash",
    "input_value" => $page_hash,
    "label" => $lang['label_pages_hash'],
    "type" => "text"
];

$input_text_page_permalink = [
    "input_name" => "page_permalink",
    "input_value" => $page_permalink,
    "label" => $lang['label_pages_permalink'],
    "input_group_start_text" => "$se_base_url",
    "type" => "text"
];

$input_text_page_canonical_url = [
    "input_name" => "page_canonical_url",
    "input_value" => $page_canonical_url,
    "label" => 'Canonical URL',
    "type" => "text"
];

$input_text_page_custom_id = [
    "input_name" => "page_custom_id",
    "input_value" => $page_custom_id,
    "label" => $lang['label_pages_custom_id'],
    "type" => "text"
];

$input_text_page_custom_classes = [
    "input_name" => "page_custom_classes",
    "input_value" => $page_custom_classes,
    "label" => $lang['label_pages_custom_classes'],
    "type" => "text"
];

$input_text_page_priority = [
    "input_name" => "page_priority",
    "input_value" => $page_priority,
    "label" => $lang['label_priority'],
    "type" => "text"
];


foreach($se_page_types as $types) {
    $str = 'type_of_use_'.$types;
    $name = $lang[$str];
    $sel_page_type = '';
    $type_options[$name] = $types;
}

$input_select_page_type = [
    "input_name" => "page_type_of_use",
    "input_value" => $page_type_of_use,
    "label" => $lang['label_pages_type_of_use'] . ' '. se_print_docs_tip("tip-page-type-of-use.md"),
    "options" => $type_options,
    "type" => "select"
];

if(empty($page_permalink_short_cnt)) {
    $page_permalink_short_cnt = 0;
}

$input_text_page_shortlink = [
    "input_name" => "page_permalink_short",
    "input_value" => $page_permalink_short,
    "label" => $lang['label_pages_permalink_short'],
    "input_group_end_text" => "$page_permalink_short_cnt",
    "type" => "text"
];

$input_text_page_funnel_urls = [
    "input_name" => "page_funnel_uri",
    "input_value" => $page_funnel_uri,
    "label" => $lang['label_pages_funnel_url'],
    "type" => "textarea"
];

$redirect_options = [
    "301" => '301',
    "302" => '302',
    "303" => '303',
    "304" => '304',
    "305" => '305',
    "306" => '306',
    "307" => '307',
    "308" => '308',
    "309" => '309',
];

$input_select_page_redirect = [
    "input_name" => "page_redirect_code",
    "input_value" => $page_redirect_code,
    "label" => $lang['label_redirect'],
    "options" => $redirect_options,
    "type" => "select"
];

$input_text_page_redirect = [
    "input_name" => "page_redirect",
    "input_value" => $page_redirect,
    "label" => null,
    "input_group_start_text" => '<i class="bi bi-arrow-right-short"></i>',
    "type" => "text"
];

$input_text_page_content = [
    "input_name" => "page_content",
    "input_value" => $page_content,
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

$input_text_page_title = [
    "input_name" => "page_title",
    "input_value" => $get_page['page_title'],
    "label" => $lang['label_title'],
    "type" => "text"
];

$input_text_page_meta_description = [
    "input_name" => "page_meta_description",
    "input_value" => $get_page['page_meta_description'],
    "input_classes" => "form-control count-chars",
    "label" => $lang['label_description'],
    "type" => "textarea"
];

$input_text_page_keywords = [
    "input_name" => "page_meta_keywords",
    "input_value" => $get_page['page_meta_keywords'],
    "input_classes" => "form-control tags",
    "label" => $lang['label_keywords'],
    "type" => "text"
];

$input_text_page_autor = [
    "input_name" => "page_meta_author",
    "input_value" => $get_page['page_meta_author'],
    "label" => $lang['label_author'],
    "type" => "text"
];

$status_options = [
    $lang['status_public'] => 'public',
    $lang['status_private'] => 'private',
    $lang['status_draft'] => 'draft',
    $lang['status_ghost'] => 'ghost'
];

$input_select_page_status = [
    "input_name" => "page_status",
    "input_value" => $page_status,
    "label" => $lang['label_status'].' ' .se_print_docs_tip('tip-page-status.md'),
    "options" => $status_options,
    "type" => "select"
];


$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    if(!in_array($langs['lang_folder'],$lang_codes)) {
        continue;
    }
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$page_language = $page_language ?? $languagePack;

$input_select_language = [
    "input_name" => "page_language",
    "input_value" => $page_language,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("&lt;-&gt;", $page_thumbnail_array);
$draggable = '';
if(is_array($array_images)) {
    $array_images = array_filter($array_images);
    foreach($array_images as $image) {
        $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
        $image_src = str_replace('../images/','/images/',$image_src);
        $draggable .= '<div class="list-group-item draggable" data-id="'.$image.'">';
        $draggable .= '<div class="d-flex flex-row gap-2">';
        $draggable .= '<div class="rounded-circle flex-shrink-0" style="width:40px;height:40px;background-image:url('.$image_src.');background-size:cover;"></div>';
        $draggable .= '<div class="text-muted small">'.basename($image).'</div>';
        $draggable .= '</div>';
        $draggable .= '</div>';
    }
}

$choose_images = '<div id="imgdropper" class="sortable_target list-group mb-3">'.$draggable.'</div>';
$choose_images .= '<div id="imgWidget" hx-post="/admin-xhr/widgets/read/?widget=img-select" hx-include="[name=\'csrf_token\']" hx-trigger="load, update_image_widget from:body">';
$choose_images .= 'Loading Images ...</div>';

$input_select_page_categories_mode = [
    "input_name" => "page_categories_mode",
    "input_value" => $page_categories_mode,
    "label" => "",
    "options" => [$lang['label_categories_show'] => "1",$lang['label_categories_hide'] => "2"],
    "type" => "select"
];

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['file'].' '.$lang['nav_btn_pages'];
echo '<a href="/admin/pages/" class="btn btn-default ms-auto">'.$icon['arrow_left_short'].' '.$lang['nav_btn_overview'].'</a>';
echo '</div>';

$form_tpl = '<div id="formResponse"></div>';

$form_tpl .= '<form>';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-9">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">';
$form_tpl .= '<ul class="nav nav-tabs card-header-tabs">';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link active" id="position" data-bs-toggle="tab" data-bs-target="#position-tab">'.$lang['label_pages_position'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link" id="info" data-bs-toggle="tab" data-bs-target="#info-tab">'.$lang['nav_btn_info'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link" id="content" data-bs-toggle="tab" data-bs-target="#content-tab">'.$lang['nav_btn_content'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link" id="metas" data-bs-toggle="tab" data-bs-target="#metas-tab">'.$lang['nav_btn_metas'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a href="#" class="nav-link" id="theme" data-bs-toggle="tab" data-bs-target="#theme-tab">Theme</a></li>';

$form_tpl .= '<li class="nav-item ms-auto"><a class="nav-link" href="#posts" data-bs-toggle="tab" title="'.$lang['nav_btn_posts'].'">'.$icon['file_earmark_post'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a class="nav-link" href="#addons" data-bs-toggle="tab" title="'.$lang['nav_btn_addons'].'">'.$icon['plugin'].'</a></li>';
if($cnt_custom_fields > 0) {
    $form_tpl .= '<li class="nav-item"><a class="nav-link" href="#custom" data-bs-toggle="tab" title="'.$lang['legend_custom_fields'].'">'.$icon['list'].'</a></li>';
}
$form_tpl .= '</ul>';
$form_tpl .= '</div>';
$form_tpl .= '<div class="card-body">';
$form_tpl .= '<div class="tab-content" id="myTabContent">';

$form_tpl .= '<div class="tab-pane fade show active" id="position-tab" role="tabpanel" tabindex="0">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-9">';

$all_pages = $db_content->select("se_pages",
    ["page_linkname","page_sort","page_title","page_language","page_status"],
    ["page_sort[!]" => "portal",
        "ORDER" => [
            "page_language" => "ASC",
            "page_sort" => "ASC"
        ]
    ]
);

$all_pages = se_array_multisort($all_pages, 'page_language', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);

$cnt_all_pages = count($all_pages);
$sm_string = '<ul class="page-list">';

for($i=0;$i<$cnt_all_pages;$i++) {

    $sm_page_id = $all_pages[$i]['page_id'];
    $sm_page_sort = $all_pages[$i]['page_sort'];
    $sm_page_linkname = $all_pages[$i]['page_linkname'];
    $sm_page_title = $all_pages[$i]['page_title'];
    $sm_page_status = $all_pages[$i]['page_status'];
    $sm_page_permalink = $all_pages[$i]['page_permalink'];
    $sm_page_lang = $all_pages[$i]['page_language'];

    $flag = '<img src="'.return_language_flag_src($sm_page_lang).'" alt="'.$sm_page_lang.'" width="15">';
    $short_title = first_words($all_pages[$i]['page_title'], 6);

    if($sm_page_sort == '') { continue; }

    $points_of_item[$i] = substr_count($sm_page_sort, '.');

    // new level
    $start_ul = '';
    if($points_of_item[$i] > $points_of_item[$i-1]) {
        $start_ul = '<ul>';
        $sm_string = substr(trim($sm_string), 0, -5);
    }

    // end this level </ul>
    $end_ul = '';
    if($points_of_item[$i] < $points_of_item[$i-1]) {
        $div_level = abs($points_of_item[$i] - $points_of_item[$i-1]);
        $end_ul = str_repeat("</ul>", $div_level);
        $end_ul .= '</li>';
    }

    $start_li = '<li>';
    $end_li = '</li>';


    if($pos = strripos($page_sort,".")) {
        $string = substr($page_sort,0,$pos);
    }

    $checked = '';
    if($sm_page_sort != "" && $sm_page_sort == "$string" && $page_language == $sm_page_lang) {
        $checked = 'checked';
    }

    $disabled = '';
    if($sm_page_sort == $page_sort) {
        $disabled = 'disabled';
    }

    $sm_string .= "$start_ul";
    $sm_string .= "$end_ul";
    $sm_string .= $start_li;
    $sm_string .= '<label class="page-container" for="radio'.$i.'">';
    $sm_string .= '<code>'.$sm_page_sort.'</code> - <strong>'.$sm_page_linkname.'</strong> '.$short_title.' '.$flag;
    $sm_string .= '<span class="page-toggler"><input type="radio" id="radio'.$i.'" name="page_position" value="'.$sm_page_sort.'" '.$checked.' '.$disabled.'></span>';
    $sm_string .= '</label>';
    $sm_string .= $end_li;
}
$sm_string .= '</ul>';

$form_tpl .= $lang['label_page_position'];

$form_tpl .= '<ul class="page-list-top">';

if($page_sort == "portal") {
    $sel_page_sort_portal = 'checked';
} else if(ctype_digit($page_sort)) {
    $sel_page_sort_mainpage = 'checked';
} else {
    $sel_page_sort_default = 'checked';
}

$form_tpl .= '<li><label class="page-container">';
$form_tpl .= $lang['label_pages_single'];
$form_tpl .= '<span class="page-toggler"><input type="radio" name="page_position" value="null" '.$sel_page_sort_default.'></span>';
$form_tpl .= '</label></li>';

$form_tpl .= '<li><label class="page-container">';
$form_tpl .= $lang['label_pages_portal'];
$form_tpl .= '<span class="page-toggler"><input type="radio" name="page_position" value="portal" '.$sel_page_sort_portal.'></span>';
$form_tpl .= '</label></li>';

$form_tpl .= '<li><label class="page-container">';
$form_tpl .= $lang['label_pages_mainmenu'];
$form_tpl .= '<span class="page-toggler"><input type="radio" name="page_position" value="mainpage" '.$sel_page_sort_mainpage.'></span>';
$form_tpl .= '</label></li>';

$form_tpl .= '</ul>';

// print the generated sitemap
$form_tpl .= $lang['label_pages_position_sub'];

$form_tpl .= '<div class="scroll-container">'.$sm_string.'</div>';


$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-3">';
$form_tpl .= se_print_form_input($input_text_page_sort);
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // position tab

$form_tpl .= '<div class="tab-pane" id="info-tab" role="tabpanel" tabindex="0">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-4">';
$form_tpl .= se_print_form_input($input_text_page_linkname);
$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-4">';
$form_tpl .= se_print_form_input($input_text_page_classes);
$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-2">';
$form_tpl .= se_print_form_input($input_select_page_target);
$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-2">';
$form_tpl .= se_print_form_input($input_text_page_hash);
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= se_print_form_input($input_text_page_permalink);
$form_tpl .= se_print_form_input($input_text_page_canonical_url);

if($page_translation_urls != '') {
    $page_translation_urls = html_entity_decode($page_translation_urls);
    $translation_urls_array = json_decode($page_translation_urls,true);
}


$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header d-flex justify-content-between">';
$form_tpl .= $lang['label_translations'].' (URLs)';
$form_tpl .= ' <button class="btn btn-sm btn-default" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTranslationURL" aria-expanded="false">'.$icon['plus'].'</button>';
$form_tpl .= '</div>';
$form_tpl .= '<div class="card-body collapse" id="collapseTranslationURL">';
foreach($active_lang as $k => $v) {

    $ls = $v['sign'];

    $form_tpl .= '<div class="input-group mb-3">';
    $form_tpl .= '<span class="input-group-text"><i class="bi bi-translate me-1"></i> '.$ls.'</span>';
    $form_tpl .= '<input class="form-control" type="text" autocomplete="off" name="translation_url['.$ls.']" id="set_canonical_url" value="'.$translation_urls_array[$ls].'">';
    $form_tpl .= '</div>';
}
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '<hr>';

$input_group = [
    se_print_form_input($input_text_page_custom_id),
    se_print_form_input($input_text_page_custom_classes),
    se_print_form_input($input_text_page_priority)
];

$form_tpl .= str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);

$form_tpl .= se_print_form_input($input_select_page_type);

$form_tpl .= '<div class="heading-line">'.$lang['label_redirect'].' &nbsp; '.se_print_docs_tip("tip-page-redirects.md").'</div>';

$form_tpl .= se_print_form_input($input_text_page_shortlink);
$form_tpl .= se_print_form_input($input_text_page_funnel_urls);


$input_group = [
    'col-md-3',
    se_print_form_input($input_select_page_redirect),
    'col-md-9',
    se_print_form_input($input_text_page_redirect),
];

$form_tpl .= str_replace(['{classes_1}','{col1}','{classes_2}','{col2}'],$input_group,$bs_row_2_cols);

$form_tpl .= '</div>'; // info tab

$form_tpl .= '<div class="tab-pane" id="content-tab" role="tabpanel" tabindex="0">';
$form_tpl .= se_print_form_input($input_text_page_content);
$form_tpl .= '</div>'; // content tab

$form_tpl .= '<div class="tab-pane" id="metas-tab" role="tabpanel" tabindex="0">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-6">';
$form_tpl .= se_print_form_input($input_text_page_title);
$form_tpl .= se_print_form_input($input_text_page_meta_description);
$form_tpl .= se_print_form_input($input_text_page_keywords);
$form_tpl .= se_print_form_input($input_text_page_autor);

$robots = array("all", "noindex", "nofollow", "none", "noarchive", "nosnippet", "noodp", "notranslate", "noimageindex");
if($page_meta_robots == '') {
    $page_meta_robots = 'all';
}

$checkbox_robots = '<p>'.$lang['label_pages_meta_robots'].'</p>';

foreach($robots as $r) {
    $active = '';
    $checked = '';
    if(str_contains($page_meta_robots, $r)) {
        $active = 'active';
        $checked = 'checked';
    }
    $checkbox_robots .= '<div class="form-check form-check-inline">';
    $checkbox_robots .= '<input type="checkbox" class="form-check-input" id="btn-check-'.$r.'" name="page_meta_robots[]" value="'.$r.'" '.$checked.'>';
    $checkbox_robots .= '<label class="form-check-label" for="btn-check-'.$r.'">'.$r.'</label> ';
    $checkbox_robots .= '</div>';
}
$form_tpl .= $checkbox_robots;

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-6">';
$form_tpl .= $choose_images;
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // metas tab

$form_tpl .= '<div class="tab-pane" id="theme-tab" role="tabpanel" tabindex="0">';


// check if this page can handle theme values
if($get_page['page_template'] == 'use_standard') {
    // get theme from prefernces
    $theme_base = SE_ROOT.'public/assets/themes/'.$se_settings['template'];
} else {
    $theme_base = SE_ROOT.'public/assets/themes/'.$get_page['page_template'];
}

$page_value_injector = $theme_base.'/php/page_values.php';

if(is_file("$page_value_injector")) {
    function include_page_value_file($f) {
        ob_start();
        include $f;
        return ob_get_clean();
    }
    $form_tpl .= include_page_value_file($page_value_injector);
} else {
    $form_tpl .= 'No injection file found';
}


$form_tpl .= '</div>'; // themes tab

$form_tpl .= '<div class="tab-pane fade" id="posts">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-6">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_pages_select_post_type'].'</div>';

$form_tpl .= '<div class="card-body">';

$post_types = ["m","i","g","v","f","l","e","p"];
$post_types_label = [
    "m" => $lang['post_type_message'],
    "i" => $lang['post_type_image'],
    "g" => $lang['post_type_gallery'],
    "v" => $lang['post_type_video'],
    "f" => $lang['post_type_file'],
    "l" => $lang['post_type_link'],
    "e" => $lang['post_type_event'],
    "p" => $lang['post_type_product'],
];

foreach($post_types as $type) {
    if(str_contains($page_posts_types, $type)) {
        $checked = 'checked';
    }
    if($type == 'p' OR $type == 'e') {
        $form_tpl .= '<hr>';
    }
    $form_tpl .= '<div class="form-check">';
    $form_tpl .= '<input type="checkbox" class="form-check-input post-types post-type-group" id="type_'.$type.'" name="page_post_types[]" value="'.$type.'" '.$checked.'>';
    $form_tpl .= '<label class="form-check-label" for="type_'.$type.'">'.$post_types_label[$type].'</label>';
    $form_tpl .= '</div>';
}

$form_tpl .= '</div>'; // card-body
$form_tpl .= '</div>'; // card

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-6">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['categories'].'</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= se_print_form_input($input_select_page_categories_mode);

$categories = se_get_categories();
$page_cats_array = explode(',', $page_posts_categories);

$checked_cat_all = '';
if(in_array('all', $page_cats_array)) {
    $checked_cat_all = 'checked';
}

$form_tpl .= '<div class="form-check">';
$form_tpl .= '<input type="checkbox" class="form-check-input" id="cat_all" name="page_post_categories[]" value="all" '.$checked_cat_all.'>';
$form_tpl .= '<label class="form-check-label" for="cat_all">'.$lang['label_categories_activate_all'].'</label>';
$form_tpl .= '</div><hr>';

for($i=0;$i<count($categories);$i++) {

    $checked_cat = '';
    if(in_array($categories[$i]['cat_hash'], $page_cats_array)) {
        $checked_cat = 'checked';
    }

    $form_tpl .= '<div class="form-check">';
    $form_tpl .= '<input type="checkbox" class="form-check-input checkbox-categories" id="cat'.$i.'" name="page_post_categories[]" value="'.$categories[$i]['cat_hash'].'" '.$checked_cat.'>';
    $form_tpl .= '<label class="form-check-label" for="cat'.$i.'">'.$categories[$i]['cat_name'].' <small>('.$categories[$i]['cat_lang'].')</small></label>';
    $form_tpl .= '</div>';
}

$form_tpl .= '</div>'; // col
$form_tpl .= '</div>'; // row

$form_tpl .= '</div>'; // card-body
$form_tpl .= '</div>'; // card

$form_tpl .= '</div>'; // posts tab

$form_tpl .= '<div class="tab-pane fade" id="addons">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-6">';

/* Select Modul */

$select_page_modul = '<select name="page_modul" class="custom-select form-control" id="selMod">';
$select_page_modul .= '<option value="">'.$lang['label_pages_no_addon'].'</option>';

foreach($all_mods as $k => $v) {
    $selected = "";
    $mod_name = $all_mods[$k]['addon']['name'];
    $mod_folder = $k;
    // skip payment plugins
    if(str_ends_with($mod_folder, '-pay')) { continue; }
    // skip delivery addons
    if(str_ends_with($mod_folder, '-delivery')) { continue; }
    if($mod_folder == $page_modul) {
        $selected = 'selected';
    }
    $select_page_modul .= "<option value='$mod_folder' $selected>$mod_name</option>";

}
$select_page_modul .= '</select>';


$form_tpl .= '<div class="form-group">';
$form_tpl .= '<label for="selMod">'.$lang['label_pages_select_addon'].'</label>';
$form_tpl .= $select_page_modul;
$form_tpl .= '</div>';

foreach($all_mods as $k => $v) {
    $show_mod = basename($k);
    $mod_id = md5($k);
    if(is_file(SE_ROOT."/plugins/$show_mod/backend/page_values.php")) {
        $form_tpl .= '<div class="card mb-1">';
        $form_tpl .= '<div class="card-header">' . $show_mod . '</div>';
        $form_tpl .= '<div class="card-body">';
        include SE_ROOT."/plugins/$show_mod/backend/page_values.php";
        $form_tpl .= $plugin_form_tpl;
        $form_tpl .= '</div>';
        $form_tpl .= '</div>';
    }
}

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-6">';
// show hooks, if available

$page_update_hooks = se_get_hook('page_updated');
if (count($page_update_hooks) > 0) {

    $form_tpl .= '<div class="card">';
    $form_tpl .= '<div class="card-header">Hooks</div>';
    $form_tpl .= '<ul class="list-group list-group-flush">';
    foreach ($page_update_hooks as $hook) {
        $form_tpl .= '<li class="list-group-item">';
        $form_tpl .= $hook;
        $form_tpl .= '</ul>';
    }
    $form_tpl .= '</ul>';
    $form_tpl .= '</div>';
}

$form_tpl .= '</div>';
$form_tpl .= '</div>';


$form_tpl .= '</div>'; // addons tab

$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-3">';

// sidebar

$form_tpl .= '<div class="card p-3">';


$form_tpl .= se_print_form_input($input_select_page_status);
$form_tpl .= se_print_form_input($input_select_language);

// select template
$get_themes = get_all_templates();
$select_template = '<select id="select_template" name="select_template"  class="form-control">';
if($page_template == '') {
    $selected_standard = 'selected';
}
$select_template .= "<option value='use_standard<|-|>use_standard' $selected_standard>$lang[label_use_default]</option>";
foreach($get_themes as $template) {

    if($template == 'administration') {continue;}
    $arr_layout_tpl = glob("../public/assets/themes/$template/templates/layout*.tpl");
    $select_template .= "<optgroup label='$template'>";

    foreach($arr_layout_tpl as $layout_tpl) {
        $layout_tpl = basename($layout_tpl);
        $selected = '';
        if($template == "$page_template" && $layout_tpl == "$page_template_layout") {
            $selected = 'selected';
        }
        $select_template .=  "<option $selected value='$template<|-|>$layout_tpl'>$template » $layout_tpl</option>";
    }
    $select_template .= '</optgroup>';
}

$select_template .= '</select>';

$form_tpl .= '<div><label class="form-label">'.$lang['label_template'].'</label>'.$select_template.'</div>';

// set password
$form_tpl .= '<div class="my-2">';
$form_tpl .= '<label class="form-label">'.$lang['label_password'].'</label>';
$placeholder = '';
$reset_psw = '';
if($page_psw != '') {
    $form_tpl .= '<input type="hidden" name="page_psw_relay" value="'.$page_psw.'">';
    $placeholder = '*****';
    $reset_psw  = '<div class="checkbox"><label>';
    $reset_psw .= '<input type="checkbox" name="page_psw_reset" value="reset"> '.$lang['label_password_reset'].'</label></div>';
}
$form_tpl .= '<input class="form-control" type="text" name="page_psw" value="" placeholder="'.$placeholder.'">';
$form_tpl .= $reset_psw;
$form_tpl .= '</div>';

// comments
if($page_comments == 1) {
    $sel_comments_yes = 'selected';
    $sel_comments_no = '';
} else {
    $sel_comments_no = 'selected';
    $sel_comments_yes = '';
}

$form_tpl .= '<div class="mb-2">';
$form_tpl .= '<label class="form-label">'.$lang['label_comments'].'</label>';
$form_tpl .= '<select id="select_comments" name="page_comments"  class="custom-select form-control">';
$form_tpl .= '<option value="1" '.$sel_comments_yes.'>'.$lang['yes'].'</option>';
$form_tpl .= '<option value="2" '.$sel_comments_no.'>'.$lang['no'].'</option>';
$form_tpl .= '</select>';
$form_tpl .= '</div>';

// usergroups
$arr_groups = get_all_groups();
$arr_checked_groups = explode(",",$page_usergroup);

for($i=0;$i<count($arr_groups);$i++) {

    $group_id = $arr_groups[$i]['group_id'];
    $group_name = $arr_groups[$i]['group_name'];

    if(in_array("$group_name", $arr_checked_groups)) {
        $checked = "checked";
    } else {
        $checked = "";
    }

    $checkbox_usergroup .= '<div class="form-check"><label>';
    $checkbox_usergroup .= "<input id='check$group_id' class='form-check-input' type='checkbox' $checked name='set_usergroup[]' value='$group_name'>";
    $checkbox_usergroup .= '<label class="form-check-label" for="check'.$group_id.'">'.$group_name.'</label></div>';
}

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">';
$form_tpl .= '<a href="#" data-bs-toggle="collapse" data-bs-target="#usergroups">'.$lang['label_choose_group'].'</a>';
$form_tpl .= '</div>';
$form_tpl .= '<div class="card-body p-1">';
$form_tpl .= '<div id="usergroups" class="collapse">';
$form_tpl .= $checkbox_usergroup;
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

// labels
$cnt_labels = count($se_labels);
$arr_checked_labels = explode(",", $page_labels);

for($i=0;$i<$cnt_labels;$i++) {
    $label_title = $se_labels[$i]['label_title'];
    $label_id = $se_labels[$i]['label_id'];
    $label_color = $se_labels[$i]['label_color'];

    if(in_array("$label_id", $arr_checked_labels)) {
        $checked_label = "checked";
    } else {
        $checked_label = "";
    }


    $checkbox_set_labels .= '<div class="form-check form-check-inline" style="border-bottom: 1px solid '.$label_color.'">';
    $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="set_page_labels[]" value="'.$label_id.'">';
    $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
    $checkbox_set_labels .= '</div>';

}


$form_tpl .= '<div class="my-2">';
$form_tpl .= '<label>'.$lang['labels'].' '.se_print_docs_tip('tip-labels.md').'</label>';
$form_tpl .= '<div class="p-3">';
$form_tpl .= $checkbox_set_labels;
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '<div class="d-flex justify-content">';
$form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_page" value="'.$form_mode.'">'.$btn_submit_text.'</button>';
if($form_mode != 'new') {
    $form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-trigger="click" hx-confirm="'.$lang['msg_confirm_delete'].'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-default text-danger ms-1" name="delete_page" value="'.$get_page_id.'">'.$lang['btn_delete'].'</button>';
}
$form_tpl .= '</div>';

$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</form>';

echo $form_tpl;

// show older snapshots from this page
if(is_numeric($get_page_id)) {
    echo '<div id="timeWarp" hx-get="/admin-xhr/pages/read/?snapshots=' . $get_page_id . '" hx-trigger="load, updated_pages from:body">Loading Snapshots ...</div>';
}