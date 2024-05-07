<?php

/**
 * SwiftyEdit backend
 *
 * global variables
 * @var object $db_posts medoo database object
 * @var array $icon icons set in acp/core/icons.php
 * @var array $lang language
 * @var array $lang_codes language
 * @var string $languagePack
 * @var string $hidden_csrf_token
 * @var array $se_labels
 * @var array $se_prefs
 */

//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require __DIR__.'/access.php';


/* delete post */

if((isset($_POST['delete_id'])) && is_numeric($_POST['delete_id'])) {
	
	$del_id = (int) $_POST['delete_id'];
	
	/* first get the post it's data and check the type */
	$this_post_data = se_get_post_data($del_id);

	if($this_post_data['post_type'] == 'g') {	
		/* it's a gallery, we have to delete the images too */
		$year = date('Y',$this_post_data['post_date']);
		se_remove_gallery($del_id,$year);
	}
	
	$delete = $db_posts->delete("se_posts", [
		"post_id" => $del_id
	]);	
	
	if($delete->rowCount() > 0) {
		echo '<div class="alert alert-success">'.$lang['msg_post_deleted'].'</div>';
        record_log($_SESSION['user_nick'],"delete post id: $del_id","8");
	}
}




/* remove fixed */

if(is_numeric($_POST['rfixed'])) {

	$change_id = (int) $_POST['rfixed'];	
	$db_posts->update("se_posts", [
		"post_fixed" => "2"
	],[
		"post_id" => $change_id
	]);	
}

/* set fixed */

if(is_numeric($_POST['sfixed'])) {

	$change_id = (int) $_POST['sfixed'];
	$db_posts->update("se_posts", [
		"post_fixed" => "1"
	],[
		"post_id" => $change_id
	]);	
	
}



/* change priority */

if(isset($_POST['post_priority'])) {
	$change_id = (int) $_POST['prio_id'];
	$db_posts->update("se_posts", [
		"post_priority" => $_POST['post_priority']
	],[
		"post_id" => $change_id
	]);
}


// defaults
$sql_start_nbr = 0;
$sql_items_limit = 10;
$posts_order = 'id';
$posts_direction = 'DESC';
$posts_filter = array();

$arr_status = array('2','1');
$arr_types = array('m','i','v','l','g','f');
$arr_lang = get_all_languages();
$arr_categories = se_get_categories();

/* items per page */
if(!isset($_SESSION['items_per_page'])) {
    $_SESSION['items_per_page'] = $sql_items_limit;
}
if(isset($_POST['items_per_page'])) {
    $_SESSION['items_per_page'] = (int) $_POST['items_per_page'];
}


/* default: check all types */
if(!isset($_SESSION['checked_type_string'])) {		
	$_SESSION['checked_type_string'] = 'm-i-v-l-g-f';
}
/* change status of selected types */
if($_GET['type']) {

    $get_type = sanitizeUserInputs($_GET['type']);
    if(!in_array($get_type, $arr_types)) {
        exit();
    }

	if(str_contains($_SESSION['checked_type_string'], "$get_type")) {
		$checked_type_string = str_replace("$get_type", '', $_SESSION['checked_type_string']);
	} else {
		$checked_type_string = $_SESSION['checked_type_string'] . '-' . $get_type;
	}
	$checked_type_string = str_replace('--', '-', $checked_type_string);
	$_SESSION['checked_type_string'] = "$checked_type_string";
}


/* default: check all categories */
if(!isset($_SESSION['checked_cat_string'])) {	
	$_SESSION['checked_cat_string'] = 'all';
}
/* filter by categories */
if(isset($_GET['cat'])) {
    if($_GET['cat'] !== 'all') {
        $_SESSION['checked_cat_string'] = se_return_clean_value($_GET['cat']);
    } else {
        $_SESSION['checked_cat_string'] = 'all';
    }
}

$cat_all_active = '';
$icon_all_toggle = $icon['circle_alt'];
if($_SESSION['checked_cat_string'] == 'all') {
	$cat_all_active = 'active';
	$icon_all_toggle = $icon['check_circle'];
}

