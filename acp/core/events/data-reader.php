<?php

$writer_uri = '/admin/events/edit/';
$duplicate_uri = '/admin/events/duplicate/';

if($_REQUEST['action'] == 'list_active_searches') {
    if(isset($_SESSION['events_text_filter']) AND $_SESSION['events_text_filter'] != "") {
        unset($all_filter);
        $all_filter = explode(" ", $_SESSION['events_text_filter']);

        foreach($all_filter as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin/events/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if(isset($btn_remove_keyword)) {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
}


// list the snippets
if($_REQUEST['action'] == 'list_events') {

    // defaults
    $order_by = 'event_startdate';
    $order_direction = 'ASC';
    $limit_start = $_SESSION['pagination_events_page'] ?? 0;
    $nbr_show_items = 10;

    $match_str = $_SESSION['events_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_events'] ?? $order_by;
    $order_direction = $_SESSION['sorting_events_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }


    // show or hide past events
    if($_SESSION['show_past_events'] == 'true') {
        $filter_base = [
            "AND" => [
                "id[>]" => 0
            ]
        ];
    } else {
        $filter_base = [
            "AND" => [
                "event_startdate[>]" => time()
            ]
        ];
    }

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "title[~]" => "%$f%",
                    "teaser[~]" => "%$f%",
                    "text[~]" => "%$f%"
                ]
            ];
        }
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];


    $events_data_cnt = $db_posts->count("se_events", $db_where);

    $events_data = $db_posts->select("se_events","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($events_data_cnt/$nbr_show_items);

    echo se_print_pagination('/admin-xhr/events/write/',$nbr_pages,$_SESSION['pagination_events_page']);

    echo '<table class="table table-sm table-hover">';
    echo '<thead><tr>';
    echo '<th>#</th>';
    echo '<th class="text-center">' . $icon['star'] . '</th>';
    echo '<th>' . $lang['label_priority'] . '</th>';
    echo '<th nowrap>' . $lang['label_date'] . '</th>';
    echo '<th>' . $lang['label_title'] . '</th>';
    echo '<th></th>';
    echo '<th></th>';
    echo '</tr></thead>';


    foreach($events_data as $post) {
       // print_r($post);

        $icon_fixed = '';
        $draft_class = '';
        $event_lang_thumb = '<img src="'.return_language_flag_src($post['event_lang']).'" width="15" title="'.$post['event_lang'].'" alt="'.$post['event_lang'].'">';

        $icon_fixed_form = '<form hx-post="/admin/events/write/">';
        if($post['fixed'] == '1') {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="rfixed" value="'.$post['id'].'">'.$icon['star'].'</button>';
        } else {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="sfixed" value="'.$post['id'].'">'.$icon['star_outline'].'</button>';
        }
        $icon_fixed_form .= $hidden_csrf_token;
        $icon_fixed_form .= '</form>';

        if($post['status'] == '2') {
            $draft_class = 'item_is_draft';
        }

        $trimmed_teaser = se_return_first_chars($post['teaser'],100);

        $post_image = explode("<->", $post['images']);
        $show_thumb = '';
        if($post_image[1] != "") {
            $image_src = $post_image[1];
            $image_src = str_replace('../content/','/',$image_src);
            $image_src = str_replace('../images/','/images/',$image_src);
            $show_thumb  = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\''.$image_src.'\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url('.$image_src.');">';
            $show_thumb .= '</div>';
        } else {
            $show_thumb = '<div class="show-thumb" style="background-image: url(images/no-image.png);">';
        }

        // labels
        $get_labels = explode(',',$post['labels']);
        $label = '';
        if($post['labels'] != '') {
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

        // categories
        $get_post_categories = explode('<->',$post['categories']);
        $categories = '';
        if($post['categories'] != '') {
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

        $prio_form  = '<form hx-post="/admin/events/write/" hx-swap="beforeend" hx-target="body">';
        $prio_form .= '<input type="number" name="priority" value="'.$post['priority'].'" class="form-control" style="max-width:100px">';
        $prio_form .= '<input type="hidden" name="prio_id" value="'.$post['id'].'">';
        $prio_form .= $hidden_csrf_token;
        $prio_form .= '</form>';

        $published_date = '<span title="'.$lang['label_data_submited'].'">'.$icon['save'].': '.se_format_datetime($post['date']).'</span>';
        $release_date = '<span title="'.$lang['label_data_releasedate'].'">'.$icon['calendar_check'].': '.se_format_datetime($post['releasedate']).'</span>';
        $lastedit_date = '';
        if($post['lastedit'] != '') {
            $lastedit_date = '<span title="'.$lang['label_data_lastedit'].'">'.$icon['edit'].': '.se_format_datetime($post['lastedit']).'</span>';
        }

        $show_items_dates = '<span class="text-muted small">'.$published_date.' | '.$lastedit_date.' | '.$release_date.'</span>';

        $show_events_date = '';
        $show_events_date = '<div class="card p-1">';
        $show_events_date .= '<span>'.$icon['calendar_event'] .' '.se_format_datetime($post['event_startdate']).'</span>';
        if($post['event_startdate'] != $post['event_enddate']) {
            $show_events_date .= '<span>' . $icon['arrow_right'] . ' ' . se_format_datetime($post['event_enddate']) . '</span>';
        }
        $show_events_date .= '</div>';

        $delete_btn = '<button name="delete_event" value="'.$post['id'].'" class="btn btn-sm btn-default text-danger" 
                            hx-post="/admin/events/write/"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-swap="beforeend"
                            hx-target="body"
                            >'.$icon['trash_alt'].'</button>';


        echo '<tr class="'.$draft_class.'">';
        echo '<td>'.$post['id'].'</td>';
        echo '<td>'.$icon_fixed_form.'</td>';
        echo '<td>'.$prio_form.'</td>';
        echo '<td nowrap><small>'.$show_events_date.'</small></td>';
        echo '<td><h5 class="mb-0">'.$event_lang_thumb.' '.$post['title'].'</h5><small>'.$trimmed_teaser.'</small><br>'.$show_items_dates.'<br>'.$categories.'<br>'.$label.'</td>';
        echo '<td>'.$show_thumb.'</td>';
        echo '<td style="min-width: 150px;">';
        echo '<nav class="nav justify-content-end">';
        echo '<form class="form-inline px-1" action="/admin/events/edit/" method="post">';
        echo '<button class="btn btn-default btn-sm text-success" type="submit" name="id" value="'.$post['id'].'">'.$icon['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form> ';
        echo $delete_btn;
        echo '</nav>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';

}