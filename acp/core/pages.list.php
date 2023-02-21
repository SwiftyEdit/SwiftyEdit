<?php
//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require 'core/access.php';

unset($result);
/* $_SESSION[filter_string] was defined in inc.pages.php */
$sql = "SELECT page_id, page_thumbnail, page_language, page_linkname, page_title, page_meta_description, page_sort, page_lastedit, page_lastedit_from, page_status, page_template, page_modul, page_authorized_users, page_permalink, page_redirect, page_redirect_code, page_labels, page_psw
		FROM se_pages
		$_SESSION[filter_string]
		ORDER BY page_language ASC, page_sort *1 ASC, LENGTH(page_sort), page_sort ASC, page_linkname ASC";

$result = $db_content->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$x=0;
foreach($result as $p) {
	$this_page_id = 'p'.$p['page_id'];
	$count_comments = $db_content->query("Select Count(*) FROM se_comments WHERE comment_parent_id LIKE '$this_page_id' ")->fetch();
	$result[$x]['cnt_comments'] = $count_comments[0];
	$x++;
}

$cnt_result = count($result);

if(!isset($_SESSION['switchPageList']) OR $_SESSION['switchPageList'] == '') {
	$_SESSION['switchPageList'] = 'both';
}

if(isset($_POST['switchPageList'])) {
	if($_POST['switchPageList'] == 'showBothPages') {
		$_SESSION['switchPageList'] = 'both';
	} else if($_POST['switchPageList'] == 'showStructuredPages') {
		$_SESSION['switchPageList'] = 'structured';
	} else if($_POST['switchPageList'] == 'showUnstructuredPages') {
		$_SESSION['switchPageList'] = 'unstructured';
	}
}

$class_switchPageList = array_fill(0, 3, '');

if($_SESSION['switchPageList'] == 'both') {
	$class_col_left = 'col-6';
	$class_col_right = 'col-6';
	$class_switchPageList[0] = 'active';
} else if($_SESSION['switchPageList'] == 'structured') {
	$class_col_left = 'col-12';
	$class_col_right = 'd-none';
	$class_switchPageList[1] = 'active';
} else if($_SESSION['switchPageList'] == 'unstructured') {
	$class_col_left = 'd-none';
	$class_col_right = 'col-12';
	$class_switchPageList[2] = 'active';
}

$item_template = file_get_contents('templates/list-pages-item.tpl');

echo '<div class="subHeader d-flex">';

echo '<form action="?tn=pages&sub=pages-list" method="post">';
echo '<div class="btn-group" role="group">';

echo '<button type="submit" name="switchPageList" value="showBothPages" class="btn btn-default '.$class_switchPageList[0].'">'.$lang['legend_all_pages'].'</button>';
echo '<button type="submit" name="switchPageList" value="showStructuredPages" class="btn btn-default '.$class_switchPageList[1].'">'.$lang['legend_structured_pages'].'</button>';
echo '<button type="submit" name="switchPageList" value="showUnstructuredPages" class="btn btn-default '.$class_switchPageList[2].'">'.$lang['legend_unstructured_pages'].'</button>';


echo '</div>';
echo $hidden_csrf_token;
echo '</form>';

echo '<a href="?tn=pages&sub=new#position" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new_page'].'</a>';

echo '</div>'; //subHeader


echo '<div class="app-container">';
echo '<div class="max-height-container">';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="row">';
echo '<div class="'.$class_col_left.'">';

/**
 * list all pages where page_sort != empty
 */


echo '<div class="card">';
echo '<div class="card-header">' . $lang['legend_structured_pages'] . ' '.se_print_docs_link('tip-ordered-pages.md').'</div>';
echo '<div class="card-body">';
echo '<div class="scroll-box">';
echo '<div class="pages-list-container">';

$sorted_pages = se_list_pages($result,"sorted");
echo $sorted_pages;


echo '</div>';
echo '</div>';
echo '</div>'; // card-body
echo '</div>'; // card

echo '</div>';
echo '<div class="'.$class_col_right.'">';



/**
 * list all pages where
 * page_sort == empty
 * or page_sort == portal
 */

echo '<div class="card">';
echo '<div class="card-header">'.$lang['legend_unstructured_pages'].' '.se_print_docs_link('tip-single-pages.md').'</div>';
echo '<div class="card-body">';

echo '<div class="scroll-box">';
echo '<div class="pages-list-container">';

$single_pages = se_list_pages($result,"single");
echo $single_pages;

echo '</div>';
echo '</div>';

echo '</div>'; // card-body
echo '</div>'; // card


echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';


/* sidebar */

echo '<div class="card">';
echo '<div class="card-header">FILTER</div>';
echo '<div class="card-body">';

echo $kw_form;

if(isset($btn_remove_keyword)) {
	echo '<div class="d-inline">';
	echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
	echo '</div><hr>';
}

echo $nav_btn_group;


echo '</div>'; // card-body
echo '</div>'; // card

/* end of sidebar */

echo '</div>';
echo '</div>';

echo '</div>'; // .max-height-container
echo '</div>'; // .app-container


/**
 * @param $data array page contents
 * @param $type string sorted|single
 * @return string
 */

