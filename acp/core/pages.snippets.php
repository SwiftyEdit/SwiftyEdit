<?php
//error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
//prohibit unauthorized access
require 'core/access.php';
$system_snippets_str = "'footer_text','extra_content_text','agreement_text','account_confirm','account_confirm_mail','no_access'";
$system_snippets = explode(',',str_replace("'",'',$system_snippets_str));
$modus = 'new';

/* update missing snippet_type for core snippets */
$upd_core_snippets = $db_content->update("se_snippets", [
	"snippet_type" =>  "snippet_core"
], [
	"snippet_name" => $system_snippets
]);

/* update missing snippet_type for user generated snippets */
$upd_core_snippets = $db_content->update("se_snippets", [
	"snippet_type" =>  "snippet"
], [
	"OR" => [
	"snippet_type" => [null,"null",""]
	]
]);


if(isset($_REQUEST['suggest_name'])) {
	$snippet_name = clean_filename($_REQUEST['suggest_name']);
}

if(isset($_REQUEST['type'])) {
	if($_REQUEST['type'] == '1') {
		$_SESSION['type'] = 'all';
	} else if($_REQUEST['type'] == '2') {
		$_SESSION['type'] = 'system';
	} else if($_REQUEST['type'] == '3') {
		$_SESSION['type'] = 'own';
	}
}

if(empty($_SESSION['type'])) {
	$_SESSION['type'] = 'all';
}

${'active_'.$_SESSION['type']} = 'active';


/**
 * delete snippet
 */

if(isset($_POST['delete_snippet'])) {

	$delete_snip_id = (int) $_POST['snip_id'];
	
	$cnt_changes=$db_content->delete("se_snippets",[
			"snippet_id" => $delete_snip_id
		]);

	if(($cnt_changes->rowCount()) > 0){
		$sys_message = '{OKAY} '. $lang['db_changed'];
		record_log($_SESSION['user_nick'],"deleted snippet id: $delete_snip_id","10");
		$modus = 'new';
	} else {
		$sys_message = '{ERROR} ' . $lang['db_not_changed'];
	}
	
	print_sysmsg("$sys_message");
}


/* Save Textsnippet */
if(isset($_POST['save_snippet'])) {
	
	foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = se_return_clean_value($val);
        }
	}

	$snippet_name = clean_filename($_POST['snippet_name']);
	$timestamp =  time();
	

	$snippet_themes = explode('<|-|>', $_POST['select_template']);
	$snippet_theme = $snippet_themes[0];
	$snippet_template = $snippet_themes[1];
	
	$snippet_thumbnail = '';
	if(is_array($_POST['picker1_images'])) {
		if(count($_POST['picker1_images']) > 1) {		
			$snippet_thumbnail = implode("<->", array_unique($_POST['picker1_images']));
		} else {
			$st = $_POST['picker1_images'];
			$snippet_thumbnail = $st[0].'<->';
		}
	}
	
	if($snippet_name == '') {
		$snippet_name = date("Y_m_d_h_i",time());
	}
	
	/* labels */
	$arr_labels = $_POST['snippet_labels'];
	if(is_array($arr_labels)) {
		sort($arr_labels);
		$string_labels = implode(",", $arr_labels);
	} else {
		$string_labels = "";
	}
	
	$snippet_priority = (int) $_POST['snippet_priority'];
	
	

	
	if($_POST['modus'] == 'update') {
		
		$snip_id = (int) $_POST['snip_id'];
		
		$data = $db_content->update("se_snippets", [
			"snippet_content" =>  $_POST['snippet_content'],
			"snippet_name" => $snippet_name,
			"snippet_lang" => $_POST['sel_language'],
			"snippet_notes" => $snippet_notes,
			"snippet_groups" => $snippet_groups,
			"snippet_title" => $snippet_title,
			"snippet_keywords" => $snippet_keywords,
			"snippet_priority" => $snippet_priority,
			"snippet_lastedit" => $timestamp,
			"snippet_lastedit_from" => $_SESSION['user_nick'],
			"snippet_template" => $snippet_template,
			"snippet_theme" => $snippet_theme,
			"snippet_images" => $snippet_thumbnail,
			"snippet_labels" => $string_labels,
            "snippet_label" => $snippet_label,
			"snippet_classes" => $snippet_classes,
			"snippet_permalink" => $snippet_permalink,
			"snippet_permalink_title" => $snippet_permalink_title,
			"snippet_permalink_name" => $snippet_permalink_name,
			"snippet_permalink_classes" => $snippet_permalink_classes
		], [
		    "snippet_id" => $snip_id
		]);

        if($data->rowCount() > 0) {
            show_toast($lang['db_changed'],'success');
            record_log("$_SESSION[user_nick]","edit textlib <strong>$snippet_name</strong>","2");
        } else {
            show_toast($lang['db_not_changed'],'danger');
        }
	
	} else {
		
		$db_content->insert("se_snippets", [
			"snippet_content" =>  $_POST['snippet_content'],
			"snippet_name" => $snippet_name,
			"snippet_type" => 'snippet',
			"snippet_lang" => $_POST['sel_language'],
			"snippet_notes" => $snippet_notes,
			"snippet_groups" => $snippet_groups,
			"snippet_title" => $snippet_title,
			"snippet_keywords" => $snippet_keywords,
			"snippet_priority" => $snippet_priority,
			"snippet_lastedit" => $timestamp,
			"snippet_lastedit_from" => $_SESSION['user_nick'],
			"snippet_template" => $snippet_template,
			"snippet_theme" => $snippet_theme,
			"snippet_images" => $snippet_thumbnail,
			"snippet_labels" => $string_labels,
            "snippet_label" => $snippet_label,
			"snippet_classes" => $snippet_classes,
			"snippet_permalink" => $snippet_permalink,
			"snippet_permalink_title" => $snippet_permalink_title,
			"snippet_permalink_name" => $snippet_permalink_name,
			"snippet_permalink_classes" => $snippet_permalink_classes
		]);

        $snip_id = $db_content->id();
        if($snip_id > 0) {
            $modus = 'update';
            show_toast($lang['db_changed'],'success');
            record_log("$_SESSION[user_nick]","insert textlib <strong>$snippet_name</strong>","2");
        } else {
            show_toast($lang['db_not_changed'],'danger');
        }

		
	}
} // eol save text