$cat_btn_group = '<div class="card">';
$cat_btn_group .= '<div class="list-group list-group-flush scroll-container">';
$cat_btn_group .= '<a href="acp.php?tn=posts&cat=all" class="list-group-item p-1 px-2 '.$cat_all_active.'">'.$icon_all_toggle.' '.$lang['btn_all_categories'].'</a>';
foreach($arr_categories as $c) {
	$cat_active = '';
	$icon_toggle = $icon['circle_alt'];
    if($_SESSION['checked_cat_string'] == $c['cat_hash']) {
		$icon_toggle = $icon['check_circle'];
		$cat_active = 'active';
	}
    $cat_lang_thumb = '<img src="/core/lang/'.$c['cat_lang'].'/flag.png" width="15" alt="'.$c['cat_lang'].'">';

    $cat_btn_group .= '<a href="acp.php?tn=posts&cat='.$c['cat_hash'].'" class="list-group-item p-1 px-2 '.$cat_active.'">';
    $cat_btn_group .= $icon_toggle.' '.$c['cat_name'].' <span class="float-end">'.$cat_lang_thumb.'</span>';
    $cat_btn_group .= '</a>';
}

$cat_btn_group .= '</div>';
$cat_btn_group .= '</div>';



if((isset($_GET['sql_start_nbr'])) && is_numeric($_GET['sql_start_nbr'])) {
    $sql_start_nbr = (int) $_GET['sql_start_nbr'];
}

if((isset($_POST['setPage'])) && is_numeric($_POST['setPage'])) {
    $sql_start_nbr = (int) $_POST['setPage'];
}

/* text filter */
if(isset($_POST['posts_text_filter'])) {
    $_SESSION['posts_text_filter'] = $_SESSION['posts_text_filter'] . ' ' . clean_filename($_POST['posts_text_filter']);
}

