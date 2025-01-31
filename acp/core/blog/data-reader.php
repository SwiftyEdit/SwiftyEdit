<?php

$se_labels = se_get_labels();
$se_categories = se_get_categories();

// search
if($_REQUEST['action'] == 'list_active_searches') {
    if(isset($_SESSION['posts_text_filter']) AND $_SESSION['posts_text_filter'] != "") {
        unset($all_filter);
        $all_filter = explode(" ", $_SESSION['posts_text_filter']);

        foreach($all_filter as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin/blog/write/" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if(isset($btn_remove_keyword)) {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
}

// list categories in sidebar
if($_REQUEST['action'] == 'list_categories') {

    $get_categories = se_get_categories();
    echo '<div class="list-group list-group-flush">';
    foreach($get_categories as $c) {

        $cat_lang_thumb = '<img src="'.return_language_flag_src($c['cat_lang']).'" width="15" alt="'.$c['cat_lang'].'">';
        $active = '';
        if(str_contains($_SESSION['filter_posts_categories'],$c['cat_hash'])) {
            $active = 'active';
        }

        echo '<button 
                hx-post="/admin/blog/write/"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="set_filter_cat"
                value="'.$c['cat_hash'].'"
                class="list-group-item list-group-item-action '.$active.'">';
        echo ''.$c['cat_name'].'';
        echo '<span class="float-end">'.$cat_lang_thumb.'</span>';
        echo '</button>';
    }
}

// list post types in sidebar
if($_REQUEST['action'] == 'list_post_types') {

    $post_types = [
        'm' => $lang['post_type_message'],
        'i' => $lang['post_type_image'],
        'g' => $lang['post_type_gallery'],
        'v' => $lang['post_type_video'],
        'l' => $lang['post_type_link'],
        'f' => $lang['post_type_file']
    ];

    echo '<div class="list-group list-group-flush">';
    foreach($post_types as $k => $v) {

        $active = '';
        if(str_contains($_SESSION['filter_posts_types'],$k)) {
            $active = 'active';
        }

        echo '<button 
                hx-post="/admin/blog/write/"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="set_filter_post_types"
                value="'.$k.'"
                class="list-group-item list-group-item-action '.$active.'">'.$v.'</button>';
    }
    echo '</div>';

}



if($_REQUEST['action'] == 'list_posts') {


    // defaults
    $order_by = 'post_id';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_posts_page'] ?? 0;
    $nbr_show_items = 10;

    $match_str = $_SESSION['posts_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_posts'] ?? $order_by;
    $order_direction = $_SESSION['sorting_posts_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "post_type" => ['m','i','g','v','l','f']
        ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "post_title[~]" => "%$f%",
                    "post_teaser[~]" => "%$f%",
                    "post_text[~]" => "%$f%"
                ]
            ];
        }
    }

    $filter_by_category = array();
    if($_SESSION['filter_posts_categories'] != '') {
        $cat_filter = explode(" ",$_SESSION['filter_posts_categories']);
        $cat_filter = array_filter($cat_filter);
        $filter_by_category = [
            "post_categories[~]" => $cat_filter
        ];
    }

    $filter_by_type = array();
    if($_SESSION['filter_posts_types'] != '') {
        $type_filter = explode(",",$_SESSION['filter_posts_types']);
        $type_filter = array_filter($type_filter);
        $filter_by_type = [
            "post_type[~]" => $type_filter
        ];
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_category+$filter_by_type
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $posts_data_cnt = $db_posts->count("se_posts", $db_where);

    $posts_data = $db_posts->select("se_posts","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($posts_data_cnt/$nbr_show_items);

    echo '<div class="card p-3">';
    echo se_print_pagination('/admin/blog/write/',$nbr_pages,$_SESSION['pagination_posts_page']);

    echo '<table class="table">';
    echo '<tr>';
    echo '<td>#</td>';
    echo '<td>'.$icon['star'].'</td>';
    echo '<td>'.$lang['type'].'</td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '</tr>';
    foreach($posts_data as $post) {

        $type_class = 'label-type label-'.$post['post_type'];
        $icon_fixed = '';
        $draft_class = '';

        $icon_fixed_form = '<form hx-post="/admin/blog/write/">';
        if($post['post_fixed'] == '1') {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="rfixed" value="'.$post['post_id'].'">'.$icon['star'].'</button>';
        } else {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="sfixed" value="'.$post['post_id'].'">'.$icon['star_outline'].'</button>';
        }
        $icon_fixed_form .= $hidden_csrf_token;
        $icon_fixed_form .= '</form>';

        if($post['post_status'] == '2') {
            $draft_class = 'item_is_draft';
        }

        $post_lang_thumb = '<img src="'.return_language_flag_src($post['post_lang']).'" width="15" title="'.$post['post_lang'].'" alt="'.$post['post_lang'].'">';

        $trimmed_teaser = se_return_first_chars($post['post_teaser'],100);

        $post_image = explode("<->", $post['post_images']);
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

        $get_labels = explode(',',$post['post_labels']);
        $label = '';
        if($post['post_labels'] != '') {
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

        $get_post_categories = explode('<->',$post['post_categories']);
        $categories = '';
        if($post['post_categories'] != '') {
            foreach($get_post_categories as $cats) {
                foreach($se_categories as $cat) {
                    if($cats == $cat['cat_hash']) {
                        $cat_title = $cat['cat_name'];
                        $cat_description = $cat['cat_description'];
                    }
                }
                $categories .= '<span class="text-muted small" title="'.$cat_description.'">'.$icon['tags'].' '.$cat_title.'</span> ';
            }
        }

        $prio_form  = '<form hx-post="/admin/blog/write/" hx-swap="beforeend" hx-target="body">';
        $prio_form .= '<input type="number" name="post_priority" value="'.$post['post_priority'].'" class="form-control" style="max-width:100px">';
        $prio_form .= '<input type="hidden" name="prio_id" value="'.$post['post_id'].'">';
        $prio_form .= $hidden_csrf_token;
        $prio_form .= '</form>';

        $published_date = '<span title="'.$lang['label_data_submited'].'">'.$icon['save'].': '.se_format_datetime($post['post_date']).'</span>';
        $release_date = '<span title="'.$lang['label_data_releasedate'].'">'.$icon['calendar_check'].': '.se_format_datetime($post['post_releasedate']).'</span>';
        $lastedit_date = '';
        if($post['post_lastedit'] != '') {
            $lastedit_date = '<span title="'.$lang['label_data_lastedit'].'">'.$icon['edit'].': '.se_format_datetime($post['post_lastedit']).'</span>';
        }

        $show_items_dates = '<span class="text-muted small">'.$published_date.' | '.$lastedit_date.' | '.$release_date.'</span>';

        $show_items_downloads = '';
        if($post['post_type'] == 'f') {
            $download_counter = (int) $post['post_file_attachment_hits'];
            $show_items_downloads = '<div class="float-end small well well-sm">';
            $show_items_downloads .= $icon['download'].' '.$download_counter;
            $show_items_downloads .= '</div>';
        }

        $show_items_redirects = '';
        if($post['post_type'] == 'l') {
            $redirects_counter = (int) $post['post_link_hits'];
            $show_items_redirects = '<div class="float-end small well well-sm">';
            $show_items_redirects .= $icon['link'].' '.$redirects_counter;
            $show_items_redirects .= '</div>';
        }

        if($post['post_type'] == 'm') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_message'].'</span>';
        } else if($post['post_type'] == 'e') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_event'].'</span>';
        } else if($post['post_type'] == 'i') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_image'].'</span>';
        } else if($post['post_type'] == 'g') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_gallery'].'</span>';
        } else if($post['post_type'] == 'v') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_video'].'</span>';
        } else if($post['post_type'] == 'l') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_link'].'</span>';
        } else if($post['post_type'] == 'f') {
            $show_type = '<span class="'.$type_class.'">'.$lang['post_type_file'].'</span>';
        }

        $delete_btn = '<button name="delete_post" value="'.$post['post_id'].'" class="btn btn-sm btn-default text-danger" 
                            hx-post="/admin/blog/write/"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-swap="beforeend"
                            hx-target="body"
                            >'.$icon['trash_alt'].'</button>';

        echo '<tr class="'.$draft_class.'">';
        echo '<td>'.$post['post_id'].'</td>';
        echo '<td>'.$icon_fixed_form.'</td>';
        echo '<td>'.$prio_form.'</td>';
        echo '<td>'.$show_type.'</td>';
        echo '<td>'.$show_thumb.'</td>';
        echo '<td>'.$show_items_downloads.$show_items_redirects.'<h5 class="mb-0">'.$post_lang_thumb.' '.$post['post_title'].'</h5><small>'.$trimmed_teaser.'</small><br>'.$show_items_dates.'<br>'.$categories.'<br>'.$label.'</td>';
        echo '<td style="min-width: 150px;">';
        echo '<nav class="nav justify-content-end">';
        echo '<form class="form-inline px-1" action="/admin/blog/edit/" method="post">';
        echo '<button class="btn btn-default btn-sm text-success" type="submit" name="post_id" value="'.$post['post_id'].'">'.$icon['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form> ';
        echo $delete_btn;
        echo '</nav>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '</div>';



}

if($_REQUEST['action'] == 'show_post_form') {
    include 'blog-edit-form.php';
}