<?php

$subinc = match (true) {
    str_starts_with($query, 'settings/labels/') => 'labels',
    str_starts_with($query, 'settings/posts/') => 'posts',
    str_starts_with($query, 'settings/events/') => 'events',
    str_starts_with($query, 'settings/shop/') => 'shop',
    str_starts_with($query, 'settings/database/') => 'database',
    default => 'general'
};

if($_SESSION['acp_system'] != "allowed"){
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include __DIR__.'/'.$subinc.'.php';
}