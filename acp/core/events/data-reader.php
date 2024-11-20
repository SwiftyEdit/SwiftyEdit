<?php

$writer_uri = '/admin/events/edit/';
$duplicate_uri = '/admin/events/duplicate/';

// list the snippets
if($_REQUEST['action'] == 'list_events') {
    echo 'My Events';
}