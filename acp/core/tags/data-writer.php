<?php

/**
 * SwiftyEdit /admin-xhr/tags/write/
 * list filters (search, sorting, unused only), rename and delete tags
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 */

// search
if(isset($_POST['tags_text_filter'])) {
    $add_text_filter = sanitizeUserInputs($_POST['tags_text_filter']);
    $text_filter = $_SESSION['tags_text_filter'] ?? '';
    if($add_text_filter != '' && !in_array($add_text_filter, explode(' ', $text_filter))) {
        $_SESSION['tags_text_filter'] = trim($text_filter.' '.$add_text_filter);
    }
    header("HX-Trigger: updated_tags");
    exit;
}

// remove a search keyword
if(isset($_POST['rmkey'])) {
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $keywords = explode(' ', $_SESSION['tags_text_filter'] ?? '');
    $keywords = array_filter($keywords, static fn($keyword) => $keyword != '' && $keyword != $remove_keyword);
    $_SESSION['tags_text_filter'] = implode(' ', $keywords);
    header("HX-Trigger: updated_tags");
    exit;
}

// sort by name or usage
if(isset($_POST['tags_sort'])) {
    $_SESSION['tags_sort'] = ($_POST['tags_sort'] == 'usage') ? 'usage' : 'name';
    header("HX-Trigger: updated_tags");
    exit;
}

// show only unused tags or all
if(isset($_POST['tags_unused_only'])) {
    $_SESSION['tags_unused_only'] = (($_SESSION['tags_unused_only'] ?? '') == 'true') ? '' : 'true';
    header("HX-Trigger: updated_tags");
    exit;
}

if(isset($_POST['delete'])) {
    $delete_id = (int) $_POST['delete'];
    se_delete_tag($delete_id);
    show_toast($lang['msg_success_entry_delete'],'success');
    record_log($_SESSION['user_nick'],"delete tag id: $delete_id",8);
    header("HX-Trigger: updated_tags");
    exit;
}

// delete all tags without content
if(isset($_POST['delete_unused'])) {
    $deleted = [];
    foreach(se_get_tags_with_usage() as $tag) {
        if($tag['tag_usage'] == 0) {
            se_delete_tag((int) $tag['tag_id']);
            $deleted[] = $tag['tag_name'];
        }
    }
    if(count($deleted) > 0) {
        // the names are stored html-encoded, show_toast() prints the text as it is
        show_toast($lang['tags_msg_deleted_unused'].' '.implode(', ', $deleted),'success');
        record_log($_SESSION['user_nick'],"delete unused tags: ".implode(', ', $deleted),8);
    } else {
        show_toast($lang['msg_info_no_entries_so_far'],'info');
    }
    header("HX-Trigger: updated_tags");
    exit;
}

if(isset($_POST['rename_tag'])) {
    $tag_id = (int) $_POST['rename_tag'];

    $tag_name = sanitizeUserInputs($_POST['tag_name']);
    $tag_name_clean = clean_filename($tag_name);

    if($tag_name !== '' && $tag_name_clean !== '') {

        // does the new name collide with a DIFFERENT existing tag?
        // (e.g. renaming "Flyers" to "Flyer" when "Flyer" already exists)
        // if so, merge into it instead of creating a second row with the
        // same tag_name_clean
        $collision = $db_content->get("se_tags", ["tag_id"], [
            "tag_name_clean" => $tag_name_clean,
            "tag_id[!]" => $tag_id
        ]);

        if (!empty($collision)) {
            se_merge_tag($tag_id, (int) $collision['tag_id']);
            show_toast($lang['msg_success_db_changed'],'success');
        } else {
            $db_content->update("se_tags", [
                "tag_name" => $tag_name,
                "tag_name_clean" => $tag_name_clean
            ], [
                "tag_id" => $tag_id
            ]);
            se_updateTagsCache();
            show_toast($lang['msg_success_db_changed'],'success');
        }
    }

    header("HX-Trigger: updated_tags");
    exit;
}
