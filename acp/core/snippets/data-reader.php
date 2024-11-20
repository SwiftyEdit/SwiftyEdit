<?php

$writer_uri = '/admin/snippets/edit/';
$duplicate_uri = '/admin/snippets/duplicate/';

// show list of keywords (search)
if($_REQUEST['action'] == 'list_keywords') {
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
    $order_key = $_SESSION['sorting_snippets'] ?? $order_by;
    $order_direction = $_SESSION['sorting_snippet_direction'] ?? $order_direction;

    $db_where = [
        "AND" => [
            "OR" => [
            "snippet_title[~]" => "%$match_str%",
            "snippet_name[~]" => "%$match_str%",
            "snippet_content[~]" => "%$match_str%",
            "snippet_keywords[~]" => "%$match_str%"
                ],
                "snippet_id[>]" => 0,

        ]];

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

    echo '<nav aria-label="Pagination">';
    echo '<ul class="pagination pagination-sm">';
    for($i=0;$i<$nbr_pages;$i++) {
        $active = '';
        if($i == $_SESSION['pagination_snippets_page']) {
            $active = 'active';
        }
        echo '<li class="page-item"><button class="page-link '.$active.'" hx-post="/admin/snippets/write/" hx-include="[name=\'csrf_token\']" name="pagination" value="'.$i.'" hx-swap="none">'.($i+1).'</button></li>';
    }
    echo '</ul>';
    echo '</nav>';

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
        echo '<td>'.$snippet['snippet_lastedit'].'</td>';
        echo '<td>'.$edit_button.' '.$duplicate_button.'</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '</div>';
}