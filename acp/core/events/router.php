<?php

$subinc = match (true) {
    str_starts_with($query, 'events/new/') => 'events-edit',
    str_starts_with($query, 'events/edit/') => 'events-edit',
    str_starts_with($query, 'events/duplicate/') => 'events-edit',
    str_starts_with($query, 'events/bookings/') => 'bookings',
    str_starts_with($query, 'events') => 'events-list',
    default => 'events-list'
};

if($_SESSION['drm_can_publish'] != "true"){
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include __DIR__.'/'.$subinc.'.php';
}