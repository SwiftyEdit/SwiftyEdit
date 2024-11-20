<?php

function show_sort_arrow() {
    global $icon,$lang;
    if($_SESSION['sort_direction'] == 'ASC') {
        $ic = '<span title="'.$lang['ascending'].'"><i class="bi bi-caret-up-fill"></i></span>';
    } else {
        $ic = '<span title="'.$lang['descending'].'"><i class="bi bi-caret-down-fill"></i></span>';
    }
    return $ic;
}