<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['code'].' Shortcodes';
echo '<a href="/admin/shortcodes/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin/shortcodes/read/';
$writer_uri = '/admin/shortcodes/write/';