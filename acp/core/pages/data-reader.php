<?php
$writer_uri = '/admin/pages/edit/';
$duplicate_uri = '/admin/pages/duplicate/';

$global_filter_languages = json_decode($_SESSION['global_filter_languages']);
$global_filter_status = json_decode($_SESSION['global_filter_status']);
$global_filter_label = json_decode($_SESSION['global_filter_label']);

$item_template = file_get_contents('../acp/templates/list-pages-item.tpl');

$sort_single_pages = 'page_lastedit';
$sort_single_pages_direction = 'DESC';

/*
if(isset($_POST['sorting_single_pages_dir'])) {
    if($_POST['sorting_single_pages_dir'] == 'desc') {
        $_SESSION['sorting_single_pages_dir'] = 'DESC';
    } else {
        $_SESSION['sorting_single_pages_dir'] = 'ASC';
    }
}
*/

/*
if(isset($_POST['sorting_single_pages'])) {
    if($_POST['sorting_single_pages'] == 'linkname') {
        $_SESSION['sorting_single_pages'] = 'page_linkname';
    } else if($_POST['sorting_single_pages'] == 'priority') {
        $_SESSION['sorting_single_pages'] = 'page_priority';
    } else {
        $_SESSION['sorting_single_pages'] = 'page_lastedit';
    }
}
*/

if(!isset($_SESSION['sorting_single_pages'])) {
    $_SESSION['sorting_single_pages'] = $sort_single_pages;
}
if(!isset($_SESSION['sorting_single_pages_dir'])) {
    $_SESSION['sorting_single_pages_dir'] = $sort_single_pages_direction;
}


if(is_array($global_filter_languages)) {
    $pages_filter['languages'] = implode("-", $global_filter_languages);
}
if(is_array($global_filter_status)) {
    $pages_filter['status'] = implode("-", $global_filter_status);
}
if(is_array($global_filter_label)) {
    $pages_filter['labels'] = implode("-", $global_filter_label);
}

$pages_filter['types'] = $_SESSION['checked_page_type_string'];
$pages_filter['text'] = $_SESSION['pages_text_filter'];
$pages_filter['sort_by'] = $_SESSION['sorting_single_pages'];
$pages_filter['sort_direction'] = $_SESSION['sorting_single_pages_dir'];

/**
 * sorted pages
 */
if($_REQUEST['action'] == 'list_pages_sorted') {


    $pages_filter['sort_type'] = 'sorted';
    $pages = se_get_pages($pages_filter);

    $sorted_pages = se_list_pages($pages,"sorted");
    echo $sorted_pages;

    exit;

}

/**
 * single pages
 */
if($_REQUEST['action'] == 'list_pages_single') {

    $pages_filter['sort_type'] = 'single';
    $pages = se_get_pages($pages_filter);

    $single_pages = se_list_pages($pages,"single");
    echo $single_pages;

    exit;

}


/**
 * @param $data array page contents
 * @param $type string sorted|single
 * @return string
 */

function se_list_pages($data,$type="sorted") {

    global $item_template, $global_filter_status;
    global $lang;
    global $icon;
    global $hidden_csrf_token;
    global $se_labels;
    global $writer_uri, $duplicate_uri;

    $listing = '';
    $cnt_pages = 0;

    if(is_array($data)) {
        $cnt_pages = count($data);
    }

    for($i=0;$i<$cnt_pages;$i++) {


        if($type == 'sorted' && $data[$i]['page_sort'] == "") {
            continue;
        }

        if($type == 'single' && ($data[$i]['page_sort'] != "" OR $data[$i]['page_sort'] == 'portal')) {
            continue;
        }


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
        $page_cnt_comments = $data[$i]['cnt_comments'];
        $pi = $data[$i]['page_hits'];
        if($data[$i]['page_labels'] != '') {
            $page_labels = explode(',',$data[$i]['page_labels']);
        }

        $page_thumbs = array();
        if($data[$i]['page_thumbnail'] != '') {
            $page_thumbs = explode('<->',html_entity_decode($data[$i]['page_thumbnail']));
        }

        $page_thumb_src = '/assets/themes/administration/images/swiftyedit-page-icon.png';
        if(isset($page_thumbs) AND $page_thumbs[0] != '') {
            $page_thumb_src = str_replace('../','/',$page_thumbs[0]);
        }

        $page_lang_thumb = '<img src="/assets/lang/'.$page_language.'/flag.png" width="15" title="'.$page_language.'" alt="'.$page_language.'">';

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

        $btn_edit_tpl  = '<form action="'.$writer_uri.'" method="post" class="d-inline">';
        $btn_edit_tpl .= '<button class="btn btn-default" name="page_id" value="'.$page_id.'">'.$icon['edit'].'</button>';
        $btn_edit_tpl .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit_tpl .=  '</form>';

        $btn_duplicate_tpl  = '<form action="'.$duplicate_uri.'" method="post" class="d-inline">';
        $btn_duplicate_tpl .= '<button class="btn btn-default" name="duplicate_id" value="'.$page_id.'">'.$icon['copy'].'</button>';
        $btn_duplicate_tpl .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_duplicate_tpl .=  '</form>';

        if($_SESSION['acp_editpages'] == "allowed") {

            $edit_button = $btn_edit_tpl;
            $duplicate_button = $btn_duplicate_tpl;

        } else {
            $edit_button = '';
            $duplicate_button = '';
        }

        $info_button = '<a class="btn btn-sm btn-default" data-bs-toggle="modal" data-bs-target="#infoModal" data-id="'.$page_id.'" data-token="'.$_SESSION['token'].'" title="info">'.$icon['info_circle'].'</a>';

        $arr_checked_admins = explode(",",$page_authorized_users);
        if(in_array($_SESSION['user_nick'], $arr_checked_admins)) {
            $edit_button = $btn_edit_tpl;
            $duplicate_button = $btn_duplicate_tpl;
        }

        $label = '';
        if($data[$i]['page_labels'] != '') {
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

        $frontend_link = "../$page_permalink";

        $show_mod = '';
        if($page_modul != '') {
            $page_modul_title = substr($page_modul, 0,-4);
            $show_mod = ' <small>'.$icon['cog'].' '.$page_modul_title.'</small><br>';
        }

        if($page_redirect != '') {
            if(!in_array("5",$global_filter_status)) {
                continue;
            }
        }

        $page_comments_link = '';

        $str = array(
            '{status-label}','{item-linkname}','{item-title}','{item-tmb-src}','{label_edit}',
            '{item-mod}','{item-class}','{item-indent}','{edit-btn}','{duplicate-btn}','{info-btn}',
            '{comment-btn}','{item-permalink}','{item-lastedit}','{item-pagesort}','{item-template}',
            '{item-redirect}','{frontend-link}','{item-description}','{item-lang}', '{page_labels}','{item-pi}','{hidden_csrf_tokken}'
        );
        $rplc = array(
            $status_label,$page_linkname,$page_title,$page_thumb_src,$lang['edit'],
            $show_mod,$item_class,$indent,$edit_button,$duplicate_button,$info_button,
            $page_comments_link,$page_permalink,$last_edit,$page_sort, $show_template_name,
            $page_redirect,$frontend_link,$page_description,$page_lang_thumb,$label,$pi,$hidden_csrf_token
        );


        $this_template = str_replace($str, $rplc, $item_template);
        $listing .= $this_template;
    }

    return $listing;
}