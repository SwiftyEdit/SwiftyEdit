<?php

// pagination
if(isset($_POST['pagination'])) {
    $_SESSION['pagination_events_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_events_list");
    exit;
}

// show or hide past events
if(isset($_POST['show_past_events'])) {
    if($_SESSION['show_past_events'] == 'true') {
        $_SESSION['show_past_events'] = '';
    } else {
        $_SESSION['show_past_events'] = 'true';
    }
    header( "HX-Trigger: update_events_list");
}

// search
if(isset($_POST['events_text_filter'])) {
    $_SESSION['events_text_filter'] = $_SESSION['events_text_filter'] . ' ' . sanitizeUserInputs($_POST['events_text_filter']);
    header( "HX-Trigger: update_events_list");
}

// remove keyword from filter list
if(isset($_POST['rmkey'])) {
    $all_filter = explode(" ", $_SESSION['events_text_filter']);
    $_SESSION['events_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['events_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_events_list");
}

// change priority
if(isset($_POST['priority'])) {
    $change_id = (int) $_POST['prio_id'];
    $db_posts->update("se_events", [
        "priority" => $_POST['priority']
    ],[
        "id" => $change_id
    ]);
    header( "HX-Trigger: update_events_list");
}

// delete event
if(isset($_POST['delete_event'])) {

    $del_id = (int) $_POST['delete_event'];

    $delete = $db_posts->delete("se_events", [
        "id" => $del_id
    ]);

    if($delete->rowCount() > 0) {
        show_toast($lang['msg_post_deleted'],'success');
        record_log($_SESSION['user_nick'],"delete event id: $del_id","8");
        header( "HX-Trigger: update_events_list");
    }
}


if(isset($_POST['save_post'])) {

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = @htmlspecialchars($val, ENT_QUOTES);
        }
    }

    $releasedate = time();
    $lastedit = time();
    $lastedit_from = $_SESSION['user_nick'];
    $priority = (int) $_POST['priority'];

    if($_POST['date'] == "") {
        $date = time();
    }

    if($_POST['releasedate'] != "") {
        $releasedate = strtotime($_POST['releasedate']);
    }

    $event_startdate = strtotime($_POST['event_start']);
    $event_enddate = strtotime($_POST['event_end']);

    if($event_enddate < $event_startdate) {
        $event_enddate = $event_startdate;
    }

    $clean_title = clean_filename($_POST['title']);
    $date_year = date("Y",$releasedate);
    $date_month = date("m",$releasedate);
    $date_day = date("d",$releasedate);

    if($_POST['slug'] == "") {
        $slug = "$date_year/$date_month/$date_day/$clean_title/";
    }

    $categories = '';
    if(is_array($_POST['categories'])) {
        $categories = implode("<->", $_POST['categories']);
    }

    $images = '';
    if(is_array($_POST['picker_0'])) {
        $event_images_string = implode("<->", $_POST['picker_0']);
        $event_images_string = "<->$event_images_string<->";
        $images = $event_images_string;
    }

    /* labels */
    $labels = '';
    if(is_array($_POST['labels'])) {
        $labels = implode(",", $_POST['labels']);
    }

    /* fix on top */

    if($_POST['fixed'] == 'fixed') {
        $fixed = 1;
    } else {
        $fixed = 2;
    }

    /* metas */
    if($_POST['meta_title'] == '') {
        $meta_title = $_POST['title'];
    } else {
        $meta_title = $_POST['meta_title'];
    }
    if($_POST['meta_description'] == '') {
        $meta_description = strip_tags($_POST['teaser']);
    } else {
        $meta_description = $_POST['meta_description'];
    }

    $meta_title = se_return_clean_value($meta_title);
    $meta_description = se_return_clean_value($meta_description);

    // get all $cols
    require SE_ROOT.'install/contents/se_events.php';
    // build sql string -> f.e. "releasedate" => $releasedate,
    foreach($cols as $k => $v) {
        if($k == 'id') {continue;}
        $value = $$k;
        $inputs[$k] = "$value";
    }

    if($_POST['save_post'] == 'update') {
        $db_posts->update("se_events", $inputs, [
            "id" => $id
        ]);
        record_log($_SESSION['user_nick'],"updated event id: $id","6");
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        $db_posts->insert("se_events", $inputs);
        $id = $db_posts->id();
        record_log($_SESSION['user_nick'],"new event id: $id","6");
        show_toast($lang['msg_success_new_record'],'success');
    }

}