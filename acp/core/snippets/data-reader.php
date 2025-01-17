<?php

$writer_uri = '/admin/snippets/edit/';
$duplicate_uri = '/admin/snippets/duplicate/';

// show list of keywords (search)
if($_REQUEST['action'] == 'list_active_searches') {
    if(isset($_SESSION['snippets_text_filter']) AND $_SESSION['snippets_text_filter'] != "") {
        unset($all_filter);
        $all_filter = explode(" ", $_SESSION['snippets_text_filter']);

        foreach($all_filter as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin/snippets/write/" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if(isset($btn_remove_keyword)) {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
}

// list the snippets
if($_REQUEST['action'] == 'list_snippets') {

    $se_labels = se_get_labels();

    // defaults
    $order_by = 'snippet_lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_snippets_page'] ?? 0;
    $nbr_show_items = (int) ($_SESSION['snippets_per_page'] ?? 10);

    $match_str = $_SESSION['snippets_text_filter'] ?? '';
    $keyword_str = $_SESSION['snippets_keyword_filter'] ?? '';
    $order_key = $_SESSION['sorting_snippets'] ?? $order_by;
    $order_direction = $_SESSION['sorting_snippet_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
      "AND" => [
          "snippet_type" => ['snippet','snippet_core']
      ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "snippet_title[~]" => "%$f%",
                    "snippet_name[~]" => "%$f%",
                    "snippet_content[~]" => "%$f%",
                    "snippet_keywords[~]" => "%$f%"
                ]
            ];
        }

    }

    $filter_by_keyword = array();
    if($keyword_str != '') {
        $this_filter = explode(" ",$keyword_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_keyword = [
                    "snippet_keywords[~]" => "%$f%"
            ];
        }

    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_keyword
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $snippet_data_cnt = $db_content->count("se_snippets", $db_where);


    $snippets_data = $db_content->select("se_snippets","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($snippet_data_cnt/$nbr_show_items);

    echo '<div class="card p-3">';

    echo '<div class="d-flex justify-content-end">';
    echo '<div>';
    echo se_print_pagination('/admin/snippets/write/',$nbr_pages,$_SESSION['pagination_snippets_page']);
    echo '</div>';
    echo '<div class="ps-3">';
    echo '<input type="number" class="form-control" hx-post="/admin/snippets/write/" hx-swap="none" hx-trigger="keyup delay:500ms changed" name="items_per_page" min="5" max="99" value="'.$nbr_show_items.'">';
    echo '</div>';
    echo '</div>';
    echo '<table class="table table-sm table-striped table-hover">';

    foreach($snippets_data as $snippet) {

        $snippet_id = $snippet['snippet_id'];
        $snippet_content = strip_tags($snippet['snippet_content']);
        $snippet_title = strip_tags($snippet['snippet_title']);
        if($snippet_title == '') {
            $snippet_title = '<em class="opacity-50">'.$lang['msg_error_no_title'].'</em>';
        }

        $snippet_url = $snippet['snippet_permalink'];
        $snippet_url_title = $snippet['snippet_permalink_title'];
        $snippet_url_name = $snippet['snippet_permalink_name'];
        $snippet_url_classes = $snippet['snippet_permalink_classes'];

        // labels
        $get_snippet_labels = explode(',',$snippet['snippet_labels']);
        $label = '';
        if($snippet['snippet_labels'] != '') {
            foreach($get_snippet_labels as $snippet_label) {

                foreach($se_labels as $l) {
                    if($snippet_label == $l['label_id']) {
                        $label_color = $l['label_color'];
                        $label_title = $l['label_title'];
                    }
                }

                $label .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
            }
        }

        // classes
        $snippet_classes = explode(' ',$snippet['snippet_classes']);
        $class_badge = '';
        foreach($snippet_classes as $class) {
            $class_badge .= '<span class="badge badge-secondary">'.$class.'</span> ';
        }

        if(strlen($snippet_content) > 150) {
            $snippet_content = substr($snippet_content, 0, 100) . ' <small><i>(...)</i></small>';
        }

        // images
        $snippet_images = explode('<->',$snippet['snippet_images']);

        $edit_button  = '<form action="'.$writer_uri.'" method="post" class="d-inline">';
        $edit_button .= '<button class="btn btn-sm btn-default text-success" name="snippet_id" value="'.$snippet_id.'">'.$icon['edit'].'</button>';
        $edit_button .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $edit_button .=  '</form>';

        $duplicate_button  = '<form action="'.$duplicate_uri.'" method="post" class="d-inline">';
        $duplicate_button .= '<button class="btn btn-sm btn-default" name="duplicate_id" value="'.$snippet_id.'">'.$icon['copy'].'</button>';
        $duplicate_button .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $duplicate_button .=  '</form>';

        $snippet_lang_thumb = '<img src="/assets/lang/'.$snippet['snippet_lang'].'/flag.png" width="15" title="'.$snippet['snippet_lang'].'" alt="'.$snippet['snippet_lang'].'">';

        echo '<tr>';
        echo '<td>'.$snippet_lang_thumb.'</td>';
        echo '<td><span class="badge text-bg-secondary">'.$snippet['snippet_name'].'</span></td>';
        echo '<td><h6 class="mb-0">'.$snippet_title.'</h6><small>'.$snippet_content.'</small></td>';
        echo '<td class="text-nowrap">'.se_format_datetime($snippet['snippet_lastedit']).'</td>';
        echo '<td>'.$class_badge.'</td>';
        echo '<td>'.$label.'</td>';
        echo '<td>';
        if(count($snippet_images) > 1) {
            $x=0;
            foreach($snippet_images as $img) {
                $img = str_replace('../content/','/',$img);
                if($img != '') {
                    $x++;
                    echo '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-title="'.$img.'" data-bs-content="<img src=\''.$img.'\'>">'.$icon['images'].'</a> ';
                }
                if($x>2) {
                    echo '<small>(...)</small>';
                    break;
                }
            }
        }
        echo '</td>';
        echo '<td>';
        if($snippet_url != '') {
            echo '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" title="'.$snippet_url_title.'" data-bs-content="URL: '.$snippet_url.'<br>Name: '.$snippet_url_name.'<br>'.$lang['label_classes'].': '.$snippet_url_classes.'">'.$icon['link'].'</a>';
        }
        echo '</td>';
        echo '<td class="text-nowrap">'.$edit_button.' '.$duplicate_button.'</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '</div>';
}

if($_GET['action'] == 'list_keywords') {
    $get_keywords = se_get_snippet_keywords();
    echo '<div class="scroll-container">';
    foreach($get_keywords as $k => $v) {
        $k = trim($k);
        if(str_contains($_SESSION['snippets_keyword_filter'],$k)) {
            echo '<button name="remove_keyword" value="'.$k.'" hx-post="/admin/snippets/write/" hx-swap="none" hx-include="[name=\'csrf_token\']" class="btn btn-default active btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        } else {
            echo '<button name="add_keyword" value="'.$k.'" hx-post="/admin/snippets/write/" hx-swap="none" hx-include="[name=\'csrf_token\']" class="btn btn-default btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        }
    }
    echo '</div>';
}