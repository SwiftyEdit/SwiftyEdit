<?php
//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require __DIR__.'/access.php';


/* delete event */

if((isset($_POST['delete_id'])) && is_numeric($_POST['delete_id'])) {

    $del_id = (int) $_POST['delete_id'];
    $delete = $db_posts->delete("se_events", [
        "id" => $del_id
    ]);

    if($delete->rowCount() > 0) {
        echo '<div class="alert alert-success">'.$lang['msg_post_deleted'].'</div>';
    }
}

/* change priority */

if(isset($_POST['priority'])) {
    $change_id = (int) $_POST['prio_id'];
    $db_posts->update("se_events", [
        "priority" => $_POST['priority']
    ],[
        "id" => $change_id
    ]);
}

/* remove fixed */

if(is_numeric($_POST['rfixed'])) {

    $change_id = (int) $_POST['rfixed'];
    $db_posts->update("se_events", [
        "fixed" => "2"
    ],[
        "id" => $change_id
    ]);
}

/* set fixed */

if(is_numeric($_POST['sfixed'])) {

    $change_id = (int) $_POST['sfixed'];
    $db_posts->update("se_events", [
        "fixed" => "1"
    ],[
        "id" => $change_id
    ]);

}


// defaults
$sql_start_nbr = 0;
$sql_items_limit = 10;
$events_order = 'id';
$events_direction = 'DESC';
$events_filter = array();

$arr_status = array('2','1');
$arr_lang = get_all_languages();
$arr_categories = se_get_categories();

/* items per page */
if(!isset($_SESSION['items_per_page'])) {
    $_SESSION['items_per_page'] = $sql_items_limit;
}
if(isset($_POST['items_per_page'])) {
    $_SESSION['items_per_page'] = (int) $_POST['items_per_page'];
}

/* default: check all languages */
if(!isset($_SESSION['checked_lang_string'])) {
    foreach($arr_lang as $langstring) {
        $checked_lang_string .= "$langstring[lang_folder]-";
    }
    $_SESSION['checked_lang_string'] = "$checked_lang_string";
}

/* change status of $_GET['switchLang'] */
if($_GET['switchLang']) {
    if(strpos("$_SESSION[checked_lang_string]", "$_GET[switchLang]") !== false) {
        $checked_lang_string = str_replace("$_GET[switchLang]-", '', $_SESSION['checked_lang_string']);
    } else {
        $checked_lang_string = $_SESSION['checked_lang_string'] . "$_GET[switchLang]-";
    }
    $_SESSION['checked_lang_string'] = "$checked_lang_string";
}

/* filter buttons for languages */
$lang_btn_group = '<div class="btn-group">';
foreach($lang_codes as $lang_code) {
    $this_btn_status = '';
    if(strpos("$_SESSION[checked_lang_string]", "$lang_code") !== false) {
        $this_btn_status = 'active';
    }
    $lang_btn_group .= '<a href="acp.php?tn=events&switchLang='.$lang_code.'" class="btn btn-sm btn-default '.$this_btn_status.'">'.$lang_code.'</a>';
}
$lang_btn_group .= '</div>';

/* default: check all status types */
if(!isset($_SESSION['checked_status_string'])) {
    $_SESSION['checked_status_string'] = '1-2';
}

/* change status types */
if($_GET['status']) {
    if(strpos("$_SESSION[checked_status_string]", "$_GET[status]") !== false) {
        $checked_status_string = str_replace("$_GET[status]", '', $_SESSION['checked_status_string']);
    } else {
        $checked_status_string = $_SESSION['checked_status_string'] . '-' . $_GET['status'];
    }
    $checked_status_string = str_replace('--', '-', $checked_status_string);
    $_SESSION['checked_status_string'] = "$checked_status_string";
}

/* change status for past events */
if($_GET['show_past_events']) {
    $_SESSION['show_past_events'] = (int) $_GET['show_past_events'];
}

