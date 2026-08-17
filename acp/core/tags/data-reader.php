<?php

/**
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 */

if($_REQUEST['action'] == 'list') {

    $all_tags = se_get_tags_with_usage();

    $hx_vals = [
        "csrf_token" => $_SESSION['token']
    ];

    if(empty($all_tags)) {
        echo '<p class="text-muted">'.$lang['msg_info_no_entries_so_far'].'</p>';
        exit;
    }

    echo '<table class="table">';

    foreach ($all_tags as $tag) {

        echo '<tr id="id_tag_'.$tag['tag_id'].'">';

        echo '<td>';
        echo '<form class="d-flex align-items-center gap-2 m-0"
                    hx-post="/admin-xhr/tags/write/"
                    hx-swap="none"
                    hx-vals=\''.json_encode($hx_vals).'\'>';
        echo '<input type="hidden" name="rename_tag" value="'.$tag['tag_id'].'">';
        echo '<input type="text" name="tag_name" value="'.htmlspecialchars($tag['tag_name'], ENT_QUOTES).'" class="form-control form-control-sm" style="max-width:260px">';
        echo '<button type="submit" class="btn btn-sm btn-default text-success">'.$icon['check_lg'].'</button>';
        echo '</form>';
        echo '</td>';

        echo '<td class="text-muted small align-middle">'.$tag['tag_usage'].' &times;</td>';

        echo '<td class="text-end text-nowrap align-middle">';
        echo '<button name="delete" value="'.$tag['tag_id'].'" class="btn btn-sm btn-default text-danger"
                    hx-post="/admin-xhr/tags/write/"
                    hx-confirm="'.$lang['msg_confirm_delete'].'"
                    hx-swap="none"
                    hx-vals=\''.json_encode($hx_vals).'\'
                    >'.$icon['trash_alt'].'</button>';
        echo '</td>';

        echo '</tr>';

    }

    echo '</table>';
    exit;
}