function se_list_pages($data,$type="sorted") {

    global $item_template;
    global $lang;
    global $icon;
    global $hidden_csrf_token;
    global $se_labels;

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
        $page_labels = explode(',',$data[$i]['page_labels']);
        $page_thumbs = explode('<->',$data[$i]['page_thumbnail']);
        $pi = $data[$i]['page_hits'];

        $page_thumb_src = 'images/swiftyedit-page-icon.png';
        if($page_thumbs[0] != '') {
            $page_thumb_src = $page_thumbs[0];
        }

        $page_lang_thumb = '<img src="/lib/lang/'.$page_language.'/flag.png" width="15" title="'.$page_language.'" alt="'.$page_language.'">';

        if($page_template == "use_standard") {
            $show_template_name =  "$lang[use_standard]";
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
            $page_description = '<span class="text-danger">'.$icon['exclamation_triangle'].' '.$lang['alert_no_page_description'].'</span>';
        }

        if($page_title == '') {
            $page_title = '<span class="text-danger">'.$icon['exclamation_triangle'].' '.$lang['alert_no_page_title'].'</span>';
        }

        if($page_sort == 'portal') {
            $page_linkname = $icon['home'].' ' . $page_linkname;
        }

        $points_of_page = substr_count($page_sort, '.');
        $indent = ($points_of_page)*10 . 'px';

        if($page_status == "public") {
            //$btn = 'ghost-btn-public';
            $item_class = 'page-list-item-public';
            $status_label = $lang['f_page_status_puplic'];
        } elseif($page_status == "ghost") {
            //$btn = 'ghost-btn-ghost';
            $item_class = 'page-list-item-ghost';
            $status_label = $lang['f_page_status_ghost'];
        } elseif($page_status == "private") {
            //$btn = 'ghost-btn-private';
            $item_class = 'page-list-item-private';
            $status_label = $lang['f_page_status_private'];
        } elseif($page_status == "draft") {
            //$btn = 'ghost-btn-draft';
            $item_class = 'page-list-item-draft';
            $status_label = $lang['f_page_status_draft'];
        }

        if($page_redirect != '') {
            $page_redirect = $icon['long_arrow_alt_right'].' '.$page_redirect;
            $item_class .= ' page-list-item-redirect';
        }

        $last_edit = se_format_datetime($page_lastedit) . " ($page_lastedit_from)";

        /* check for display edit button */

        if($_SESSION['acp_editpages'] == "allowed"){
            $edit_button = '<button class="dropdown-item" name="editpage" value="'.$page_id.'" title="'.$lang['edit'].'">'.$icon['edit'].' '.$lang['btn_edit_page'].'</button>';
            $edit_button_fast = '<button class="btn btn-sm btn-default" name="editpage" value="'.$page_id.'" title="'.$lang['edit'].'">'.$icon['edit'].'</button>';
            $duplicate_button = '<button class="dropdown-item" name="duplicate" value="'.$page_id.'" title="'.$lang['duplicate'].'">'.$icon['copy'].' '.$lang['duplicate'].'</button>';
        } else {
            $edit_button = '';
            $edit_button_fast = '';
            $duplicate_button = '';
        }

        $info_button = '<a class="dropdown-item page-info-btn" data-bs-toggle="modal" data-bs-target="#infoModal" data-id="'.$page_id.'" data-token="'.$_SESSION['token'].'" title="info">'.$icon['info_circle'].' Info</a>';

        $arr_checked_admins = explode(",",$page_authorized_users);
        if(in_array("$_SESSION[user_nick]", $arr_checked_admins)) {
            $edit_button = '<button class="dropdown-item" name="editpage" value="'.$page_id.'" title="'.$lang['edit'].'">'.$icon['edit'].' '.$lang['btn_edit_page'].'</button>';
            $edit_button_fast = '<button class="btn btn-sm btn-default" name="editpage" value="'.$page_id.'" title="'.$lang['edit'].'">'.$icon['edit'].'</button>';

        }

        $label = '';
        if($data[$i]['page_labels'] != '') {
            foreach($page_labels as $page_label) {

                foreach($se_labels as $l) {
                    if($page_label == $l['label_id']) {
                        $label_color = $l['label_color'];
                        $label_title = $l['label_title']. ' '.$l['label_description'];
                    }
                }

                $label .= '<span class="label-dot" style="background-color:'.$label_color.';" data-bs-toggle="tooltip" data-bs-title="'.$label_title.'"></span>';
            }
        }

        $frontend_link = "../$page_permalink";

        $show_mod = '';
        if($page_modul != '') {
            $page_modul_title = substr($page_modul, 0,-4);
            $show_mod = ' <small>'.$icon['cog'].' '.$page_modul_title.'</small><br>';
        }

        if($page_redirect != '') {
            if($_SESSION['checked_redirect'] != "checked") {
                continue;
            }
        }

        $page_comments_link = '';

        $str = array(
            '{status-label}','{item-linkname}','{item-title}','{item-tmb-src}','{label_edit}',
            '{item-mod}','{item-class}','{item-indent}','{edit-btn}','{edit-btn-fast}','{duplicate-btn}','{info-btn}',
            '{comment-btn}','{item-permalink}','{item-lastedit}','{item-pagesort}','{item-template}',
            '{item-redirect}','{frontend-link}','{item-description}','{item-lang}', '{page_labels}','{item-pi}','{hidden_csrf_tokken}'
        );
        $rplc = array(
            $status_label,$page_linkname,$page_title,$page_thumb_src,$lang['edit'],
            $show_mod,$item_class,$indent,$edit_button,$edit_button_fast,$duplicate_button,$info_button,
            $page_comments_link,$page_permalink,$last_edit,$page_sort, $show_template_name,
            $page_redirect,$frontend_link,$page_description,$page_lang_thumb,$label,$pi,$hidden_csrf_token
        );


        $this_template = str_replace($str, $rplc, $item_template);
        $listing .= $this_template;
    }

    return $listing;
}