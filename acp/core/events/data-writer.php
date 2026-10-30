<?php

/**
 * SwiftyEdit /admin-xhr/events/write/
 * list actions (pagination, search, pin, priority, delete) and saving events
 *
 * global variables
 * @var array $lang
 * @var object $db_posts
 * @var object $db_content
 * @var string $se_base_url
 * @var array $cols columns of se_events, from install/contents/se_events.php (save_post)
 */

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
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $all_filter = explode(" ", $_SESSION['events_text_filter']);
    $_SESSION['events_text_filter'] = '';
    foreach($all_filter as $f) {
        if($remove_keyword == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['events_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_events_list");
}

// pin (sfixed) or unpin (rfixed) an event, the star in the list
if(isset($_POST['sfixed']) || isset($_POST['rfixed'])) {
    $db_posts->update("se_events", [
        "fixed" => isset($_POST['sfixed']) ? 1 : 2
    ],[
        "id" => (int) ($_POST['sfixed'] ?? $_POST['rfixed'])
    ]);
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
        $db_content->delete("se_tags_relations", [
            "content_type" => "event",
            "content_id" => $del_id
        ]);
        show_toast($lang['msg_success_entry_delete'],'success');
        record_log($_SESSION['user_nick'],"delete event id: $del_id",8);
        header( "HX-Trigger: update_events_list");
    }
}


if(isset($_POST['save_post'])) {

    // all columns of se_events ($cols) - only these POST fields are taken
    // into $values, everything else in the request is ignored
    require SE_ROOT.'install/contents/se_events.php';

    $values = [];
    foreach($_POST as $key => $val) {
        if(is_string($val) && array_key_exists($key, $cols)) {
            $values[$key] = @htmlspecialchars($val, ENT_QUOTES);
        }
    }

    // "id" is empty for new events
    $id = $values['id'] ?? '';
    unset($values['id']);

    $values['releasedate'] = time();
    $values['lastedit'] = time();
    $values['lastedit_from'] = $_SESSION['user_nick'];
    $values['priority'] = (int) $_POST['priority'];

    if($_POST['date'] == "") {
        $values['date'] = time();
    }

    if($_POST['releasedate'] != "") {
        $values['releasedate'] = strtotime($_POST['releasedate']);
    }

    $values['event_startdate'] = strtotime($_POST['event_start']);
    $values['event_enddate'] = strtotime($_POST['event_end']);

    if($values['event_enddate'] < $values['event_startdate']) {
        $values['event_enddate'] = $values['event_startdate'];
    }

    $clean_title = clean_filename($_POST['title']);

    if($_POST['slug'] == "") {
        $values['slug'] = $clean_title.'/';
    } else {
        $values['slug'] = se_clean_permalink($_POST['slug']);
    }

    if($_POST['main_category_slug'] == "default") {
        // find a page that accepts event content, same pattern as the
        // blog's "Hauptkategorie-Seite" resolution.
        $values['main_category_slug'] = $db_content->get("se_pages", "page_permalink", [
            "AND" => [
                "page_posts_types[~]" => "e",
                "page_language" => $values['event_lang'] ?? ''
            ]
        ]);
    } else {
        $values['main_category_slug'] = se_clean_permalink($_POST['main_category_slug']);
    }

    if(is_numeric($id)) {
        $filename = str_replace("/", "", $values['slug']) . '-' . $id . '.html';

        // canonical url: keep an explicit value if one was set on the form,
        // otherwise build it from the main category page + slug, same
        // pattern as product and post canonical urls.
        if ($_POST['canonical_url'] == "") {
            $values['canonical_url'] = $se_base_url . $values['main_category_slug'] . $filename;
        }
    }

    $values['categories'] = '';
    if(is_array($_POST['categories'] ?? null)) {
        $values['categories'] = implode("<->", $_POST['categories']);
    }

    $values['images'] = '';
    if(is_array($_POST['picker_0'] ?? null)) {
        $values['images'] = "<->".implode("<->", $_POST['picker_0'])."<->";
    }

    $values['labels'] = '';
    if(is_array($_POST['labels'] ?? null)) {
        $values['labels'] = implode(",", $_POST['labels']);
    }

    $values['status'] = (int) $_POST['status'];

    // fix on top
    $values['fixed'] = (($_POST['fixed'] ?? '') == 'fixed') ? 1 : 2;

    // metas
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

    $values['meta_title'] = se_return_clean_value($meta_title);
    $values['meta_description'] = se_return_clean_value($meta_description);

    // uuid is not part of the form, so new events never got one. Keep the
    // existing one on update (or generate one if it is missing), generate a
    // new one for new events. Set after the $_POST loop above, so a posted
    // "uuid" can't override it.
    $uuid = '';
    if($_POST['save_post'] == 'update') {
        $uuid = (string) $db_posts->get("se_events", "uuid", ["id" => (int) $id]);
    }
    if($uuid === '') {
        $uuid = se_generate_uuid();
    }
    $values['uuid'] = $uuid;

    // the columns to store - a column without a value (e.g. "hits") is left
    // untouched, null counts as "no value"
    $inputs = [];
    foreach($values as $k => $value) {
        if($value === null) {
            continue;
        }
        $inputs[$k] = "$value";
    }

    if($_POST['save_post'] == 'update') {
        $db_posts->update("se_events", $inputs, [
            "id" => $id
        ]);
        record_log($_SESSION['user_nick'],"updated event id: $id",6);
        show_toast($lang['msg_success_db_changed'],'success');
    } else {
        $db_posts->insert("se_events", $inputs);
        $id = $db_posts->id();
        record_log($_SESSION['user_nick'],"new event id: $id",6);
        show_toast($lang['msg_success_new_record'],'success');
        // continue on the edit form, a second click on "save" must update instead of insert again
        header( "HX-Redirect: /admin/events/edit/$id/");
    }

    se_set_content_tags('event', $id, explode(',', $_POST['content_tags'] ?? ''));

}