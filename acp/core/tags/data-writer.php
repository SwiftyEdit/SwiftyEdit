<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_content
 */

if(isset($_POST['delete'])) {
    $delete_id = (int) $_POST['delete'];
    se_delete_tag($delete_id);
    record_log($_SESSION['user_nick'],"delete tag id: $delete_id","8");
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