/**
 * get all saved snippets
 */


/* expand filter */
if(isset($_POST['snippet_filter']) && (trim($_POST['snippet_filter']) != '')) {
	$_SESSION['snippet_filter'] = $_SESSION['snippet_filter'] . ' ' . clean_filename($_POST['snippet_filter']);
}

/* remove keyword from filter list */
if($_REQUEST['rm_keyword'] != "") {
	$all_snippet_filter = explode(" ", $_SESSION['snippet_filter']);
	unset($_SESSION['snippet_filter'],$f);
	foreach($all_snippet_filter as $f) {
		if($_REQUEST['rm_keyword'] == "$f") { continue; }
		if($f == "") { continue; }
		$_SESSION['snippet_filter'] .= "$f ";
	}
	unset($all_snippet_filter);
}

if($_SESSION['snippet_filter'] != "") {
	unset($all_snippet_filter);
	$btn_remove_keyword = '';
	$all_snippet_filter = explode(" ", $_SESSION['snippet_filter']);
	foreach($all_snippet_filter as $f) {
		if($_REQUEST['rm_keyword'] == "$f") { continue; }
		if($f == "") { continue; }
		$btn_remove_keyword .= '<a class="btn btn-default btn-sm" href="acp.php?tn=pages&sub=snippets&rm_keyword='.$f.'">'.$icon['times_circle'].' '.$f.'</a> ';
		$set_snippet_keyword_filter .= "(snippet_name like '%$f%' OR snippet_title like '%$f%' OR snippet_content like '%$f%' OR snippet_groups like '%$f%' OR snippet_keywords like '%$f%') AND";
	}
}
$set_snippet_keyword_filter = substr("$set_snippet_keyword_filter", 0, -4); // cut the last ' AND'

$snippet_lang_filter = "";
for($i=0;$i<count($arr_lang);$i++) {
	$lang_folder = $arr_lang[$i]['lang_folder'];
	if(strpos("$_SESSION[checked_lang_string]", "$lang_folder") !== false) {
		$snippet_lang_filter .= "snippet_lang = '$lang_folder' OR ";
	}
}
$snippet_lang_filter = substr("$snippet_lang_filter", 0, -3); // cut the last ' OR'