/* remove keyword from filter list */
if(isset($_REQUEST['rm_keyword'])) {
    $all_posts_text_filter = explode(" ", $_SESSION['posts_text_filter']);
    $_SESSION['posts_text_filter'] = '';
    foreach($all_posts_text_filter as $f) {
        if($_REQUEST['rm_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['posts_text_filter'] .= "$f ";
    }
}

if(isset($_SESSION['posts_text_filter']) AND $_SESSION['posts_text_filter'] != "") {
    unset($all_posts_text_filter);
    $all_posts_text_filter = explode(" ", $_SESSION['posts_text_filter']);
    $btn_remove_keyword = '';
    foreach($all_posts_text_filter as $f) {
        if($_REQUEST['rm_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $btn_remove_keyword .= '<a class="btn btn-sm btn-default" href="acp.php?tn=posts&sub='.$sub.'&rm_keyword='.$f.'">'.$icon['x'].' '.$f.'</a> ';
    }
}


$posts_filter['languages'] = implode("-",$global_filter_languages);
$posts_filter['types'] = $_SESSION['checked_type_string'];
$posts_filter['status'] = implode("-",$global_filter_status);
$posts_filter['categories'] = $_SESSION['checked_cat_string'];
$posts_filter['labels'] = implode("-",$global_filter_label);
$posts_filter['text'] = $_SESSION['posts_text_filter'];

$get_posts = se_get_post_entries($sql_start_nbr,$_SESSION['items_per_page'],$posts_filter);
$cnt_filter_posts = $get_posts[0]['cnt_posts'];
$cnt_all_posts = $get_posts[0]['cnt_all_posts'];
$cnt_get_posts = count($get_posts);

$pagination_query = '?tn=posts&sql_start_nbr={page}';
$pagination = se_return_pagination($pagination_query,$cnt_filter_posts,$sql_start_nbr,$_SESSION['items_per_page'],10,3,2);

$dropdown_new_post = '<div class="dropdown">
  <button class="btn btn-deafult text-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'.$lang['btn_new'].'</button>
  <ul class="dropdown-menu">
  
<li><a class="dropdown-item list-group-item-ghost" href="?tn=posts&sub=edit&new=m"><span class="color-message">'.$icon['plus'].'</span> '.$lang['post_type_message'].'</a></li>
<li><a class="dropdown-item list-group-item-ghost" href="?tn=posts&sub=edit&new=i"><span class="color-image">'.$icon['plus'].'</span> '.$lang['post_type_image'].'</a></li>
<li><a class="dropdown-item list-group-item-ghost" href="?tn=posts&sub=edit&new=g"><span class="color-gallery">'.$icon['plus'].'</span> '.$lang['post_type_gallery'].'</a></li>
<li><a class="dropdown-item list-group-item-ghost" href="?tn=posts&sub=edit&new=v"><span class="color-video">'.$icon['plus'].'</span> '.$lang['post_type_video'].'</a></li>
<li><a class="dropdown-item list-group-item-ghost" href="?tn=posts&sub=edit&new=l"><span class="color-link">'.$icon['plus'].'</span> '.$lang['post_type_link'].'</a></li>
<li><a class="dropdown-item list-group-item-ghost" href="?tn=posts&sub=edit&new=f"><span class="color-file">'.$icon['plus'].'</span> '.$lang['post_type_file'].'</a></li>
  </ul>
</div>';

echo '<div class="subHeader d-flex flex-row align-items-center">';
echo '<h3 class="align-middle">' . sprintf($lang['label_show_entries'], $cnt_filter_posts, $cnt_all_posts) .'</h3>';

echo '<div class="ms-auto ps-3">';
echo $dropdown_new_post;
echo '</div>';

echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="card p-3">';

echo '<div class="d-flex flex-row-reverse">';
echo '<div class="ps-3">';
echo '<form action="?tn=posts&sub=blog-list" method="POST" data-bs-toggle="tooltip" data-bs-title="'.$lang['label_items_per_page'].'">';
echo '<input type="number" class="form-control" name="items_per_page" min="5" max="99" value="'.$_SESSION['items_per_page'].'" onchange="this.form.submit()">';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';
echo '<div class="p-0">';
echo $pagination;
echo '</div>';
echo '</div>';

if($cnt_filter_posts > 0) {

	echo '<table class="table table-sm table-hover">';
	
	echo '<thead><tr>';
	echo '<th>#</th>';
	echo '<th class="text-center">'.$icon['star'].'</th>';
	echo '<th>'.$lang['label_priority'].'</th>';
	echo '<th>'.$lang['label_post_type'].'</th>';
	echo '<th></th>';
	echo '<th>'.$lang['label_post_title'].'</th>';
	echo '<th></th>';
	echo '</tr></thead>';
	
	for($i=0;$i<$cnt_get_posts;$i++) {
		
		$type_class = 'label-type label-'.$get_posts[$i]['post_type'];
		$icon_fixed = '';
		$draft_class = '';
		
		$icon_fixed_form = '<form action="?tn=posts" method="POST" class="form-inline">';
		if($get_posts[$i]['post_fixed'] == '1') {
			$icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="rfixed" value="'.$get_posts[$i]['post_id'].'">'.$icon['star'].'</button>';
		} else {
			$icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="sfixed" value="'.$get_posts[$i]['post_id'].'">'.$icon['star_outline'].'</button>';
		}
		$icon_fixed_form .= $hidden_csrf_token;
		$icon_fixed_form .= '</form>';
		
		if($get_posts[$i]['post_status'] == '2') {
			$draft_class = 'item_is_draft';
		}

        $post_lang_thumb = '<img src="/core/lang/'.$get_posts[$i]['post_lang'].'/flag.png" width="15" title="'.$get_posts[$i]['post_lang'].'" alt="'.$get_posts[$i]['post_lang'].'">';
		
		/* trim teaser to $trim chars */
        $trimmed_teaser = se_return_first_chars($get_posts[$i]['post_teaser'],100);

		$post_image = explode("<->", $get_posts[$i]['post_images']);
		$show_thumb = '';
		if($post_image[1] != "") {
			$image_src = $post_image[1];
			$show_thumb  = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\''.$image_src.'\'>">';
			$show_thumb .= '<div class="show-thumb" style="background-image: url('.$image_src.');">';
			$show_thumb .= '</div>';
		} else {
            $show_thumb = '<div class="show-thumb" style="background-image: url(images/no-image.png);">';
        }
		
		/* labels */
		$get_labels = explode(',',$get_posts[$i]['post_labels']);
		$label = '';
		if($get_posts[$i]['post_labels'] != '') {
			foreach($get_labels as $labels) {
				
				foreach($se_labels as $l) {
					if($labels == $l['label_id']) {
						$label_color = $l['label_color'];
						$label_title = $l['label_title'];
					}
				}
				
				$label .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
			}
		}
		
		/* categories */
		$get_post_categories = explode('<->',$get_posts[$i]['post_categories']);
		$categories = '';
		if($get_posts[$i]['post_categories'] != '') {
			foreach($get_post_categories as $cats) {
				
				foreach($arr_categories as $cat) {
					if($cats == $cat['cat_hash']) {
						$cat_title = $cat['cat_name'];
						$cat_description = $cat['cat_description'];
					}
				}
                $categories .= '<span class="text-muted small" title="'.$cat_description.'">'.$icon['tags'].' '.$cat_title.'</span> ';
			}
		}
		

        $prio_form  = '<form action="?tn=posts&a=start" method="POST">';
        $prio_form .= '<input type="number" name="post_priority" value="'.$get_posts[$i]['post_priority'].'" class="form-control" style="max-width:100px" onchange="this.form.submit()">';
        $prio_form .= '<input type="hidden" name="prio_id" value="'.$get_posts[$i]['post_id'].'">';
        $prio_form .= $hidden_csrf_token;
        $prio_form .= '</form>';


        $published_date = '<span title="'.$lang['label_data_submited'].'">'.$icon['save'].': '.se_format_datetime($get_posts[$i]['post_date']).'</span>';
        $release_date = '<span title="'.$lang['label_data_releasedate'].'">'.$icon['calendar_check'].': '.se_format_datetime($get_posts[$i]['post_releasedate']).'</span>';
        $lastedit_date = '';
        if($get_posts[$i]['post_lastedit'] != '') {
            $lastedit_date = '<span title="'.$lang['label_data_lastedit'].'">'.$icon['edit'].': '.se_format_datetime($get_posts[$i]['post_lastedit']).'</span>';
        }

        $show_items_dates = '<span class="text-muted small">'.$published_date.' | '.$lastedit_date.' | '.$release_date.'</span>';
		
		$show_items_downloads = '';
		if($get_posts[$i]['post_type'] == 'f') {
			$download_counter = (int) $get_posts[$i]['post_file_attachment_hits'];
			$show_items_downloads = '<div class="float-end small well well-sm">';
			$show_items_downloads .= $icon['download'].' '.$download_counter;
			$show_items_downloads .= '</div>';
		}
		
		$show_items_redirects = '';
		if($get_posts[$i]['post_type'] == 'l') {
			$redirects_counter = (int) $get_posts[$i]['post_link_hits'];
			$show_items_redirects = '<div class="float-end small well well-sm">';
			$show_items_redirects .= $icon['link'].' '.$redirects_counter;
			$show_items_redirects .= '</div>';
		}
		
		
		
		if($get_posts[$i]['post_type'] == 'm') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_message'].'</span>';
		} else if($get_posts[$i]['post_type'] == 'e') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_event'].'</span>';
		} else if($get_posts[$i]['post_type'] == 'i') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_image'].'</span>';
		} else if($get_posts[$i]['post_type'] == 'g') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_gallery'].'</span>';
		} else if($get_posts[$i]['post_type'] == 'v') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_video'].'</span>';
		} else if($get_posts[$i]['post_type'] == 'l') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_link'].'</span>';
		} else if($get_posts[$i]['post_type'] == 'f') {
			$show_type = '<span class="'.$type_class.'">'.$lang['post_type_file'].'</span>';
		}
		
		
		
		echo '<tr class="'.$draft_class.'">';
		echo '<td>'.$get_posts[$i]['post_id'].'</td>';
		echo '<td>'.$icon_fixed_form.'</td>';
		echo '<td>'.$prio_form.'</td>';
		echo '<td>'.$show_type.'</td>';
		echo '<td>'.$show_thumb.'</td>';
		echo '<td>'.$show_items_downloads.$show_items_redirects.'<h5 class="mb-0">'.$post_lang_thumb.' '.$get_posts[$i]['post_title'].'</h5><small>'.$trimmed_teaser.'</small><br>'.$show_items_dates.'<br>'.$categories.'<br>'.$label.'</td>';
		echo '<td style="min-width: 150px;">';
		echo '<nav class="nav justify-content-end">';
		echo '<form class="form-inline px-1" action="?tn=posts&sub=edit" method="POST">';
		echo '<button class="btn btn-default btn-sm text-success" type="submit" name="post_id" value="'.$get_posts[$i]['post_id'].'">'.$icon['edit'].'</button>';
		echo $hidden_csrf_token;
		echo '</form> ';
		echo '<form class="form-inline px-1" action="acp.php?tn=posts" method="POST">';
		echo '<button class="btn btn-default text-danger btn-sm" type="submit" name="delete_id" value="'.$get_posts[$i]['post_id'].'">'.$icon['trash_alt'].'</button>';
		echo $hidden_csrf_token;
		echo '</form>';
		echo '</nav>';
		echo '</td>';
		echo '</tr>';

	}
	
	echo '</table>';

} else {
	echo '<div class="alert alert-info">'.$lang['msg_info_no_entries'].'</div>';
}