/* default: check all categories */
if(!isset($_SESSION['checked_cat_string'])) {
    $_SESSION['checked_cat_string'] = 'all';
}
/* filter by categories */
if(isset($_GET['cat'])) {
    if($_GET['cat'] !== 'all') {
        $_SESSION['checked_cat_string'] = (int)$_GET['cat'];
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
$cat_btn_group .= '<div class="list-group list-group-flush">';
$cat_btn_group .= '<a href="acp.php?tn=events&cat=all" class="list-group-item list-group-item-ghost p-1 px-2 '.$cat_all_active.'">'.$icon_all_toggle.' '.$lang['btn_all_categories'].'</a>';
foreach($arr_categories as $c) {
    $cat_active = '';
    $icon_toggle = $icon['circle_alt'];
    if($_SESSION['checked_cat_string'] == $c['cat_id']) {
        $icon_toggle = $icon['check_circle'];
        $cat_active = 'active';
    }

    $cat_btn_group .= '<a href="acp.php?tn=events&cat='.$c['cat_id'].'" class="list-group-item list-group-item-ghost p-1 px-2 '.$cat_active.'">'.$icon_toggle.' '.$c['cat_name'].'</a>';
}

$cat_btn_group .= '</div>';
$cat_btn_group .= '</div>';

/* filter buttons for labels */

if(!isset($_SESSION['checked_label_str'])) {
    $_SESSION['checked_label_str'] = '';
}

$a_checked_labels = explode('-', $_SESSION['checked_label_str']);

if(isset($_GET['switchLabel'])) {

    if(in_array($_GET['switchLabel'], $a_checked_labels)) {
        /* remove label*/
        if(($key = array_search($_GET['switchLabel'], $a_checked_labels)) !== false) {
            unset($a_checked_labels[$key]);
        }
    } else {
        /* add label */
        $a_checked_labels[] = $_GET['switchLabel'];
    }

    $_SESSION['checked_label_str'] = implode('-', $a_checked_labels);
}

$a_checked_labels = explode('-', $_SESSION['checked_label_str']);

$label_filter_box  = '<div class="card mt-2">';
$label_filter_box .= '<div class="card-header p-1 px-2">'.$lang['labels'].'</div>';
$label_filter_box .= '<div class="card-body">';
$this_btn_status = '';
foreach($se_labels as $label) {

    if(in_array($label['label_id'], $a_checked_labels)) {
        $this_btn_status = 'active';
    } else {
        $this_btn_status = '';
    }

    $label_title = '<span class="label-dot" style="background-color:'.$label['label_color'].';"></span> '.$label['label_title'];
    $label_filter_box .= '<a href="acp.php?tn=events&sub=list&switchLabel='.$label['label_id'].'" class="btn btn-default btn-sm m-1 '.$this_btn_status.'">'.$label_title.'</a>';

}
$label_filter_box .= '</div>';
$label_filter_box .= '</div>'; // card



if((isset($_GET['sql_start_nbr'])) && is_numeric($_GET['sql_start_nbr'])) {
    $sql_start_nbr = (int) $_GET['sql_start_nbr'];
}

if((isset($_POST['setPage'])) && is_numeric($_POST['setPage'])) {
    $events_start = (int) $_POST['setPage'];
}

$events_filter['languages'] = $_SESSION['checked_lang_string'];
$events_filter['status'] = $_SESSION['checked_status_string'];
$events_filter['categories'] = $_SESSION['checked_cat_string'];
$events_filter['labels'] = $_SESSION['checked_label_str'];

$get_events = se_get_event_entries($sql_start_nbr,$_SESSION['items_per_page'],$events_filter);

$cnt_filter_events = $get_events[0]['cnt_events'];
$cnt_all_events = $get_events[0]['cnt_all_events'];
$cnt_get_events = count($get_events);

$pagination_query = '?tn=events&sub=events-list&sql_start_nbr={page}';
$pagination = se_return_pagination($pagination_query,$cnt_filter_events,$sql_start_nbr,$_SESSION['items_per_page'],10,3,2);

echo '<div class="subHeader d-flex flex-row align-items-center">';
echo '<h3 class="align-middle">' . sprintf($lang['label_show_events'], $cnt_filter_events, $cnt_all_events) .'</h3>';
echo '<div class="ms-auto ps-3">';
echo '<a class="btn btn-default text-success" href="?tn=events&sub=edit&new=e">'.$icon['plus'].' '.$lang['label_new_post'].'</a>';
echo '</div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div class="card p-3">';


echo '<div class="d-flex flex-row-reverse">';
echo '<div class="ps-3">';
echo '<form action="?tn=events&sub=events-list" method="POST" data-bs-toggle="tooltip" data-bs-title="'.$lang['items_per_page'].'">';
echo '<input type="number" class="form-control" name="items_per_page" min="5" max="99" value="'.$_SESSION['items_per_page'].'" onchange="this.form.submit()">';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';
echo '<div class="p-0">';
echo $pagination;
echo '</div>';
echo '</div>';


if($cnt_filter_events > 0) {

    echo '<table class="table table-sm table-hover">';

    echo '<thead><tr>';
    echo '<th>#</th>';
    echo '<th class="text-center">' . $icon['star'] . '</th>';
    echo '<th>' . $lang['label_priority'] . '</th>';
    echo '<th nowrap>' . $lang['label_date'] . '</th>';
    echo '<th>' . $lang['label_post_title'] . '</th>';
    echo '<th></th>';
    echo '<th></th>';
    echo '</tr></thead>';

    for($i=0;$i<$cnt_get_events;$i++) {

        $icon_fixed = '';
        $draft_class = '';

        $event_lang_thumb = '<img src="/core/lang/'.$get_events[$i]['event_lang'].'/flag.png" width="15" title="'.$get_events[$i]['event_lang'].'" alt="'.$get_events[$i]['event_lang'].'">';

        $icon_fixed_form = '<form action="?tn=events" method="POST" class="form-inline">';
        if($get_events[$i]['post_fixed'] == '1') {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="rfixed" value="'.$get_events[$i]['id'].'">'.$icon['star'].'</button>';
        } else {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="sfixed" value="'.$get_events[$i]['id'].'">'.$icon['star_outline'].'</button>';
        }
        $icon_fixed_form .= $hidden_csrf_token;
        $icon_fixed_form .= '</form>';

        if($get_events[$i]['status'] == '2') {
            $draft_class = 'item_is_draft';
        }

        /* trim teaser to $trim chars */
        $trim = 150;
        $teaser = strip_tags(htmlspecialchars_decode($get_events[$i]['teaser']));
        if(strlen($teaser) > $trim) {
            $ellipses = ' <small><i>(...)</i></small>';
            $last_space = strrpos(substr($teaser, 0, $trim), ' ');
            if($last_space !== false) {
                $trimmed_teaser = substr($teaser, 0, $last_space);
            } else {
                $trimmed_teaser = substr($teaser, 0, $trim);
            }
            $trimmed_teaser = $trimmed_teaser.$ellipses;
        } else {
            $trimmed_teaser = $teaser;
        }


        $post_image = explode("<->", $get_events[$i]['images']);
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
        $get_labels = explode(',',$get_events[$i]['labels']);
        $label = '';
        if($get_events[$i]['labels'] != '') {
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
        $get_post_categories = explode('<->',$get_events[$i]['categories']);
        $categories = '';
        if($get_events[$i]['post_categories'] != '') {
            foreach($get_post_categories as $cats) {

                foreach($arr_categories as $cat) {
                    if($cats == $cat['cat_id']) {
                        $cat_title = $cat['cat_name'];
                        $cat_description = $cat['cat_description'];
                    }
                }
                $categories .= '<span class="text-muted small" title="'.$cat_description.'">'.$icon['tags'].' '.$cat_title.'</span> ';
            }
        }


        $prio_form  = '<form action="?tn=events&a=events-list" method="POST">';
        $prio_form .= '<input type="number" name="priority" value="'.$get_events[$i]['priority'].'" class="form-control" style="max-width:150px" onchange="this.form.submit()">';
        $prio_form .= '<input type="hidden" name="prio_id" value="'.$get_events[$i]['id'].'">';
        $prio_form .= $hidden_csrf_token;
        $prio_form .= '</form>';

        $published_date = '<span title="'.$lang['label_data_submited'].'">'.$icon['save'].': '.se_format_datetime($get_events[$i]['date']).'</span>';
        $release_date = '<span title="'.$lang['label_data_releasedate'].'">'.$icon['calendar_check'].': '.se_format_datetime($get_events[$i]['releasedate']).'</span>';
        $lastedit_date = '';
        if($get_events[$i]['lastedit'] != '') {
            $lastedit_date = '<span title="'.$lang['label_data_lastedit'].'">'.$icon['edit'].': '.se_format_datetime($get_events[$i]['lastedit']).'</span>';
        }

        $show_items_dates = '<span class="text-muted small">'.$published_date.' | '.$lastedit_date.' | '.$release_date.'</span>';

        $show_events_date = '';
        $show_events_date = '<div class="card p-1">';
        $show_events_date .= '<span>'.$icon['calendar_event'] .' '.se_format_datetime($get_events[$i]['event_startdate']).'</span>';
        if($get_events[$i]['event_startdate'] != $get_events[$i]['event_enddate']) {
            $show_events_date .= '<span>' . $icon['arrow_right'] . ' ' . se_format_datetime($get_events[$i]['event_enddate']) . '</span>';
        }
        $show_events_date .= '</div>';


        echo '<tr class="'.$draft_class.'">';
        echo '<td>'.$get_events[$i]['id'].'</td>';
        echo '<td>'.$icon_fixed_form.'</td>';
        echo '<td>'.$prio_form.'</td>';
        echo '<td nowrap><small>'.$show_events_date.'</small></td>';
        echo '<td><h5 class="mb-0">'.$event_lang_thumb.' '.$get_events[$i]['title'].'</h5><small>'.$trimmed_teaser.'</small><br>'.$show_items_dates.'<br>'.$categories.'<br>'.$label.'</td>';
        echo '<td>'.$show_thumb.'</td>';
        echo '<td style="min-width: 150px;">';
        echo '<nav class="nav justify-content-end">';
        echo '<form class="form-inline px-1" action="?tn=events&sub=edit" method="POST">';
        echo '<button class="btn btn-default btn-sm text-success" type="submit" name="id" value="'.$get_events[$i]['id'].'">'.$icon['edit'].'</button>';
        echo $hidden_csrf_token;
        echo '</form> ';
        echo '<form class="form-inline px-1" action="acp.php?tn=events" method="POST">';
        echo '<button class="btn btn-danger btn-sm" type="submit" name="delete_id" value="'.$get_events[$i]['id'].'">'.$icon['trash_alt'].'</button>';
        echo $hidden_csrf_token;
        echo '</form>';
        echo '</nav>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';

} else {
    echo '<div class="alert alert-info">'.$lang['msg_no_posts_to_show'].'</div>';
}

echo $pagination;

echo '</div>'; // card


echo '</div>';
echo '<div class="col-md-3">';


/* sidebar */
echo '<div class="card p-2">';


echo '<fieldset class="mt-4">';
echo '<legend>'.$icon['filter'].' Filter</legend>';

/* Filter Options */
echo '<div class="card mt-1">';
echo '<div class="card-header p-1 px-2">'.$lang['label_language'].'</div>';
echo '<div class="list-group list-group-flush">';
echo $lang_btn_group;
echo '</div>';
echo '</div>';



echo '<div class="card mt-2">';
echo '<div class="card-header p-1 px-2">'.$lang['label_status'].'</div>';

/* status filter */
echo '<div class="btn-group d-flex">';
if(strpos("$_SESSION[checked_status_string]", "2") !== false) {
    $icon_toggle = $icon['check_circle'];
    echo '<a href="acp.php?tn=events&status=2" class="btn btn-sm btn-default active w-100">'.$icon_toggle.' '.$lang['status_draft'].'</a>';
} else {
    $icon_toggle = $icon['circle_alt'];
    echo '<a href="acp.php?tn=events&status=2" class="btn btn-sm btn-default w-100">'.$icon_toggle.' '.$lang['status_draft'].'</a>';
}
if(strpos("$_SESSION[checked_status_string]", "1") !== false) {
    $icon_toggle = $icon['check_circle'];
    echo '<a href="acp.php?tn=events&status=1" class="btn btn-sm btn-default active w-100">'.$icon_toggle.' '.$lang['status_public'].'</a>';
} else {
    $icon_toggle = $icon['circle_alt'];
    echo '<a href="acp.php?tn=events&status=1" class="btn btn-sm btn-default w-100">'.$icon_toggle.' '.$lang['status_public'].'</a>';
}
echo '</div>';

/* show or hide past events  */
echo '<div class="btn-group d-flex mt-1">';
if($_SESSION['show_past_events'] == 1 OR $_SESSION['show_past_events'] == '') {
    echo '<a href="acp.php?tn=events&show_past_events=2" class="btn btn-sm btn-default active w-100">'.$icon['check_circle'].' '.$lang['status_past_events'].'</a>';
} else {
    echo '<a href="acp.php?tn=events&show_past_events=1" class="btn btn-sm btn-default w-100">'.$icon['circle_alt'].' '.$lang['status_past_events'].'</a>';
}
echo '</div>';
echo '</div>';

echo '<div class="card mt-2">';
echo '<div class="card-header p-1 px-2">'.$lang['label_categories'].'</div>';

echo $cat_btn_group;

echo '</div>';

echo $label_filter_box;

echo '</fieldset>';
echo '</div>'; // card


echo '</div>';
echo '</div>';