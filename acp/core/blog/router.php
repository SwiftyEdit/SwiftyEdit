<?php

$subinc = match (true) {
str_starts_with($query, 'blog/new/') => 'blog-edit',
str_starts_with($query, 'blog/edit/') => 'blog-edit',
str_starts_with($query, 'blog/duplicate/') => 'blog-edit',
str_starts_with($query, 'blog') => 'blog-list',
default => ''
};

if($_SESSION['drm_can_publish'] != "true"){
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include __DIR__.'/'.$subinc.'.php';
}