echo $pagination;

echo '</div>'; // card


echo '</div>';
echo '<div class="col-md-3">';


/* sidebar */
echo '<div class="card">';
echo '<div class="card-header">'.$icon['filter'].' Filter</div>';
echo '<div class="card-body">';

echo '<form action="?tn=posts&sub=blog-list" method="POST" class="ms-auto">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="posts_text_filter" value="" placeholder="'.$lang['button_search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

if(isset($btn_remove_keyword)) {
    echo '<div class="d-inline">';
    echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
    echo '</div><hr>';
}


echo '<div class="card mt-2">';
echo '<div class="card-header p-1 px-2">'.$lang['label_post_type'].'</div>';

/* type filter */
echo '<div class="list-group list-group-flush">';
if(strpos("$_SESSION[checked_type_string]", "m") !== false) {
	$class = 'list-group-item list-group-item-ghost p-1 px-2 active';
	$icon_toggle = $icon['check_circle'];
} else {
	$class = 'list-group-item list-group-item-ghost p-1 px-2';
	$icon_toggle = $icon['circle_alt'];
}

echo '<a href="acp.php?tn=posts&type=m" class="'.$class.'">'.$icon_toggle.' '.$lang['post_type_message'].'</a>';

if(strpos("$_SESSION[checked_type_string]", "i") !== false) {
	$class = 'list-group-item list-group-item-ghost p-1 px-2 active';
	$icon_toggle = $icon['check_circle'];
} else {
	$class = 'list-group-item list-group-item-ghost p-1 px-2';
	$icon_toggle = $icon['circle_alt'];
}

echo '<a href="acp.php?tn=posts&type=i" class="'.$class.'">'.$icon_toggle.' '.$lang['post_type_image'].'</a>';

if(strpos("$_SESSION[checked_type_string]", "g") !== false) {
	$class = 'list-group-item list-group-item-ghost p-1 px-2 active';
	$icon_toggle = $icon['check_circle'];
} else {
	$class = 'list-group-item list-group-item-ghost p-1 px-2';
	$icon_toggle = $icon['circle_alt'];
}

echo '<a href="acp.php?tn=posts&type=g" class="'.$class.'">'.$icon_toggle.' '.$lang['post_type_gallery'].'</a>';

if(strpos("$_SESSION[checked_type_string]", "v") !== false) {
	$class = 'list-group-item list-group-item-ghost p-1 px-2 active';
	$icon_toggle = $icon['check_circle'];
} else {
	$class = 'list-group-item list-group-item-ghost p-1 px-2';
	$icon_toggle = $icon['circle_alt'];
}

echo '<a href="acp.php?tn=posts&type=v" class="'.$class.'">'.$icon_toggle.' '.$lang['post_type_video'].'</a>';

if(strpos("$_SESSION[checked_type_string]", "l") !== false) {
	$class = 'list-group-item list-group-item-ghost p-1 px-2 active';
	$icon_toggle = $icon['check_circle'];
} else {
	$class = 'list-group-item list-group-item-ghost p-1 px-2';
	$icon_toggle = $icon['circle_alt'];
}

echo '<a href="acp.php?tn=posts&type=l" class="'.$class.'">'.$icon_toggle.' '.$lang['post_type_link'].'</a>';


if(strpos("$_SESSION[checked_type_string]", "f") !== false) {
	$class = 'list-group-item list-group-item-ghost p-1 px-2 active';
	$icon_toggle = $icon['check_circle'];
} else {
	$class = 'list-group-item list-group-item-ghost p-1 px-2';
	$icon_toggle = $icon['circle_alt'];
}


echo '<a href="acp.php?tn=posts&type=f" class="'.$class.'">'.$icon_toggle.' '.$lang['post_type_file'].'</a>';

echo '</div>';
echo '</div>';

echo '<div class="card mt-2">';
echo '<div class="card-header p-1 px-2">'.$lang['categories'].'</div>';

echo $cat_btn_group;

echo '</div>';

echo '</div>'; // card-body
echo '</div>'; // card


echo '</div>';
echo '</div>';
