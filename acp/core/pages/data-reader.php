<?php

use Medoo\Medoo;

$writer_uri = '/admin/pages/edit/';
$duplicate_uri = '/admin/pages/duplicate/';

$se_labels = se_get_labels();

$global_filter_languages = json_decode($_SESSION['global_filter_languages'],true);
$global_filter_status = json_decode($_SESSION['global_filter_status'],true);
$global_filter_label = json_decode($_SESSION['global_filter_label'],true);
$item_template = file_get_contents('../acp/templates/list-pages-item.tpl');

$sort_single_pages = 'page_lastedit';
$sort_single_pages_direction = 'DESC';


if(!isset($_SESSION['sorting_single_pages'])) {
    $_SESSION['sorting_single_pages'] = $sort_single_pages;
}
if(!isset($_SESSION['sorting_single_pages_dir'])) {
    $_SESSION['sorting_single_pages_dir'] = $sort_single_pages_direction;
}

// list pages
if($_REQUEST['action'] == 'list_pages') {

    $type = (int) $_REQUEST['type']; // 1 = sorted 2 = single

    // defaults
    $order_by = 'page_lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_get_pages'] ?? 0;
    $nbr_show_items = (int) ($_SESSION['items_per_page'] ?? 25);

    $match_str = $_SESSION['pages_text_filter'] ?? '';
    $keyword_str = $_SESSION['pages_keyword_filter'] ?? '';
    $page_type_str = $_SESSION['checked_page_type_string'] ?? '';
    $order_key = $_SESSION['sorting_single_pages'] ?? $order_by;
    $order_direction = $_SESSION['sorting_single_pages_dir'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    if ($type == 1) {
        $filter_base = [
            'AND' => [
                'AND' => [
                    'page_sort[!]' => null,
                    'page_sort[!] #empty' => ''
                ]
            ]
        ];
    } else {
        $filter_base = [
            'AND' => [
                'OR' => [
                    'page_sort' => null,
                    'page_sort #empty' => ''
                ]
            ]
        ];
    }

    // text search
    $filter_by_str = [];
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "page_title[~]" => "%$f%",
                    "page_meta_description[~]" => "%$f%",
                    "page_meta_keywords[~]" => "%$f%",
                    "page_content[~]" => "%$f%",
                    "page_content_values[~]" => "%$f%",
                    "page_permalink[~]" => "%$f%"
                ]
            ];
        }
    }

    // filter by keywords
    $filter_by_keyword = [];
    if($keyword_str != '') {
        $this_filter = explode(",",$keyword_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_keyword = [
                "page_meta_keywords[~]" => "$f"
            ];
        }
    }

    // filter by page types
    $filter_by_page_types = [];
    if($page_type_str != '') {
        $this_filter = explode(" ",$page_type_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_page_types = [
                "page_type_of_use[~]" => "$f"
            ];
        }
    }

    // global language filter
    $filter_by_language = [];
    if(is_array($global_filter_languages)) {
        $lang_filter = array_filter($global_filter_languages);
        $filter_by_language = [
            "page_language[~]" => $lang_filter
        ];
    }

    // global status filter and redirects
    $filter_by_status = [];
    $filter_by_redirect = [];
    if(is_array($global_filter_status)) {
        $status_filter = array_filter($global_filter_status);

        $index = array_search(5,$status_filter);
        if ($index !== false) {
            $filter_by_redirect = [
                "page_redirect[!]" => ' '
            ];
        }

        $index = array_search(1,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'public'; }
        $index = array_search(2,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'draft'; }
        $index = array_search(3,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'private'; }
        $index = array_search(4,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'ghost'; }
        $index = array_search(5,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'redirect'; }
        $filter_by_status = [
            "page_status[~]" => $status_filter
        ];
    }

    // global label filter
    $filter_by_label = [];
    if(is_array($global_filter_label)) {
        $label_filter = array_filter($global_filter_label);
        $filter_by_label = [
            "page_labels[~]" => $label_filter
        ];
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_keyword+$filter_by_page_types+$filter_by_language+$filter_by_status+$filter_by_redirect+$filter_by_label
    ];

    // order single pages
    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    if($type == 1) {
        $db_order = [
            "ORDER" => Medoo::raw("page_language ASC, page_sort *1 ASC, LENGTH(page_sort), page_sort ASC")
        ];
    }

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $pages_data_cnt = $db_content->count("se_pages", $db_where);
    $pages_data = $db_content->select("se_pages","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pag_pages = ceil($pages_data_cnt/$nbr_show_items);
    
    echo '<div class="card p-3">';
    echo '<div class="d-flex justify-content-end">';
    echo '<div>';
    echo se_print_pagination('/admin/xhr/pages/write/',$nbr_pag_pages,$_SESSION['pagination_get_pages']);
    echo '</div>';
    echo '<div class="ps-3">';
    echo '<div class="input-group mb-3">';
    echo '<input type="number" class="form-control" hx-post="/admin/xhr/pages/write/" hx-swap="none" hx-trigger="keyup delay:500ms changed" hx-include="[name=\'csrf_token\']" name="items_per_page" min="5" max="99" value="'.$nbr_show_items.'">';
    echo '<span class="input-group-text" id="basic-addon2"> / '.$pages_data_cnt.'</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo se_list_pages($pages_data);
    echo '</div>';


}