$snippet_label_filter = '';
$checked_labels_array = explode('-', $_SESSION['checked_label_str']);
for($i=0;$i<count($se_labels);$i++) {
	$label = $se_labels[$i]['label_id'];
	if(in_array($label, $checked_labels_array)) {
		$snippet_label_filter .= "snippet_labels LIKE '%,$label,%' OR snippet_labels LIKE '%,$label' OR snippet_labels LIKE '$label,%' OR snippet_labels = '$label' OR ";
	}
}
$snippet_label_filter = substr("$snippet_label_filter", 0, -3); // cut the last ' OR'


if($_SESSION['type'] == 'all') {
	$filter_string = "WHERE (snippet_type = 'snippet' OR snippet_type = 'snippet_core')";
} else if($_SESSION['type'] == 'system') {
	$filter_string = "WHERE snippet_type = 'snippet_core'";
} else if($_SESSION['type'] == 'own') {
	$filter_string = "WHERE snippet_type = 'snippet'";
}


if($set_snippet_keyword_filter != "") {
	$filter_string .= " AND $set_snippet_keyword_filter";
}

if($snippet_label_filter != "") {
	$filter_string .= " AND ($snippet_label_filter)";
}

if($snippet_lang_filter != "") {
	$filter_string .= " AND ($snippet_lang_filter)";
}


$sql_cnt = "SELECT count(*) AS 'cnt_all_snippets',
(SELECT count(*) FROM `se_snippets` WHERE `snippet_type` NOT IN('shortcode','post_feature','post_option') ) AS 'cnt_snippets',
(SELECT count(*) FROM `se_snippets` WHERE `snippet_type` = 'snippet_core' ) AS 'cnt_system_snippets',
(SELECT count(*) FROM `se_snippets` WHERE `snippet_type` = 'snippet' ) AS 'cnt_custom_snippets',
(SELECT count(*) FROM `se_snippets` ".$filter_string." ) AS 'cnt_filter_snippets'
FROM se_snippets";

$cnt = $db_content->query($sql_cnt)->fetch(PDO::FETCH_ASSOC);

$sql_start_nbr = 0;
$sql_items_limit = 10;

/* items per page */
if(!isset($_SESSION['items_per_page'])) {
    $_SESSION['items_per_page'] = $sql_items_limit;
}
if(isset($_POST['items_per_page'])) {
    $_SESSION['items_per_page'] = (int) $_POST['items_per_page'];
}

$items_per_page = $_SESSION['items_per_page'];

if((isset($_GET['sql_start_nbr'])) && is_numeric($_GET['sql_start_nbr'])) {
    $sql_start_nbr = (int) $_GET['sql_start_nbr'];
}


$sql = "SELECT * FROM se_snippets $filter_string ORDER BY snippet_name ASC LIMIT $sql_start_nbr, $items_per_page";

foreach($system_snippets as $snippet) {
	$snippet_exception[] = " snippet_name != '$snippet' ";
}

$snippets_list = $db_content->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$cnt_snippets = count($snippets_list);

$pagination_query = '?tn=pages&sub=snippets&sql_start_nbr={page}';
$pagination = se_return_pagination($pagination_query,$cnt['cnt_filter_snippets'],$sql_start_nbr,$_SESSION['items_per_page'],10,3,2);

/**
 * open snippet
 * show or hide the form
 */

$show_snippet_form = false;

if(isset($_POST['snip_id'])) {
    $show_snippet_form = true;
}

if(isset($_POST['snip_id_duplicate'])) {
    $show_snippet_form = true;
}

if($modus == 'update' OR $_POST['modus'] == 'update') {
    $show_snippet_form = true;
}

if(isset($delete_snip_id)) {
    $show_snippet_form = false;
}


