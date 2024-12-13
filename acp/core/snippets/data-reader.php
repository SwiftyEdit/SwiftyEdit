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

    // defaults
    $order_by = 'snippet_lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_snippets_page'] ?? 0;
    $nbr_show_items = 10;

    $match_str = $_SESSION['snippets_text_filter'] ?? '';
    $keyword_str = $_SESSION['snippets_keyword_filter'] ?? '';
    $order_key = $_SESSION['sorting_snippets'] ?? $order_by;
    $order_direction = $_SESSION['sorting_snippet_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
      "AND" => [
          "snippet_id[>]" => 0
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

    echo se_print_pagination('/admin/snippets/write/',$nbr_pages,$_SESSION['pagination_snippets_page']);

    echo '<table class="table table-sm table-striped table-hover">';

    foreach($snippets_data as $snippet) {

        $snippet_id = $snippet['snippet_id'];
        $snippet_content = strip_tags($snippet['snippet_content']);

        if(strlen($snippet_content) > 150) {
            $snippet_content = substr($snippet_content, 0, 100) . ' <small><i>(...)</i></small>';
        }

        $edit_button  = '<form action="'.$writer_uri.'" method="post" class="d-inline">';
        $edit_button .= '<button class="btn btn-default" name="snippet_id" value="'.$snippet_id.'">'.$icon['edit'].'</button>';
        $edit_button .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $edit_button .=  '</form>';

        $duplicate_button  = '<form action="'.$duplicate_uri.'" method="post" class="d-inline">';
        $duplicate_button .= '<button class="btn btn-default" name="duplicate_id" value="'.$snippet_id.'">'.$icon['copy'].'</button>';
        $duplicate_button .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $duplicate_button .=  '</form>';

        echo '<tr>';
        echo '<td>'.$snippet['snippet_lang'].'</td>';
        echo '<td><kbd>'.$snippet['snippet_name'].'</kbd></td>';
        echo '<td>'.$snippet['snippet_title'].'<br><small>'.$snippet_content.'</small></td>';
        echo '<td>'.se_format_datetime($snippet['snippet_lastedit']).'</td>';
        echo '<td>'.$edit_button.' '.$duplicate_button.'</td>';
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