/**
 * list active keywords from search input
 * used in sidebar
 */
if($_REQUEST['action'] == 'list_active_searches') {

    if(isset($_SESSION['pages_text_filter']) AND $_SESSION['pages_text_filter'] != "") {
        unset($all_filter);
        $all_filter = explode(" ", $_SESSION['pages_text_filter']);

        foreach($all_filter as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin/xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if(isset($btn_remove_keyword)) {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
    exit;
}

/**
 * list all keywords
 * used in sidebar
 */
if($_REQUEST['action'] == 'list_keyword_btn') {
    $get_keywords = se_get_pages_keywords();
    arsort($get_keywords);
    $vals = ['csrf_token' => $_SESSION['token']];
    echo '<div class="scroll-container">';
    foreach($get_keywords as $k => $v) {
        $k = trim($k);
        if(str_contains($_SESSION['pages_keyword_filter'],$k)) {
            echo '<button name="remove_keyword" value="'.$k.'" hx-post="/admin/xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\' class="btn btn-default active btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        } else {
            echo '<button name="add_keyword" value="'.$k.'" hx-post="/admin/xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\' class="btn btn-default btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        }
    }
    echo '</div>';
    exit;
}

/**
 * list all page types
 * used in the sidebar
 */

if($_REQUEST['action'] == 'list_page_types') {

    $find_target_page = $db_content->select("se_pages", "page_type_of_use", [
        "page_type_of_use" => $se_page_types
    ]);

    $cnt_page_types = array_count_values($find_target_page);
    echo '<div class="scroll-container">';
    foreach($se_page_types as $types) {
        $str = 'type_of_use_'.$types;
        $name = $lang[$str];
        $classes = 'list-group-item list-group-item-action d-flex justify-content-between align-items-start';
        if(str_contains($_SESSION['checked_page_type_string'],"$types")) {
            $classes .= ' active';
        }
        $vals = ['csrf_token' => $_SESSION['token']];
        echo '<button class=" '.$classes.'" name="filter_type" value="'.$types.'" hx-post="/admin/xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>';
        echo '<div class="me-auto">'.$name.'</div>';
        if($cnt_page_types[$types] < 1) {
            echo '<span class="badge text-bg-danger">0</span>';
        } else {
            echo '<span class="badge text-bg-secondary">' . $cnt_page_types[$types] . '</span>';
        }
        echo '</button>';
    }
    echo '</div>';
    exit;
}

// show snapshots of pages
if(isset($_REQUEST['snapshots']) && is_numeric($_REQUEST['snapshots'])) {
    $snapshot_id = (int) $_REQUEST['snapshots'];

    $max = (int) $se_settings['nbr_page_versions'] ?: 25;
    $cnt_all_snapshots = $db_content->count("se_pages_cache",[
        "page_id_original" => $snapshot_id,
        "page_cache_type" => "history"
    ]);

    $delete_nbr = $cnt_all_snapshots-$max;

    $get_snapshots = $db_content->select("se_pages_cache",["page_id", "page_linkname", "page_title", "page_lastedit", "page_lastedit_from"],[
        "AND" => [
            "page_id_original" => $snapshot_id,
            "page_cache_type" => "history"
            ],
        "ORDER" => [
            "page_id" => "DESC"
        ]
    ]);

    echo '<div class="card my-3">';
    echo '<div class="accordion accordion-flush" id="accordionVersions">';
    echo '<div class="accordion-item">';
    echo '<div class="accordion-header" id="headingOne">';
    echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVersions" aria-expanded="false" aria-controls="collapseOne">Versions ('.$cnt_all_snapshots.')</button>';
    echo '</div>';
    echo '<div id="collapseVersions" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionVersions">';
    echo '<div class="accordion-body">';
    echo '<div class="scroll-container">';
    echo '<table class="table table-hover">';
    foreach($get_snapshots as $snapshot) {

        $page_id = $snapshot['page_id'];

        echo '<tr>';
        echo '<td>'.se_format_datetime($snapshot['page_lastedit']).'</td>';
        echo '<td>'.htmlentities($snapshot['page_title']).'</td>';
        echo '<td>'.htmlentities($snapshot['page_lastedit_from']).'</td>';
        echo '<td>';
        echo '<form action="/admin/pages/edit/" method="POST">';
        echo '<button class="btn btn-sm btn-default w-100" name="restore_id" value="'.$page_id.'" title="'.$lang['edit'].'">'.$icon['edit'].' '.$lang['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>'; // scroll-container
    echo '</div>'; // accordion-body
    echo '</div>'; // collapse
    echo '</div>'; // accordion-item
    echo '</div>'; // accordion
    echo '</div>';

}

if(isset($_GET['page_info'])) {
    include 'pages-info.php';
}

/**
 * @param $data array page contents
 * @param $type string sorted|single
 * @return string
 */

function se_list_pages($data) {

    global $item_template, $global_filter_status, $lang, $icon, $hidden_csrf_token, $se_labels, $writer_uri, $duplicate_uri;

    $item_template_vars = array(
        '{status-label}','{item-linkname}','{item-title}','{item-tmb-src}','{label_edit}',
        '{item-mod}','{item-class}','{item-indent}','{edit-btn}','{duplicate-btn}','{info-btn}',
        '{comment-btn}','{item-permalink}','{item-lastedit}','{item-pagesort}','{item-template}',
        '{item-redirect}','{frontend-link}','{item-description}','{item-lang}', '{page_labels}','{item-pi}','{hidden_csrf_tokken}'
    );

    $listing = '';
    $cnt_pages = 0;

    if(is_array($data)) {
        $cnt_pages = count($data);
    }

    for($i=0;$i<$cnt_pages;$i++) {

        unset($show_redirect,$page_modul);
        $indent = 0;

        $page_id = $data[$i]['page_id'];
        $page_sort = $data[$i]['page_sort'];
        $page_linkname = $data[$i]['page_linkname'];
        $page_title = $data[$i]['page_title'];
        $page_description = $data[$i]['page_meta_description'];
        $page_status = $data[$i]['page_status'];
        $page_lastedit = $data[$i]['page_lastedit'];
        $page_lastedit_from = $data[$i]['page_lastedit_from'];
        $page_template = $data[$i]['page_template'];
        $page_authorized_users = $data[$i]['page_authorized_users'];
        $page_language = $data[$i]['page_language'];
        $page_permalink = $data[$i]['page_permalink'];
        $page_redirect = $data[$i]['page_redirect'];
        $page_modul = $data[$i]['page_modul'];
        $pi = $data[$i]['page_hits'];
        $page_labels = [];
        if($data[$i]['page_labels'] != '') {
            $page_labels = explode(',',$data[$i]['page_labels']);
        }

        $page_thumbs = array();
        if($data[$i]['page_thumbnail'] != '') {
            $page_thumbs = explode('<->',html_entity_decode($data[$i]['page_thumbnail']));
        }

        $page_thumb_src = '/assets/themes/administration/images/swiftyedit-page-icon.png';
        if(isset($page_thumbs) AND $page_thumbs[0] != '') {
            $page_thumb_src = str_replace('../content/images/','/images/',$page_thumbs[0]);
        }

        $page_lang_thumb = '<img src="'.return_language_flag_src($page_language).'" width="15" title="'.$page_language.'" alt="'.$page_language.'">';

        if($page_template == "use_standard") {
            $show_template_name =  $lang['label_default_template'];
        } else {
            $show_template_name = "$page_template";
        }

        if($data[$i]['page_psw'] != '') {
            $page_title = $icon['lock'].' '.$page_title;
        }

        if(strlen($page_description) > 100) {
            $page_description = substr($page_description, 0, 100) .' <small>(&hellip;)</small>';
        }

        if($page_description == '') {
            $page_description = '<span class="text-danger">'.$icon['exclamation_triangle'].' '.$lang['msg_error_no_description'].'</span>';
        }

        if($page_title == '') {
            $page_title = '<span class="text-danger">'.$icon['exclamation_triangle'].' '.$lang['msg_error_no_title'].'</span>';
        }

        if($page_sort == 'portal') {
            $page_linkname = $icon['home'].' ' . $page_linkname;
        }

        $points_of_page = substr_count($page_sort, '.');
        $indent = ($points_of_page)*10 . 'px';

        if($page_status == "public") {
            $item_class = 'page-list-item-public';
            $status_label = $lang['status_puplic'];
        } elseif($page_status == "ghost") {
            $item_class = 'page-list-item-ghost';
            $status_label = $lang['status_ghost'];
        } elseif($page_status == "private") {
            $item_class = 'page-list-item-private';
            $status_label = $lang['status_private'];
        } elseif($page_status == "draft") {
            $item_class = 'page-list-item-draft';
            $status_label = $lang['status_draft'];
        }

        if($page_redirect != '') {
            $page_redirect = $icon['long_arrow_alt_right'].' '.$page_redirect;
            $item_class .= ' page-list-item-redirect';
        }

        $last_edit = se_format_datetime($page_lastedit) . " ($page_lastedit_from)";

        /* check for display edit button */

        $btn_edit_tpl  = '<form action="'.$writer_uri.'" method="post" class="d-inline flex-fill me-1">';
        $btn_edit_tpl .= '<button class="btn btn-sm btn-default text-success w-100" name="page_id" value="'.$page_id.'">'.$icon['edit'].'</button>';
        $btn_edit_tpl .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit_tpl .=  '</form>';

        $btn_duplicate_tpl  = '<form action="'.$duplicate_uri.'" method="post" class="d-inline flex-fill me-1">';
        $btn_duplicate_tpl .= '<button class="btn btn-sm btn-default w-100" name="duplicate_id" value="'.$page_id.'">'.$icon['copy'].'</button>';
        $btn_duplicate_tpl .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_duplicate_tpl .=  '</form>';

        if($_SESSION['acp_editpages'] == "allowed") {

            $edit_button = $btn_edit_tpl;
            $duplicate_button = $btn_duplicate_tpl;

        } else {
            $edit_button = '';
            $duplicate_button = '';
        }

        $info_button = '<a class="btn btn-sm btn-default flex-fill" hx-get="/admin/xhr/pages/read/" hx-vals=\'{"page_info":"'.$page_id.'"}\' hx-target="#infoModal" data-bs-toggle="modal" data-bs-target="#infoModal" title="info">'.$icon['info_circle'].'</a>';
        $arr_checked_admins = explode(",",$page_authorized_users);
        if(in_array($_SESSION['user_nick'], $arr_checked_admins)) {
            $edit_button = $btn_edit_tpl;
            $duplicate_button = $btn_duplicate_tpl;
        }

        $label = '';
        if(is_array($page_labels)) {
            foreach($page_labels as $page_label) {

                foreach($se_labels as $l) {
                    if($page_label == $l['label_id']) {
                        $label_color = $l['label_color'];
                        $label_title = $l['label_title']. ' '.$l['label_description'];
                        $label .= '<span class="label-dot" style="background-color:'.$label_color.';" data-bs-toggle="tooltip" data-bs-title="'.$label_title.'"></span>';
                    }
                }
            }
        }

        $frontend_link = "/$page_permalink";

        if($page_redirect != '') {
            if((is_array($global_filter_status)) && !in_array("5",$global_filter_status)) {
                continue;
            }
        }

        $page_comments_link = '';


        $replace = array(
            $status_label,$page_linkname,$page_title,$page_thumb_src,$lang['edit'],
            $page_modul,$item_class,$indent,$edit_button,$duplicate_button,$info_button,
            $page_comments_link,$page_permalink,$last_edit,$page_sort, $show_template_name,
            $page_redirect,$frontend_link,$page_description,$page_lang_thumb,$label,$pi,$hidden_csrf_token
        );


        $this_template = str_replace($item_template_vars, $replace, $item_template);
        $listing .= $this_template;
    }

    return $listing;
}