if($show_snippet_form)  {

	include 'pages.snippets_form.php';
	
} else {
	
	/* list snippets */

    echo '<div class="subHeader d-flex align-items-center">';
    echo '<h3>'.$lang['nav_snippets'].'</h3>';
    echo '<form action="?tn=pages&sub=snippets" method="post" class="d-inline ms-auto">';
    echo '<button class="btn btn-default text-success ms-auto" name="snip_id" value="n">'.$icon['plus'].' '.$lang['new'].'</button>';
    echo $hidden_csrf_token;
    echo '</form>';
    echo '</div>';

	
	echo '<div class="app-container">';
	echo '<div class="max-height-container">';
	
	echo '<div class="row">';
	echo '<div class="col-md-9">';
	
	echo '<div class="card p-3">';

    echo '<div class="d-flex flex-row-reverse">';
    echo '<div class="ps-3">';
    echo '<form action="?tn=pages&sub=snippets" method="POST" data-bs-toggle="tooltip" data-bs-title="'.$lang['items_per_page'].'">';
    echo '<input type="number" class="form-control" name="items_per_page" min="5" max="99" value="'.$_SESSION['items_per_page'].'" onchange="this.form.submit()">';
    echo $hidden_csrf_token;
    echo '</form>';
    echo '</div>';
    echo '<div class="p-0">';
    echo $pagination;
    echo '</div>';
    echo '</div>';
	
	echo '<div class="scroll-box">';
	
	echo '<table class="table table-hover table-striped table-sm mt-3">';
	
	echo '<thead><tr>';
	echo '<th> </th>';
	echo '<th>'.$lang['filename'].'</th>';
	echo '<th>'.$lang['label_title'].'/'.$lang['label_content'].'</th>';
	echo '<th>'.$lang['label_classes'].'</th>';
	echo '<th>'.$lang['labels'].'</th>';
	echo '<th>'.$lang['images'].'</th>';
	echo '<th>URL</th>';
	echo '<th>'.$lang['date_of_change'].'</th>';
	echo '<th></th>';
	echo '</tr></thead>';
	
	for($i=0;$i<$cnt_snippets;$i++) {
		$active_class = '';
		$get_snip_id = $snippets_list[$i]['snippet_id'];
		$get_snip_name = $snippets_list[$i]['snippet_name'];
		$get_snip_lang = $snippets_list[$i]['snippet_lang'];
		$get_snip_title = strip_tags($snippets_list[$i]['snippet_title']);
		$get_snip_content = strip_tags($snippets_list[$i]['snippet_content']);
		$get_snip_lastedit = (int) $snippets_list[$i]['snippet_lastedit'];
		$get_snip_lastedit_from = $snippets_list[$i]['snippet_lastedit_from'];
		$get_snip_keywords = $snippets_list[$i]['snippet_keywords'];	
		$get_snip_labels = explode(',',$snippets_list[$i]['snippet_labels']);
		$get_snip_url = $snippets_list[$i]['snippet_permalink'];
		$get_snip_url_title = $snippets_list[$i]['snippet_permalink_title'];
		$get_snip_url_name = $snippets_list[$i]['snippet_permalink_name'];
		$get_snip_url_classes = $snippets_list[$i]['snippet_permalink_classes'];
		$get_snip_images = $snippets_list[$i]['snippet_images'];
        $get_snip_notes = strip_tags($snippets_list[$i]['snippet_notes']);
		

		if(strlen($get_snip_content) > 150) {
			$get_snip_content = substr($get_snip_content, 0, 100) . ' <small><i>(...)</i></small>';
		}

        if($get_snip_title == '') {
            $get_snip_title = '<em class="opacity-50">'.$lang['missing_title'].'</em>';
        }

        if($get_snip_notes != '') {
            $info_tooltip = '<span title="'.$get_snip_notes.'">'.$icon['info_circle'].'</span> ';

            $get_snip_title = $info_tooltip.$get_snip_title;
        }

		
		$label = '';
		if($snippets_list[$i]['snippet_labels'] != '') {
			foreach($get_snip_labels as $snippet_label) {
				
				foreach($se_labels as $l) {
					if($snippet_label == $l['label_id']) {
						$label_color = $l['label_color'];
						$label_title = $l['label_title'];
					}
				}
				
				$label .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
			}
		}
		
		$snippet_classes = explode(' ',$snippets_list[$i]['snippet_classes']);
		$class_badge = '';
		foreach($snippet_classes as $class) {
			$class_badge .= '<span class="badge badge-secondary">'.$class.'</span> ';
		}
		
		$kw_string = '';
		$snippet_keywords = explode(',',$get_snip_keywords);
		if(count($snippet_keywords) > 0) {
			$kw_string = '<form action="acp.php?tn=pages&sub=snippets" method="POST" class="form-inline">';
			foreach($snippet_keywords as $kw) {
				if($kw == '') {
					continue;
				}
				$kw_string .= ' <button type="submit" class="btn btn-default btn-xs mr-1" name="snippet_filter" value="'.$kw.'">'.$kw.'</button> ';
			}
			$kw_string .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
			$kw_string .= '</form>';
		}
		
		if(in_array($get_snip_name, $system_snippets)) {
			$show_snip_name = '<span>' . $get_snip_name.'</span>'.' <sup>'.$icon['cog'].'</sup>';
			$data_groups = '"system"';
		} else {
			$show_snip_name = '<span>'.$get_snip_name.'</span>';
			$data_groups = '';
		}
		
		$lang_thumb = '<img src="/core/lang/'.$get_snip_lang.'/flag.png" width="20">';
		
		$snippet_images = explode('<->',$get_snip_images);
		
		echo '<tr>';
		echo '<td>'.$lang_thumb.'</td>';
		echo '<td nowrap>'.$show_snip_name.'</td>';
		echo '<td>'.$get_snip_title.'<br><small>'.$get_snip_content.'</small><br>'.$kw_string.'</td>';
		echo '<td>'.$class_badge.'</td>';
		echo '<td>'.$label.'</td>';
		echo '<td>';
		if(count($snippet_images) > 1) {
			$x=0;
			foreach($snippet_images as $img) {
				if(is_file("$img")) {
					$x++;
					echo '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\''.$img.'\'>">'.$icon['images'].'</a> ';
				}
				if($x>2) {
					echo '<small>(...)</small>';
					break;
				}
			}
		}
		echo '</td>';
		echo '<td>';
		if($get_snip_url != '') {
			echo '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" title="'.$get_snip_url_title.'" data-bs-content="URL: '.$get_snip_url.'<br>Name: '.$get_snip_url_name.'<br>'.$lang['label_classes'].': '.$get_snip_url_classes.'">'.$icon['link'].'</a>';
		}
		echo '</td>';
		echo '<td nowrap><small>'.$icon['clock']. ' '.date('Y.m.d H:i:s',$get_snip_lastedit).'<br>'.$icon['user'].' '.$get_snip_lastedit_from.'</small></td>';
		echo '<td class="text-right">';

        echo '<form action="?tn=pages&sub=snippets" method="POST">';
        echo '<div class="btn-group" role="group">';
        echo '<button class="btn btn-default text-success" name="snip_id" value="'.$get_snip_id.'" title="'.$lang['edit'].'">'.$icon['edit'].'</button>';
        echo '<button class="btn btn-default" name="snip_id_duplicate" value="'.$get_snip_id.'" title="'.$lang['duplicate'].'">'.$icon['copy'].'</button>';
        echo '</div>';
        echo $hidden_csrf_token;
        echo '</form>';

		echo '</td>';	
		echo '</tr>';
		
	}
	
	
	echo '</table>';


    echo $pagination;
	
	echo '</div>'; // scroll-box
	
	echo '</div>'; // card
	
	echo '</div>';
	echo '<div class="col-md-3">';

	/* sidebar */

	echo '<div class="card">';
	echo '<div class="card-header">FILTER</div>';
	echo '<div class="card-body">';

	echo '<form action="acp.php?tn=pages&sub=snippets" method="POST" class="form-inline ms-auto dirtyignore">';
	echo '<div class="input-group">';
	echo '<span class="input-group-text">'.$icon['search'].'</span>';
	echo '<input class="form-control" type="text" name="snippet_filter" value="" placeholder="'.$lang['button_search'].'">';
	echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
	echo '</div>';
	echo '</form>';
	
	if($btn_remove_keyword != '') {
		echo '<p class="p-2">'.$btn_remove_keyword.'</p>';
	}
	
	
	echo '<div class="btn-group d-flex my-3">';
	echo '<a class="btn btn-default w-100 '.$active_all.'" href="?tn=pages&sub=snippets&type=1">'.$lang['btn_snippets_all'].' <span class="badge badge-fc position-absolute top-0 end-0">'.$cnt['cnt_snippets'].'</span></a>';
	echo '<a class="btn btn-default w-100 '.$active_system.'" href="?tn=pages&sub=snippets&type=2">'.$lang['btn_snippets_system'].' <span class="badge badge-fc position-absolute top-0 end-0">'.$cnt['cnt_system_snippets'].'</span></a>';
	echo '<a class="btn btn-default w-100 '.$active_own.'" href="?tn=pages&sub=snippets&type=3">'.$lang['btn_snippets_own'].' <span class="badge badge-fc position-absolute top-0 end-0">'.$cnt['cnt_custom_snippets'].'</span></a>';
	echo '</div>';
	
	echo $lang_filter_box;
	echo $label_filter_box;
	
	echo '</div>'; // card-body
	echo '</div>'; // card
	
	/* end of sidebar */


	echo '</div>';
	echo '</div>';
		
	echo '</div>';
	echo '</div>'; // .app-container
	
	
}
?>