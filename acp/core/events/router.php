<?php

$subinc = match (true) {
    str_starts_with($query, 'events/new/') => 'events-edit',
    str_starts_with($query, 'events/edit/') => 'events-edit',
    str_starts_with($query, 'events/duplicate/') => 'events-edit',
    str_starts_with($query, 'events/bookings/') => 'bookings',
    str_starts_with($query, 'events') => 'events-list',
    default => 'events-list'
};

include $subinc.'.php';