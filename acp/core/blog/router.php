<?php

$subinc = match (true) {
str_starts_with($query, 'blog/new/') => 'blog-edit',
str_starts_with($query, 'blog/edit/') => 'blog-edit',
str_starts_with($query, 'blog/duplicate/') => 'blog-edit',
str_starts_with($query, 'blog') => 'blog-list',
default => ''
};

if (!se_hasPermission('drm_can_publish')) {
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include __DIR__.'/'.$subinc.'.php';
}